<?php

namespace App\FilamentCustomer\Pages\Indexes;

use App\Models\Customer;
use App\Models\FolderItem;
use App\Server\SupportedApplicationTypes;
use Filament\Forms\Components\Radio;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;


class IndexesPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament-customer.pages.indexes.indexes-page';

    protected static ?string $title = 'Indexes';
    public array $sections;

    #[Url(except: '')]
    public string $path = '';
    protected string $disk = 'public';
    protected $listeners = ['updatePath' => '$refresh'];

    public function mount(): void
    {
        $this->sections = $this->getSections();
    }

    public function getSections(): array
    {
        return [
            'subtitle' => 'Example Index Files',
            'section_title' => 'The “Index Manager” allows you to customize the way a directory appears when no index files reside in a directory. Click a directory’s icon or name to navigate the file system. To select a folder, click “Edit”.',
            'section_subtitle' => 'index.php index.php5 index.php4 index.php3 index.perl index.pl index.plx index.ppl index.cgi index.jsp index.jp
                index.phtml index.shtml index.xhtml index.html index.htm index.wml Default.html Default.htm default.html default.htm home.html home.htm index.js'
        ];
    }

    public function table(Table $table): Table
    {

        $hostingSubscription = Customer::getHostingSubscriptionSession();

        $this->disk = "/home/$hostingSubscription->system_username";

        $storage = Storage::build([
            'driver' => 'local',
            'throw' => false,
            'root' => $this->disk,
        ]);

        return $table
            ->heading($this->path ?: 'Root')
            ->query(
                FolderItem::queryForDiskAndPath($this->disk, $this->path)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('directory')
                    ->icon(fn($record): string => match ($record->directory) {
                        'Up One Level' => 'heroicon-o-arrow-turn-left-up',
                        default => 'heroicon-o-folder'
                    })
                    ->iconColor(fn($record): string => match ($record->type) {
                        'Folder' => 'warning',
                        default => 'gray',
                    })
                    ->action(function (FolderItem $record) {
                        if ($record->isFolder()) {
                            $this->path = $record->path;

                            $this->dispatch('updatePath');
                        }
                    })
                    ->sortable(),

                TextColumn::make('index_type')
                    ->label('Index Type'),

            ])
            ->actions([
                EditAction::make('edit')
                    ->label('EDIT')
                    ->icon('heroicon-o-pencil-square')
                    ->form([

                        Radio::make('index_type')
                            ->label('Set Indexing Settings for all directories.')
                            ->options(SupportedApplicationTypes::getIndexesIndexTypes())
                            ->default(function (FolderItem $record) {
                                return $record->index_type;
                            })
                            ->live()
                            ->helperText(function ($state) {
                                $indexTypes = [
                                    'inherit' => 'Select this mode to use the parent directory’s setting. If the index settings are not defined in the parent directory, the system will use its default settings.',
                                    'no_indexing' => 'No files will appear for this directory if a default file is missing.',
                                    'show_filename_only' => 'This mode shows a simple list of the files present if the default file is missing.',
                                    'show_filename_and_description' => 'This mode shows a list of files and their attributes: file size and file type.',
                                ];

                                $helperTexts = $indexTypes;
                                return $helperTexts[$state] ?? '';
                            })
                    ])

            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->successNotificationTitle('Files deleted')
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, BulkAction $action) {
                        $records->each(fn(FolderItem $record) => $record->delete());
                        $action->sendSuccessNotification();
                    }),
            ])
            ->checkIfRecordIsSelectableUsing(fn(FolderItem $record): bool => !$record->isPreviousPath())
            ->headerActions([
//                Action::make('settings')
//                    ->label('Settings')
//                    ->icon('heroicon-o-cog-8-tooth')
//                    ->form([
//                        Radio::make('option')
//                            ->options([
//                                'Home' => 'Home',
//                                'Web Root (public_html or www)' => 'Web Root (public_html or www)',
//                                'Document Root for' => 'Document Root for',
//                            ])
//                            ->default('Home')
//                            ->live(),
//
//                        Select::make('domain')
//                            ->options([
//                                'domain' => 'domain'
//                            ])
//                            ->disabled(fn($state) => !isset($state['option']) || $state['option'] !== 'Document Root for'),
//
//                        Checkbox::make('open_directory')
//                            ->label('Always open this deirectory in the future')
//                            ->default(false),
//                    ])
//                    }),

                Action::make('home')
                    ->label('Home')
                    ->icon('heroicon-o-home')
                    ->action(function () {
                        return redirect('customer/indexes-page');
                    })
            ]);
    }
}
