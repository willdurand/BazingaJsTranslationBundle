# ExposeTranslationBundle

A pretty nice way to expose your translated messages to your JavaScript.

**Warning:** This bundle is a Proof of Concept and it should **NOT** be used in production at the moment.


## Installation

As usual, add this bundle to your submodules:

    git submodule add git://github.com/Bazinga/ExposeTranslationBundle.git vendor/bundles/Bazinga/ExposeTranslationBundle

Register the namespace in `app/autoload.php`:

``` php
<?php
// app/autoload.php
$loader->registerNamespaces(array(
    // ...
    'Bazinga' => __DIR__.'/../vendor/bundles',
));
```

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
    resource: "@BazingaExposeTranslationBundle/Resources/config/routing/routing.xml"
```

Publish assets:

    php app/console assets:install --symlink web


## Usage

Just add this line in your layout:

``` html
<script type="text/javascript" src="{{ asset('bundles/bazingaexposetranslation/js/translation.js') }}"></script>
```

Now, you just have to specify which [translation files](http://symfony.com/doc/current/book/translation.html#translation-locations-and-naming-conventions) to load.

### Expose your translation

But **how to do that ?**
Just by adding a line as below:

``` html
<script type="text/javascript" src="{{ url('bazinga_exposetranslation_js' }}"></script>
```

This will use the current `locale` and will provide translated messages found in each `messages.CURRENT_LOCALE.*` files of your project.


#### Domain

``` html
<script type="text/javascript" src="{{ url('bazinga_exposetranslation_js', { 'domain_name': 'DOMAIN_NAME' } }}"></script>
```

This will use the current `locale` and will provide translated messages found in each `DOMAIN_NAME.CURRENT_LOCALE.*` files of your project.

#### Locale

You can specify a `locale` to use for translation if you want, just add the `_locale` parameter:

``` html
<script type="text/javascript" src="{{ url('bazinga_exposetranslation_js', { 'domain_name': 'DOMAIN_NAME', '_locale' : 'MY_LOCALE' } }}"></script>
```

This will provide translated messages found in each `DOMAIN_NAME.MY_LOCALE.*` files of your project.


### The JavaScript side

It's quite simple:

``` javascript
$.ExposeTranslation.has('DOMAIN_NAME:key');
// true or false

$.ExposeTranslation.get('DOMAIN_NAME:key');
// the translated message or undefined
```

#### Guesser

If you don't specify any **domain**, a guesser is provided to find the best translated message for the given `key`.
To configure the guesser, you have to set the `defaultDomains` attribute. By default, the configured default domain is `messages`.

``` javascript
$.ExposeTranslation.get('key');
// will try to find a translated message in default domains.
```

#### Message placeholders

Read the official documentation about Symfony2 [message placeholders](http://symfony.com/doc/current/book/translation.html#message-placeholders).

The `get()` method accepts a second argument that takes placeholders without `%` delimiters:

``` javascript
$.ExposeTranslation.get('DOMAIN_NAME:key', { "foo" : "bar" });
// will replace each "%foo%" in the message by "bar".
```

You can override the placeholder delimiters by setting the `placeHolderSuffix` and `placeHolderPrefix` attributes.

#### Get the locale

You can get the current locale by accessing the `locale` attribute:

``` javascript
$.ExposeTranslation.locale;
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
$.ExposeTranslation.get('Hello:foo');
// will return 'Bar' if the current locale is set to 'fr', undefined otherwise.

$.ExposeTranslation.get('Hello:ba.bar');
// will return 'Hello world' if the current locale is set to 'fr', undefined otherwise.

$.ExposeTranslation.get('Hello:placeholder');
// will return 'Hello %username% !' if the current locale is set to 'fr', undefined otherwise.

$.ExposeTranslation.get('Hello:placeholder', { "username" : "will" });
// will return 'Hello will !' if the current locale is set to 'fr', undefined otherwise.

$.ExposeTranslation.get('placeholder', { "username" : "will" });
// will return 'Hello will, how are you ?' if the current locale is set to 'fr', undefined otherwise.

$.ExposeTranslation.get('placeholder');
// will return 'Hello %username%, how are you ?' if the current locale is set to 'fr', undefined otherwise.
```


## Credits

* William DURAND (Bazinga).
