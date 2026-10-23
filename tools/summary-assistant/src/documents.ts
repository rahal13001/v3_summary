import { mkdir, mkdtemp, lstat, realpath, rename, rm, writeFile } from 'node:fs/promises';
import { createWriteStream } from 'node:fs';
import { finished } from 'node:stream/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import ExcelJS from 'exceljs';
import PDFDocument from 'pdfkit';
import PptxGenJS from 'pptxgenjs';
import { Document, Packer, Paragraph, TextRun, HeadingLevel, Table, TableRow, TableCell, WidthType, Footer, PageNumber } from 'docx';
import { z } from 'zod';
import { analyze, type Analysis, type Dataset, type ReportRecord } from './dataset.js';

export const documentInput = z.object({
  title: z.string().trim().min(1).max(200),
  groupBy: z.string().min(1).max(100),
  formats: z.array(z.enum(['xlsx', 'md', 'pdf', 'pptx', 'docx'])).min(1).max(5),
  sections: z.array(z.object({ heading: z.string().min(1).max(200), text: z.string().max(12000),
    sourceIds: z.array(z.number().int().positive()).max(5000).default([]) })).max(50).default([]),
}).strict();
export type DocumentInput = z.input<typeof documentInput>;
type ParsedInput = z.output<typeof documentInput>;

const labels: Record<string, string> = { indicators: 'IKU', teams: 'Tim', work_units: 'Unit kerja', involvements: 'Keterlibatan',
  author: 'Penulis', month: 'Bulan', quarter: 'Triwulan', year: 'Tahun', what: 'Nama kegiatan', why: 'Latar belakang',
  when: 'Tanggal mulai', tanggal_selesai: 'Tanggal selesai', where: 'Lokasi', who: 'Pihak terlibat', how: 'Hasil kegiatan',
  penyelenggara: 'Penyelenggara', total_peserta: 'Jumlah peserta', total_wanita: 'Jumlah peserta perempuan', no_st: 'Nomor surat tugas', kode: 'Kode',
  indicator_id: 'ID IKU', team_id: 'ID tim', work_unit_id: 'ID unit kerja', involvement_id: 'ID keterlibatan' };

function text(value: unknown): string {
  if (value === null || value === undefined) return '';
  return typeof value === 'object' ? JSON.stringify(value) : String(value);
}

function details(dataset: Dataset, result: Analysis): string[] {
  const operators: Record<string, string> = { eq: 'sama dengan', in: 'salah satu', contains: 'memuat', gte: 'minimal', lte: 'maksimal', is_null: 'kosong' };
  const filters = Object.entries(dataset.query).filter(([key]) => key === 'q' || key.startsWith('filter[')).map(([key, value]) => {
    const match = /^filter\[([^\]]+)\]\[([^\]]+)\]$/.exec(key);
    return key === 'q' ? `Pencarian: ${value}` : `${labels[match?.[1] ?? ''] ?? match?.[1]} ${operators[match?.[2] ?? ''] ?? ''}: ${value}`;
  });
  const period = dataset.query.period_start && dataset.query.period_end
    ? `${dataset.query.period_start} sampai ${dataset.query.period_end}` : 'Sesuai rentang data yang diminta';
  return [
    `Status data: ${dataset.status === 'complete' ? 'LENGKAP SESUAI QUERY' : 'PARSIAL'}`,
    `Waktu pengambilan: ${dataset.retrievedAt}`,
    `Periode: ${period}`,
    `Pemilihan kegiatan: ${dataset.query.period_mode === 'start' ? 'tanggal mulai dalam periode' : 'beririsan dengan periode'}. Data dibatasi izin pengguna.`,
    ...(filters.length ? [`Filter tambahan: ${filters.join('; ')}`] : []),
    `Pengelompokan: ${labels[result.groupBy] ?? result.groupBy}`,
    `Laporan unik: ${result.uniqueReports}`,
    ...(result.attendance ? [`Jumlah kehadiran sah: ${result.attendance.total}; nilai sah: ${result.attendance.valid}; tidak valid: ${result.attendance.invalid}; kosong: ${result.attendance.missing}.`] : []),
    ...result.warnings,
    'Narasi merupakan interpretasi AI; rekap dihitung kode lokal. Tidak menyimpulkan pencapaian target IKU tanpa data target.',
  ];
}

