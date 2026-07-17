<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewQuote extends ViewRecord
{
    protected static string $resource = QuoteResource::class;

    /**
     * @return array<\Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('open_storefront')
                ->label('Open storefront quote')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn (): string => QuoteResource::quoteUrl($this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->url(fn (): string => QuoteResource::quoteUrl($this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('whatsapp')
                ->label('WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->url(fn (): string => QuoteResource::whatsAppUrl($this->record))
                ->openUrlInNewTab()
                ->visible(fn (): bool => filled($this->record->customer_phone)),
        ];
    }
}
