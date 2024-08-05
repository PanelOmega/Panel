<?php

namespace App\FilamentCustomer\Pages;

use Filament\Pages\Page;

class HotlinkProtection extends Page
{
    protected static string $view = 'filament.customer.pages.hotlink-protection';

    protected static bool $shouldRegisterNavigation = false;

    public string $mainTitle;
    public array $sections;

    public function mount(): void
    {
        $this->mainTitle = 'Hotlink Protecition';
        $this->sections = [
            [
                'helperTexts' => 'Hotlink protection prevents other websites from directly linking to files
                                  (as specified below) on your website. Other sites will still be able to link to any file type that you don’t specify below
                                  (ie. html files). An example of hotlinking would be using a \<img\> tag to display an image from your
                                  site from somewhere else on the net. The end result is that the other site is stealing your bandwidth. List all sites below from which you
                                  wish to allow direct links. This system attempts to add all sites it knows you own to the list; however, you may need to add others.'
            ],
            [
                'title' => 'Configure Hotlink Protection',
            ]
        ];
    }
}
