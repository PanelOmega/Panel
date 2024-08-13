<?php

namespace App;

trait HtConfigBuildTrait
{
    public function getHotlinkData($directory, $hotlinkProtectionData)
    {
        $currentDirectory = !$directory ? '/' : $directory;

        if ($hotlinkProtectionData && $currentDirectory === '/') {

            $urlAllowAccessArray = explode(',', $hotlinkProtectionData->url_allow_access);
            $urls = array_map(function ($url) {
                $url = trim($url);
                $parsedUrl = parse_url($url);

                return [
                    'protocol' => $parsedUrl['scheme'] ?? 'http',
                    'subdomain' => isset($parsedUrl['host']) ? explode('.', $parsedUrl['host'])[0] : '',
                    'domain' => isset($parsedUrl['host']) ? implode('.', array_slice(explode('.', $parsedUrl['host']), -2)) : '',
                    'full_url' => $url,
                ];
            }, $urlAllowAccessArray);

            $blockedExtensions = rtrim(preg_replace('/\s+/', '', $hotlinkProtectionData->block_extensions), ',');
            $redirectTo = $hotlinkProtectionData->redirect_to;

            return [
                'enabled' => $hotlinkProtectionData->enabled,
                'allow_direct_requests' => $hotlinkProtectionData->allow_direct_requests ? true : false,
                'url_allow_access' => $urls,
                'block_extensions' => $blockedExtensions,
                'redirect_to' => $redirectTo
            ];
        }
        return [];
    }

    public function getHtAccessFileConfig($params)
    {
        $label = $params['label'];
        $phpVersion = $params['phpVersion'];
        $htPasswdFilePath = $params['htPasswdFilePath'];
        $hotlinkData = $params['hotlinkData'];
        $htAccessView = $params['view'];

        $htaccessContent = view($htAccessView, [
            'phpVersion' => $phpVersion,
            'dPrivacyContent' => [
                'auth_name' => $label,
                'auth_user_file' => $htPasswdFilePath,
                'hotlinkData' => $hotlinkData
            ],
        ])->render();

        $htaccessContent = preg_replace_callback(
            '/(^\s*)(Rewrite.*|$)/m',
            function ($matches) {
                return str_repeat(' ', 4) . trim($matches[0]);
            },
            $htaccessContent
        );
        return $htaccessContent;
    }

    public function getHtPasswdFileConfig(array $params)
    {
        $htPasswdRecords = $params['htPasswdRecords'];
        $htPasswdView = $params['view'];

        $htpasswdContent = view($htPasswdView, [
            'dPrivacyContent' => $htPasswdRecords
        ])->render();

        $htpasswdContent = preg_replace_callback(
            '/(^\s*)(Rewrite.*|$)/m',
            function ($matches) {
                return str_repeat(' ', 4) . trim($matches[0]);
            },
            $htpasswdContent
        );

        return $htpasswdContent;
    }

    public function updateSystemFile($filePath, $newContent, array $innerComments = [])
    {
        $existingContent = file_exists($filePath) ? file_get_contents($filePath) : '';
        $updatedContent = $this->replaceContentBetweenComments($existingContent, $newContent, $innerComments);
        file_put_contents($filePath, $updatedContent);
    }

    public function replaceContentBetweenComments($existingContent, $newContent, array $innerComments)
    {
        $startComment = '# BEGIN PanelOmega-generated handler, do not edit';
        $endComment = '# END PanelOmega-generated handler, do not edit';

        $startInnerComment = $innerComments['start'] ?? null;
        $endInnerComment = $innerComments['end'] ?? null;

        $pattern = '/' . preg_quote($startComment, '/') . '(.*?)' . preg_quote($endComment, '/') . '/s';

        $currentContent = '';
        if (preg_match($pattern, $existingContent, $matches)) {
            $currentContent = trim($matches[1]);
        }

        $patternInnerComments = '/(' . preg_quote($startInnerComment, '/') . ')(.*?)(?=' . preg_quote($endInnerComment, '/') . ')/s';

        if (($startInnerComment && $endInnerComment) && preg_match($patternInnerComments, $currentContent, $matches)) {
            $innerCommentsContentAdd = trim($newContent);
            $currentContent = preg_replace($patternInnerComments, "$startInnerComment\n$innerCommentsContentAdd\n", $currentContent);
        } else {
            $innerCommentsContentAdd = trim($newContent);
            $currentContent .= "\n$startInnerComment\n$innerCommentsContentAdd\n$endInnerComment";
        }

        $existingContent = "$startComment\n$currentContent\n$endComment";
        return preg_replace('/(\n\s*\n)+/', "\n", $existingContent);
    }
}
