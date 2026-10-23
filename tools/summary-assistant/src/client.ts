import { setTimeout as delay } from 'node:timers/promises';
import { z } from 'zod';

export type QueryParams = Record<string, string | number>;
export type Endpoint = 'capabilities' | 'schema' | 'reports' | 'report' | 'references' | 'evaluations' | 'followers';
export interface ApiResponse {
  data: unknown;
  meta?: { page?: number; per_page?: number; total?: number; has_more?: boolean; retrieved_at?: string };
}

const envelope = z.object({
  data: z.unknown(),
  meta: z.object({ page: z.number().int().positive().optional(), per_page: z.number().int().positive().optional(),
    total: z.number().int().nonnegative().optional(), has_more: z.boolean().optional(), retrieved_at: z.string().optional() }).passthrough().optional(),
});

export class SummaryClient {
  readonly baseUrl: URL;
  private readonly token: string;
  private readonly fetcher: typeof fetch;
  private readonly sleeper: (ms: number) => Promise<unknown>;
  private active = 0;
  private waiting: (() => void)[] = [];

  constructor(baseUrl: string, token: string, options: { fetch?: typeof fetch; sleep?: (ms: number) => Promise<unknown> } = {}) {
    this.baseUrl = new URL(baseUrl);
    if (this.baseUrl.protocol !== 'https:' || this.baseUrl.username || this.baseUrl.password || this.baseUrl.search || this.baseUrl.hash) {
      throw new Error('SUMMARY_BASE_URL wajib HTTPS tanpa kredensial, query, atau fragment.');
    }
    if (!token || /[\r\n]/.test(token)) throw new Error('SUMMARY_API_TOKEN wajib diisi dengan token Summary.');
    this.token = token;
    this.fetcher = options.fetch ?? fetch;
    this.sleeper = options.sleep ?? (ms => delay(ms));
  }

  reportUrl(slug?: unknown): string {
    const root = this.baseUrl.toString().replace(/\/$/, '');
    const safeSlug = typeof slug === 'string' && /^[a-z0-9]+(?:[-_][a-z0-9]+)*$/i.test(slug) && !/^\d+$/.test(slug);
    return `${root}/laporan-5w1h${safeSlug ? `/${encodeURIComponent(slug)}` : ''}`;
  }

  private browserSource(value: unknown): unknown {
    if (!value || typeof value !== 'object' || Array.isArray(value)) return value;
    const row = value as Record<string, unknown>;
    const source = row.source && typeof row.source === 'object' ? row.source as Record<string, unknown> : {};
    return { ...row, source: { url: this.reportUrl(row.slug), updated_at: source.updated_at ?? null } };
  }

  private url(path: string): URL {
    const root = this.baseUrl.toString().replace(/\/$/, '');
    return new URL(`${root}/api/ai/v1/${path}`);
  }

