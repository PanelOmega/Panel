<?php

namespace App;

class GitClient
{
    public static function parseGitUrl($url)
    {
        $name = '';
        $owner = '';
        $provider = '';

        if (str_contains($url, ':') && str_contains($url, 'git@')) {
            $urlExploded = explode(':', $url);

            $provider = $urlExploded[0];
            $provider = str_replace('git@', '', $provider);

            $urlExploded = explode('/', $urlExploded[1]);
            $owner = $urlExploded[0];
            $name = str_replace('.git', '', $urlExploded[1]);

        } else if (str_contains($url, '.git')) {

            $parsedUrl = parse_url($url);

            $provider = $parsedUrl['host'];

            $urlExploded = explode('/', $parsedUrl['path']);

            $owner = $urlExploded[1];
            $name = str_replace('.git', '', $urlExploded[2]);

        } else {
            $parsedUrl = parse_url($url);

            $provider = $parsedUrl['host'];

            $urlExploded = explode('/', $parsedUrl['path']);

            $owner = $urlExploded[1];
            $name = $urlExploded[2];
        }

        return [
            'name' => $name,
            'owner' => $owner,
            'provider' => $provider
        ];
    }

    public static function checoutToCurrentBranch(string $path, string $branch)
    {
        $output = shell_exec("cd $path && git branch");
        $output = explode("\n", $output);

        $key = array_search(true, array_map(function ($b) {
            return strpos($b, '*') !== false;
        }, $output));

        $currentBranch = $output[$key] ?? null;

        if ($currentBranch) {
            $currentBranch = trim(ltrim($currentBranch, '* '));
            if ($currentBranch !== $branch) {
                shell_exec("cd $path && git checkout {$branch}");
            }
        }
    }
}
