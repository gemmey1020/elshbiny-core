<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-wrap gap-3">
            <x-filament::button wire:click="downloadTemplate" color="gray">
                Download CSV template
            </x-filament::button>
        </div>

        <form wire:submit="import" class="space-y-4">
            {{ $this->form }}

            <x-filament::button type="submit">
                Import catalog
            </x-filament::button>
        </form>

        @if ($report)
            <x-filament::section>
                <x-slot name="heading">Import report</x-slot>

                <div class="grid gap-3 md:grid-cols-4">
                    <div>Successful: <strong>{{ $report['success'] }}</strong></div>
                    <div>Created: <strong>{{ $report['created'] }}</strong></div>
                    <div>Updated: <strong>{{ $report['updated'] }}</strong></div>
                    <div>Failed: <strong>{{ $report['failed'] }}</strong></div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left">
                                <th class="py-2 pr-4">Row</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">SKU</th>
                                <th class="py-2 pr-4">Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($report['rows'] as $row)
                                <tr>
                                    <td class="py-2 pr-4">{{ $row['row'] }}</td>
                                    <td class="py-2 pr-4">{{ $row['status'] }}</td>
                                    <td class="py-2 pr-4">{{ $row['sku'] ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $row['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