  async read(endpoint: Endpoint, query: QueryParams = {}, key?: string | number): Promise<ApiResponse> {
    const paths: Record<Endpoint, string> = { capabilities: 'capabilities', schema: 'schema', reports: 'reports',
      report: 'reports', references: 'references', evaluations: 'evaluations', followers: 'reports' };
    let path = paths[endpoint];
    if (!path) throw new Error('Endpoint tidak dikenal.');
    if (endpoint === 'report' || endpoint === 'followers') {
      if (!/^\d+$/.test(String(key))) throw new Error('ID laporan wajib numerik.');
      path += `/${key}`;
      if (endpoint === 'followers') path += '/followers';
    } else if (endpoint === 'references') {
      if (!['indicators', 'teams', 'work_units', 'involvements'].includes(String(key))) throw new Error('Dataset referensi tidak sah.');
      path += `/${key}`;
    }
    const url = this.url(path);
    for (const [name, value] of Object.entries(query)) {
      if (!/^(fields|include|q|sort|page|per_page|period_start|period_end|period_mode|filter\[[a-z_]+\]\[(eq|in|contains|gte|lte|is_null)\])$/.test(name)
        || !['string', 'number'].includes(typeof value) || String(value).length > 2000) throw new Error('Parameter query tidak sah.');
      url.searchParams.set(name, String(value));
    }
    if (this.active >= 2) await new Promise<void>(resolve => this.waiting.push(resolve));
    else this.active++;
    try {
      const result = await this.request(url);
      if (['reports', 'references', 'evaluations', 'followers'].includes(endpoint) && !Array.isArray(result.data)) throw new Error('Daftar data Summary tidak sah.');
      if (['capabilities', 'schema', 'report'].includes(endpoint) && (result.data === null || typeof result.data !== 'object' || Array.isArray(result.data))) throw new Error('Objek data Summary tidak sah.');
      if (endpoint === 'reports') result.data = (result.data as unknown[]).map(row => this.browserSource(row));
      if (endpoint === 'report') result.data = this.browserSource(result.data);
      return result;
    } finally {
      const waiting = this.waiting.shift();
      if (waiting) waiting();
      else this.active--;
    }
  }

  private async request(url: URL): Promise<ApiResponse> {
    for (let attempt = 0; attempt < 3; attempt++) {
      let response: Response;
      try {
        response = await this.fetcher(url, { method: 'GET', redirect: 'manual', signal: AbortSignal.timeout(30_000),
          headers: { Authorization: `Bearer ${this.token}`, Accept: 'application/json' } });
      } catch {
        if (attempt < 2) { await this.sleeper(500 * 2 ** attempt); continue; }
        throw new Error('Koneksi Summary gagal atau timeout. Periksa jaringan dan sertifikat HTTPS.');
      }
      if (response.status >= 300 && response.status < 400) {
        await response.body?.cancel();
        throw new Error('Redirect Summary ditolak untuk melindungi token.');
      }
      if ((response.status === 429 || response.status >= 500) && attempt < 2) {
        const retry = response.headers.get('retry-after');
        const ms = retry ? (/^\d+$/.test(retry) ? Number(retry) * 1000 : Date.parse(retry) - Date.now()) : 500 * 2 ** attempt;
        if (Number.isFinite(ms) && ms > 30_000) {
          await response.body?.cancel();
          throw new Error(`Summary HTTP ${response.status}; tunggu sesuai Retry-After sebelum melanjutkan.`);
        }
        await response.body?.cancel();
        await this.sleeper(Number.isFinite(ms) ? Math.max(0, ms) : 1000);
        continue;
      }
      if (!response.ok) {
        await response.body?.cancel();
        throw new Error(`Summary HTTP ${response.status}. Periksa izin, masa berlaku token, atau parameter; tidak ada fallback API model berbayar.`);
      }
      const reader = response.body?.getReader();
      if (!reader) throw new Error('Respons Summary kosong.');
      const chunks: Uint8Array[] = [];
      let bytes = 0;
      let exceeded = false;
      try {
        while (true) {
          const item = await reader.read();
          if (item.done) break;
          bytes += item.value.length;
          if (bytes > 50 * 1024 * 1024) { exceeded = true; await reader.cancel(); throw new Error('capacity'); }
          chunks.push(item.value);
        }
      } catch {
        if (exceeded) throw new Error('Respons Summary melebihi batas 50 MiB.');
        if (attempt < 2) { await this.sleeper(500 * 2 ** attempt); continue; }
        throw new Error('Koneksi terputus saat membaca respons Summary.');
      }
      try {
        const result = envelope.parse(JSON.parse(Buffer.concat(chunks).toString('utf8')));
        if (!Object.hasOwn(result, 'data')) throw new Error('missing data');
        return { data: result.data, meta: result.meta };
      } catch {
        throw new Error('Struktur JSON Summary tidak sah.');
      }
    }
    throw new Error('Koneksi Summary gagal.');
  }
}
