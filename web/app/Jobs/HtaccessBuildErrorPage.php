<?php

namespace App\Jobs;

use App\Jobs\Traits\ErrorCodeDefaultContentTrait;
use App\Jobs\Traits\HtaccessBuildTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HtaccessBuildErrorPage
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtaccessBuildTrait, ErrorCodeDefaultContentTrait;

    public $fixPermissions = false;
    public $errorPagePath;
    public $startComment = '# Section managed by Panel Omega: Error Pages, do not edit';
    public $endComment = '# End section managed by Panel Omega: Error Pages';

    public function __construct($fixPermissions = false, $errorPagePath)
    {
        $this->fixPermissions = $fixPermissions;
        $this->errorPagePath = $errorPagePath;
    }

    public function handle($model)
    {
        $this->addErrorPageToSystem($model);
        $errorCodes = $this->getAllErrorCodes();
        $htaccessView = $this->getHtaccessErrorCodesConfig($errorCodes);
        $htaccessSystemPath = $this->errorPagePath . '/.htaccess';
        $this->updateSystemFile($htaccessSystemPath, $htaccessView);
    }

    public function addErrorPageToSystem($model)
    {
        $errorCode = $this->getErrorCode($model->name);
        $errorPagePath = "{$this->errorPagePath}/{$errorCode}.shtml";
        $errorPageContent = $this->trimContent($model->content);
        file_put_contents($errorPagePath, $errorPageContent);
    }

    public function getErrorCode($errorPage)
    {
        if (preg_match('/^\d+/', $errorPage, $matches)) {
            return $matches[0];
        }
        throw new \Exception("Error page \"{$errorPage}\" does not exist.");
    }

    public function trimContent($content)
    {
        $content = html_entity_decode($content);
        $content = str_replace("\u{A0}", ' ', $content);
        return trim($content);
    }

    public function getAllErrorCodes(): array
    {
        $errorCodes = [];
        foreach (scandir($this->errorPagePath) as $page) {
            if (preg_match('/^\d+$/', pathinfo($page, PATHINFO_FILENAME)) && pathinfo($page, PATHINFO_EXTENSION) === 'shtml') {
                $errorCodes[] = pathinfo($page, PATHINFO_FILENAME);
            }
        }
        return $errorCodes;
    }

    public function getHtaccessErrorCodesConfig($errorCodes)
    {
        $htaccessErrorCodesContent = view('server.samples.apache.php.error-page-htaccess', [
            'errorCodes' => $errorCodes,
            'errorPagePath' => $this->errorPagePath,
        ])->render();

        $htaccessErrorCodesContent = preg_replace_callback(
            '/^.*$/m',
            function ($matches) {
                return preg_replace('/\s+/', ' ', trim($matches[0]));
            },
            $htaccessErrorCodesContent
        );
        $htaccessErrorCodesContent = preg_replace('/^\s*[\r\n]/m', '', $htaccessErrorCodesContent);
        return $htaccessErrorCodesContent;
    }

    public function getErrorPageContent($errorPage)
    {
        $errorCode = $this->getErrorCode($errorPage);

        $errorPagePath = "{$this->errorPagePath}/{$errorCode}.shtml";
        $content = null;
        if (file_exists($errorPagePath)) {
            $content = file_get_contents($errorPagePath);
        }
        if (empty($content) || $content == '') {
            $content = $this->getErrorCodeDefaultContent($errorCode);
        }
        return $content;
    }
}
