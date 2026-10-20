import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { getDocument } from 'pdfjs-dist/legacy/build/pdf.mjs';
import { createCanvas } from '@napi-rs/canvas';

const file = path.resolve(process.argv[2] ?? '');
if (!file.includes(`${path.sep}storage${path.sep}framework${path.sep}testing${path.sep}summary-ai-smoke-`) || !file.endsWith('.pdf')) {
  throw new Error('Only isolated Summary smoke PDFs may be rendered by this verification script.');
}
const loading = getDocument({ data: new Uint8Array(await readFile(file)), useSystemFonts: true });
const document = await loading.promise;
const pageNumber = Number(process.argv[3] ?? 1);
const page = await document.getPage(pageNumber);
const viewport = page.getViewport({ scale: 1.5 });
const canvas = createCanvas(Math.ceil(viewport.width), Math.ceil(viewport.height));
await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
const output = file.replace(/\.pdf$/, `-page-${pageNumber}.png`);
await writeFile(output, canvas.toBuffer('image/png'));
console.log(JSON.stringify({ pages: document.numPages, page: pageNumber, image: output }));
await loading.destroy();
