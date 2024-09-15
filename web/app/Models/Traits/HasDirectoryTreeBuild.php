<?php

namespace App\Models\Traits;

use App\Models\Customer;

trait HasDirectoryTreeBuild
{
    public static function buildDirectoryTree()
    {
        $customer = Customer::getHostingSubscriptionSession();
        $username = $customer['system_username'];
        $baseDir = '/home/' . $username;

        $directoryTree = [];

        if (!is_dir($baseDir)) {
            return $directoryTree;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $path => $fileInfo) {
            if ($fileInfo->isDir()) {
                $relativePath = str_replace($baseDir, '', $path);
                $relativePath = trim($relativePath, '/');
                $pathParts = explode('/', $relativePath);

                $currentLevel = &$directoryTree;
                foreach ($pathParts as $part) {
                    if (!isset($currentLevel[$part])) {
                        $currentLevel[$part] = [
                            'name' => $part,
                            'value' => $relativePath,
                            'children' => []
                        ];
                    }
                    $currentLevel = &$currentLevel[$part]['children'];
                }
            }
        }
        $treeToFormat[] = isset($directoryTree['public_html']) ? $directoryTree['public_html'] : [];
        $tree = self::_formatTree($treeToFormat);
        return $tree;
    }

    private static function _formatTree(array $tree)
    {
        $formatted = [];
        foreach ($tree as $node) {
            $children = !empty($node['children']) ? self::_formatTree($node['children']) : [];
            $formatted[] = [
                'name' => $node['name'],
                'value' => $node['value'],
                'children' => $children
            ];
        }
        return $formatted;
    }
}
