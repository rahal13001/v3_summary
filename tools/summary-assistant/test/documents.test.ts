import assert from 'node:assert/strict';
import { test } from 'node:test';
import { mkdtemp, readFile, rm, symlink } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import JSZip from 'jszip';
import ExcelJS from 'exceljs';
import { getDocument } from 'pdfjs-dist/legacy/build/pdf.mjs';
import { createDocuments } from '../src/documents.js';
import type { Dataset } from '../src/dataset.js';

export const fixture: Dataset = {
  id: 'fixture', status: 'complete', bytes: 0, touchedAt: Date.now(), retrievedAt: '2026-10-20T00:00:00Z',
  query: { period_start: '2026-07-01', period_end: '2026-09-30', period_mode: 'overlap' },
  records: [
    { id: 1, slug: 'juli', what: '=HYPERLINK("https://evil.test")', when: '2026-07-01', total_peserta: '10',
      indicators: [{ id: 7, nama_iku: 'IKU Layanan' }], how: 'Pelayanan berjalan baik.',
      source: { url: 'https://summary.test/laporan-5w1h/juli', updated_at: '2026-07-02T00:00:00Z' } },
    { id: 2, slug: 'agustus', what: 'Kegiatan Agustus', when: '2026-08-20', total_peserta: 'tidak tersedia',
      indicators: [{ id: 7, nama_iku: 'IKU Layanan' }, { id: 8, nama_iku: 'IKU Kerja Sama' }],
      source: { url: 'https://summary.test/laporan-5w1h/agustus' } },
  ],
};

