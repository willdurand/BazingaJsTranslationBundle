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
    var _messages = {},
        _locale = '';

    return {
      /**
       * The current locale.
       *
       * @api public
       */
      locale: '',
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
      get: function(key) {
        return _messages[key] || undefined;
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
