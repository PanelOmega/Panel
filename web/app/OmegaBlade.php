<?php

namespace App;

use _PHPStan_9815bbba4\Nette\Neon\Exception;
use Illuminate\Support\Facades\Blade;

class OmegaBlade
{
    public static function render($file, $data = [])
    {
        $namespace = '';
        if (strpos($file, '::') !== false) {
            [$namespace, $file] = explode('::', $file);
        }

        $file = str_replace('.', '/', $file);
        $file = preg_replace('/\/([^\/]*)$/', '.$1', $file);
        $hints = app()->view->getFinder()->getHints();
        if (isset($hints[$namespace])) {
            $path = $hints[$namespace][0] . '/' . $file;
        } else {
            $viewPath = app()->view->getFinder()->getPaths()[0];
            $path = $viewPath . '/' . $file;
        }

        if (!is_file($path)) {
            throw new \Exception('File not found: ' . $path);
        }

        $content = file_get_contents($path);
        $compiled = Blade::render($content, $data);

        return $compiled;
    }

}
