<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $this->attachUploadedProductMedia();
    }

    private function attachUploadedProductMedia(): void
    {
        $uploads = data_get($this->form->getRawState(), 'media_uploads', []);

        foreach ((array) $uploads as $path) {
            if (! is_string($path) || $path === '') {
                continue;
            }

            $this->record
                ->addMediaFromDisk($path, 'public')
                ->toMediaCollection(Product::MEDIA_COLLECTION);
        }
    }
}
