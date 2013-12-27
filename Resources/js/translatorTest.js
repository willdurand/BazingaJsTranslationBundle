var TestedTranslator = $.extend({}, Translator, {
    reset: function () {
        delete _messages;
    }
});

module('Translator', {
    setup: function () {
        TestedTranslator.reset();
    }
});

test('api definition', function () {
    expect(4);

    ok(TestedTranslator, 'TestedTranslator is defined');
    ok($.isFunction(TestedTranslator.trans), 'TestedTranslator.trans is a function');
    ok($.isFunction(TestedTranslator.transChoice), 'TestedTranslator.transChoice is a function');
    ok($.isFunction(TestedTranslator.add), 'TestedTranslator.add is a function');
});

test('add method', function () {
    expect(1);

    strictEqual(TestedTranslator.add('foo', 'bar'), TestedTranslator, 'The add method returns a TestedTranslator');
});

test('trans method', function () {
    expect(10);

    TestedTranslator.add('foo', 'bar', 'Foo');
    TestedTranslator.add('foo.with.arg', 'This is Ba %arg%');
    TestedTranslator.add('foo.with.arg', 'This is Ba %arg%', 'Foo');
    TestedTranslator.add('foo.with.args', 'There are %bananas% bananas and %apples% apples.', 'Foo');
    TestedTranslator.add('foo.with.replaces', '%repeat% %repeat% %repeat% !!!', 'Bar');
    TestedTranslator.add('empty', '', 'Foo');
    TestedTranslator.add('empty', '');

    // Basic
    equal(TestedTranslator.trans('foo', {}, 'Foo'), 'bar', 'Returns the correct message for the given key');
    equal(TestedTranslator.trans('foo.with.arg', {}, 'Foo'), 'This is Ba %arg%', 'Returns the correct message for the given key');
    equal(TestedTranslator.trans('foo.with.args', {}, 'Foo'), 'There are %bananas% bananas and %apples% apples.', 'Returns the correct message for the given key');

    equal(TestedTranslator.trans(''), '', 'Empty key returns empty message');
    equal(TestedTranslator.trans('unknown.key'), 'unknown.key', 'Unknown key returns the key as message');

    // Placeholders
    equal(TestedTranslator.trans('foo.with.arg', { arg: 'Bar' }, 'Foo'), 'This is Ba Bar', 'Returns the message with correct replaces');
    equal(TestedTranslator.trans('foo.with.args', { bananas: 10, apples: 2 }, 'Foo'), 'There are 10 bananas and 2 apples.', 'Returns the message with correct replaces');
    equal(TestedTranslator.trans('foo.with.replaces', { repeat: 'ah' }, 'Bar'), 'ah ah ah !!!', 'Returns the message with correct repeat replaces');

    // Empty string translations
    equal(TestedTranslator.trans('empty', {}, 'Foo'), '', 'An empty string translation should return the empty string and not the key.');

    // Message not in a domain with replaces
    equal(TestedTranslator.trans('Message not in the domain with %arg%', {arg: 'Bar'}), 'Message not in the domain with Bar', 'Message not in the domain with args returns the processed message');
});

