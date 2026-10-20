import assert from 'node:assert/strict';
import { test } from 'node:test';
import { fileURLToPath } from 'node:url';
import SwaggerParser from '@apidevtools/swagger-parser';

test('OpenAPI 3.1 contract validates including all internal schema references', async () => {
  const specification = await SwaggerParser.validate(fileURLToPath(new URL('../../../docs/api/ai-v1.openapi.yaml', import.meta.url)), { resolve: { external: false } });
  assert.equal(specification.info.title, 'Summary Readonly AI API');
  assert.equal(Object.keys(specification.paths ?? {}).length, 7);
});
