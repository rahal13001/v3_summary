<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AiOpenApiContractTest extends TestCase
{
    public function test_openapi_documents_every_readonly_ai_route(): void
    {
        // JSON is also valid YAML; keeping this subset permits dependency-free contract checks.
        $spec = json_decode(file_get_contents(base_path('docs/api/ai-v1.openapi.yaml')), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('3.1.0', $spec['openapi']);
        $actual = [];
        foreach (Route::getRoutes() as $route) {
            if (str_starts_with($route->uri(), 'api/ai/v1/')) {
                $this->assertSame(['GET', 'HEAD'], $route->methods());
                $actual[] = '/'.substr($route->uri(), strlen('api/ai/v1/'));
            }
        }
        $documented = array_keys($spec['paths']);
        sort($actual);
        sort($documented);
        $this->assertSame($actual, $documented);
        foreach ($spec['paths'] as $path) {
            $this->assertSame(['get'], array_keys($path));
            foreach (['200', '401', '403', '404', '405', '422', '429', '500', '503'] as $status) {
                $this->assertArrayHasKey($status, $path['get']['responses']);
            }
        }
    }
}
