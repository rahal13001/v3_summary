import assert from 'node:assert/strict';
import { test } from 'node:test';
import { SummaryClient } from '../src/client.js';
import { DatasetStore, analyze } from '../src/dataset.js';

test('client rejects insecure origins and redirects without exposing credentials', async () => {
  assert.throws(() => new SummaryClient('http://summary.test', 'secret'), /HTTPS/);
  const client = new SummaryClient('https://summary.test', 'secret', {
    fetch: async (_url, options) => {
      assert.equal(options?.redirect, 'manual');
      assert.equal(new Headers(options?.headers).get('authorization'), 'Bearer secret');
      return new Response(null, { status: 302, headers: { location: 'https://evil.test' } });
    },
  });
  await assert.rejects(client.read('reports'), /redirect/i);
});

test('client retries rate limits but not authorization failures', async () => {
  let calls = 0;
  const client = new SummaryClient('https://summary.test', 'secret', {
    sleep: async () => {},
    fetch: async () => ++calls === 1
      ? new Response('{}', { status: 429, headers: { 'retry-after': '1' } })
      : Response.json({ data: [], meta: { page: 1, per_page: 25, total: 0, has_more: false } }),
  });
  await client.read('reports');
  assert.equal(calls, 2);
  const denied = new SummaryClient('https://summary.test', 'secret', {
    fetch: async () => { calls++; return new Response('{}', { status: 403 }); },
  });
  await assert.rejects(denied.read('reports'), /403/);
  assert.equal(calls, 3);
});

test('collection deduplicates IDs and reports partial data at capacity', async () => {
  const client = new SummaryClient('https://summary.test', 'secret', { fetch: async url => String(url).includes('/schema') ? Response.json({ data: { reports: { what: {} }, indicators: { id: {} } } }) : Response.json({
    data: [
      { id: 1, slug: 'one', what: 'One', indicators: [{ id: 11, nama_iku: 'IKU A' }], source: { url: 'https://summary.test/api/ai/v1/reports/1' } },
      { id: 1, slug: 'one', what: 'One' }, { id: 2, slug: 'two', what: 'Two' },
    ], meta: { page: 1, per_page: 100, total: 9, has_more: true },
  }) });
  const store = new DatasetStore({ maxRecords: 1 });
  const result = await store.prepare(client, {});
  assert.equal(result.count, 1);
  assert.equal(result.status, 'partial');
  assert.match(result.reason ?? '', /kapasitas/i);
});

test('group totals count unique reports and separate invalid attendance', () => {
  const dataset = {
    id: 'sample', records: [
      { id: 1, slug: 'a', total_peserta: '10', indicators: [{ id: 1, nama_iku: 'A' }, { id: 2, nama_iku: 'B' }] },
      { id: 2, slug: 'b', total_peserta: 'unknown', indicators: [{ id: 1, nama_iku: 'A' }] },
      { id: 3, slug: 'c', total_peserta: null, indicators: [] },
    ], query: {}, status: 'complete' as const, retrievedAt: '2026-10-20T00:00:00Z', touchedAt: Date.now(), bytes: 0,
  };
  const result = analyze(dataset, 'indicators');
  assert.equal(result.uniqueReports, 3);
  assert.equal(result.attendance?.total, 10);
  assert.equal(result.attendance?.invalid, 1);
  assert.equal(result.attendance?.missing, 1);
  assert.equal(result.groups.find(group => group.label === 'A')?.count, 2);
  assert.equal(result.groups.find(group => group.label === 'Belum terkait')?.count, 1);
  assert.throws(() => analyze(dataset, 'why'), /kolom|Kolom/);
});

test('datasets expire after 30 minutes idle', async () => {
  let now = Date.parse('2026-10-20T00:00:00Z');
  const store = new DatasetStore({ now: () => now });
  assert.throws(() => store.get('missing'), /kedaluwarsa|tersedia/);
  const client = new SummaryClient('https://summary.test', 'secret', { fetch: async url => String(url).includes('/schema')
    ? Response.json({ data: { reports: { what: {} } } })
    : Response.json({ data: [], meta: { page: 1, per_page: 100, total: 0, has_more: false } }) });
  const prepared = await store.prepare(client, {});
  now += 29 * 60_000;
  assert.equal(store.get(prepared.datasetId).status, 'complete');
  now += 30 * 60_000;
  assert.throws(() => store.get(prepared.datasetId), /kedaluwarsa/);
});

