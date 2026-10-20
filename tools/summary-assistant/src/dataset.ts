import { randomUUID } from 'node:crypto';
import { z } from 'zod';
import { SummaryClient, type QueryParams } from './client.js';

const recordSchema = z.object({ id: z.number().int().positive(), slug: z.string().optional(),
  source: z.object({ url: z.string(), updated_at: z.string().nullable().optional() }).optional() }).passthrough();
export type ReportRecord = z.infer<typeof recordSchema>;
export interface Dataset {
  id: string;
  records: ReportRecord[];
  query: QueryParams;
  status: 'complete' | 'partial';
  reason?: string;
  retrievedAt: string;
  touchedAt: number;
  bytes: number;
  fields?: string[];
}
export interface Attendance { total: number; valid: number; invalid: number; missing: number }
export interface Group { key: string; label: string; count: number; reportIds: number[]; attendance?: Attendance }
export interface Analysis {
  groupBy: string;
  uniqueReports: number;
  groups: Group[];
  attendance?: Attendance;
  warnings: string[];
}

export class DatasetStore {
  private datasets = new Map<string, Dataset>();
  private maxRecords: number;
  private maxBytes: number;
  private now: () => number;
  private preparing = false;

  constructor(options: { maxRecords?: number; maxBytes?: number; now?: () => number } = {}) {
    this.maxRecords = options.maxRecords ?? 5000;
    this.maxBytes = options.maxBytes ?? 50 * 1024 * 1024;
    this.now = options.now ?? Date.now;
    if (!Number.isSafeInteger(this.maxRecords) || this.maxRecords <= 0 || !Number.isSafeInteger(this.maxBytes) || this.maxBytes <= 0) throw new Error('Batas dataset tidak sah.');
  }

  get(id: string): Dataset {
    const data = this.datasets.get(id);
    if (!data || this.now() - data.touchedAt >= 30 * 60_000) {
      this.datasets.delete(id);
      throw new Error('Dataset tidak tersedia atau kedaluwarsa. Ambil ulang dari Summary.');
    }
    data.touchedAt = this.now();
    return data;
  }

  async prepare(client: SummaryClient, query: QueryParams): Promise<{ datasetId: string; count: number; status: string; reason?: string; sample: ReportRecord[]; retrievedAt: string }> {
    if (this.preparing) throw new Error('Persiapan dataset lain sedang berjalan. Tunggu hingga selesai lalu coba lagi.');
    this.preparing = true;
    try { return await this.collect(client, query); }
    finally { this.preparing = false; }
  }

  private reserve(bytes: number): void {
    for (const [id, old] of this.datasets) if (this.now() - old.touchedAt >= 30 * 60_000) this.datasets.delete(id);
    while ([...this.datasets.values()].reduce((sum, old) => sum + old.bytes, bytes) > this.maxBytes) {
      const oldest = this.datasets.keys().next().value;
      if (!oldest) break;
      this.datasets.delete(oldest);
    }
  }

  private async collect(client: SummaryClient, query: QueryParams) {
    const schema = await client.read('schema');
    const definitions = z.record(z.record(z.unknown())).parse(schema.data);
    const records: ReportRecord[] = [];
    const seen = new Set<number>();
    let bytes = 0;
    let status: 'complete' | 'partial' = 'complete';
    let reason: string | undefined;
    let expectedTotal: number | undefined;
    let page = 1;
    let changed = false;
    let nestedPartial = false;
    while (true) {
      const response = await client.read('reports', { ...query, page, per_page: 100 });
      const rows = z.array(recordSchema).parse(response.data);
      if (typeof response.meta?.has_more !== 'boolean' || response.meta.page !== page || response.meta.total === undefined) throw new Error('Pagination Summary tidak sah.');
      if (expectedTotal !== undefined && expectedTotal !== response.meta.total) changed = true;
      expectedTotal = response.meta.total;
      let added = 0;
      for (const raw of rows) {
        if (seen.has(raw.id)) { changed = true; continue; }
        const row = { ...raw, source: { url: client.reportUrl(raw.id), updated_at: raw.source?.updated_at ?? null } };
        if (raw._relation_meta && typeof raw._relation_meta === 'object') {
          nestedPartial ||= Object.values(raw._relation_meta).some(meta => meta && typeof meta === 'object' && 'has_more' in meta && meta.has_more === true);
        }
        const size = Buffer.byteLength(JSON.stringify(row));
        if (records.length >= this.maxRecords || bytes + size > this.maxBytes) {
          status = 'partial'; reason = 'Batas kapasitas dataset tercapai; persempit query atau sesuaikan batas lokal.'; break;
        }
        this.reserve(bytes + size);
        bytes += size; seen.add(row.id); records.push(row); added++;
      }
      if (status === 'partial' || !response.meta.has_more) break;
      if (added === 0) { status = 'partial'; reason = 'Pagination tidak maju; data mungkin berubah.'; break; }
      page++;
    }
    if (changed) { status = 'partial'; reason ??= 'Data berubah selama pengambilan; ulangi sebelum membuat kesimpulan lengkap.'; }
    if (nestedPartial) { status = 'partial'; reason ??= 'Relasi bertingkat belum lengkap. Gunakan pagination evaluasi/pengikut sebelum menyusun analisis menyeluruh.'; }
    if (status === 'complete' && records.length !== expectedTotal) { status = 'partial'; reason = 'Jumlah hasil tidak cocok dengan total sumber; ulangi pengambilan.'; }
    const dataset: Dataset = { id: randomUUID(), records, query: { ...query }, status, reason, bytes,
      retrievedAt: new Date(this.now()).toISOString(), touchedAt: this.now(),
      fields: [...new Set(['id', 'slug',
        ...(query.fields === undefined ? Object.keys(definitions.reports ?? {}) : String(query.fields).split(',').map(field => field.trim()).filter(Boolean)),
        ...String(query.include ?? '').split(',').map(field => field.trim()).filter(Boolean)])] };
    for (const [id, old] of this.datasets) if (this.now() - old.touchedAt >= 30 * 60_000) this.datasets.delete(id);
    while (this.datasets.size >= 4 || [...this.datasets.values()].reduce((sum, old) => sum + old.bytes, bytes) > this.maxBytes) {
      const oldest = this.datasets.keys().next().value;
      if (!oldest) break;
      this.datasets.delete(oldest);
    }
    this.datasets.set(dataset.id, dataset);
    const sample = records.slice(0, 3).map(row => Object.fromEntries(Object.entries(row).map(([key, value]) => [key, typeof value === 'string' ? value.slice(0, 800) : value])) as ReportRecord);
    return { datasetId: dataset.id, count: records.length, status, reason, sample, retrievedAt: dataset.retrievedAt };
  }
}

