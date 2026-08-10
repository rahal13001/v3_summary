<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Support\Carbon as IlluminateCarbon;
use Tests\TestCase;

class GazeCacheCompatibilityTest extends TestCase
{
    public function test_cache_allows_carbon_expiration_values_used_by_gaze(): void
    {
        $allowedClasses = config('cache.serializable_classes');

        $this->assertIsArray($allowedClasses);
        $this->assertContains(Carbon::class, $allowedClasses);
        $this->assertContains(IlluminateCarbon::class, $allowedClasses);

        $restored = unserialize(
            serialize(['expires' => now()]),
            ['allowed_classes' => $allowedClasses],
        );

        $this->assertInstanceOf(IlluminateCarbon::class, $restored['expires']);
    }
}
