<?php

namespace App;

trait HtaccessBuildTrait
{
    public function updateSystemFile($filePath, $newContent)
    {
        $existingContent = file_exists($filePath) ? file_get_contents($filePath) : '';
        $updatedContent = $this->replaceContentBetweenComments($existingContent, $newContent);
        file_put_contents($filePath, $updatedContent);
    }

    public function replaceContentBetweenComments($existingContent, $newContent)
    {
        $startComment = '# BEGIN PanelOmega-generated handler, do not edit';
        $endComment = '# END PanelOmega-generated handler, do not edit';

        $startInnerComment = $this->startComment ?? null;
        $endInnerComment = $this->endComment ?? null;

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

        $currentContent = preg_replace_callback(
            '/^#.*\n(?!#)/m',
            function ($matches) {
                return $matches[0] . "\t";
            },
            $currentContent
        );

        $existingContent = "$startComment\n$currentContent\n$endComment";
        return $existingContent;
    }
}
