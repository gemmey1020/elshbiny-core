<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsultationResource\Pages;
use App\Models\Consultation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ConsultationResource extends Resource
{
    protected static ?string $model = Consultation::class;

    protected static ?string $navigationGroup = 'Elshbiny CRM';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company')
                    ->maxLength(255),
                Forms\Components\TextInput::make('interest_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('source_page')
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'closed' => 'Closed',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('message')
                    ->columnSpanFull(),
                Forms\Components\Section::make('UTM')
                    ->schema([
                        Forms\Components\TextInput::make('utm_source')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('utm_medium')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('utm_campaign')
                            ->maxLength(255),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('company')
                    ->searchable(),
                Tables\Columns\TextColumn::make('interest_type')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('source_page')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn (Consultation $record): string => self::whatsAppUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (Consultation $record): bool => filled($record->phone)),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Contact')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')->copyable(),
                        Infolists\Components\TextEntry::make('phone')->copyable(),
                        Infolists\Components\TextEntry::make('email')->copyable()->placeholder('-'),
                        Infolists\Components\TextEntry::make('company')->placeholder('-'),
                    ])
                    ->columns(4),
                Infolists\Components\Section::make('Request')
                    ->schema([
                        Infolists\Components\TextEntry::make('interest_type')->placeholder('-'),
                        Infolists\Components\TextEntry::make('source_page')->placeholder('-'),
                        Infolists\Components\TextEntry::make('status')->badge(),
                        Infolists\Components\TextEntry::make('created_at')->dateTime(),
                        Infolists\Components\TextEntry::make('message')->columnSpanFull()->placeholder('-'),
                    ])
                    ->columns(4),
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

    public static function whatsAppUrl(Consultation $consultation): string
    {
        $phone = preg_replace('/\D+/', '', (string) $consultation->phone);
        $message = implode("\n", [
            'موقع الشبيني يتحدث',
            'أهلاً بحضرتك، وصلنا طلبك من موقع الشبيني ونتابع معاك بخصوصه.',
        ]);

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsultations::route('/'),
            'view' => Pages\ViewConsultation::route('/{record}'),
        ];
    }
}
