/*!
 * William DURAND <william.durand1@gmail.com>
 * MIT Licensed
 */

/**
 * Define Translation class.
 */
$.ExposeTranslation = $.ExposeTranslation || {};

(function(Translation, $, undefined) {
  // now register our routing methods
  $.extend(Translation, (function() {
    var _messages = {};

    /**
     * replace placeholders in given message.
     * **WARNING:** used placeholders are removed.
     *
     * @param {String} message      The translated message.
     * @param {Object} placeholders The placeholders to replace.
     * @return {String}             A human readable message.
     * @api private
     */
    function replace_placeholders(message, placeholders) {
        var _i,
            _message = message,
            _prefix = Translation.placeHolderPrefix,
            _suffix = Translation.placeHolderSuffix;

        for (_i in placeholders) {
          var _r = new RegExp(_prefix + _i + _suffix, 'g');

          if (_r.test(_message)) {
            _message = _message.replace(_r, placeholders[_i]);
            delete(placeholders[_i]);
          }
        }

        return _message;
    }

    /**
     * Guess the domain if you don't specify it, based on
     * given default domains.
     *
     * @param {String} key  A message key.
     * @return {String}     The message if found, undefined otherwise.
     */
    function guess_domain(key) {
      var _k,
          _key = key,
          _defaultDomains = Translation.defaultDomains;

      if (!$.isArray(Translation.defaultDomains)) {
        _defaultDomains = [Translation.defaultDomains];
      }

      for (_k in _defaultDomains) {
        if (Translation.has(_defaultDomains[_k] + ':' + key)) {
          return Translation.get(_defaultDomains[_k] + ':' + key);
        }
      }

      return undefined;
    }

    return {
      /**
       * The current locale.
       * @type {String}
       * @api public
       */
      locale: '',
      /**
       * Placeholder prefix.
       * @type {String}
       * @api public
       */
      placeHolderPrefix: '%',
      /**
       * Placeholder suffix.
       * @type {String}
       * @api public
       */
      placeHolderSuffix: '%',
      /**
       * Default domains.
       * @type {String|Array}
       * @api public
       */
      defaultDomains: [],
      /**
       * Add a translation entry.
       *
       * @param {String} key      A translation key.
       * @param {String} message  A message for this key.
       * @return {Object}         Translation.
       * @api public
       */
      add: function(key, message) {
        _messages[key] = message;
        return Translation;
      },
      /**
       * Get the translated message for the given key.
       *
       * @param {String} key    A translation key.
       * @return {String}       The corresponding message if the key exists.
       */
      get: function(key, placeholders) {
        var _message = _messages[key] || guess_domain(key),
            _placeholders = $.extend({}, placeholders || {});

        if (!_message) {
          return key;
        }

        _message = replace_placeholders(_message, _placeholders);

        return _message;
      },
      /**
       * Determines wether a message is registered or not.
       *
       * @param {String} key  A translation id.
       * @return {Boolean}    Wether the message is registered or not.
       * @api public
       */
      has: function(key) {
        return (_messages[key] ? true : false);
      }
    };
  })());
})($.ExposeTranslation, jQuery);
