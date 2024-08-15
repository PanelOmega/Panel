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

        $startInnerComment = $this->startComment ?? null;
        $endInnerComment = $this->endComment ?? null;

        if ($startInnerComment && $endInnerComment) {
            $escapedStartComment = preg_quote($startInnerComment, '/');
            $escapedEndComment = preg_quote($endInnerComment, '/');

            $newContent = $this->trimContent($newContent);

            if(empty($newContent)) {
                return $this->removeCommentsUponDeletion($escapedStartComment, $escapedEndComment, $existingContent);
            }

            $patternInnerComments = '/(' . $escapedStartComment . ')(.*?)(?=' . $escapedEndComment . ')/s';

            if (preg_match($patternInnerComments, $existingContent)) {
                $innerCommentsContentAdd = trim($newContent);
                $currentContent = preg_replace($patternInnerComments, "$startInnerComment\n$innerCommentsContentAdd\n", $existingContent);
            } else {
                $innerCommentsContentAdd = trim($newContent);
                $currentContent = $existingContent . "\n$startInnerComment\n$innerCommentsContentAdd\n$endInnerComment";
            }
        } else {
            $innerCommentsContentAdd = trim($newContent);
            $currentContent = $existingContent . "\n$innerCommentsContentAdd";
        }

        return $this->trimContent($currentContent);
    }

    public function trimContent($content) {
        $content = preg_replace('/<!--\[if.*?\]>\s*<!\[endif\]-->\s*/s', '', $content);
        $content = preg_replace('/^[ \t]+/m', '', $content);
        $content = preg_replace('/^[\r\n]+/', '', $content);
        return $content;
    }

    public function removeCommentsUponDeletion($escapedStartComment, $escapedEndComment, $existingContent) {

        $patternRemoveComments = '/' . $escapedStartComment . '.*?' . $escapedEndComment . '/s';
        $existingContent = preg_replace($patternRemoveComments, '', $existingContent);
        return $this->trimContent($existingContent);
    }
}
