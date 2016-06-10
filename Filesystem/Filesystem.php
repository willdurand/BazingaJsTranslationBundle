<?php

namespace Bazinga\Bundle\JsTranslationBundle\Filesystem;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;

class Filesystem extends BaseFilesystem
{
    public function getContents($filepath)
    {
        $content = @file_get_contents($filepath);

        if (false === $content) {
            throw new IOException(
                sprintf('Could not get the content of %s', $filepath)
            );
        }

        return $content;
    }
}
