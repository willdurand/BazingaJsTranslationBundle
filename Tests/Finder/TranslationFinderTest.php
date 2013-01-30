<?php

namespace Bazinga\ExposeTranslationBundle\Tests\Finder;

use Bazinga\ExposeTranslationBundle\Finder\TranslationFinder;

class TranslationFinderTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateLocalesArrayWithSingleLocale()
    {
        $translationFinder = $this->createTranslationFinder();
        $result = $translationFinder->createLocalesArray(array('de'));
        $this->assertEquals(
            array('de'),
            $result
        );
    }

    public function testCreateLocalesArrayWithSingleLocaleWithArea()
    {
        $translationFinder = $this->createTranslationFinder();
        $result = $translationFinder->createLocalesArray(array('de_DE'));
        $this->assertEquals(
            array('de', 'de_DE'),
            $result
        );
    }

    public function testCreateLocalesArrayWithMultipleLocales()
    {
        $translationFinder = $this->createTranslationFinder();
        $result = $translationFinder->createLocalesArray(array('de', 'en', 'fr'));
        $this->assertEquals(
            array('de', 'en', 'fr'),
            $result
        );
    }

    public function testCreateLocalesArrayWithMultipleDuplicates()
    {
        $translationFinder = $this->createTranslationFinder();
        $result = $translationFinder->createLocalesArray(array('de', 'en', 'de'));
        $this->assertEquals(
            array('de', 'en'),
            $result
        );
    }

    public function testCreateLocalesArrayWithMultipleLocalesWithAreas()
    {
        $translationFinder = $this->createTranslationFinder();
        $result = $translationFinder->createLocalesArray(array('de_DE', 'en_GB'));
        $this->assertEquals(
            array('de', 'de_DE', 'en', 'en_GB'),
            $result
        );
    }

    public function testCreateLocalesArrayWithMultipleLocalesWithDuplicateAreas()
    {
        $translationFinder = $this->createTranslationFinder();
        $result = $translationFinder->createLocalesArray(array('en_US', 'en_GB'));
        $this->assertEquals(
            array('en', 'en_US', 'en_GB'),
            $result
        );
    }

    public function testCreateLocalesArrayWithMultipleLocalesWithAreasAndDuplicateLocales()
    {
        $translationFinder = $this->createTranslationFinder();
        $result = $translationFinder->createLocalesArray(array('en_US', 'en'));
        $this->assertEquals(
            array('en', 'en_US'),
            $result
        );
    }

    public function testCreateLocalesArrayIgnoreEmptyLocale()
    {
        $translationFinder = $this->createTranslationFinder();
        $result = $translationFinder->createLocalesArray(array('en_US', null));
        $this->assertEquals(
            array('en', 'en_US'),
            $result
        );
    }

    /**
     * @return \Bazinga\ExposeTranslationBundle\Service\TranslationFinder
     */
    public function createTranslationFinder()
    {
        $httpKernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $trantslationFinder = new TranslationFinder($httpKernel);

        return $trantslationFinder;
    }
}