function sources(dataset: Dataset): string[] {
  return dataset.records.map(row => `Laporan ${row.id}: ${row.source?.url ?? 'URL sumber tidak tersedia'}; pembaruan: ${row.source?.updated_at ?? 'tidak tersedia'}`);
}

function markdown(dataset: Dataset, result: Analysis, input: ParsedInput): string {
  const escape = (value: unknown) => text(value).replace(/[\\`*_{}\[\]<>#|]/g, '\\$&').replace(/\r?\n/g, ' ');
  const lines = [`# ${escape(input.title)}`, '', ...details(dataset, result).map(item => escape(item)), '', '## Rekap', '',
    '| Kelompok | Jumlah laporan | ID laporan |', '| --- | ---: | --- |',
    ...result.groups.map(group => `| ${escape(group.label)} | ${group.count} | ${group.reportIds.join(', ')} |`)];
  for (const section of input.sections) lines.push('', `## ${escape(section.heading)} — Interpretasi AI`, '', escape(section.text), '', `Sumber: ${section.sourceIds.join(', ') || 'Tidak ada rujukan khusus'}`);
  lines.push('', '## Sumber', '', ...dataset.records.map(row => {
    const link = row.source?.url ? `[Lihat laporan](<${row.source.url}>)` : 'URL sumber tidak tersedia';
    return `- Laporan ${row.id}: ${link}; pembaruan: ${escape(row.source?.updated_at ?? 'tidak tersedia')}`;
  }));
  return `${lines.join('\n')}\n`;
}

async function excel(dataset: Dataset, result: Analysis, filename: string): Promise<void> {
  const workbook = new ExcelJS.Workbook();
  workbook.creator = 'Summary Assistant';
  workbook.created = new Date(dataset.retrievedAt);
  const sheet = (name: string, rows: Record<string, unknown>[]) => {
    const worksheet = workbook.addWorksheet(name);
    const expanded = rows.map(row => {
      const expandedRow: Record<string, string | number> = {};
      for (const [key, value] of Object.entries(row)) {
        if (typeof value === 'number') { expandedRow[key] = value; continue; }
        const string = text(value);
        let offset = 0;
        let part = 0;
        do {
          const end = safeBoundary(string, Math.min(offset + 32000, string.length));
          expandedRow[part === 0 ? key : `${key} [bagian ${part + 1}]`] = string.slice(offset, end);
          offset = end; part++;
        } while (offset < string.length);
      }
      return expandedRow;
    });
    const keys = [...new Set(expanded.flatMap(row => Object.keys(row)))];
    worksheet.columns = keys.map(key => ({ header: key, key, width: key.includes('url') ? 60 : 28 }));
    worksheet.addRows(expanded);
    worksheet.views = [{ state: 'frozen', ySplit: 1 }];
    if (keys.length) worksheet.autoFilter = { from: { row: 1, column: 1 }, to: { row: 1, column: keys.length } };
    worksheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
    worksheet.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF17365D' } };
    worksheet.eachRow(row => { row.alignment = { vertical: 'top', wrapText: true }; });
    return worksheet;
  };
  sheet('Rekap', result.groups.map(group => ({ kelompok: group.label, jumlah_laporan: group.count,
    id_laporan: group.reportIds.join(', '), ...(group.attendance ? { kehadiran_sah: group.attendance.total, angka_tidak_valid: group.attendance.invalid, angka_kosong: group.attendance.missing } : {}) })));
  sheet('Data Kegiatan', dataset.records.map(row => ({ ...row })));
  const mappings: Record<string, unknown>[] = [];
  for (const row of dataset.records) if (Array.isArray(row.indicators)) for (const indicator of row.indicators) {
    if (indicator && typeof indicator === 'object') mappings.push({ report_id: row.id, ...indicator });
  }
  sheet('Relasi IKU', mappings);
  sheet('Sumber', dataset.records.map(row => ({ report_id: row.id, url: row.source?.url, updated_at: row.source?.updated_at })));
  sheet('Keterangan', details(dataset, result).map(value => ({ keterangan: value })));
  await workbook.xlsx.writeFile(filename);
}

async function pdf(dataset: Dataset, result: Analysis, input: ParsedInput, filename: string): Promise<void> {
  const doc = new PDFDocument({ size: 'A4', margin: 45, bufferPages: true, info: { Title: input.title, Author: 'Summary Assistant' } });
  doc.registerFont('Summary', fileURLToPath(new URL('../assets/DejaVuSans.ttf', import.meta.url)));
  doc.font('Summary');
  const stream = createWriteStream(filename, { flags: 'wx' });
  const completion = finished(stream);
  doc.on('error', error => stream.destroy(error));
  doc.pipe(stream);
  doc.fontSize(19).text(input.title).moveDown();
  doc.fontSize(10);
  for (const item of details(dataset, result)) doc.text(item).moveDown(0.3);
  doc.moveDown().fontSize(15).text('Rekap kegiatan').moveDown(0.5).fontSize(10);
  for (const group of result.groups) {
    doc.text(`${group.label}: ${group.count} laporan`).text(`ID laporan: ${group.reportIds.join(', ')}`).moveDown(0.5);
  }
  for (const section of input.sections) {
    doc.moveDown().fontSize(14).text(`${section.heading} — Interpretasi AI`).fontSize(10).moveDown(0.4);
    doc.text(section.text).moveDown(0.3).text(`Sumber: ${section.sourceIds.join(', ') || 'Tidak ada rujukan khusus'}`);
  }
  doc.addPage().fontSize(15).text('Sumber laporan').moveDown().fontSize(9);
  for (const source of sources(dataset)) doc.text(source).moveDown(0.4);
  const pages = doc.bufferedPageRange();
  for (let index = pages.start; index < pages.start + pages.count; index++) {
    doc.switchToPage(index);
    doc.fontSize(8).text(`${index + 1} / ${pages.count}`, 45, doc.page.height - 30, { lineBreak: false });
  }
  doc.end();
  await completion;
}

function safeBoundary(value: string, end: number): number {
  if (end > 0 && end < value.length && /[\uD800-\uDBFF]/.test(value[end - 1]!) && /[\uDC00-\uDFFF]/.test(value[end]!)) return end - 1;
  return end;
}

function chunkText(value: string, max: number): string[] {
  const chunks: string[] = [];
  const lines = value.split(/\r?\n/).flatMap(line => {
    if (!line) return [''];
    const wrapped: string[] = [];
    let remaining = line;
    while (remaining.length > 80) {
      const space = remaining.lastIndexOf(' ', 80);
      const at = safeBoundary(remaining, space >= 40 ? space : 80);
      wrapped.push(remaining.slice(0, at)); remaining = remaining.slice(at).trimStart();
    }
    wrapped.push(remaining);
    return wrapped;
  });
  let current: string[] = [];
  for (const line of lines) {
    if (current.length >= 14 || current.join('\n').length + line.length + 1 > max) {
      chunks.push(current.join('\n')); current = [];
    }
    current.push(line);
  }
  if (current.length) chunks.push(current.join('\n'));
  return chunks;
}

async function powerpoint(dataset: Dataset, result: Analysis, input: ParsedInput, filename: string): Promise<void> {
  const presentation = new PptxGenJS();
  presentation.layout = 'LAYOUT_WIDE'; presentation.author = 'Summary Assistant'; presentation.title = input.title;
  presentation.theme = { headFontFace: 'Aptos Display', bodyFontFace: 'Aptos' };
  let number = 0;
  const addSlide = (heading: string, body: string, notes = '') => {
    const title = heading.replace(/\s+/g, ' ');
    for (const [part, content] of chunkText(body, 850).entries()) {
    const slide = presentation.addSlide(); number++;
    slide.background = { color: 'F7F9FC' };
    slide.addShape(presentation.ShapeType.rect, { x: 0, y: 0, w: 13.333, h: 0.16, fill: { color: '17365D' }, line: { color: '17365D' } });
    slide.addText(`${title}${part ? ' (lanjutan)' : ''}`, { x: 0.5, y: 0.4, w: 12.3, h: 0.8, fontSize: title.length > 100 ? 16 : 24, bold: true, color: '17365D', breakLine: false, margin: 0 });
    slide.addText(content, { x: 0.55, y: 1.4, w: 12.2, h: 5.2, fontSize: 17, color: '182230', valign: 'top', margin: 0, breakLine: false });
    slide.addText(`Summary | ${dataset.status === 'partial' ? 'DATA PARSIAL' : 'Sesuai query'} | ${number}`, { x: 0.55, y: 7.0, w: 12.2, h: 0.25, fontSize: 9, color: '5B6575', margin: 0 });
    slide.addNotes(notes || body);
    }
  };
  addSlide(input.title, `Laporan unik: ${result.uniqueReports}\nPengelompokan: ${labels[result.groupBy] ?? result.groupBy}\nWaktu pengambilan: ${dataset.retrievedAt}\nStatus: ${dataset.status === 'partial' ? 'PARSIAL' : 'Lengkap sesuai query'}`);
  addSlide('Ringkasan angka', `Laporan unik: ${result.uniqueReports}\nKelompok: ${result.groups.length}\n\n${result.attendance ? `Kehadiran dilaporkan: ${result.attendance.total}\nNilai sah: ${result.attendance.valid}; kosong: ${result.attendance.missing}; tidak valid: ${result.attendance.invalid}` : 'Kolom jumlah peserta tidak diambil; jumlah kehadiran tidak dihitung.'}\n\nTotal kelompok dapat tumpang tindih. Kehadiran bukan orang unik.`);
  for (const chunk of chunkText(details(dataset, result).join('\n'), 850)) addSlide('Cakupan dan metode', chunk);
  for (let index = 0; index < result.groups.length; index += 7) {
    const groups = result.groups.slice(index, index + 7);
    addSlide('Rekap kegiatan', groups.map(group => `${group.label.length > 110 ? group.label.slice(0, safeBoundary(group.label, 110)) + '…' : group.label}: ${group.count} laporan`).join('\n\n'),
      groups.map(group => `${group.label}: ${group.count}; ID laporan: ${group.reportIds.join(', ')}`).join('\n') + '\nLabel panjang disingkat pada slide; label lengkap tersedia dalam catatan ini.');
  }
  for (const section of input.sections) for (const [index, chunk] of chunkText(section.text, 850).entries()) {
    addSlide(`${section.heading}${index ? ' (lanjutan)' : ''}`, `${chunk}\n\nSumber ID: ${section.sourceIds.slice(0, 20).join(', ')}${section.sourceIds.length > 20 ? ' (selengkapnya di catatan)' : ''}`,
      `Interpretasi AI. Sumber: ${section.sourceIds.join(', ')}\n${section.text}`);
  }
  addSlide('Catatan kualitas data', result.warnings.join('\n\n'));
  addSlide('Tindak lanjut', 'Periksa kegiatan tanpa relasi dan angka yang belum valid.\n\nBedakan interpretasi AI dari data laporan.\n\nPencapaian target IKU memerlukan target dan metode pengukuran yang sah.');
  addSlide('Sumber laporan', `${dataset.records.length} laporan sumber.\n\n${sources(dataset).slice(0, 5).join('\n\n')}\n\nDaftar sumber lengkap tersedia pada catatan pembicara slide ini.`, sources(dataset).join('\n'));
  await presentation.writeFile({ fileName: filename, compression: true });
}

async function word(dataset: Dataset, result: Analysis, input: ParsedInput, filename: string): Promise<void> {
  const paragraph = (value: string) => new Paragraph({ children: [new TextRun(value)], spacing: { after: 140 } });
  const heading = (value: string) => new Paragraph({ text: value, heading: HeadingLevel.HEADING_1 });
  const row = (cells: string[]) => new TableRow({ children: cells.map(value => new TableCell({ children: [paragraph(value)] })) });
  const children: (Paragraph | Table)[] = [new Paragraph({ text: input.title, heading: HeadingLevel.TITLE }),
    ...details(dataset, result).map(paragraph), heading('Rekap'),
    new Table({ width: { size: 100, type: WidthType.PERCENTAGE }, rows: [row(['Kelompok', 'Laporan', 'ID laporan']),
      ...result.groups.map(group => row([group.label, String(group.count), group.reportIds.join(', ')]))] })];
  for (const section of input.sections) children.push(heading(`${section.heading} — Interpretasi AI`), ...section.text.split(/\r?\n/).map(paragraph), paragraph(`Sumber: ${section.sourceIds.join(', ') || 'Tidak ada rujukan khusus'}`));
  children.push(heading('Sumber laporan'), ...sources(dataset).map(paragraph));
  const document = new Document({ creator: 'Summary Assistant', title: input.title,
    styles: { default: { document: { run: { font: 'Calibri', size: 22 } } } },
    sections: [{ properties: { page: { size: { width: 11906, height: 16838 }, margin: { top: 1134, bottom: 1134, left: 1134, right: 1134 } } },
      footers: { default: new Footer({ children: [new Paragraph({ children: [new TextRun({ children: [PageNumber.CURRENT] })] })] }) }, children }] });
  await writeFile(filename, await Packer.toBuffer(document), { flag: 'wx' });
}

export async function createDocuments(dataset: Dataset, request: DocumentInput, outputRoot: string): Promise<{ directory: string; files: string[]; status: string }> {
  const input = documentInput.parse(request);
  const known = new Set(dataset.records.map(row => row.id));
  for (const section of input.sections) if (section.sourceIds.some(id => !known.has(id))) throw new Error('ID sumber narasi tidak terdapat dalam dataset.');
  for (const record of dataset.records) {
    if (record.source?.url) {
      const source = new URL(record.source.url);
      if (source.protocol !== 'https:' || source.username || source.password) throw new Error('URL sumber tidak sah.');
    }
  }
  const result = analyze(dataset, input.groupBy);
  const root = path.resolve(outputRoot);
  await mkdir(root, { recursive: true });
  if ((await lstat(root)).isSymbolicLink() || await realpath(root) !== root) throw new Error('Folder keluaran tidak boleh melalui symlink atau tautan simbolik.');
  const staging = await mkdtemp(path.join(root, '.summary-'));
  const slug = input.title.normalize('NFKD').replace(/[^a-zA-Z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 80).toLowerCase() || 'laporan';
  const directory = path.join(root, `${slug}-${new Date().toISOString().replace(/[:.]/g, '-')}-${path.basename(staging).slice(-6)}`);
  const files: string[] = [];
  try {
    for (const format of [...new Set(input.formats)]) {
      const filename = path.join(staging, `${slug}.${format}`);
      if (format === 'md') await writeFile(filename, markdown(dataset, result, input), { encoding: 'utf8', flag: 'wx' });
      if (format === 'xlsx') await excel(dataset, result, filename);
      if (format === 'pdf') await pdf(dataset, result, input, filename);
      if (format === 'pptx') await powerpoint(dataset, result, input, filename);
      if (format === 'docx') await word(dataset, result, input, filename);
      files.push(path.join(directory, path.basename(filename)));
    }
    await rename(staging, directory);
    return { directory, files, status: dataset.status };
  } catch (error) {
    await rm(staging, { recursive: true, force: true });
    throw error;
  }
}
