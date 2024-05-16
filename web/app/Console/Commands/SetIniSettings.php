<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Jelix\IniFile\IniModifier;

class SetIniSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:set-ini-settings {key} {value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $key = $this->argument('key');
        $value = $this->argument('value');

        $ini = new IniModifier('omega-config.ini');
        $ini->setValue($key, $value, 'omega');
        $ini->save();

    }
}
