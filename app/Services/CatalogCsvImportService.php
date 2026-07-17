<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CatalogCsvImportService
{
    public const TEMPLATE_HEADERS = [
        'category_ar',
        'subcategory_ar',
        'category_en',
        'subcategory_en',
        'product_name_ar',
        'product_name_en',
        'sku',
        'price',
        'is_active',
        'sort_order',
        'description_ar',
        'description_en',
        'spec_material',
        'spec_height',
        'spec_finish',
    ];

    /**
     * @return array{success:int,created:int,updated:int,failed:int,rows:array<int,array<string,mixed>>}
     */
    public function import(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return $this->emptyReportWithFailure('Unable to open CSV file.');
        }

        $headers = $this->normalizeHeaders((array) fgetcsv($handle));
        $report = [
            'success' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'rows' => [],
        ];

        $rowNumber = 1;

        while (($rawRow = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $row = $this->rowFromHeaders($headers, $rawRow);
            $result = $this->importRow($row, $rowNumber);
            $report['rows'][] = $result;

            if ($result['status'] === 'failed') {
                $report['failed']++;
                continue;
            }

            $report['success']++;
            $report[$result['status']]++;
        }

        fclose($handle);

        return $report;
    }

    public function templateCsv(): string
    {
        return implode(',', self::TEMPLATE_HEADERS).PHP_EOL
            .'مانيكانات الواجهات,رجالي,Premium Window Mannequins,Men,مانيكان رجالي واجهة,Window Male Mannequin,ELS-IMPORT-001,1200,false,10,وصف عربي,English description,fiberglass,185cm,matte'.PHP_EOL;
    }

    /**
     * @param array<string, string> $row
     * @return array{row:int,status:string,sku:?string,message:string}
     */
    private function importRow(array $row, int $rowNumber): array
    {
        $sku = $this->clean(Arr::get($row, 'sku'));
        $productNameAr = $this->clean(Arr::get($row, 'product_name_ar'));
        $categoryAr = $this->clean(Arr::get($row, 'category_ar'));

        if ($sku === '') {
            return $this->rowFailure($rowNumber, null, 'SKU is required. No product was created.');
        }

        if ($productNameAr === '') {
            return $this->rowFailure($rowNumber, $sku, 'Arabic product name is required.');
        }

        if ($categoryAr === '') {
            return $this->rowFailure($rowNumber, $sku, 'Arabic category name is required.');
        }

        $category = $this->findOrCreateCategory(
            $categoryAr,
            $this->clean(Arr::get($row, 'category_en')),
            null
        );

        $subcategoryAr = $this->clean(Arr::get($row, 'subcategory_ar'));

        if ($subcategoryAr !== '') {
            $category = $this->findOrCreateCategory(
                $subcategoryAr,
                $this->clean(Arr::get($row, 'subcategory_en')),
                $category->id
            );
        }

        $product = Product::withTrashed()->where('sku', $sku)->first();
        $status = $product ? 'updated' : 'created';

        $product ??= new Product(['sku' => $sku]);

        $product->fill([
            'category_id' => $category->id,
            'name_ar' => $productNameAr,
            'name_en' => $this->clean(Arr::get($row, 'product_name_en')) ?: $productNameAr,
            'slug' => $product->exists ? $product->slug : $this->uniqueSlug(Product::class, $sku),
            'base_price' => $this->nullableDecimal(Arr::get($row, 'price')),
            'price_visibility' => 'authenticated',
            'technical_specs' => $this->specsFromRow($row),
            'sort_order' => $this->nullableInteger(Arr::get($row, 'sort_order')) ?? 0,
            'is_active' => $this->parseBoolean(Arr::get($row, 'is_active'), false),
        ]);

        $product->save();

        if ($product->trashed()) {
            $product->restore();
        }

        return [
            'row' => $rowNumber,
            'status' => $status,
            'sku' => $sku,
            'message' => $status === 'created' ? 'Product created.' : 'Product updated.',
        ];
    }

    private function findOrCreateCategory(string $nameAr, string $nameEn, ?int $parentId): Category
    {
        $category = Category::query()
            ->where('name_ar', $nameAr)
            ->where('parent_id', $parentId)
            ->first();

        if ($category) {
            return $category;
        }

        return Category::create([
            'parent_id' => $parentId,
            'name_ar' => $nameAr,
            'name_en' => $nameEn !== '' ? $nameEn : $nameAr,
            'slug' => $this->uniqueSlug(Category::class, $nameEn !== '' ? $nameEn : $nameAr),
            'is_active' => true,
        ]);
    }

    /**
     * @param array<string, string> $row
     * @return array<string, string>
     */
    private function specsFromRow(array $row): array
    {
        $specs = [];

        foreach ($row as $key => $value) {
            if (! str_starts_with($key, 'spec_')) {
                continue;
            }

            $cleanValue = $this->clean($value);

            if ($cleanValue === '') {
                continue;
            }

            $specs[substr($key, 5)] = $cleanValue;
        }

        return $specs;
    }

    private function uniqueSlug(string $modelClass, string $base): string
    {
        $slug = Str::slug($base);
        $slug = $slug !== '' ? $slug : 'item-'.substr(md5($base), 0, 8);
        $candidate = $slug;
        $counter = 2;

        while ($this->slugExists($modelClass, $candidate)) {
            $candidate = $slug.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    private function slugExists(string $modelClass, string $slug): bool
    {
        $query = $modelClass::query();

        if ($modelClass === Product::class) {
            $query = Product::withTrashed();
        }

        return $query->where('slug', $slug)->exists();
    }

    /**
     * @param array<int, string|null> $headers
     * @return array<int, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        return array_map(fn ($header) => Str::snake($this->clean($header)), $headers);
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, string|null> $row
     * @return array<string, string>
     */
    private function rowFromHeaders(array $headers, array $row): array
    {
        $values = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $values[$header] = $this->clean($row[$index] ?? null);
        }

        return $values;
    }

    /**
     * @return array{success:int,created:int,updated:int,failed:int,rows:array<int,array<string,mixed>>}
     */
    private function emptyReportWithFailure(string $message): array
    {
        return [
            'success' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 1,
            'rows' => [
                [
                    'row' => 0,
                    'status' => 'failed',
                    'sku' => null,
                    'message' => $message,
                ],
            ],
        ];
    }

    /**
     * @return array{row:int,status:string,sku:?string,message:string}
     */
    private function rowFailure(int $rowNumber, ?string $sku, string $message): array
    {
        return [
            'row' => $rowNumber,
            'status' => 'failed',
            'sku' => $sku,
            'message' => $message,
        ];
    }

    private function clean(mixed $value): string
    {
        return trim((string) $value);
    }

    private function nullableDecimal(mixed $value): ?string
    {
        $clean = $this->clean($value);

        if ($clean === '') {
            return null;
        }

        return is_numeric($clean) ? number_format((float) $clean, 2, '.', '') : null;
    }

    private function nullableInteger(mixed $value): ?int
    {
        $clean = $this->clean($value);

        return $clean !== '' && is_numeric($clean) ? (int) $clean : null;
    }

    private function parseBoolean(mixed $value, bool $default): bool
    {
        $clean = strtolower($this->clean($value));

        if ($clean === '') {
            return $default;
        }

        return in_array($clean, ['1', 'true', 'yes', 'y', 'active'], true);
    }
}
