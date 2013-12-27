/*!
 * William DURAND <william.durand1@gmail.com>
 * MIT Licensed
 */
var Translator = (function () {
    "use strict";

    var _messages     = {},
        _sPluralRegex = /^\w+\: +(.+)$/,
        _cPluralRegex = /^\s*((\{\s*(\-?\d+[\s*,\s*\-?\d+]*)\s*\})|([\[\]])\s*(-Inf|\-?\d+)\s*,\s*(\+?Inf|\-?\d+)\s*([\[\]]))\s?(.+?)$/,
        _iPluralRegex = /^\s*(\{\s*(\-?\d+[\s*,\s*\-?\d+]*)\s*\})|([\[\]])\s*(-Inf|\-?\d+)\s*,\s*(\+?Inf|\-?\d+)\s*([\[\]])/;

    /**
     * Replace placeholders in given message.
     *
     * **WARNING:** used placeholders are removed.
     *
     * @param {String} message      The translated message
     * @param {Object} placeholders The placeholders to replace
     * @return {String}             A human readable message
     * @api private
     */
    function replace_placeholders(message, placeholders) {
        var _i,
            _prefix = Translator.placeHolderPrefix,
            _suffix = Translator.placeHolderSuffix;

        for (_i in placeholders) {
            var _r = new RegExp(_prefix + _i + _suffix, 'g');

            if (_r.test(message)) {
                message = message.replace(_r, placeholders[_i]);
                delete(placeholders[_i]);
            }
        }

        return message;
    }

    /**
     * Guess the domain if you don't specify it, based on given
     * and/or default domains.
     *
     * @param {String} key      The message id
     * @param {Array} domains   An array of domains
     * @param {String} locale   The number to use to find the indice of the message
     * @return {String}         The right message if found, undefined otherwise
     * @api private
     */
    function guess_domain(key, domains, locale) {
        var _key;

        if (domains.constructor != Array) {
            domains = [ domains ];
        }

        for (var _k in domains) {
            _key = [ domains[_k], key ].join(':');

            if (Translator.has(_key, locale)) {
                return Translator.trans(_key);
            }
        }

        return undefined;
    }

    /**
     * The logic comes from the Symfony2 PHP Framework.
     *
     * Given a message with different plural translations separated by a
     * pipe (|), this method returns the correct portion of the message based
     * on the given number, the current locale and the pluralization rules
     * in the message itself.
     *
     * The message supports two different types of pluralization rules:
     *
     * interval: {0} There is no apples|{1} There is one apple|]1,Inf] There is %count% apples
     * indexed:  There is one apple|There is %count% apples
     *
     * The indexed solution can also contain labels (e.g. one: There is one apple).
     * This is purely for making the translations more clear - it does not
     * affect the functionality.
     *
     * The two methods can also be mixed:
     *     {0} There is no apples|one: There is one apple|more: There is %count% apples
     *
     * @param {String} message  The message id
     * @param {Number} number   The number to use to find the indice of the message
     * @param {String} locale   The locale or null to use the default
     * @return {String}         The message part to use for translation
     * @api private
     */
    function pluralize(message, number, locale) {
        var _p,
            _e,
            _explicitRules = [],
            _standardRules = [],
            _parts         = message.split(Translator.pluralSeparator),
            _matches       = [];

        for (_p in _parts) {
            var _part = _parts[_p];
            var _rc = new RegExp(_cPluralRegex);
            var _rs = new RegExp(_sPluralRegex);

            if (_rc.test(_part)) {
                _matches = _part.match(_rc);
                _explicitRules[_matches[0]] = _matches[_matches.length - 1];
            } else if (_rs.test(_part)) {
                _matches = _part.match(_rs);
                _standardRules.push(_matches[1]);
            } else {
                _standardRules.push(_part);
            }
        }

        for (_e in _explicitRules) {
            var _r = new RegExp(_iPluralRegex);

            if (_r.test(_e)) {
                _matches = _e.match(_r);

                if (_matches[1]) {
                    var _ns = _matches[2].split(','),
                        _n;

                    for (_n in _ns) {
                        if (number == _ns[_n]) {
                            return _explicitRules[_e];
                        }
                    }
                } else {
                    var _leftNumber  = convert_number(_matches[4]);
                    var _rightNumber = convert_number(_matches[5]);

                    if (('[' === _matches[3] ? number >= _leftNumber : number > _leftNumber) &&
                        (']' === _matches[6] ? number <= _rightNumber : number < _rightNumber)) {
                        return _explicitRules[_e];
                    }
                }
            }
        }

        return _standardRules[plural_position(number, locale)] || _standardRules[0] || undefined;
    }

    /**
     * The logic comes from the Symfony2 PHP Framework.
     *
     * Convert number as String, "Inf" and "-Inf"
     * values to number values.
     *
     * @param {String} number   A litteral number
     * @return {Number}         The int value of the number
     * @api private
     */
    function convert_number(number) {
        if ('-Inf' === number) {
            return Math.log(0);
        } else if ('+Inf' === number || 'Inf' === number) {
            return -Math.log(0);
        }

        return parseInt(number, 10);
    }

    /**
     * The logic comes from the Symfony2 PHP Framework.
     *
     * Returns the plural position to use for the given locale and number.
     *
     * @param {Number} number  The number to use to find the indice of the message
     * @param {String} locale  The locale or null to use the default
     * @return {Number}        The plural position
     * @api private
     */
    function plural_position(number, locale) {
        var _locale = locale || Translator.locale || Translator.fallback;

        if ('pt_BR' === _locale) {
            _locale = 'xbr';
        }

        if (_locale.length > 3) {
            _locale = _locale.split('_')[0];
        }

        switch (_locale) {
            case 'bo':
            case 'dz':
            case 'id':
            case 'ja':
            case 'jv':
            case 'ka':
            case 'km':
            case 'kn':
            case 'ko':
            case 'ms':
            case 'th':
            case 'tr':
            case 'vi':
            case 'zh':
                return 0;
            case 'af':
            case 'az':
            case 'bn':
            case 'bg':
            case 'ca':
            case 'da':
            case 'de':
            case 'el':
            case 'en':
            case 'eo':
            case 'es':
            case 'et':
            case 'eu':
            case 'fa':
            case 'fi':
            case 'fo':
            case 'fur':
            case 'fy':
            case 'gl':
            case 'gu':
            case 'ha':
            case 'he':
            case 'hu':
            case 'is':
            case 'it':
            case 'ku':
            case 'lb':
            case 'ml':
            case 'mn':
            case 'mr':
            case 'nah':
            case 'nb':
            case 'ne':
            case 'nl':
            case 'nn':
            case 'no':
            case 'om':
            case 'or':
            case 'pa':
            case 'pap':
            case 'ps':
            case 'pt':
            case 'so':
            case 'sq':
            case 'sv':
            case 'sw':
            case 'ta':
            case 'te':
            case 'tk':
            case 'ur':
            case 'zu':
                return (number == 1) ? 0 : 1;

            case 'am':
            case 'bh':
            case 'fil':
            case 'fr':
            case 'gun':
            case 'hi':
            case 'ln':
            case 'mg':
            case 'nso':
            case 'xbr':
            case 'ti':
            case 'wa':
                return ((number === 0) || (number == 1)) ? 0 : 1;

            case 'be':
            case 'bs':
            case 'hr':
            case 'ru':
            case 'sr':
            case 'uk':
                return ((number % 10 == 1) && (number % 100 != 11)) ? 0 : (((number % 10 >= 2) && (number % 10 <= 4) && ((number % 100 < 10) || (number % 100 >= 20))) ? 1 : 2);

            case 'cs':
            case 'sk':
                return (number == 1) ? 0 : (((number >= 2) && (number <= 4)) ? 1 : 2);

            case 'ga':
                return (number == 1) ? 0 : ((number == 2) ? 1 : 2);

            case 'lt':
                return ((number % 10 == 1) && (number % 100 != 11)) ? 0 : (((number % 10 >= 2) && ((number % 100 < 10) || (number % 100 >= 20))) ? 1 : 2);

            case 'sl':
                return (number % 100 == 1) ? 0 : ((number % 100 == 2) ? 1 : (((number % 100 == 3) || (number % 100 == 4)) ? 2 : 3));

            case 'mk':
                return (number % 10 == 1) ? 0 : 1;

            case 'mt':
                return (number == 1) ? 0 : (((number === 0) || ((number % 100 > 1) && (number % 100 < 11))) ? 1 : (((number % 100 > 10) && (number % 100 < 20)) ? 2 : 3));

            case 'lv':
                return (number === 0) ? 0 : (((number % 10 == 1) && (number % 100 != 11)) ? 1 : 2);

            case 'pl':
                return (number == 1) ? 0 : (((number % 10 >= 2) && (number % 10 <= 4) && ((number % 100 < 12) || (number % 100 > 14))) ? 1 : 2);

            case 'cy':
                return (number == 1) ? 0 : ((number == 2) ? 1 : (((number == 8) || (number == 11)) ? 2 : 3));

            case 'ro':
                return (number == 1) ? 0 : (((number === 0) || ((number % 100 > 0) && (number % 100 < 20))) ? 1 : 2);

            case 'ar':
                return (number === 0) ? 0 : ((number == 1) ? 1 : ((number == 2) ? 2 : (((number >= 3) && (number <= 10)) ? 3 : (((number >= 11) && (number <= 99)) ? 4 : 5))));

            default:
                return 0;
        }
    }

    return {
        /**
         * The current locale.
         *
         * @type {String}
         * @api public
         */
        locale: '',

        /**
         * Fallback locale.
         *
         * @type {String}
         * @api public
         */
        fallback: 'en',

        /**
         * Placeholder prefix.
         *
         * @type {String}
         * @api public
         */
        placeHolderPrefix: '%',

        /**
         * Placeholder suffix.
         *
         * @type {String}
         * @api public
         */
        placeHolderSuffix: '%',

        /**
         * Default domains.
         *
         * @type {String|Array}
         * @api public
         */
        defaultDomains: [],

        /**
         * Plurar separator.
         *
         * @type {String}
         * @api public
         */
        pluralSeparator: '|',

        /**
         * Add a translation entry.
         *
         * @param {String} key      The message id
         * @param {String} message  The message to register for the given id
         * @return {Object}         Translator
         * @api public
         */
        add: function(key, message) {
            var _locale = Translator.locale || Translator.fallback;

            if (!_messages[_locale]) {
                _messages[_locale] = {};
            }

            _messages[_locale][key] = message;

            return this;
        },


        /**
         * Translates the given message.
         *
         * @param {String} id             The message id
         * @param {Object} parameters     An array of parameters for the message
         * @param {String} domain         The domain for the message or null to guess it
         * @param {String} locale         The locale or null to use the default
         * @return {String}               The translated string
         */
        trans: function(id, parameters, domain, locale) {
            var _locale  = locale || Translator.locale || Translator.fallback,
                _message = _messages[_locale][id],
                _domains = Translator.defaultDomains;

            if (domain) {
                _domains = [ domain ];
            }

            if (_message === undefined) {
                _message = guess_domain(id, _domains, _locale);
            }

            if (_message === undefined) {
                _message = id;
            }

            return replace_placeholders(_message, parameters || {});
        },

        /**
         * Translates the given choice message by choosing a translation according to a number.
         *
         * @param {String} id             The message id
         * @param {Number} number         The number to use to find the indice of the message
         * @param {Object} parameters     An array of parameters for the message
         * @param {String} domain         The domain for the message or null to guess it
         * @param {String} locale         The locale or null to use the default
         * @return {String}               The translated string
         */
        transChoice: function(id, number, parameters, domain, locale) {
            var _locale  = locale || Translator.locale || Translator.fallback,
                _message = _messages[_locale][id],
                _number  = parseInt(number, 10),
                _domains = Translator.defaultDomains;

            if (domain) {
                _domains = [ domain ];
            }

            if (_message === undefined) {
                _message = guess_domain(id, _domains, _locale);
            }

            if (_message === undefined) {
                _message = id;
            }

            if (_message && !isNaN(_number)) {
                _message = pluralize(_message, _number);
            }

            _message = replace_placeholders(_message, parameters || {});

            return _message;
        },

        /**
         * Determines whether a message is registered or not.
         *
         * @param {String} key      The message id
         * @param {String} locale   The locale or null to use the default
         * @return {Boolean}        Returns `true` if the message is registered, `false` otherwise
         * @api public
         */
        has: function(key, locale) {
            var _locale = locale || Translator.locale || Translator.fallback;

            return undefined !== _messages[_locale] && undefined !== _messages[_locale][key];
        },

        /**
         * Accepts a JSON string to feed translations.
         *
         * @param {String} data     A JSON string or object literal
         * @return {Object}         Translator
         */
        fromJSON: function (data) {
            if(typeof data === "string") {
                data = JSON.parse(data);
            }

            if (data.locale) {
                this.locale = data.locale;
            }

            if (data.defaultDomains) {
                this.defaultDomains = data.defaultDomains;
            }

            if (data.messages) {
                for(var key in data.messages) {
                    this.add(key, data.messages[key]);
                }
            }

            return this;
        }
    };
})();

if (typeof window.define === "function" && window.define.amd) {
    window.define("Translator", [], function () {
        return Translator;
    });
}
