# ExposeTranslationBundle

A pretty nice way to expose your translated messages to your JavaScript.

**Warning:**

* This bundle is a Proof of Concept and it should **NOT** be used in production at the moment.
* This bundle is not unit tested, so don't use it or provides tests if you want to contribute.
* I plan to add tests soon, it's really important. Mainly qUnit tests !
* I also plan to add an easy way to declare more default domains in `app/config/config*.yml`.


## Prerequisite

The famous JavaScript framework [jQuery](http://jquery.com/) is mandatory.


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
But **how to do that ?**


### Load translation domains

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

**Note:** this will only work if default domains are previously loaded (see the _Load translation domains_ first section).

#### Message placeholders

Read the official documentation about Symfony2 [message placeholders](http://symfony.com/doc/current/book/translation.html#message-placeholders).

The `get()` method accepts a second argument that takes placeholders without `%` delimiters:

``` javascript
$.ExposeTranslation.get('DOMAIN_NAME:key', { "foo" : "bar" });
// will replace each "%foo%" in the message by "bar".
```

You can override the placeholder delimiters by setting the `placeHolderSuffix` and `placeHolderPrefix` attributes.

#### Pluralization

Probably the best feature provided in this bundle ! It allows you to use pluralization exactly like you can do in Symfony2.

Read the official doc about [pluralization](http://symfony.com/doc/current/book/translation.html#pluralization).

A third parameter can be added to the `get()` method, it's the **number** of objects being described. Here is an example:

``` yaml
# app/Resources/messages.en.yml
apples:    "{0} There is no apples|{1} There is one apple|]1,19] There are %count% apples|[20,Inf] There are many apples"
```

``` javascript
$.ExposeTranslation.locale = 'en';

$.ExposeTranslation.get('apples', {"count" : 0}, 0);
// will return "There is no apples"

$.ExposeTranslation.get('apples', {"count" : 1}, 1);
// will return "There is one apple"

$.ExposeTranslation.get('apples', {"count" : 2}, 2);
// will return "There are 2 apples"

$.ExposeTranslation.get('apples', {"count" : 10}, 10);
// will return "There are 10 apples"

$.ExposeTranslation.get('apples', {"count" : 19}, 19);
// will return "There are 19 apples"

$.ExposeTranslation.get('apples', {"count" : 20}, 20);
// will return "There are many apples"

$.ExposeTranslation.get('apples', {"count" : 100}, 100);
// will return "There are many apples"
```

**Note:** This is not tested at the moment. It works fine for english/french translations.

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
