<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Consultation;
use App\Models\Product;
use App\Models\Quote;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OmegaStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make('Products', Product::query()->count())
                ->description('Catalog records'),
            Stat::make('Categories', Category::query()->count())
                ->description('Parent and child categories'),
            Stat::make('Quotes', Quote::query()->count())
                ->description('Read-only buyer requests'),
            Stat::make('Consultations', Consultation::query()->count())
                ->description('Stored contact submissions'),
        ];
    }
}
