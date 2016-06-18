<?php

namespace Bazinga\Bundle\JsTranslationBundle\Finder;

use Symfony\Component\Finder\Finder;

class FinderFactory
{
    public function createNewFinder()
    {
        return new Finder();
    }
}
