ExposeTranslationBundle
=======================

A pretty nice way to expose your translated messages to your JavaScript.


Installation
------------

Install the bundle:

    composer require "willdurand/expose-translation-bundle:@stable"

**Protip:** you should browse the
[`willdurand/expose-translation-bundle`](https://packagist.org/packages/willdurand/expose-translation-bundle)
page to choose a stable version to use, avoid the `@stable` meta constraint.

Register the bundle in `app/AppKernel.php`:

``` php
<?php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Bazinga\ExposeTranslationBundle\BazingaExposeTranslationBundle(),
    );
}
```

Register the routing in `app/config/routing.yml`:

``` yaml
# app/config/routing.yml
_bazinga_exposetranslation:
    resource: "@BazingaExposeTranslationBundle/Resources/config/routing/routing.yml"
```

Publish assets:

    php app/console assets:install --symlink web


Usage
-----

Add this line in your layout:

``` html
<script type="text/javascript" src="{{ asset('bundles/bazingaexposetranslation/js/translator.min.js') }}"></script>
```

Now, you have to specify which [translation
files](http://symfony.com/doc/current/book/translation.html#translation-locations-and-naming-conventions) to load.

### Load Translation Domains

By adding a line as below:

``` html
<script type="text/javascript" src="{{ url('bazinga_exposetranslation_js') }}"></script>
```

This will use the current `locale` and will provide translated messages found in each `messages.CURRENT_LOCALE.*` files of your project.

#### Domain

``` html
<script type="text/javascript" src="{{ url('bazinga_exposetranslation_js', { 'domain_name': 'DOMAIN_NAME' }) }}"></script>
```

This will use the current `locale` and will provide translated messages found in each `DOMAIN_NAME.CURRENT_LOCALE.*` files of your project.

#### Locale

You can specify a `locale` to use for translation if you want, just add the `_locale` parameter:

``` html
<script type="text/javascript" src="{{ url('bazinga_exposetranslation_js', { 'domain_name': 'DOMAIN_NAME', '_locale' : 'MY_LOCALE' }) }}"></script>
```

This will provide translated messages found in each `DOMAIN_NAME.MY_LOCALE.*` files of your project.

#### Loading via JSON

Alternatively, you can load your translated messages via JSON (e.g. using jQuery's `ajax()` or RequireJS's text plugin).
Just amend the above mentioned URLs to also contain the `'_format': 'json'` parameter like so:

``` html
{{ url('bazinga_exposetranslation_js', { '_format': 'json' }) }}
```

Then, feed the translator via `Translator.fromJSON(myRetrievedJSONString)`.

### Load Translations Via Dumped JavaScript Files

#### Dump JavaScript Translation Files

Dump the translation files to the `web/js` folder:

    php app/console bazinga:expose-translation:dump web/js

You can use the optional `--symlink` option. The `target` (`web/js` in the
example above) argument is also optionally, `web` is the default `target`.

#### Use With Assetic

```twig
{% javascripts
    'bundles/bazingaexposetranslation/js/translator.js'
    'js/i18n/*/*.js' %}
    <script type="text/javascript" src="{{ asset_url }}"></script>
{% endjavascripts %}
```
With `'js/i18n/*/*.js'`, you load all the translation files from all of the
translation domains. Of couse, you can load domains one by one
`'js/i18n/admin/*.js'`.

### The JavaScript Side

The `Translator` object implements the Symfony2
[`TranslatorInterface`](https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Translation/TranslatorInterface.php)
and provides the same `trans()` and `transChoice()` methods:

``` javascript
Translator.has('DOMAIN_NAME:key');
// true or false

Translator.trans('key', {}, 'DOMAIN_NAME');
// the translated message or undefined

Translator.trans('DOMAIN_NAME:key');
// the translated message or undefined

Translator.transChoice('key', 1, {}, 'DOMAIN_NAME');
// the translated message or undefined

Translator.transChoice('DOMAIN_NAME:key', 1, {});
// the translated message or undefined
```

> **Note:** The JavaScript is AMD ready.

#### Guesser

If you don't specify any **domain**, a guesser is provided to find the best translated message for the given `key`.
To configure the guesser, you have to set the `defaultDomains` attribute. By default, the configured default domain is `messages`.

``` javascript
Translator.trans('key');
// will try to find a translated message in default domains.
```

**Note:** this will only work if default domains are previously loaded (see the _Load translation domains_ first section).

#### Message Placeholders / Parameters

The `trans()` method accepts a second argument that takes an array of parameters:

``` javascript
Translator.trans('DOMAIN_NAME:key', { "foo" : "bar" });
// will replace each "%foo%" in the message by "bar".
```

You can override the placeholder delimiters by setting the `placeHolderSuffix` and `placeHolderPrefix` attributes.

The `transChoice()` method accepts this array of parameters as third argument:

``` javascript
Translator.transChoice('DOMAIN_NAME:key', 123, { "foo" : "bar" });
// will replace each "%foo%" in the message by "bar".
```

> Read the official documentation about Symfony2 [message
placeholders](http://symfony.com/doc/current/book/translation.html#message-placeholders).

#### Pluralization

Probably the best feature provided by this bundle! It allows you to use
pluralization exactly like you would do using the Symfony Translator
component.

``` yaml
# app/Resources/messages.en.yml
apples: "{0} There is no apples|{1} There is one apple|]1,19] There are %count% apples|[20,Inf] There are many apples"
```

``` javascript
Translator.locale = 'en';

Translator.transChoice('apples', 0, {"count" : 0});
// will return "There is no apples"

Translator.transChoice('apples', 1, {"count" : 1});
// will return "There is one apple"

Translator.transChoice('apples', 2, {"count" : 2});
// will return "There are 2 apples"

Translator.transChoice('apples', 10, {"count" : 10});
// will return "There are 10 apples"

Translator.transChoice('apples', 19, {"count" : 19});
// will return "There are 19 apples"

Translator.transChoice('apples', 20, {"count" : 20});
// will return "There are many apples"

Translator.transChoice('apples', 100, {"count" : 100});
// will return "There are many apples"
```

> Read the official doc about
[pluralization](http://symfony.com/doc/current/book/translation.html#pluralization).

#### Get The Locale

You can get the current locale by accessing the `locale` attribute:

``` javascript
Translator.locale;
// will return the current locale.
```

Examples
--------

Consider the following translation files:

``` yaml
# app/Resources/translations/Hello.fr.yml
foo: "Bar"
ba:
    bar: "Hello world"

placeholder: "Hello %username% !"
```

``` yaml
# app/Resources/translations/messages.fr.yml
placeholder: "Hello %username%, how are you ?"
```

You can do:

``` javascript
Translator.trans('Hello:foo');
// will return 'Bar' if the current locale is set to 'fr', undefined otherwise.

Translator.trans('Hello:ba.bar');
// will return 'Hello world' if the current locale is set to 'fr', undefined otherwise.

Translator.trans('Hello:placeholder');
// will return 'Hello %username% !' if the current locale is set to 'fr', undefined otherwise.

Translator.trans('Hello:placeholder', { "username" : "will" });
// will return 'Hello will !' if the current locale is set to 'fr', undefined otherwise.

Translator.trans('placeholder', { "username" : "will" });
// will return 'Hello will, how are you ?' if the current locale is set to 'fr', undefined otherwise.

Translator.trans('placeholder');
// will return 'Hello %username%, how are you ?' if the current locale is set to 'fr', undefined otherwise.
```


More configuration
------------------

#### Custom Default Domains

You can easily add your own default domains by adding these lines in your
`app/config/config*.yml` files:

``` yaml
bazinga_expose_translation:
    default_domains: [ messages ]
```

**Note:** You still have to include a `<script>` tag to expose messages but you
avoid writing domain names before each of your keys.

#### Locale Fallback

In a similar way, if some of your translations are not complete you can enable a
fallback for untranslated messages:

``` yaml
bazinga_expose_translation:
    locale_fallback: "en" # It is recommended to set the same value used for the
                          # translator fallback.
```


Reference Configuration
-----------------------

``` yaml
# app/config/config*.yml
bazinga_expose_translation:
    locale_fallback:      ''
    default_domains:      []
```


Testing
-------

### PHP

Setup the test suite using [Composer](http://getcomposer.org/):

    $ composer install --dev

Run it using PHPUnit:

    $ phpunit

### JavaScript

You can run the JavaScript test suite using [PhantomJS](http://phantomjs.org/):

    $ phantomjs Resources/js/run-qunit.js file://`pwd`/Resources/js/index.html