function attendance(records: ReportRecord[], enabled = false): Attendance | undefined {
  if (!enabled && !records.some(row => Object.hasOwn(row, 'total_peserta'))) return undefined;
  const result = { total: 0, valid: 0, invalid: 0, missing: 0 };
  for (const row of records) {
    const value = row.total_peserta;
    if (value === null || value === undefined || (typeof value === 'string' && value.trim() === '')) result.missing++;
    else if ((typeof value === 'string' || typeof value === 'number') && /^\d+$/.test(String(value).trim()) && Number.isSafeInteger(Number(value)) && Number.isSafeInteger(result.total + Number(value))) {
      result.total += Number(value); result.valid++;
    } else result.invalid++;
  }
  return result;
}

function buckets(row: ReportRecord, groupBy: string): { key: string; label: string }[] {
  if (['month', 'quarter', 'year'].includes(groupBy)) {
    const date = typeof row.when === 'string' ? /^(\d{4})-(\d{2})-(\d{2})$/.exec(row.when) : null;
    const parsed = date ? new Date(`${row.when}T00:00:00Z`) : null;
    if (!date || !parsed || Number.isNaN(parsed.getTime()) || parsed.toISOString().slice(0, 10) !== row.when) return [{ key: 'missing', label: 'Belum terkait' }];
    const label = groupBy === 'year' ? date[1]! : groupBy === 'quarter' ? `${date[1]} Q${Math.ceil(Number(date[2]) / 3)}` : `${date[1]}-${date[2]}`;
    return [{ key: `value:${label}`, label }];
  }
  let value = row[groupBy];
  if (groupBy === 'author') value = (row.people as { author?: unknown } | undefined)?.author;
  const values = (Array.isArray(value) ? value : [value]).filter(item => item !== null && item !== undefined && item !== '');
  if (!values.length) return [{ key: 'missing', label: 'Belum terkait' }];
  return values.map(value => {
    if (typeof value === 'object' && value !== null) {
      const object = value as Record<string, unknown>;
      const label = object.nama_iku ?? object.nama_tim ?? object.name;
      if (label === undefined && object.id === undefined) throw new Error('Kolom identitas kelompok tidak tersedia; pilih ID atau nama relasi.');
      if (label !== undefined && !['string', 'number'].includes(typeof label)) throw new Error('Nama kelompok tidak sah.');
      if (object.id !== undefined && !['string', 'number'].includes(typeof object.id)) throw new Error('ID kelompok tidak sah.');
      return { key: `reference:${String(object.id ?? label)}`, label: String(label ?? `ID ${object.id}`) };
    }
    return { key: `value:${String(value)}`, label: String(value) };
  });
}

export function analyze(dataset: Dataset, groupBy: string): Analysis {
  const field = ['month', 'quarter', 'year'].includes(groupBy) ? 'when' : groupBy === 'author' ? 'people' : groupBy;
  if (!dataset.records.some(row => Object.hasOwn(row, field)) && !(dataset.records.length === 0 && dataset.fields?.includes(field))) {
    throw new Error('Kolom pengelompokan belum diambil atau tidak diizinkan. Ambil ulang dengan kolom atau relasi yang diperlukan.');
  }
  if (['source', 'evaluations', 'documentation_metadata', 'people'].includes(groupBy)) throw new Error('Kolom kelompok tidak didukung; gunakan author untuk penulis.');
  const unique = [...new Map(dataset.records.map(row => [row.id, row])).values()];
  const hasAttendance = unique.some(row => Object.hasOwn(row, 'total_peserta')) || dataset.fields?.includes('total_peserta') === true;
  const groups = new Map<string, { label: string; rows: Map<number, ReportRecord> }>();
  for (const row of unique) for (const bucket of buckets(row, groupBy)) {
    let group = groups.get(bucket.key);
    if (!group) { group = { label: bucket.label, rows: new Map() }; groups.set(bucket.key, group); }
    group.rows.set(row.id, row);
  }
  return { groupBy, uniqueReports: unique.length, attendance: attendance(unique, hasAttendance),
    groups: [...groups].map(([key, group]) => ({ key, label: group.label, count: group.rows.size,
      reportIds: [...group.rows.keys()], attendance: attendance([...group.rows.values()], hasAttendance) })).sort((a, b) => a.label.localeCompare(b.label, 'id')),
    warnings: ['Total kelompok dapat melebihi jumlah laporan unik jika laporan memiliki beberapa relasi.',
      'Jumlah peserta berarti kehadiran dilaporkan, bukan orang unik.',
      ...(dataset.status === 'partial' ? [`DATA PARSIAL: ${dataset.reason ?? 'Pengambilan belum lengkap.'}`] : [])] };
}
