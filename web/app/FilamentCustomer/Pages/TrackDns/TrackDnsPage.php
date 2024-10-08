<?php

    namespace App\FilamentCustomer\Pages\TrackDns;


    use App\Models\HostingSubscription\TrackDns;
    use Filament\Actions\Action;
    use Filament\Forms\Components\Actions;
    use Filament\Forms\Components\Section;
    use Filament\Forms\Components\Textarea;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Components\View;
    use Filament\Forms\Concerns\InteractsWithForms;
    use Filament\Forms\Form;
    use Filament\Pages\Page;
    use Filament\Tables\Columns\TextColumn;
    use Filament\Tables\Table;
    use Illuminate\Database\Query\Builder;
    use Illuminate\Support\Facades\Session;

    class TrackDnsPage extends Page
    {
        use InteractsWithForms;

        protected static bool $shouldRegisterNavigation = false;
        protected static ?string $title = 'Track DNS';
        protected static ?string $model = TrackDns::class;
        protected static string $view = 'filament-customer.pages.track-dns.track-dns-page';
        public ?array $sections = [];
        public ?string $host = '';
        public ?array $result = [];

        public ?bool $isValidDomain = null;

        public function mount(): void {
            $this->sections = $this->getSections();
        }

        public function getSections(): array
        {
            return [
                'section_title' => [
                    'Network Tools allow a user to find out information about any domain, or to trace the route from the server your site is on to the computer you are accessing PanelOmega from.
                    Finding out information about a domain can be useful in making sure your DNS is set up properly as you will find out information about your IP address as well as your DNS.'
                ],
                'subtitle_lookup' => [
                    'Domain Lookup'
                ],
                'section_subtitle_lookup' => [
                    'The Domain Lookup tool allows you to find out the IP address of any domain, as well as DNS information about that domain.
                    This can be a very useful tool right after your site is set up or after DNS changes have been made to make sure your DNS is setup properly.',
                ],

                'subtitle_trace' => [
                  'Trace Route'
                ],
                'section_subtitle_trace' => [
                    'This function allows you to trace the route from the computer you are accessing PanelOmega from to the
                    server your site is on (i.e. the number of servers and what servers your data must pass through to get to your site).'
                ]
            ];
        }

        public function form(Form $form):Form
        {
            return $form
                ->schema([

                    View::make('filament-customer.pages.track-dns.track-dns-lookup-section'),

                        TextInput::make('host')
                            ->label('Enter a domain to look up:')
                            ->live()
                            ->required()
                            ->hint(function($state) {
                                if(!empty($state)) {
                                    return $this->isValidDomain ? '' : 'Invalid domain!';
                                }

                                return '';
                            })
                            ->hintColor('danger')
                            ->afterStateUpdated(function($set, $state) {
//                                $pattern = '/^[a-zA-Z0-9-]{1,63}\.[a-zA-Z]{2,}$/';
//                                $this->isValidDomain = preg_match($pattern, $state);
                                $this->isValidDomain = true;
                                if (!$this->isValidDomain) {
                                    $set('isValidDomain', false);
                                } else {
                                    $set('host', $state);
                                }

                            })
                            ->maxWidth('xl'),

                    Actions::make([
                        Actions\Action::make('look_up')
                            ->label('Look Up')
                            ->modalHeading('Domain Look Up')
                            ->disabled(fn() => !$this->isValidDomain)
                            ->modalSubmitAction(false)
                            ->modalCancelAction()
                            ->form([
                                Section::make('Ip Details')
                                ->schema([
                                    TextArea::make('ip_and_mx_data')
                                        ->label('')
                                        ->default(function() {
                                            $this->result = TrackDns::getDomainAddresses($this->host);
                                            $content = '';
                                            foreach($this->result as $res) {
                                                $content .= $res . "\n";
                                            }
                                            return $content;
                                        })
                                        ->rows(4)
                                        ->disabled(),
                                ]),
                                Section::make('Zone Information')
                                    ->schema([
                                        Textarea::make('zone_information')
                                            ->label('')
                                            ->default(function() {
                                                $this->result = TrackDns::getDomainZoneInformation($this->host);
                                                $content = '';
                                                foreach ($this->result as $res) {
                                                    $content .= $res . "\n";
                                                }
                                                return $content;
                                            })
                                            ->rows(10)
                                            ->disabled(),
                                    ])
                            ])
                    ]),

                    View::make('filament-customer.pages.track-dns.track-dns-trace-section'),

                    Actions::make([
                        Actions\Action::make('trace')
                        ->label('Trace')
                            ->form([
                                Textarea::make('')
                                    ->label(function() {
                                        return TrackDns::getHostData() ?? '';
                                    })
                                    ->default(function() {
                                        Session::put('host', $this->host);
                                        $track = new TrackDns();
                                        $rows = $track->getRows();
                                        return implode("\n", array_map(function($row) {
                                            return "ID: {$row['id']}, Trace: {$row['trace']}";
                                        }, $rows));
                                    })
                                    ->disabled()
                                    ->rows(10)
                            ])
                        ->modalSubmitAction(false)
                    ])
                ]);
        }
    }
