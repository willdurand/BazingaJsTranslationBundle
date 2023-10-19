<?php

namespace Bazinga\Bundle\JsTranslationBundle;

use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;

class Util
{
    public static function cleanDomain(string $domain): string
    {
        // remove domain suffix, ex `+intl-icu`
        if (false !== strpos($domain, '+')) {
            [$domain,] = explode('+', $domain, 2);
        }

        return $domain;
    }

    /**
     * Retrieve messages from translator bag.
     *
     * @param TranslatorBagInterface $translatorBag
     * @param string $locale
     * @param string $domain
     * @return array 2D array, where first-level keys are domain without suffixes, and domain with '+intl-icu' suffix. Second level keys are translation keys.
     */
    public static function getMessagesFromTranslatorBag(TranslatorBagInterface $translatorBag, string $locale, string $domain): array
    {
        $cleanDomain = self::cleanDomain($domain);

        $messageCatalogue = $translatorBag->getCatalogue($locale);
        $allMessages = $messageCatalogue->all($cleanDomain);
        $icuMessages = $messageCatalogue->all($cleanDomain . MessageCatalogueInterface::INTL_DOMAIN_SUFFIX);
        $nonIcuMessages = array_diff($allMessages, $icuMessages);

        $translations = [];

        if (!empty($icuMessages)) {
            $translations[$cleanDomain . MessageCatalogueInterface::INTL_DOMAIN_SUFFIX] = $icuMessages;
        }
        if (!empty($nonIcuMessages)) {
            $translations[$cleanDomain] = $nonIcuMessages;
        }

        return $translations;
    }
}
