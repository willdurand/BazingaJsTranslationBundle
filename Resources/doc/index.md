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

``` javascript
Translator.has('DOMAIN_NAME:key');
// true or false

Translator.get('DOMAIN_NAME:key');
// the translated message or undefined
```

> **Note:** The JavaScript is AMD ready.

#### Guesser

If you don't specify any **domain**, a guesser is provided to find the best translated message for the given `key`.
To configure the guesser, you have to set the `defaultDomains` attribute. By default, the configured default domain is `messages`.

``` javascript
Translator.get('key');
// will try to find a translated message in default domains.
```

**Note:** this will only work if default domains are previously loaded (see the _Load translation domains_ first section).

#### Message Placeholders

Read the official documentation about Symfony2 [message placeholders](http://symfony.com/doc/current/book/translation.html#message-placeholders).

The `get()` method accepts a second argument that takes placeholders without `%` delimiters:

``` javascript
Translator.get('DOMAIN_NAME:key', { "foo" : "bar" });
// will replace each "%foo%" in the message by "bar".
```

You can override the placeholder delimiters by setting the `placeHolderSuffix` and `placeHolderPrefix` attributes.

#### Pluralization

Probably the best feature provided in this bundle ! It allows you to use pluralization exactly like you can do in Symfony2.

Read the official doc about [pluralization](http://symfony.com/doc/current/book/translation.html#pluralization).

A third parameter can be added to the `get()` method, it's the **number** of objects being described. Here is an example:

``` yaml
# app/Resources/messages.en.yml
apples: "{0} There is no apples|{1} There is one apple|]1,19] There are %count% apples|[20,Inf] There are many apples"
```

``` javascript
Translator.locale = 'en';

Translator.get('apples', {"count" : 0}, 0);
// will return "There is no apples"

Translator.get('apples', {"count" : 1}, 1);
// will return "There is one apple"

Translator.get('apples', {"count" : 2}, 2);
// will return "There are 2 apples"

Translator.get('apples', {"count" : 10}, 10);
// will return "There are 10 apples"

Translator.get('apples', {"count" : 19}, 19);
// will return "There are 19 apples"

Translator.get('apples', {"count" : 20}, 20);
// will return "There are many apples"

Translator.get('apples', {"count" : 100}, 100);
// will return "There are many apples"
```

**Note:** This is not tested at the moment. It works fine for english/french translations.

#### Get The Locale

You can get the current locale by accessing the `locale` attribute:

``` javascript
Translator.locale;
// will return the current locale.
```


## Example

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
Translator.get('Hello:foo');
// will return 'Bar' if the current locale is set to 'fr', undefined otherwise.

Translator.get('Hello:ba.bar');
// will return 'Hello world' if the current locale is set to 'fr', undefined otherwise.

Translator.get('Hello:placeholder');
// will return 'Hello %username% !' if the current locale is set to 'fr', undefined otherwise.

Translator.get('Hello:placeholder', { "username" : "will" });
// will return 'Hello will !' if the current locale is set to 'fr', undefined otherwise.

Translator.get('placeholder', { "username" : "will" });
// will return 'Hello will, how are you ?' if the current locale is set to 'fr', undefined otherwise.

Translator.get('placeholder');
// will return 'Hello %username%, how are you ?' if the current locale is set to 'fr', undefined otherwise.
```


More configuration
------------------

#### Custom Default Domains

You can easily add your own default domains by adding these lines in your `app/config/config*.yml` files:

``` yaml
bazinga_expose_translation:
    default_domains: [ messages ]
```

**Note:** You still have to include a `<script>` tag to expose messages but you avoid writing domain names before each of your keys.

#### Locale Fallback

In a similar way, if some of your translations are not complete you can enable a fallback for untranslated messages:
``` yaml
bazinga_expose_translation:
    locale_fallback: "en" # put here locale code of some complete translation, I recommend the value used for translator fallback
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
