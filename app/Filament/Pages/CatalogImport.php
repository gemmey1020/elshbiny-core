<?php

namespace App\Filament\Pages;

use App\Services\CatalogCsvImportService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogImport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Elshbiny CRM';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $title = 'Catalog CSV Import';

    protected static string $view = 'filament.pages.catalog-import';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    /**
     * @var array<string, mixed>|null
     */
    public ?array $report = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('csv')
                    ->label('CSV file')
                    ->disk('local')
                    ->directory('catalog-imports')
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                    ->required(),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $state = $this->form->getState();
        $path = data_get($state, 'csv');

        if (! is_string($path) || $path === '') {
            Notification::make()->title('Upload a CSV file first.')->danger()->send();

            return;
        }

        $this->report = app(CatalogCsvImportService::class)->import(Storage::disk('local')->path($path));

        Notification::make()
            ->title('Catalog import finished')
            ->body("{$this->report['success']} successful, {$this->report['failed']} failed.")
            ->success()
            ->send();
    }

    public function downloadTemplate(): StreamedResponse
    {
        $template = app(CatalogCsvImportService::class)->templateCsv();

        return response()->streamDownload(
            fn () => print $template,
            'elshbiny-catalog-template.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }
}