test('arbitrary JSON attendance, invalid dates, and missing-group collisions cannot alter counts', () => {
  const dataset = { id: 'edge', records: [
    { id: 1, what: 'unassigned', when: '2026-99-99', total_peserta: ['10'], teams: [null, ''] },
    { id: 2, what: null, when: '2026-02-30', total_peserta: '10', teams: [] },
    { id: 3, what: 'other', when: '2026-07-01', teams: [{ id: 1, name: 'Team' }] },
  ], query: {}, status: 'complete' as const, retrievedAt: '', touchedAt: Date.now(), bytes: 0 };
  const result = analyze(dataset, 'what');
  assert.equal(result.groups.length, 3);
  assert.equal(result.attendance?.total, 10);
  assert.equal(result.attendance?.invalid, 1);
  assert.equal(result.groups.find(group => group.label === 'other')?.attendance?.missing, 1);
  assert.equal(analyze(dataset, 'quarter').groups.find(group => group.label === 'Belum terkait')?.count, 2);
  assert.equal(analyze(dataset, 'teams').groups.find(group => group.label === 'Belum terkait')?.count, 2);
});

test('mid-body failures retry and sanitize final transport errors', async () => {
  let calls = 0;
  const client = new SummaryClient('https://summary.test', 'secret', { sleep: async () => {}, fetch: async () => {
    calls++;
    return new Response(new ReadableStream({ start(controller) { controller.error(new Error('secret connection details')); } }));
  } });
  await assert.rejects(client.read('reports'), /Koneksi terputus/);
  assert.equal(calls, 3);
});

test('dataset metadata describes fetched projection, not all permitted columns and relations', async () => {
  let empty = false;
  const client = new SummaryClient('https://summary.test', 'secret', { fetch: async url => String(url).includes('/schema')
    ? Response.json({ data: { reports: { what: {}, total_peserta: {} }, indicators: { id: {} } } })
    : Response.json({ data: empty ? [] : [{ id: 1, what: 'Kegiatan' }], meta: { page: 1, per_page: 100, total: empty ? 0 : 1, has_more: false } }) });
  const store = new DatasetStore();
  const prepared = await store.prepare(client, { fields: 'id,what' });
  assert.equal(analyze(store.get(prepared.datasetId), 'what').attendance, undefined);
  empty = true;
  const missing = await store.prepare(client, { fields: 'what' });
  assert.throws(() => analyze(store.get(missing.datasetId), 'indicators'), /belum diambil/);
  const included = await store.prepare(client, { fields: 'what', include: 'indicators' });
  assert.equal(analyze(store.get(included.datasetId), 'indicators').uniqueReports, 0);
});

test('concurrent preparations are bounded and failed preparations release their slot', async () => {
  let unblock!: () => void;
  let started!: () => void;
  const gate = new Promise<void>(resolve => { unblock = resolve; });
  const entered = new Promise<void>(resolve => { started = resolve; });
  const slow = new SummaryClient('https://summary.test', 'secret', { fetch: async () => {
    started(); await gate;
    return new Response('{}', { status: 403 });
  } });
  const store = new DatasetStore();
  const good = new SummaryClient('https://summary.test', 'secret', { fetch: async url => String(url).includes('/schema')
    ? Response.json({ data: { reports: { what: {} } } })
    : Response.json({ data: [], meta: { page: 1, per_page: 100, total: 0, has_more: false } }) });
  const first = store.prepare(slow, {});
  await entered;
  try { await assert.rejects(store.prepare(good, {}), /sedang berjalan/); }
  finally { unblock(); }
  await assert.rejects(first, /403/);
  assert.equal((await store.prepare(good, {})).status, 'complete');
});

test('in-flight collection reserves space by evicting cached data before fetching another page', async () => {
  let collecting = false;
  let resume!: () => void;
  let reached!: () => void;
  const gate = new Promise<void>(resolve => { resume = resolve; });
  const entered = new Promise<void>(resolve => { reached = resolve; });
  const client = new SummaryClient('https://summary.test', 'secret', { fetch: async url => {
    if (String(url).includes('/schema')) return Response.json({ data: { reports: { what: {} } } });
    const page = Number(new URL(String(url)).searchParams.get('page'));
    if (page === 2) {
      reached(); await gate;
      return Response.json({ data: [], meta: { page: 2, per_page: 100, total: 1, has_more: false } });
    }
    return Response.json({ data: [{ id: collecting ? 2 : 1, what: 'a'.repeat(200) }], meta: { page: 1, per_page: 100, total: 1, has_more: collecting } });
  } });
  const store = new DatasetStore({ maxBytes: 500 });
  const cached = await store.prepare(client, { fields: 'what' });
  collecting = true;
  const pending = store.prepare(client, { fields: 'what' });
  await entered;
  try { assert.throws(() => store.get(cached.datasetId), /tersedia/); }
  finally { resume(); await pending; }
});
