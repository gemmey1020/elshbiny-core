<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationGroup = 'Elshbiny CRM';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Quote')
                    ->schema([
                        Forms\Components\TextInput::make('hash_id')
                            ->required()
                            ->maxLength(6)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->options([
                                Quote::STATUS_PENDING => 'Pending',
                                Quote::STATUS_CONTACTED => 'Contacted',
                                Quote::STATUS_WON => 'Won',
                                Quote::STATUS_LOST => 'Lost',
                                Quote::STATUS_CANCELLED => 'Cancelled',
                            ])
                            ->required(),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Customer')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('customer_phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('customer_email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('UTM')
                    ->schema([
                        Forms\Components\TextInput::make('utm_source')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('utm_medium')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('utm_campaign')
                            ->maxLength(255),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hash_id')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Quote::STATUS_PENDING => 'Pending',
                        Quote::STATUS_CONTACTED => 'Contacted',
                        Quote::STATUS_WON => 'Won',
                        Quote::STATUS_LOST => 'Lost',
                        Quote::STATUS_CANCELLED => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('open_storefront')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Quote $record): string => self::quoteUrl($record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn (Quote $record): string => self::whatsAppUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (Quote $record): bool => filled($record->customer_phone)),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Quote')
                    ->schema([
                        Infolists\Components\TextEntry::make('hash_id')->copyable(),
                        Infolists\Components\TextEntry::make('status')->badge(),
                        Infolists\Components\TextEntry::make('user.email')->label('Buyer account')->placeholder('-'),
                        Infolists\Components\TextEntry::make('created_at')->dateTime(),
                    ])
                    ->columns(4),
                Infolists\Components\Section::make('Customer')
                    ->schema([
                        Infolists\Components\TextEntry::make('customer_name')->placeholder('-'),
                        Infolists\Components\TextEntry::make('customer_phone')->copyable()->placeholder('-'),
                        Infolists\Components\TextEntry::make('customer_email')->copyable()->placeholder('-'),
                    ])
                    ->columns(3),
                Infolists\Components\Section::make('Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->schema([
                                Infolists\Components\TextEntry::make('product_name_snapshot')->label('Product'),
                                Infolists\Components\TextEntry::make('sku_snapshot')->label('SKU')->placeholder('-'),
                                Infolists\Components\TextEntry::make('quantity'),
                                Infolists\Components\TextEntry::make('unit_price_snapshot')
                                    ->label('Unit price')
                                    ->money('EGP')
                                    ->placeholder('-'),
                            ])
                            ->columns(4),
                    ]),
                Infolists\Components\Section::make('UTM')
                    ->schema([
                        Infolists\Components\TextEntry::make('utm_source')->placeholder('-'),
                        Infolists\Components\TextEntry::make('utm_medium')->placeholder('-'),
                        Infolists\Components\TextEntry::make('utm_campaign')->placeholder('-'),
                    ])
                    ->columns(3)
                    ->collapsed(),
            ]);
    }

    public static function quoteUrl(Quote $quote): string
    {
        $frontendUrl = trim(explode(',', (string) env('FRONTEND_URL', ''))[0] ?? '');
        $baseUrl = $frontendUrl !== '' ? rtrim($frontendUrl, '/') : rtrim(url('/'), '/');

        return $baseUrl.'/quote/'.$quote->hash_id;
    }

    public static function whatsAppUrl(Quote $quote): string
    {
        $phone = preg_replace('/\D+/', '', (string) $quote->customer_phone);
        $message = implode("\n", [
            'موقع الشبيني يتحدث',
            'أهلاً بحضرتك، بنتابع معاك بخصوص عرض السعر رقم '.$quote->hash_id.'.',
            'رابط العرض: '.self::quoteUrl($quote),
        ]);

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotes::route('/'),
            'view' => Pages\ViewQuote::route('/{record}'),
        ];
    }
}
