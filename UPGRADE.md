From 1.x to 2.0
===============

* The bundle has been renamed from `BazingaExposeTranslationBundle` to
  `BazingaJsTranslationBundle`.

* The namespace has been changed to be consistent with the other _Bazinga_
  bundles:

```
// before
new \Bazinga\ExposeTranslationBundle\BazingaExposeTranslationBunde()

// after
new \Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle()
```

* The JS `Translator` now mimics the Symfony `TranslatorInterface` and `get()`,
  `has()` methods have since been removed. Methods `trans()` and `transChoice()`
  have been introduced:

```
// before
Translator.get('foo')
Translator.get('foo.plural')

// after
Translator.trans('foo')
Translator.transChoice('foo')
```

* Messages keys (aka ids) were prefixed by their translation domain, and you
  could get a translation by using the following `DOMAIN:id`. This is not
  possible anymore. You must pass the `DOMAIN` to the `trans()`/`transChoice()`
  methods, or let the `Translator` find the `id` itself (hopefully).

```
// before
Translator.get('HELLO:foo')

// after
Translator.trans('foo', {}, 'HELLO')
```

* The `defaultDomains` configuration parameter has been removed. This parameter
  was useful to retrieve a translation domain when not provided. This is now
  automatically done.

* The route pattern has changed:

```
// before
/i18n/DOMAIN/LOCALE.js

// after
/translations/DOMAIN.js
```

By default, it serves the translation messages for the current application's
locale. However, you can ask for different locales by using the `locales` query
parameter:

```
/translations/DOMAIN.js?locales=fr,en
```

* The `dump` command has evolved, and now generates files into
  `web/translations`. It generates both JavaScript and JSON files, without
  configuration sections (i.e. it generates files that only add translation
  messages to the JS `Translator`). Two special files, `config.js` and
  `config.json`, are now generated and contain the configuration for the JS
  `Translator`.
