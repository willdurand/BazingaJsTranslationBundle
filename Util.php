<?php

namespace Bazinga\Bundle\JsTranslationBundle;

class Util
{
    /**
     * @param string $filename
     *
     * @return array
     */
    public static function extractCatalogueInformationFromFilename(string $filename)
    {
        [$domain, $locale, $extension] = explode('.', basename($filename), 3);

        // Domains using ICU Message Format suffix their names with "+intl-icu".
        // See https://symfony.com/doc/current/translation/message_format.html#using-the-icu-message-format
        if (false !== $icuPos = strpos($domain, '+intl-icu')) {
            $domain = substr($domain, 0, $icuPos);
        }

        return array($domain, $locale, $extension);
    }
}
