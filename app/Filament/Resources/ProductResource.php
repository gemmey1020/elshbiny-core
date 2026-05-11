<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Elshbiny CRM';

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Catalog')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name_en')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name_en')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_ar')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('sku')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
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
                    ->relationship('category', 'name_en'),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
