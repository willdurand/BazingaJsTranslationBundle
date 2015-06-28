<?php

namespace Bazinga\JsTranslationBundle\Tests;

use Bazinga\Bundle\JsTranslationBundle\Tests\WebTestCase;
use Bazinga\Bundle\JsTranslationBundle\LocaleCode;

/**
 * @author Dan Bettles <danbettles@yahoo.co.uk>
 */
class LocaleCodeTest extends WebTestCase
{
    public function testIsInstantiable()
    {
        $localeCode = new LocaleCode('en-GB');

        $this->assertSame('en-GB', $localeCode->getString());
    }

    public static function providesInvalidLocaleCodeStrings()
    {
        return array(
            array('foo'),
            array(''),
            array(' '),
            array('EN-GB'),
            array('EN_GB'),
        );
    }

    /**
     * @dataProvider providesInvalidLocaleCodeStrings
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The locale code is invalid.
     */
    public function testConstructorThrowsAnExceptionIfTheSpecifiedStringIsInvalid($invalidLocaleCode)
    {
        new LocaleCode($invalidLocaleCode);
    }

    public static function providesLocaleCodeStringsAndTheirParts()
    {
        return array(
            array(
                'en',
                'GB',
                'en-GB',
            ),
            array(
                'en',
                'GB',
                'en_GB',
            ),
            array(
                'en',
                null,
                'en',
            ),
        );
    }

    /**
     * @dataProvider providesLocaleCodeStringsAndTheirParts
     */
    public function testGetlanguagecodeReturnsTheLanguageCodeInTheLocaleCode($expectedLanguageCode, $expectedCountryCode, $localeCodeString)
    {
        $localeCode = new LocaleCode($localeCodeString);

        $this->assertSame($expectedLanguageCode, $localeCode->getLanguageCode());
        $this->assertSame($expectedCountryCode, $localeCode->getCountryCode());
    }

    public static function providesValidLocaleCodeStrings()
    {
        return array(
            array('en-GB'),
            array('en_GB'),
            array('en'),
        );
    }

    /**
     * @dataProvider providesValidLocaleCodeStrings
     */
    public function testValidatestringReturnsTrueIfTheSpecifiedLocaleCodeStringIsValid($localeCodeString)
    {
        $this->assertTrue(LocaleCode::validateString($localeCodeString));
    }

    /**
     * @dataProvider providesInvalidLocaleCodeStrings
     */
    public function testValidatestringReturnsFalseIfTheSpecifiedLocaleCodeStringIsValid($invalidLocaleCodeString)
    {
        $this->assertFalse(LocaleCode::validateString($invalidLocaleCodeString));
    }

    public function testHascountrycodeReturnsTrueIfTheLocaleCodeIncludesACountryCode()
    {
        $localeCodeWithCountryCode = new LocaleCode('en-GB');
        $this->assertTrue($localeCodeWithCountryCode->hasCountryCode());

        $localeCodeWithoutCountryCode = new LocaleCode('en');
        $this->assertFalse($localeCodeWithoutCountryCode->hasCountryCode());
    }

    public function testTostringReturnsTheStringTheLocalecodeWasConstructedFrom()
    {
        $localeCodeString = 'en-GB';
        $localeCode = new LocaleCode($localeCodeString);

        $this->assertSame($localeCodeString, (string) $localeCode);
    }

    public function providesSymfonyLocaleCodeStrings()
    {
        return array(
            array(
                'en',
                'en',
            ),
            array(
                'en_GB',
                'en-GB',
            ),
            array(
                'en_GB',
                'en_GB',
            ),
            array(
                'en_gb',
                'en-gb',
            ),
            array(
                'en_gb',
                'en_gb',
            ),
        );
    }

    /**
     * @dataProvider providesSymfonyLocaleCodeStrings
     */
    public function testGetsymfonystringReturnsTheStringInTheFormatUsedBySymfony(
        $expectedSymfonyLocaleCodeString,
        $localeCodeString
    ) {
        $localeCode = new LocaleCode($localeCodeString);

        $this->assertSame($expectedSymfonyLocaleCodeString, $localeCode->getSymfonyString());
    }
}
