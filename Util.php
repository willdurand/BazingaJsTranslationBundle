<?php

namespace Bazinga\Bundle\JsTranslationBundle;

class Util
{
    public static function extractCatalogueInformationFromFilename(string $filename): array
    {
        [$domain, $locale, $extension] = explode('.', basename($filename), 3);

        return [$domain, $locale, $extension];
    }

    public static function cleanDomain(string $domain): string
    {
        // remove domain suffix, ex `+intl-icu`
        if (false !== strpos($domain, '+')) {
            [$domain,] = explode('+', $domain, 2);
        }

        return $domain;
    }
}
