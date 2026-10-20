import assert from 'node:assert/strict';
import { test } from 'node:test';
import { mkdtemp, rm, stat } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { Client } from '@modelcontextprotocol/sdk/client/index.js';
import { InMemoryTransport } from '@modelcontextprotocol/sdk/inMemory.js';
import { createServer } from '../src/index.js';
import { SummaryClient } from '../src/client.js';
import { DatasetStore } from '../src/dataset.js';

test('real MCP protocol discovers tools, prepares data, analyzes and creates requested files', async () => {
  const root = await mkdtemp(path.join(tmpdir(), 'summary-mcp-'));
  const api = new SummaryClient('https://summary.test', 'secret', { fetch: async url => {
    if (String(url).includes('/schema')) return Response.json({ data: { reports: { what: {}, when: {} }, indicators: { id: {}, nama_iku: {} } } });
    if (String(url).includes('/capabilities')) return Response.json({ data: { readonly: true } });
    return Response.json({ data: [{ id: 1, slug: 'one', what: 'Kegiatan', when: '2026-07-01', indicators: [{ id: 7, nama_iku: 'Layanan' }] }],
      meta: { page: 1, per_page: 100, total: 1, has_more: false } });
  } });
  const server = createServer(api, new DatasetStore(), root);
  const client = new Client({ name: 'summary-test', version: '1.0.0' });
  const [clientTransport, serverTransport] = InMemoryTransport.createLinkedPair();
  await server.connect(serverTransport);
  await client.connect(clientTransport);
  const call = async (name: string, args: Record<string, unknown>) => {
    const result = await client.callTool({ name, arguments: args });
    assert.notEqual(result.isError, true, JSON.stringify(result));
    const content = result.content as { type: string; text: string }[];
    return JSON.parse(content[0]!.text);
  };
  try {
    const tools = await client.listTools();
    assert.equal(tools.tools.length, 10);
    assert.equal(tools.tools.find(tool => tool.name === 'summary_create_documents')?.annotations?.readOnlyHint, false);
    assert.equal(tools.tools.find(tool => tool.name === 'summary_list_reports')?.annotations?.readOnlyHint, true);
    const dataset = await call('summary_prepare_dataset', { query: { include: 'indicators' } });
    const analysis = await call('summary_analyze_dataset', { datasetId: dataset.datasetId, groupBy: 'indicators' });
    assert.equal(analysis.uniqueReports, 1);
    const output = await call('summary_create_documents', { datasetId: dataset.datasetId, groupBy: 'indicators', title: 'Uji MCP', formats: ['md'], sections: [] });
    assert.equal(output.files.length, 1);
    assert.ok((await stat(output.files[0])).size > 0);
    const invalid = await client.callTool({ name: 'summary_analyze_dataset', arguments: { datasetId: dataset.datasetId, groupBy: 'password' } });
    assert.equal(invalid.isError, true);
  } finally { await client.close(); await server.close(); await rm(root, { recursive: true, force: true }); }
});
