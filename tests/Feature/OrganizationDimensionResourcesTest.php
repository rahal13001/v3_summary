<?php

namespace Tests\Feature;

use App\Filament\Resources\InvolvementResource;
use App\Filament\Resources\WorkUnitResource;
use App\Models\Involvement;
use App\Models\WorkUnit;
use ReflectionClass;
use Tests\TestCase;

class OrganizationDimensionResourcesTest extends TestCase
{
    public function test_work_unit_resource_exposes_the_confirmed_admin_fields_and_filters(): void
    {
        $source = file_get_contents((new ReflectionClass(WorkUnitResource::class))->getFileName());

        $this->assertSame(WorkUnit::class, WorkUnitResource::getModel());
        $this->assertStringContainsString("TextInput::make('name')", $source);
        $this->assertStringContainsString('->unique(', $source);
        $this->assertStringContainsString("->where('unit', \$get('unit'))", $source);
        $this->assertStringContainsString("Select::make('status')", $source);
        $this->assertStringContainsString('->options(WorkUnit::statusOptions())', $source);
        $this->assertStringContainsString("Select::make('unit')", $source);
        $this->assertStringContainsString('->options(WorkUnit::unitOptions())', $source);
        $this->assertStringContainsString("SelectFilter::make('status')", $source);
        $this->assertStringContainsString("SelectFilter::make('unit')", $source);
        $this->assertMatchesRegularExpression('/protected static string\s*\|\s*\\\\UnitEnum\s*\|\s*null \$navigationGroup = \'Admin Area\'/', $source);
    }

    public function test_involvement_resource_exposes_flexible_behavior_configuration(): void
    {
        $source = file_get_contents((new ReflectionClass(InvolvementResource::class))->getFileName());

        $this->assertSame(Involvement::class, InvolvementResource::getModel());
        $this->assertStringContainsString("TextInput::make('name')", $source);
        $this->assertStringContainsString("Select::make('status')", $source);
        $this->assertStringContainsString('->options(Involvement::statusOptions())', $source);
        $this->assertStringContainsString("Toggle::make('is_lprl_organizer')", $source);
        $this->assertStringContainsString("SelectFilter::make('status')", $source);
        $this->assertMatchesRegularExpression('/protected static string\s*\|\s*\\\\UnitEnum\s*\|\s*null \$navigationGroup = \'Admin Area\'/', $source);
    }

    public function test_edit_pages_disable_deletion_for_referenced_master_records(): void
    {
        $workUnitEdit = file_get_contents(base_path('app/Filament/Resources/WorkUnitResource/Pages/EditWorkUnit.php'));
        $involvementEdit = file_get_contents(base_path('app/Filament/Resources/InvolvementResource/Pages/EditInvolvement.php'));

        $this->assertStringContainsString('->disabled(fn (WorkUnit $record): bool => $record->reports()->exists())', $workUnitEdit);
        $this->assertStringContainsString('->disabled(fn (Involvement $record): bool => $record->reports()->exists())', $involvementEdit);
    }
}
