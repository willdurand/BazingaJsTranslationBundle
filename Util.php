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
        $format = null;

        // Domains using ICU Message Format suffix their names with "+intl-icu".
        // See https://symfony.com/doc/current/translation/message_format.html#using-the-icu-message-format
        if (false !== strpos($domain, '+')) {
            [$domain, $format] = explode('+', $domain, 2);
        }

        return array($domain, $locale, $extension, $format);
    }
}
