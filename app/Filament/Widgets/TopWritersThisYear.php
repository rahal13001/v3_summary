<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TopWritersThisYear extends Widget
{
    protected static ?int $sort = 6;
    protected static string $view = 'filament.widgets.top-writers-this-year';
}
