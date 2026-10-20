import assert from 'node:assert/strict';
import { execFile, execFileSync } from 'node:child_process';
import { randomUUID } from 'node:crypto';
import { readFile, writeFile, rm, stat, realpath } from 'node:fs/promises';
import { createServer as httpsServer } from 'node:https';
import { createServer as httpServer, type IncomingMessage, type ServerResponse } from 'node:http';
import { promisify } from 'node:util';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { Client } from '@modelcontextprotocol/sdk/client/index.js';
import { StdioClientTransport } from '@modelcontextprotocol/sdk/client/stdio.js';

const run = promisify(execFile);
const packageRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const projectRoot = path.resolve(packageRoot, '../..');
const id = `summary-ai-smoke-${randomUUID()}`;
const fixture = path.join(projectRoot, 'storage/framework/testing', id);
const php = process.env.SUMMARY_TEST_PHP ?? 'php';
const router = 'tests/Fixtures/ai-http.php';
const result = await run(php, [router, 'prepare', id], { cwd: projectRoot, maxBuffer: 10 * 1024 * 1024 });
assert.equal(JSON.parse(result.stdout).prepared, true);
const token = (await readFile(path.join(fixture, 'token.txt'), 'utf8')).trim();
const cert = path.join(fixture, 'cert.pem');
const key = path.join(fixture, 'key.pem');
execFileSync('openssl', ['req', '-x509', '-newkey', 'rsa:2048', '-nodes', '-keyout', key, '-out', cert,
  '-days', '1', '-subj', '/CN=localhost', '-addext', 'subjectAltName=DNS:localhost,IP:127.0.0.1'], { stdio: 'ignore' });

const handle = (scheme: 'https' | 'http') => async (request: IncomingMessage, response: ServerResponse) => {
  try {
    const url = new URL(request.url ?? '/', `${scheme}://${request.headers.host}`);
    const requested = decodeURIComponent(url.pathname);
    const publicRoot = path.join(projectRoot, 'public');
    const asset = path.resolve(publicRoot, `.${requested}`);
    if (asset.startsWith(publicRoot + path.sep)) {
      try {
        const info = await stat(asset);
        if (info.isFile() && (await realpath(asset)).startsWith(await realpath(publicRoot) + path.sep)) {
          const types: Record<string, string> = { '.js': 'application/javascript', '.css': 'text/css', '.svg': 'image/svg+xml', '.png': 'image/png', '.woff2': 'font/woff2', '.ico': 'image/x-icon' };
          response.writeHead(200, { 'Content-Type': types[path.extname(asset)] ?? 'application/octet-stream' });
          response.end(await readFile(asset)); return;
        }
      } catch { /* Route handled by the isolated Laravel kernel below. */ }
    }
    const chunks: Buffer[] = [];
    for await (const chunk of request) chunks.push(Buffer.from(chunk));
    const payload = JSON.stringify({ url: url.toString(), method: request.method, headers: request.headers, body: Buffer.concat(chunks).toString('utf8') });
    const child = execFile(php, [router, 'request', id], { cwd: projectRoot, maxBuffer: 25 * 1024 * 1024 }, (error, stdout) => {
      if (error) { response.writeHead(500); response.end('Isolated PHP fixture failed.'); return; }
      try {
        const returned = JSON.parse(stdout);
        response.writeHead(returned.status, returned.headers);
        response.end(Buffer.from(returned.body, 'base64'));
      } catch { response.writeHead(500); response.end('Invalid isolated fixture response.'); }
    });
    child.stdin?.end(payload);
  } catch { response.writeHead(500); response.end('Fixture request failed.'); }
};

const https = httpsServer({ cert: await readFile(cert), key: await readFile(key) }, handle('https'));
const http = httpServer(handle('http'));
await new Promise<void>(resolve => https.listen(0, '127.0.0.1', resolve));
await new Promise<void>(resolve => http.listen(0, '127.0.0.1', resolve));
const httpsPort = (https.address() as { port: number }).port;
const httpPort = (http.address() as { port: number }).port;
const driver = new Client({ name: 'summary-smoke', version: '1.0.0' });
const transport = new StdioClientTransport({ command: process.execPath, args: ['dist/index.js'], cwd: packageRoot,
  env: { PATH: process.env.PATH ?? '', NODE_EXTRA_CA_CERTS: cert,
    SUMMARY_BASE_URL: `https://127.0.0.1:${httpsPort}`, SUMMARY_API_TOKEN: token, SUMMARY_OUTPUT_DIR: path.join(fixture, 'documents') }, stderr: 'pipe' });

try {
  await driver.connect(transport);
  const tools = await driver.listTools();
  assert.equal(tools.tools.length, 10);
  const call = async (name: string, args: Record<string, unknown> = {}) => {
    const returned = await driver.callTool({ name, arguments: args }, undefined, { timeout: 120_000 });
    assert.notEqual(returned.isError, true, JSON.stringify(returned));
    return JSON.parse((returned.content as { text: string }[])[0]!.text);
  };
  assert.equal((await call('summary_capabilities')).data.readonly, true);
  const dataset = await call('summary_prepare_dataset', { query: {
    fields: 'what,when,tanggal_selesai,how,total_peserta', include: 'indicators',
    period_start: '2026-07-01', period_end: '2026-09-30', period_mode: 'overlap',
  } });
  assert.equal(dataset.count, 2);
  assert.equal(dataset.status, 'complete');
  const analysis = await call('summary_analyze_dataset', { datasetId: dataset.datasetId, groupBy: 'indicators' });
  assert.equal(analysis.uniqueReports, 2);
  assert.equal(analysis.attendance.total, 25);
  const documents = await call('summary_create_documents', { datasetId: dataset.datasetId, title: 'Kegiatan IKU Triwulan III 2026',
    groupBy: 'indicators', formats: ['xlsx', 'md', 'pdf', 'pptx', 'docx'], sections: [
      { heading: 'Ringkasan hasil', text: 'Dua laporan kegiatan layanan tercatat pada periode yang diminta. Jumlah kehadiran dilaporkan adalah 25.', sourceIds: analysis.groups[0].reportIds },
    ] });
  assert.equal(documents.files.length, 5);
  for (const filename of documents.files) assert.ok((await stat(filename)).size > 0);
  const marker = { fixture: id, reports: 2, attendance: 25, formats: 5,
    directory: documents.directory, browserUrl: `http://127.0.0.1:${httpPort}/fixture-login` };
  await writeFile(path.join(fixture, 'result.json'), JSON.stringify(marker, null, 2));
  console.log(JSON.stringify(marker));
} finally {
  await driver.close();
  if (!process.env.SUMMARY_SMOKE_KEEP_ALIVE) {
    https.closeAllConnections(); http.closeAllConnections(); https.close(); http.close();
    await rm(fixture, { recursive: true, force: true });
  }
}

if (process.env.SUMMARY_SMOKE_KEEP_ALIVE) {
  console.log('Isolated UI fixture running; stop this process after browser verification.');
  const finish = async () => { https.closeAllConnections(); http.closeAllConnections(); https.close(); http.close(); await rm(path.join(fixture, 'token.txt'), { force: true }); process.exit(0); };
  process.once('SIGINT', finish); process.once('SIGTERM', finish);
}
