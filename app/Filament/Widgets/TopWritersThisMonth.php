<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TopWritersThisMonth extends Widget
{
    protected static ?int $sort = 5;
    protected static string $view = 'filament.widgets.top-writers-this-month';
}
