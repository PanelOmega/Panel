<?php

namespace App\Filament\Enums;

use Filament\Support\Contracts\HasLabel;
use JaOcero\RadioDeck\Contracts\HasDescriptions;
use JaOcero\RadioDeck\Contracts\HasIcons;

enum ServerApplicationType: string implements HasLabel, HasDescriptions, HasIcons
{
    case APACHE_PHP = 'apache_php';
    case APACHE_NODEJS = 'apache_nodejs';
    case APACHE_PYTHON = 'apache_python';
    case APACHE_RUBY = 'apache_ruby';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::APACHE_PHP => 'Apache + PHP (FCGI)',
            self::APACHE_NODEJS => 'Apache + Node.js',
            self::APACHE_PYTHON => 'Apache + Python',
            self::APACHE_RUBY => 'Apache + Ruby',
        };
    }

    public function getDescriptions(): ?string
    {
        return match ($this) {
            self::APACHE_PHP => 'Install applications like WordPress, Joomla, Drupal and more.',
            self::APACHE_NODEJS => 'Install applications like Ghost, KeystoneJS, and more.',
            self::APACHE_PYTHON => 'Install applications like Django, Flask, and more.',
            self::APACHE_RUBY => 'Install applications like Ruby on Rails, Sinatra, and more.',
        };
    }

    public function getIcons(): ?string
    {
        return match ($this) {
            self::APACHE_PHP => 'omega-php',
            self::APACHE_NODEJS => 'omega-nodejs',
            self::APACHE_PYTHON => 'omega-python',
            self::APACHE_RUBY => 'omega-ruby',
        };
    }
}