test('transChoice method', function () {
    expect(24);

    TestedTranslator.add('foo.plural', '{0} Nothing|[1,Inf[ Many things', 'Foo');
    TestedTranslator.add('foo.plural.with.args', '{0} Nothing|{1} One thing|[2,Inf[ %count% things', 'Foo');
    TestedTranslator.add('foo.plural.with.inf', ']-Inf,0[ Underground|{0} Ground 0|{1} First level|[2,Inf[ High level', 'Foo');
    TestedTranslator.add('complex.plural', '{0} There is no apples|[20,Inf] There are many apples|There is one apple|a_few: There are %count% apples', 'Foo');
    TestedTranslator.add('foo.plural.space.before.interval', ' {0} Nothing| [1,Inf[ Many things', 'Foo');
    TestedTranslator.add('foo.plural.without.space', '{0}Nothing|[1,Inf[Many things', 'Foo');

    // Basic
    equal(TestedTranslator.transChoice('foo.plural', null, {}, 'Foo'), '{0} Nothing|[1,Inf[ Many things', 'Returns the correct message for the given key');

    // Translations
    equal(TestedTranslator.transChoice('foo.plural', 0, {}, 'Foo'), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(TestedTranslator.transChoice('foo.plural', 1, {}, 'Foo'), 'Many things', 'number = 1 returns the [1,Inf[ part of the message');
    equal(TestedTranslator.transChoice('foo.plural', 100, {}, 'Foo'), 'Many things', 'number = 100 returns the [1,Inf[ part of the message');

    equal(TestedTranslator.transChoice('foo.plural.with.args', 0, { count: 0 }, 'Foo'), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(TestedTranslator.transChoice('foo.plural.with.args', 1, { count: 1 }, 'Foo'), 'One thing', 'number = 1 returns the {1} part of the message');
    equal(TestedTranslator.transChoice('foo.plural.with.args', 2, { count: 2 }, 'Foo'), '2 things', 'number = 2 returns the [2,Inf[ part of the message');
    equal(TestedTranslator.transChoice('foo.plural.with.args', 100, { count: 100 }, 'Foo'), '100 things', 'number = 100 returns the [2,Inf[ part of the message');

    equal(TestedTranslator.transChoice('foo.plural.with.inf', -100000, {}, 'Foo'), 'Underground', 'number = -100000 returns the ]-Inf,0] part of the message');
    equal(TestedTranslator.transChoice('foo.plural.with.inf', -1, {}, 'Foo'), 'Underground', 'number = -1 returns the ]-Inf,0] part of the message');
    equal(TestedTranslator.transChoice('foo.plural.with.inf', 0, {}, 'Foo'), 'Ground 0', 'number = 0 returns the {0} part of the message');
    equal(TestedTranslator.transChoice('foo.plural.with.inf', 1, {}, 'Foo'), 'First level', 'number = 1 returns the {1} part of the message');
    equal(TestedTranslator.transChoice('foo.plural.with.inf', 10000, {}, 'Foo'), 'High level', 'number = 1000 returns the [2,Inf[ part of the message');

    equal(TestedTranslator.transChoice('complex.plural', 0, {}, 'Foo'), 'There is no apples', 'number = 0 returns the {0} part of the message');
    equal(TestedTranslator.transChoice('complex.plural', 1, {}, 'Foo'), 'There is one apple', 'number = 1 returns the standard rule');
    equal(TestedTranslator.transChoice('complex.plural', 9, { count: 9 }, 'Foo'), 'There are 9 apples', 'number = 10 returns the "a_few" part of the message');
    equal(TestedTranslator.transChoice('complex.plural', 20, {}, 'Foo'), 'There are many apples', 'number = 20 returns the [20,Inf] part of the message');

    // Translations with spaces before intervals
    equal(TestedTranslator.transChoice('foo.plural.space.before.interval', 0, {}, 'Foo'), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(TestedTranslator.transChoice('foo.plural.space.before.interval', 1, {}, 'Foo'), 'Many things', 'number = 1 returns the [1,Inf[ part of the message');
    equal(TestedTranslator.transChoice('foo.plural.space.before.interval', 100, {}, 'Foo'), 'Many things', 'number = 100 returns the [1,Inf[ part of the message');

    // Translations witout spaces
    equal(TestedTranslator.transChoice('foo.plural.without.space', 0, {}, 'Foo'), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(TestedTranslator.transChoice('foo.plural.without.space', 1, {}, 'Foo'), 'Many things', 'number = 1 returns the [1,Inf[ part of the message');
    equal(TestedTranslator.transChoice('foo.plural.without.space', 100, {}, 'Foo'), 'Many things', 'number = 100 returns the [1,Inf[ part of the message');

    // Message not in a domain with pluralization
    equal(TestedTranslator.transChoice('{0} Nothing|[1,Inf[ Many things', 0, {}), 'Nothing', 'number = 0 returns the {0} part of the message');
});

test('guesser', function () {
    expect(7);

    TestedTranslator.defaultDomains = [ 'Domain', 'messages' ];

    TestedTranslator.add('test', 'yop', 'Domain');
    TestedTranslator.add('test', 'lait', 'messages');
    TestedTranslator.add('foo.bar', 'baz', 'Domain');

    deepEqual(TestedTranslator.defaultDomains, [ 'Domain', 'messages' ], 'Default domains are well registered.');
    equal(TestedTranslator.trans('test'), 'yop', 'Returns the first guessed message corresponding to the given key');
    equal(TestedTranslator.trans('test', {}, 'messages'), 'lait', 'Guesser does not impact basic usage of get');
    equal(TestedTranslator.trans('foo.bar'), 'baz', 'Returns the correct guessed message');
    equal(TestedTranslator.trans('boo.baz'), 'boo.baz', 'Returns the key as the key cannot be guessed');
    equal(TestedTranslator.trans('foo.bar', {}, 'Domain'), 'baz', 'Returns the correct guessed message');
    equal(TestedTranslator.trans('foo.bar', {}, 'messages'), 'foo.bar', 'Returns the key as the given domain is wrong');
});

test('fromJSON', function () {
    expect(10);

    // accepts valid JSON string
    TestedTranslator.fromJSON('{ "locale": "en", "defaultDomains": [ "messages" ], "translations": { "en": { "messages": { "foo": "bar" } } } }');

    equal(TestedTranslator.locale, 'en', 'JSON parser processes locale from valid JSON string');
    deepEqual(TestedTranslator.defaultDomains, [ 'messages' ], 'JSON parser processes defaultDomains from valid JSON string');
    equal(TestedTranslator.trans('foo'), 'bar', 'JSON parser processes messages from valid JSON string');
    equal(TestedTranslator.trans('foo', {}, 'messages'), 'bar', 'JSON parser processes messages from valid JSON string');
    equal(TestedTranslator.trans('foo', {}, 'messages', 'en'), 'bar', 'JSON parser processes messages from valid JSON string');

    // accepts object literal
    TestedTranslator.fromJSON({
        "locale": "pt",
        "defaultDomains": [
            "more_messages"
        ],
        "translations": {
            "pt": {
                "more_messages": {
                    "moo": "mar"
                }
            }
        }
    });

    equal(TestedTranslator.locale, 'pt', 'JSON parser processes locale from valid object literal');
    deepEqual(TestedTranslator.defaultDomains, [ 'more_messages' ], 'JSON parser processes defaultDomains from valid object literal');
    equal(TestedTranslator.trans('moo'), 'mar', 'JSON parser processes messages from valid object literal');
    equal(TestedTranslator.trans('moo', {}, 'more_messages'), 'mar', 'JSON parser processes messages from valid object literal');
    equal(TestedTranslator.trans('moo', {}, 'more_messages', 'pt'), 'mar', 'JSON parser processes messages from valid object literal');
});

test('multiple locales', function () {
    expect(3);

    TestedTranslator.defaultDomains = [ 'messages' ];

    // Simulate i18n/messages/en.js loading
    TestedTranslator.add('symfony2.great', 'I like Symfony2', 'messages', 'en');
    TestedTranslator.add('symfony2.powerful', 'Symfony2 is powerful', 'messages', 'en');

    // Simulate i18n/messages/fr.js loading
    TestedTranslator.add('symfony2.great', 'J\'aime Symfony2', 'messages', 'fr');

    // Test with locale = fr
    TestedTranslator.locale = 'fr';
    equal(TestedTranslator.trans('symfony2.great'), 'J\'aime Symfony2', 'Return translation based on current locale');

    // Test with locale = en
    TestedTranslator.locale = 'en';
    equal(TestedTranslator.trans('symfony2.great'), 'I like Symfony2', 'Return translation based on previous locale');
    equal(TestedTranslator.trans('symfony2.powerful'), 'Symfony2 is powerful', 'Return translation based on previous locale');
});
