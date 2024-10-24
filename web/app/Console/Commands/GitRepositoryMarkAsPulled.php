<?php

namespace App\Console\Commands;

use App\Models\HostingSubscription\GitRepository;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GitRepositoryMarkAsPulled extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'omega:git-repository-mark-as-pulled {id}';

    /**
     * The console command description.
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');

        $repository = GitRepository::find($id);
        if (!$repository) {
            $this->error('Repository not found.');
            return;
        }

        $repository->status = GitRepository::STATUS_UP_TO_DATE;
        if($repository->save()) {
            $this->info('Status updated');
        } else {
            $this->info('Status not updated');
        }
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
            ['id', InputArgument::REQUIRED, 'Git repository ID.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
