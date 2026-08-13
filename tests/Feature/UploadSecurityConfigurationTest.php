<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\RelationManagers\ExecutorRelationManager;
use App\Filament\Resources\OrganizationSettingResource;
use ReflectionClass;
use Tests\TestCase;

class UploadSecurityConfigurationTest extends TestCase
{
    public function test_all_public_upload_fields_reject_wildcard_images_and_svg(): void
    {
        foreach ([OrderResource::class, ExecutorRelationManager::class, EditProfile::class, OrganizationSettingResource::class] as $class) {
            $source = file_get_contents((new ReflectionClass($class))->getFileName());

            $this->assertStringNotContainsString("'image/*'", $source, $class);
            $this->assertStringNotContainsString("'image/svg+xml'", $source, $class);
            $this->assertStringContainsString("'image/jpeg', 'image/png', 'image/webp'", $source, $class);
        }
    }
}
