<?php

namespace Tests\Feature;

use Tests\TestCase;

class LivewirePayloadConfigurationTest extends TestCase
{
    public function test_rich_editor_payload_allows_nested_table_documents(): void
    {
        $maxNestingDepth = config('livewire.payload.max_nesting_depth');

        $this->assertGreaterThanOrEqual(20, $maxNestingDepth);
    }
}
