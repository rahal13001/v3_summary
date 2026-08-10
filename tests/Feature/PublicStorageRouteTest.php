<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageRouteTest extends TestCase
{
    public function test_public_disk_is_not_exposed_through_a_direct_web_server_symlink(): void
    {
        $this->assertSame([], config('filesystems.links'));
    }

    public function test_public_storage_files_can_be_served_through_the_application(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('posts/covers/example.png', $this->tinyPng());

        $this->get('/public-storage/posts/covers/example.png')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertStreamedContent($this->tinyPng());
    }

    public function test_public_disk_urls_use_the_application_storage_route(): void
    {
        $this->assertSame(
            rtrim(config('app.url'), '/').'/public-storage/posts/covers/example.jpg',
            Storage::disk('public')->url('posts/covers/example.jpg'),
        );
    }

    public function test_legacy_storage_urls_still_work_when_the_web_server_passes_them_to_laravel(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('posts/covers/example.png', $this->tinyPng());

        $this->get('/storage/posts/covers/example.png')
            ->assertOk()
            ->assertStreamedContent($this->tinyPng());
    }

    public function test_missing_public_storage_files_return_not_found(): void
    {
        Storage::fake('public');

        $this->get('/public-storage/posts/covers/missing.jpg')
            ->assertNotFound();
    }

    public function test_executable_or_active_content_is_not_served_from_public_storage(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('dokumentasi/malicious.svg', '<svg onload="alert(1)"></svg>');

        $this->get('/public-storage/dokumentasi/malicious.svg')
            ->assertNotFound();

        Storage::disk('public')->put('dokumentasi/disguised.png', '<svg onload="alert(1)"></svg>');

        $this->get('/public-storage/dokumentasi/disguised.png')
            ->assertNotFound();
    }

    private function tinyPng(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);
    }
}
