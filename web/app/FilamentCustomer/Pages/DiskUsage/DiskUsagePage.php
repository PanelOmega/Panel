<?php

namespace App\FilamentCustomer\Pages\DiskUsage;

use App\Http\Controllers\FileManager\FileManagerController;
use App\Models\Customer;
use App\Models\HostingSubscription\DiskUsage;
use App\Services\FileManager\FileManager;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use App\Tables\Columns\DiskUsageColumn;

class DiskUsagePage extends Page implements HasTable
{
    use InteractsWithTable;
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament-customer.pages.disk-usage.disk-usage-page';
    protected static ?string $title = 'Disk Usage';
    public array $sections;
    #[Url(except: '')]
    public string $path = '';
    protected string $disk = 'public';
    protected $listeners = ['updatePath' => '$refresh'];

    public $totalDiskUsage = 0;

    public function mount(): void {
        $this->sections = $this->getSections();
    }

    public function getSections(): array {
        return [
            'title_text' => [
                'Monitor your account\'s available space with the Disk Usage feature. All presented figures are relative to the largest directory.
                Use the <a href="' . route('file-manager.index') . '" target="_blank" style="text-decoration: underline">File Manager</a> to see usage data for individual files and the
                <a href="' . route('filament.customer.resources.databases.index') . '" target="_blank" style="text-decoration: underline">Manage My Databases</a> feature to see data for individual databases.
                For more information, read our <a href="" style="text-decoration: underline">documentation</a>.',
                '†Email account storage may occupy less space on the disk if you use compression or hard-link optimizations designed to save space.
                Email account storage does not include the metadata that the system uses to store email.',
                '‡The files outside of your home directory, the metadata that the system uses to store email in the mail directory, the email in Trash folders, or the files that you do not have permission to access.'
            ],
            'subtitle' => 'These figures may not reflect recent changes to your account’s disk usage.',
            'subtitle_text' => 'The Disk Usage table below indicates how much space the directories’ contents use, not how much space the directory itself uses.
            Files typically occupy more disk space than their actual size. This may cause discrepancies between the data you see in the <a href="' . route('file-manager.index') . '" target="_blank" style="text-decoration: underline">File Manager</a> versus the information you find here.'
        ];
    }

    public function table(Table $table): Table
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $this->disk = "/home/{$hostingSubscription->system_username}";

        $storage = Storage::build([
            'driver' => 'local',
            'throw' => false,
            'root' => $this->disk,
        ]);

        return $table
            ->query(
                $this->query()
            )
            ->paginated(false)
            ->openRecordUrlInNewTab()
            ->columns([
                TextColumn::make('location')
                    ->label('Location')
                    ->action(function($record) {
                        switch ($record->location) {
                            case 'Databases':
                                return redirect()->route('filament.customer.resources.databases.index');
                            case 'Mailing Lists':
                                return redirect()->route('#');
                            case 'Email Accounts†':
                                return redirect()->route('#');
                            default:
                                if (str_contains($record->location, '/')) {
                                    $location = htmlspecialchars($record->location, ENT_QUOTES);
//                                    $data = [
//                                        'ajaxRequest' => [
//                                            'url' => "route('send-to-dir')",
//                                            'type' => 'POST',
//                                            'dataType' => 'json',
//                                            'data' => [
//                                                'path' => "$location",
//                                                'disk' => 'public'
//                                            ]
//                                        ],
//                                        'successResponse' => [
//                                            'redirect_url' => 'if present, redirect here'
//                                        ],
//                                        'errorResponse' => [
//                                            'message' => 'Error:',
//                                            'xhrResponse' => 'xhr.responseText'
//                                        ]
//                                    ];
//
//                                    $toJson = json_encode($data);
//                                    dd($toJson);
                                    return redirect()->route('file-manager.index');
                                }
                                return null;
                        }
                    })->html(),

                TextColumn::make('size')
                            ->label('Size (MB)'),

                DiskUsageColumn::make('disk_usage_indicator')
                ->label('Disk Usage')
                ->default(function($record) {
                    if ($record->disk_usage > 0 && $record->disk_usage < 1) {
                        $record->disk_usage *= 100;
                    } elseif ($record->disk_usage >= 1) {
                        $record->disk_usage *= 10;
                    }
                    $this->totalDiskUsage += floatval($record->size);
                    return $record->disk_usage;
                }),

            ]);
    }

    public function query(): Builder {
        return DiskUsage::queryForDiskAndPath($this->disk, $this->path);
    }

    public function getTotalDiskUsage() {
        $totalUsage = $this->query()->sum('size');
        return number_format(floatval($totalUsage), 2, '.', ',');
    }
}
