<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Omega quick actions</x-slot>
        <x-slot name="description">Fast entry points for the daily Elshbiny admin workflow.</x-slot>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <a class="rounded-lg border border-gray-700 px-4 py-3 text-sm font-semibold transition hover:border-gray-400"
               href="{{ \App\Filament\Resources\ProductResource::getUrl('index') }}">Products</a>
            <a class="rounded-lg border border-gray-700 px-4 py-3 text-sm font-semibold transition hover:border-gray-400"
               href="{{ \App\Filament\Resources\CategoryResource::getUrl('index') }}">Categories</a>
            <a class="rounded-lg border border-gray-700 px-4 py-3 text-sm font-semibold transition hover:border-gray-400"
               href="{{ \App\Filament\Resources\QuoteResource::getUrl('index') }}">Quotes</a>
            <a class="rounded-lg border border-gray-700 px-4 py-3 text-sm font-semibold transition hover:border-gray-400"
               href="{{ \App\Filament\Resources\ConsultationResource::getUrl('index') }}">Consultations</a>
            <a class="rounded-lg border border-gray-700 px-4 py-3 text-sm font-semibold transition hover:border-gray-400"
               href="{{ \App\Filament\Pages\CatalogImport::getUrl() }}">CSV Import</a>
            <a class="rounded-lg border border-gray-700 px-4 py-3 text-sm font-semibold transition hover:border-gray-400"
               href="{{ \App\Filament\Pages\AdminGuide::getUrl() }}">Admin Guide</a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