test('five formats contain consistent counts, sources, and safe literal cells', async () => {
  const root = await mkdtemp(path.join(tmpdir(), 'summary-documents-'));
  try {
    const output = await createDocuments(fixture, { title: 'Kegiatan IKU Triwulan III 2026', groupBy: 'indicators',
      formats: ['xlsx', 'md', 'pdf', 'pptx', 'docx'], sections: [{ heading: 'Temuan', text: 'Pelaksanaan didukung kegiatan layanan.', sourceIds: [1, 2] }] }, root);
    assert.equal(output.files.length, 5);
    const file = (extension: string) => output.files.find(name => name.endsWith(`.${extension}`))!;
    const markdown = await readFile(file('md'), 'utf8');
    assert.match(markdown, /Laporan unik: 2/);
    assert.match(markdown, /laporan-5w1h\/juli/);
    assert.ok(markdown.includes('[Lihat laporan](<https://summary.test/laporan-5w1h/juli>)'));
    assert.doesNotMatch(markdown, /\/api\/ai\//);
    const workbook = new ExcelJS.Workbook();
    await workbook.xlsx.readFile(file('xlsx'));
    assert.equal(workbook.getWorksheet('Data Kegiatan')?.rowCount, 3);
    const allCells: string[] = [];
    workbook.eachSheet(sheet => sheet.eachRow(row => row.eachCell(cell => allCells.push(String(cell.value)))));
    assert.match(allCells.join(' '), /laporan-5w1h\/juli/);
    assert.doesNotMatch(allCells.join(' '), /\/api\/ai\//);
    const literal = workbook.getWorksheet('Data Kegiatan')?.getRow(2).values;
    assert.ok(JSON.stringify(literal).includes('HYPERLINK'));
    workbook.eachSheet(sheet => sheet.eachRow(row => row.eachCell(cell => assert.notEqual(cell.type, ExcelJS.ValueType.Formula))));
    for (const extension of ['docx', 'pptx']) {
      const zip = await JSZip.loadAsync(await readFile(file(extension)));
      const xmlFiles = Object.keys(zip.files).filter(name => name.endsWith('.xml'));
      const text = (await Promise.all(xmlFiles.map(name => zip.file(name)!.async('string')))).join('\n');
      assert.match(text, /Kegiatan IKU Triwulan III 2026/);
      assert.match(text, /summary\.test/);
      assert.match(text, /laporan-5w1h\/juli/);
      assert.doesNotMatch(text, /\/api\/ai\//);
    }
    const loading = getDocument({ data: new Uint8Array(await readFile(file('pdf'))), useSystemFonts: true });
    const pdf = await loading.promise;
    const text = [];
    for (let page = 1; page <= pdf.numPages; page++) text.push((await (await pdf.getPage(page)).getTextContent()).items.map(item => 'str' in item ? item.str : '').join(' '));
    assert.match(text.join(' '), /Laporan unik: 2/);
    assert.match(text.join(' '), /summary\.test/);
    assert.match(text.join(' '), /laporan-5w1h\/juli/);
    assert.doesNotMatch(text.join(' '), /\/api\/ai\//);
    await loading.destroy();
    const second = await createDocuments(fixture, { title: 'Kegiatan IKU Triwulan III 2026', groupBy: 'indicators', formats: ['md'], sections: [] }, root);
    assert.notEqual(output.directory, second.directory);
  } finally { await rm(root, { recursive: true, force: true }); }
});

test('reject unknown citations, executable paths, and symlink output directories', async () => {
  const root = await mkdtemp(path.join(tmpdir(), 'summary-paths-'));
  try {
    await assert.rejects(createDocuments(fixture, { title: 'Test', groupBy: 'indicators', formats: ['md'],
      sections: [{ heading: 'Bad', text: 'Claim', sourceIds: [999] }] }, root), /sumber|Sumber/);
    const link = path.join(root, 'link');
    await symlink(root, link, 'dir');
    await assert.rejects(createDocuments(fixture, { title: '../../outside', groupBy: 'indicators', formats: ['md'], sections: [] }, link), /symlink|simbolik/);
  } finally { await rm(root, { recursive: true, force: true }); }
});

test('empty and partial datasets remain explicit across all formats and long slide content continues', async () => {
  const root = await mkdtemp(path.join(tmpdir(), 'summary-empty-'));
  try {
    const dataset = { ...fixture, records: [], fields: ['indicators'], status: 'partial' as const, reason: 'Batas kapasitas fixture.' };
    const output = await createDocuments(dataset, { title: 'Laporan kosong', groupBy: 'indicators',
      formats: ['md', 'xlsx', 'pdf', 'docx', 'pptx'], sections: [{ heading: 'Catatan', text: '\n'.repeat(100) + 'Akhir teks panjang', sourceIds: [] }] }, root);
    assert.equal(output.status, 'partial');
    const md = await readFile(output.files.find(file => file.endsWith('.md'))!, 'utf8');
    assert.match(md, /PARSIAL/);
    assert.match(md, /Laporan unik: 0/);
    const zip = await JSZip.loadAsync(await readFile(output.files.find(file => file.endsWith('.pptx'))!));
    const slides = Object.keys(zip.files).filter(file => /^ppt\/slides\/slide\d+\.xml$/.test(file));
    assert.ok(slides.length >= 10, 'Long newline content must be split over continuation slides.');
    const text = (await Promise.all(slides.map(file => zip.file(file)!.async('string')))).join(' ');
    assert.match(text, /Akhir teks panjang/);
    assert.match(text, /PARSIAL/);
  } finally { await rm(root, { recursive: true, force: true }); }
});

test('long Unicode remains intact at Excel cell and PowerPoint line boundaries', async () => {
  const root = await mkdtemp(path.join(tmpdir(), 'summary-unicode-'));
  try {
    const original = 'a'.repeat(31999) + '😀tail';
    const output = await createDocuments({ ...fixture, records: [{ ...fixture.records[0]!, what: original }] }, {
      title: 'Unicode', groupBy: 'indicators', formats: ['xlsx', 'pptx'],
      sections: [{ heading: 'Unicode boundary', text: 'b'.repeat(79) + '😀tail', sourceIds: [1] }],
    }, root);
    const workbook = new ExcelJS.Workbook();
    await workbook.xlsx.readFile(output.files.find(file => file.endsWith('.xlsx'))!);
    const sheet = workbook.getWorksheet('Data Kegiatan')!;
    const headers = sheet.getRow(1).values as string[];
    const cells = headers.map((header, index) => ({ header, index })).filter(({ header }) => header === 'what' || header?.startsWith('what [bagian '));
    assert.equal(cells.map(({ index }) => sheet.getRow(2).getCell(index).value).join(''), original);
    const zip = await JSZip.loadAsync(await readFile(output.files.find(file => file.endsWith('.pptx'))!));
    const slides = Object.keys(zip.files).filter(file => /^ppt\/slides\/slide\d+\.xml$/.test(file));
    const xml = (await Promise.all(slides.map(file => zip.file(file)!.async('string')))).join('');
    assert.ok(xml.includes('😀tail'), 'PowerPoint wrapping must preserve the emoji code point.');
    assert.ok(!xml.includes('�'), 'No replacement characters may be introduced.');
  } finally { await rm(root, { recursive: true, force: true }); }
});
