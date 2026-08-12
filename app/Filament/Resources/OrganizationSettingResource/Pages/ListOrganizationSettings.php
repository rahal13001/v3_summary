<?php

namespace App\Filament\Resources\OrganizationSettingResource\Pages;

use App\Filament\Resources\OrganizationSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizationSettings extends ListRecords
{
    protected static string $resource = OrganizationSettingResource::class;

    protected function getHeaderActions(): array
    {
        return OrganizationSettingResource::canCreate()
            ? [CreateAction::make()]
            : [];
    }
}
