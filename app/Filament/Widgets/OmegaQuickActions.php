<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class OmegaQuickActions extends Widget
{
    protected static string $view = 'filament.widgets.omega-quick-actions';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';
}
