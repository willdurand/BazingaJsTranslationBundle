JsTranslationBundle
===================

[![CI](https://github.com/willdurand/BazingaJsTranslationBundle/actions/workflows/run-tests.yml/badge.svg?branch=master)](https://github.com/willdurand/BazingaJsTranslationBundle/actions/workflows/run-tests.yml)
[![Total
Downloads](https://poser.pugx.org/willdurand/js-translation-bundle/downloads.png)](https://packagist.org/packages/willdurand/js-translation-bundle)
[![Latest Stable
Version](https://poser.pugx.org/willdurand/js-translation-bundle/v/stable.png)](https://packagist.org/packages/willdurand/js-translation-bundle)

A pretty nice way to expose your Symfony translation messages to your client applications.

## Requirements

PHP 8.2 or higher and Symfony 6.4 or higher (within Symfony 6.x, 7.x or 8.x) are
required. Projects using older PHP or Symfony versions must upgrade before
installing the next release of this bundle.

CI explicitly tests Symfony 6.4 and 7.4 on PHP 8.2–8.5, and Symfony 8.1 on
PHP 8.4–8.5. Symfony 8.1 requires PHP 8.4 or higher even though the bundle's
minimum PHP version remains 8.2.
CI also tests the published Composer requirements without additional Symfony
filters on PHP 8.2–8.5 and nightly PHP. The explicit version jobs add temporary
Composer update constraints without replacing the published requirements.
An optional CI job tests Symfony 8.2 development versions on PHP 8.5 to catch
compatibility issues before its stable release; only this job enables development
stability.


## Documentation

For documentation, see:

    Resources/doc/

[Read the
documentation](https://github.com/willdurand/BazingaJsTranslationBundle/blob/master/Resources/doc/index.md)


Contributing
------------

See
[CONTRIBUTING](https://github.com/willdurand/BazingaJsTranslationBundle/blob/master/CONTRIBUTING.md)
file.


Credits
-------

* William DURAND as main author
* Jan Sorgalla as contributor
* Stan Chollet as contributor
* Kamil Kaproń as contributor
* [All contributors](https://github.com/willdurand/BazingaJsTranslationBundle/graphs/contributors)


License
-------

This bundle is released under the MIT license. See the complete license [in the
bundle](LICENSE).
