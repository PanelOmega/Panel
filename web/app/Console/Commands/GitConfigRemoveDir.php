<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GitConfigRemoveDir extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:git-config-remove-dir {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes repo path to Git safe directories';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('path');
        $confFilePath = getenv('HOME') . '/.gitconfig';

        if (!file_exists($confFilePath)) {
            file_put_contents($confFilePath, '');
        }

        $gitPaths = [];
        $confContent = file_get_contents($confFilePath);
        $confLines = explode("\n", $confContent);



        foreach ($confLines as $line) {
            if ($line !== '[safe]' && $line !== '') {
                $gitPaths[] = trim($line);
            }
        }

        if (in_array("directory = {$path}", $gitPaths)) {
            $key = array_search($path, $gitPaths);
            unset($gitPaths[$key]);
        };

        $gitConfig = view('filament-customer.pages.git-version-control.git.update-gitconfig', [
            'gitPaths' => $gitPaths
        ])->render();

        $save = file_put_contents($confFilePath, $gitConfig);

        if ($save) {
            $this->info('The Git directory has been removed.');
        } else {
            $this->info('The Git directory has not been removed.');
        }
    }
}
