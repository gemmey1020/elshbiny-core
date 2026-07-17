<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Elshbiny CRM';

    protected static ?string $navigationIcon = 'heroicon-o-cube';

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
                Forms\Components\Section::make('Catalog')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->options(fn () => Category::query()->orderBy('sort_order')->orderBy('name_ar')->pluck('name_ar', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name_en')
                            ->helperText('Optional. If left empty, Arabic name is reused internally.')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_ar')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->helperText('Optional. Generated automatically if left empty.')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('base_price')
                            ->numeric()
                            ->prefix('EGP'),
                        Forms\Components\Select::make('price_visibility')
                            ->options([
                                'public' => 'Public',
                                'authenticated' => 'Authenticated',
                                'hidden' => 'Hidden',
                            ])
                            ->default('authenticated')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->required(),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Specs + Media')
                    ->schema([
                        Forms\Components\KeyValue::make('technical_specs')
                            ->keyLabel('Spec')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('media_uploads')
                            ->image()
                            ->multiple()
                            ->directory('product-media-staging')
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_en')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name_en')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_visibility')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name_ar'),
                Tables\Filters\SelectFilter::make('price_visibility')
                    ->options([
                        'public' => 'Public',
                        'authenticated' => 'Authenticated',
                        'hidden' => 'Hidden',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
