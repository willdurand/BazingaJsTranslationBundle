<?php

namespace Bazinga\Bundle\JsTranslationBundle;

/**
 * @author Dan Bettles <danbettles@yahoo.co.uk>
 */
class LocaleCode
{
    /**
     * @var string
     */
    const REG_EXP_LOCALE_CODE = '/^([a-z]{2})(?:[-_]([a-zA-Z]{2}))?$/';

    /**
     * @var string
     */
    private $string;

    /**
     * @var string
     */
    private $languageCode;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $symfonyString;

    public function __construct($code)
    {
        $this->setString($code);
    }

    private function setString($string)
    {
        $parts = array();
        $matched = (bool) preg_match(self::REG_EXP_LOCALE_CODE, $string, $parts);

        if (!$matched) {
            throw new \InvalidArgumentException('The locale code is invalid.');
        }

        $this->string = $string;

        $this->languageCode = $parts[1];
        $this->countryCode = count($parts) > 2 ? $parts[2] : null;

        //@todo Always uppercase the country code?  See the top of https://symfony.com/doc/current/book/translation.html.
        $this->symfonyString = $this->languageCode . (null === $this->countryCode ? '' : '_' . $this->countryCode);
    }

    public function getString()
    {
        return $this->string;
    }

    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    public function getCountryCode()
    {
        return $this->countryCode;
    }

    public function hasCountryCode()
    {
        return null !== $this->countryCode;
    }

    public function __toString()
    {
        return $this->getString();
    }

    public static function validateString($code)
    {
        return (bool) preg_match(self::REG_EXP_LOCALE_CODE, $code);
    }

    public function getSymfonyString()
    {
        return $this->symfonyString;
    }
}
