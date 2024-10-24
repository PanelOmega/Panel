<?php

namespace App\FilamentCustomer\Pages\GitVersionControl;

use App\GitClient;
use App\Models\HostingSubscription\GitRepository;
use App\Models\HostingSubscription\GitSshKey;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GitVersionControlPage extends Page implements HasTable
{
    use InteractsWithTable, InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament-customer.pages.git-version-control.git-version-control-page';
    protected static ?string $title = 'Git™ Version Control';

    public ?array $sections = [];
    public ?array $formData = [];
    public function mount(): void
    {
        $this->sections = $this->getSections();
    }

    public function getSections(): array
    {
        return [
            'warning_text' => '<strong>Warning</strong>: Your system administrator <strong>must</strong> enable shell access to allow you to view clone URLs.',
            'title_text' => 'Create and manage Git™ repositories. You can use Git to maintain any set of files and track the history of changes from multiple editors (version control).
            For more information, read our documentation.'
        ];
    }

    public function query(): Builder
    {
        return GitRepository::query();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->query())
            ->columns([
                TextColumn::make('name')
                    ->label('Repository')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('repository_path')
                    ->label('Repository Path')
                    ->default(function ($record) {
                        return "{$record->domain->domain_root}/{$record->dir}";
                    })
                    ->sortable()
                    ->searchable()
            ])
            ->actions([
                Action::make('manage')
                    ->label('Manage')
                    ->icon('heroicon-o-wrench')
                    ->form([
                        Tabs::make('manage_tabs')
                            ->label('')
                            ->default(function($record) {
                                $record->setRepoData();
                            })
                            ->schema([
                                Tabs\Tab::make('basic_information')
                                    ->label('Basic Information')
                                    ->schema([
                                        Group::make()
                                            ->schema([
                                                TextInput::make('dir')
                                                    ->label('Repository Path')
                                                    ->default(function ($record) {
                                                        return "{$record->domain->domain_root}/$record->dir";
                                                    })
                                                    ->disabled()
                                                    ->suffixAction(function ($record) {
                                                        return \Filament\Forms\Components\Actions\Action::make("{$record->domain->domain_root}/$record->dir")
                                                            ->icon('heroicon-o-arrow-top-right-on-square')
                                                            ->url(function () {
                                                                return '#';
                                                            });
                                                    }),

                                                TextInput::make('name')
                                                    ->label('Repository Name')
                                                    ->default(function ($record) {
                                                        return $record->name;
                                                    })
                                                    ->afterStateUpdated(function ($state) {
                                                        $this->formData['name'] = $state;
                                                    }),

                                                Select::make('current_branch')
                                                    ->label('Checked-Out Branch')
                                                    ->live()
                                                    ->options(function ($record) {
                                                        return $record->getRepoBranches();
                                                    })
                                                    ->default(function ($record) {
                                                        return $record->branch;
                                                    })
                                                    ->afterStateUpdated(function ($state) {
                                                        $this->formData['branch'] = $state;
                                                    }),
                                            ]),

                                        Group::make()
                                            ->schema([
                                                Section::make('Currently Checked-Out Branch')
                                                    ->schema([
                                                        Actions::make([
                                                            Actions\Action::make('checked_out_branch')
                                                                ->label(function($record) {
                                                                    return $record->branch;
                                                                })
                                                                ->disabled()
                                                                ->icon('heroicon-o-arrow-top-right-on-square')
                                                                ->url(function () {
                                                                    return '#';
                                                                })
                                                        ])
                                                    ]),

                                                Textarea::make('commit_info')
                                                    ->label('HEAD Commit')
                                                    ->disabled()
                                                    ->default(function($record) {
                                                        return $this->getCommitInfo($record);
                                                    })
                                                    ->rows(5),

                                                Section::make('')
                                                    ->schema([
                                                        Actions::make([
                                                            Actions\Action::make('history')
                                                                ->label('History')
                                                                ->disabled()
                                                                ->icon('heroicon-o-arrow-top-right-on-square')
                                                                ->url(function () {
                                                                    return '#';
                                                                })
                                                        ]),
                                                    ]),

                                                TextInput::make('url')
                                                    ->label('Remote URL')
                                                    ->hint('The URL for the remote (cloned) repository.')
                                                    ->disabled()
                                                    ->default(function ($record) {
                                                        return $record->clone_from;
                                                    }),
                                            ]),

                                        Actions::make([
                                            Actions\Action::make('Update')
                                                ->action(function ($record) {
                                                    if (isset($this->formData['name'])) {
                                                        $record->name = $this->formData['name'];
                                                    }

                                                    if (isset($this->formData['branch'])) {
                                                        $record->branch = $this->formData['branch'];
                                                        $path = "{$record->domain->domain_root}/{$record->dir}";
                                                        GitClient::checoutToCurrentBranch($path, $record->branch);
                                                    }

                                                    Notification::make()
                                                        ->title("'{$record->name}' successfully updated.")
                                                        ->success()
                                                        ->send();
                                                })
                                                ->after(function ($record, $set) {
                                                    $record->setRepoData();
                                                    $set('commit_info', $this->getCommitInfo($record));
                                                    $set('deployment_script', $this->getDeploymentInfo($record));
                                                }),
                                        ])
                                    ])
                                    ->columns(2),

                                Tabs\Tab::make('pull_or_deploy')
                                    ->label('Pull Or Deploy')
                                    ->schema([
                                        Group::make()
                                            ->schema([
                                                TextInput::make('dir')
                                                    ->label('Repository Path')
                                                    ->default(function ($record) {
                                                        return "{$record->domain->domain_root}/$record->dir";
                                                    })
                                                    ->disabled()
                                                    ->suffixAction(function ($record) {
                                                        return Actions\Action::make("{$record->domain->domain_root}/$record->dir")
                                                            ->icon('heroicon-o-arrow-top-right-on-square')
                                                            ->url(function () {
                                                                return '#';
                                                            });
                                                    }),

                                                TextInput::make('url')
                                                    ->label('Remote URL')
                                                    ->disabled(),

                                                Section::make('Currently Checked-Out Branch')
                                                    ->schema([
                                                        Actions::make([
                                                            Actions\Action::make('checked_out_branch')
                                                                ->label(function($record) {
                                                                    return $record->branch;
                                                                })
                                                                ->disabled()
                                                                ->icon('heroicon-o-arrow-top-right-on-square')
                                                                ->url(function () {
                                                                    return '#';
                                                                })
                                                        ])
                                                    ]),

                                                Textarea::make('commit_info')
                                                    ->label('HEAD Commit')
                                                    ->disabled()
                                                    ->default(function ($record) {
                                                        return $this->getCommitInfo($record);
                                                    })
                                                    ->rows(5),

                                            ]),

                                        Group::make()
                                            ->schema([
                                                Textarea::make('deployment_script')
                                                    ->label('Last Deployment Information')
                                                    ->default(function ($record) {
                                                       return $this->getDeploymentInfo($record);
                                                    })
                                                    ->rows(5)
                                                    ->disabled(),
                                            ]),

                                        Actions::make([
                                            Actions\Action::make('update_from_remote')
                                                ->label('Update From Remote')
                                                ->icon('heroicon-o-cloud-arrow-down')
                                                ->action(function ($record) {
                                                    $record->pull();
                                                    $logData = $record->getLog();;
                                                    $lines = explode("\n", trim($logData));
                                                    $pullStatus = end($lines);

                                                    Notification::make()
                                                        ->title(rtrim($pullStatus, '<br />'))
                                                        ->success()
                                                        ->send();

                                                })
                                                ->after(function ($record, $set) {
                                                    $record->setRepoData();
                                                    $set('commit_info', $this->getCommitInfo($record));
                                                    $set('deployment_script', $this->getDeploymentInfo($record));
                                                }),

                                            Actions\Action::make('deploy_head_commit')
                                                ->label('Deploy HEAD Commit')
                                                ->action(function ($record) {
                                                    $record->push();

                                                    Notification::make()
                                                        ->title('Deployment successful')
                                                        ->success()
                                                        ->send();

                                                })
                                                ->after(function ($record, $set) {
                                                    $record->setRepoData();
                                                    $set('commit_info', $this->getCommitInfo($record));
                                                    $set('deployment_script', $this->getDeploymentInfo($record));
                                                }),
                                        ])
                                    ])
                            ])
                            ->columns(2)
                    ])
                    ->modalFooterActions(function() {
                        return [];
                    }),

                Action::make('history')
                    ->label('History')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(function () {
                        return '#';
                    }),

                DeleteAction::make('delete')
                    ->label('Remove')
                    ->icon('heroicon-o-trash')
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Create')
                    ->form([
                        Section::make('')
                            ->schema([
                                Group::make()
                                    ->schema([
                                        Toggle::make('clone_repo')
                                            ->label('Clone Repository')
                                            ->default(true)
                                            ->live()
                                            ->helperText('Enable this toggle if you want to clone a remote repository, or disable this toggle to create a new repository.'),

                                        TextInput::make('url')
                                            ->label('Clone URL')
                                            ->helperText('Enter the clone URL for the remote repository. All clone URLs must begin with the http://, https://, ssh://, or git:// protocols or begin with a username and domain.')
                                            ->visible(fn($get) => $get('clone_repo'))
                                            ->live()
                                            ->rules('required', 'regex:/^(https?:\/\/|ssh:\/\/|git:\/\/|[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,})/')
                                            ->afterStateUpdated(function ($state) {
                                                if ($state) {
                                                    $this->formData['url'] = $state;
                                                }
                                            }),

                                        TextInput::make('dir')
                                            ->label('Repository Path')
                                            ->hint('Enter the desired path for the repository’s directory. If you enter a path that does not exist, the system will create the directory when it creates or clones the repository.')
                                            ->prefix(function () {
                                                return GitRepository::getDirectoryPath();
                                            })
                                            ->helperText('The path cannot contain the “./” and “../” directory references, whitespace, or the following characters: \ * | " \' < > & @ ` $ { } [ ] ( ) ; ? : = % #')
                                            ->live()
                                            ->required()
                                            ->afterStateUpdated(function ($state) {
                                                if ($state) {
                                                    $this->formData['dir'] = $state;
                                                }
                                            }),

                                        TextInput::make('name')
                                            ->label('Repository Name')
                                            ->hint('This name does not impact functionality, and instead functions only as a display name.')
                                            ->helperText('The repository name may not include the “<” and “>” characters.')
                                            ->live()
                                            ->required()
                                            ->afterStateUpdated(function ($state) {
                                                if ($state) {
                                                    $this->formData['name'] = $state;
                                                }
                                            })
                                    ]),

                                Group::make()
                                    ->schema([

                                        Section::make('Related Links')
                                            ->schema([
                                                Actions::make([
                                                    Actions\Action::make('ssh_access')
                                                        ->label('SSH Access')
                                                        ->icon('heroicon-o-arrow-top-right-on-square')
                                                        ->url(function () {
                                                            return route('filament.customer.resources.git-ssh-keys.index');
                                                        })
                                                        ->openUrlInNewTab(),
                                            ]),
                                        ]),

                                        Select::make('git_ssh_key_id')
                                            ->label('SSH Key')
                                            ->options(fn () => GitSshKey::pluck('name', 'id'))
                                            ->columnSpanFull()
                                            ->afterStateUpdated(function($state) {
                                                $this->formData['git_ssh_key_id'] = $state;
                                            }),
                                    ])
                            ])->columns(2)

                    ])
                    ->action(function () {
                        $repo = new GitRepository();
                        $repo->name = $this->formData['name'];
                        $repo->url = $this->formData['url'] ?? '';
                        $repo->dir = ltrim($this->formData['dir'], '/');
                        $repo->git_ssh_key_id = $this->formData['git_ssh_key_id'] ?? null;
                        $repo->save();

                        Notification::make()
                            ->title('Repository Created')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitActionLabel('Create')
            ]);
    }

    public function getCommitInfo($record)
    {
        return empty($record->author) ? 'No branch information available!' : (
            'Commit: ' . $record->last_commit_hash . "\n" .
            'Author: ' . $record->author . "\n" .
            'Date: ' . $record->last_commit_date . "\n" .
            $record->last_commit_message . "\n"
        );
    }

    public function getDeploymentInfo($record)
    {
        $logData = $record->getLog();
        $logData = str_replace('<br />', '', $logData);
        return $logData;
    }

}
