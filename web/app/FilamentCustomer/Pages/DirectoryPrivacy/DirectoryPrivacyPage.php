<?php

namespace App\FilamentCustomer\Pages\DirectoryPrivacy;

use App\Models\Customer;
use App\Models\HostingSubscription\DirectoryPrivacy;
use App\Models\HostingSubscription\DirectoryPrivacyBrowse;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
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

class DirectoryPrivacyPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament-customer.pages.directory-privacy.directory-privacy-page';

    protected static ?string $title = 'Directory Privacy';
    public array $sections;
    #[Url(except: '')]
    public string $path = '';
    protected string $disk = 'public';
    protected $listeners = ['updatePath' => '$refresh'];

    public function table(Table $table): Table
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $this->disk = "/home/{$hostingSubscription->system_username}/public_html";

        $storage = Storage::build([
            'driver' => 'local',
            'throw' => false,
            'root' => $this->disk,
        ]);

        return $table
            ->heading($this->path ?: 'Root')
            ->query(
                DirectoryPrivacyBrowse::queryForDiskAndPath($this->disk, $this->path)
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
                    ->action(function (DirectoryPrivacyBrowse $record) {
                        if ($record->isFolder()) {
                            $this->path = $record->path;

                            $this->dispatch('updatePath');
                        }
                    })
                    ->sortable(),

                TextColumn::make('protected')
                    ->label('Protected')
                    ->icon(function ($state) {
                        if ($state === 'Yes') {
                            return 'heroicon-o-lock-closed';
                        }
                        return 'heroicon-o-lock-open';
                    })
            ])
            ->actions([
                EditAction::make('edit')
                    ->label('EDIT')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        Checkbox::make('protected')
                            ->label('Password protect this directory')
                            ->live()
                            ->default(false),

                        \Filament\Forms\Components\Group::make([
                            TextInput::make('label')
                                ->label('Enter a name for the protected directory'),

                            Section::make('Create user')
                                ->label('Create User')
                                ->schema([
                                    TextInput::make('username')
                                        ->label('Username')
                                        ->rules([
                                            \Illuminate\Validation\Rule::unique('directory_privacies', 'username')
                                                ->where(function ($query) {
                                                    $directory = request()->input('directory');

                                                    if ($directory) {
                                                        $query->where('directory', $directory);
                                                    }
                                                })
                                        ])
                                        ->required(),

                                    TextInput::make('password')
                                        ->label('Password')
                                        ->password()
                                        ->revealable()
                                        ->afterStateHydrated(function ($set, $state, $record) {
                                            if ($record && $record->exists) {
                                                $set('password', '');
                                            }
                                        })
                                        ->hintAction(
                                            \Filament\Forms\Components\Actions\Action::make('generate_password')
                                                ->icon('heroicon-m-key')
                                                ->action(function (Set $set) {
                                                    $randomPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+'), 0, 20);
                                                    $set('password', $randomPassword);
                                                    $set('password_confirmation', $randomPassword);
                                                })
                                        )
                                        ->required()
                                ])
                                ->columns(2),

                            Section::make('Authorized Users')
                                ->schema([
                                    Repeater::make('authorized_usernames')
                                        ->label('')
                                        ->schema([
                                            TextInput::make('username')
                                                ->label('User')
                                                ->reactive()
                                                ->disabled(),

                                        ])
                                        ->addable(fn() => false)
                                        ->reorderable(false)
                                        ->afterStateHydrated(function ($set, $state, $record) {

                                            $authorizedUsernames = $this->getAuthorizedUsernames();
                                            if (!empty($authorizedUsernames)) {
                                                $set('authorized_usernames', $authorizedUsernames);
                                            }
                                        })
                                        ->afterStateUpdated(function ($set, $state, $record) {
                                            $authorizedUsernames = $this->getAuthorizedUsernames();
                                            $currentUsernames = array_column($state, 'username');
                                            $previousUsernames = array_column($authorizedUsernames, 'username');
                                            $deletedUsername = array_diff($previousUsernames, $currentUsernames);
                                            $deletedUsername = array_shift($deletedUsername);
                                            $directoryPrivacy = DirectoryPrivacy::where('username', $deletedUsername)->first();
                                            if ($directoryPrivacy) {
                                                $directoryPrivacy->delete();
                                            }
                                        }),
                                ])
                        ])
                            ->hidden(function (Get $get) {
                                return !$get('protected');
                            }),
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
                        $records->each(fn(DirectoryPrivacyBrowse $record) => $record->delete());
                        $action->sendSuccessNotification();
                    }),
            ])
            ->checkIfRecordIsSelectableUsing(fn(DirectoryPrivacyBrowse $record): bool => !$record->isPreviousPath())
            ->headerActions([
                Action::make('home')
                    ->label('Home')
                    ->icon('heroicon-o-home')
                    ->action(function () {
                        return redirect('customer/directory-privacy-page');
                    })
            ]);
    }

    public function getAuthorizedUsernames()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $directoryPrivacy = DirectoryPrivacy::where('hosting_subscription_id', $hostingSubscription->id)->get();

        $users = [
            'authorized_usernames' => $directoryPrivacy->map(function ($dPrivacy) {
                return [
                    'username' => $dPrivacy->username
                ];
            })->toArray()
        ];
        $users['authorized_usernames'] = array_filter($users['authorized_usernames'], function ($item) {
            return !empty($item['username']);
        });

        return $users['authorized_usernames'];
    }

    public function mount(): void
    {
        $this->sections = $this->getSections();
    }

    public function getSections(): array
    {
        return [
            'section' => [
                'Set a password to protect certain directories of your account. When you enable this feature, a user that tries to open a protected folder will be prompted to enter a username and password before they can access your content.
                    For more information, read our documentation.',
                'Click a folder’s icon or name to navigate the file system. To select a folder, click “Edit”.'
            ]
        ];
    }
}
