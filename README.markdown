# ExposeTranslationBundle

[Symfony2] A pretty nice way to expose your translation messages to your JavaScript.

**Warning:** This bundle is a Prood of Concept and it should **NOT** be used in production at the moment.


## Installation

As usual, add this bundle to your submodules:

    git clone git://github.com/Bazinga/ExposeTranslationBundle.git vendor/bundles/Bazinga/ExposeTranslationBundle

Register the namespace in `app/autoload.php`:

    // app/autoload.php
    $loader->registerNamespaces(array(
        // ...
        'Bazinga' => __DIR__.'/../vendor/bundles',
    ));

Register the bundle in `app/AppKernel.php`:

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Bazinga\ExposeTranslationBundle\BazingaExposeTranslationBundle(),
        );
    }

Register the routing in `app/config/routing.yml`:

``` yaml
# app/config/routing.yml
_bazinga_exposetranslation:
    resource: "@BazingaExposeTranslationBundle/Resources/config/routing/routing.xml"
```

Publish assets:

    $ php app/console assets:install --symlink web


## Usage

Just add this line in your layout:

``` html
<script type="text/javascript" src="{{ asset('bundles/bazingaexposetranslation/js/translation.js') }}"></script>
```

Now, you just have to specify which translation files to load.

But _how to do that ?_
Just by adding a line as below:

``` html
<script type="text/javascript" src="{{ url('bazinga_exposetranslation_js' }}"></script>
```

This will use the current `locale` and will provide translated messages for each `messages.LOCALE.*` files of your project.

``` html
<script type="text/javascript" src="{{ url('bazinga_exposetranslation_js', { 'domain_name': 'DOMAIN_NAME' } }}"></script>
```

This will use the current `locale` and will provide translated messages for each `DOMAIN_NAME.CURRENT_LOCALE.*` files of your project.


#### Locale

You can specify a `locale` if you want, just add the `_locale` parameter:

``` html
<script type="text/javascript" src="{{ url('bazinga_exposetranslation_js', { 'domain_name': 'DOMAIN_NAME', '_locale' : 'MY_LOCALE' } }}"></script>
```

This will use the current `locale` and will provide translated messages for each `DOMAIN_NAME.MY_LOCALE.*` files of your project.


#### JavaScript side

``` javascript
$.ExposeTranslation.has('DOMAIN_NAME:key');
// true or false

$.ExposeTranslation.get('DOMAIN_NAME:key');
// whe translated message or undefined
```


#### Example

Consider the following translation file:

``` yaml
# app/Resources/translations/Hello.fr.yml
foo: "Bar"
```

``` javascript
$.ExposeTranslation.get('Hello:foo');
// will return 'Bar' if the current locale is set to 'fr', undefined otherwise.
```


## Credits

* William DURAND (Bazinga).
