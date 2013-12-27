test('api definition', function() {
    expect(5);

    ok(Translator, 'Translator is defined');
    ok($.isFunction(Translator.trans), 'Translator.trans is a function');
    ok($.isFunction(Translator.transChoice), 'Translator.transChoice is a function');
    ok($.isFunction(Translator.has), 'Translator.has is a function');
    ok($.isFunction(Translator.add), 'Translator.add is a function');
});

test('add/has methods', function() {
    expect(6);

    strictEqual(Translator.add('foo', 'bar'), Translator, 'The add method returns a Translator');
    ok(Translator.has('foo'), 'The has method returns true for a valid key');
    ok(!Translator.has('bar'), 'The has method returns false for a wrong key');
    ok(!Translator.has(undefined), 'The has method returns false for an undefined key');
    ok(!Translator.has(0), 'The has method returns false for the key 0');
    ok(!Translator.has(''), 'The has method returns false for an empty key');
});

test('trans method', function() {
    expect(17);

    Translator.add('Foo:foo', 'bar');
    Translator.add('Foo:foo.with.arg', 'This is Ba %arg%');
    Translator.add('Foo:foo.with.args', 'There are %bananas% bananas and %apples% apples.');
    Translator.add('Bar:foo.with.replaces', '%repeat% %repeat% %repeat% !!!');
    Translator.add('Foo:empty', '');

    // Basic
    equal(Translator.trans('Foo:foo'), 'bar', 'Returns the correct message for the given key');
    equal(Translator.trans('foo', {}, 'Foo'), 'bar', 'Returns the correct message for the given key');
    equal(Translator.trans('Foo:foo.with.arg'), 'This is Ba %arg%', 'Returns the correct message for the given key');
    equal(Translator.trans('foo.with.arg', {}, 'Foo'), 'This is Ba %arg%', 'Returns the correct message for the given key');
    equal(Translator.trans('Foo:foo.with.args'), 'There are %bananas% bananas and %apples% apples.', 'Returns the correct message for the given key');
    equal(Translator.trans('foo.with.args', {}, 'Foo'), 'There are %bananas% bananas and %apples% apples.', 'Returns the correct message for the given key');

    equal(Translator.trans(''), '', 'Empty key returns empty message');
    equal(Translator.trans('unknown.key'), 'unknown.key', 'Unknown key returns the key as message');

    // Placeholders
    equal(Translator.trans('Foo:foo.with.arg', { arg: 'Bar' }), 'This is Ba Bar', 'Returns the message with correct replaces');
    equal(Translator.trans('foo.with.arg', { arg: 'Bar' }, 'Foo'), 'This is Ba Bar', 'Returns the message with correct replaces');
    equal(Translator.trans('Foo:foo.with.args', { bananas: 10, apples: 2 }), 'There are 10 bananas and 2 apples.', 'Returns the message with correct replaces');
    equal(Translator.trans('foo.with.args', { bananas: 10, apples: 2 }, 'Foo'), 'There are 10 bananas and 2 apples.', 'Returns the message with correct replaces');
    equal(Translator.trans('Bar:foo.with.replaces', { repeat: 'ah' }), 'ah ah ah !!!', 'Returns the message with correct repeat replaces');
    equal(Translator.trans('foo.with.replaces', { repeat: 'ah' }, 'Bar'), 'ah ah ah !!!', 'Returns the message with correct repeat replaces');

    // Empty string translations
    equal(Translator.trans('Foo:empty'), '', 'An empty string translation should return the empty string and not the key.');
    equal(Translator.trans('empty', {}, 'Foo'), '', 'An empty string translation should return the empty string and not the key.');

    // Message not in a domain with replaces
    equal(Translator.trans('Message not in the domain with %arg%', {arg: 'Bar'}), 'Message not in the domain with Bar', 'Message not in the domain with args returns the processed message');
});

test('transChoice method', function() {
    expect(32);

    Translator.add('Foo:foo.plural', '{0} Nothing|[1,Inf[ Many things');
    Translator.add('Foo:foo.plural.with.args', '{0} Nothing|{1} One thing|[2,Inf[ %count% things');
    Translator.add('Foo:foo.plural.with.inf', ']-Inf,0[ Underground|{0} Ground 0|{1} First level|[2,Inf[ High level');
    Translator.add('Foo:complex.plural', '{0} There is no apples|[20,Inf] There are many apples|There is one apple|a_few: There are %count% apples');
    Translator.add('Foo:foo.plural.space.before.interval', ' {0} Nothing| [1,Inf[ Many things');
    Translator.add('Foo:foo.plural.without.space', '{0}Nothing|[1,Inf[Many things');

    // Basic
    equal(Translator.transChoice('Foo:foo.plural'), '{0} Nothing|[1,Inf[ Many things', 'Returns the correct message for the given key');
    equal(Translator.transChoice('foo.plural', null, {}, 'Foo'), '{0} Nothing|[1,Inf[ Many things', 'Returns the correct message for the given key');

    // Translations
    equal(Translator.transChoice('Foo:foo.plural', 0, {}), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('foo.plural', 0, {}, 'Foo'), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('Foo:foo.plural', 1, {}), 'Many things', 'number = 1 returns the [1,Inf[ part of the message');
    equal(Translator.transChoice('foo.plural', 1, {}, 'Foo'), 'Many things', 'number = 1 returns the [1,Inf[ part of the message');
    equal(Translator.transChoice('Foo:foo.plural', 100, {}), 'Many things', 'number = 100 returns the [1,Inf[ part of the message');
    equal(Translator.transChoice('foo.plural', 100, {}, 'Foo'), 'Many things', 'number = 100 returns the [1,Inf[ part of the message');

    equal(Translator.transChoice('Foo:foo.plural.with.args', 0, { count: 0 }), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('foo.plural.with.args', 0, { count: 0 }, 'Foo'), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('Foo:foo.plural.with.args', 1, { count: 1 }), 'One thing', 'number = 1 returns the {1} part of the message');
    equal(Translator.transChoice('foo.plural.with.args', 1, { count: 1 }, 'Foo'), 'One thing', 'number = 1 returns the {1} part of the message');
    equal(Translator.transChoice('Foo:foo.plural.with.args', 2, { count: 2 }), '2 things', 'number = 2 returns the [2,Inf[ part of the message');
    equal(Translator.transChoice('foo.plural.with.args', 2, { count: 2 }, 'Foo'), '2 things', 'number = 2 returns the [2,Inf[ part of the message');
    equal(Translator.transChoice('Foo:foo.plural.with.args', 100, { count: 100 }), '100 things', 'number = 100 returns the [2,Inf[ part of the message');
    equal(Translator.transChoice('foo.plural.with.args', 100, { count: 100 }, 'Foo'), '100 things', 'number = 100 returns the [2,Inf[ part of the message');

    equal(Translator.transChoice('Foo:foo.plural.with.inf', -100000, {}), 'Underground', 'number = -100000 returns the ]-Inf,0] part of the message');
    equal(Translator.transChoice('Foo:foo.plural.with.inf', -1, {}), 'Underground', 'number = -1 returns the ]-Inf,0] part of the message');
    equal(Translator.transChoice('Foo:foo.plural.with.inf', 0, {}), 'Ground 0', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('Foo:foo.plural.with.inf', 1, {}), 'First level', 'number = 1 returns the {1} part of the message');
    equal(Translator.transChoice('Foo:foo.plural.with.inf', 10000, {}), 'High level', 'number = 1000 returns the [2,Inf[ part of the message');

    equal(Translator.transChoice('Foo:complex.plural', 0, {}), 'There is no apples', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('Foo:complex.plural', 1, {}), 'There is one apple', 'number = 1 returns the standard rule');
    equal(Translator.transChoice('Foo:complex.plural', 9, { count: 9 }), 'There are 9 apples', 'number = 10 returns the "a_few" part of the message');
    equal(Translator.transChoice('Foo:complex.plural', 20, {}), 'There are many apples', 'number = 20 returns the [20,Inf] part of the message');

    // Translations with spaces before intervals
    equal(Translator.transChoice('Foo:foo.plural.space.before.interval', 0, {}), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('Foo:foo.plural.space.before.interval', 1, {}), 'Many things', 'number = 1 returns the [1,Inf[ part of the message');
    equal(Translator.transChoice('Foo:foo.plural.space.before.interval', 100, {}), 'Many things', 'number = 100 returns the [1,Inf[ part of the message');

    // Translations witout spaces
    equal(Translator.transChoice('Foo:foo.plural.without.space', 0, {}), 'Nothing', 'number = 0 returns the {0} part of the message');
    equal(Translator.transChoice('Foo:foo.plural.without.space', 1, {}), 'Many things', 'number = 1 returns the [1,Inf[ part of the message');
    equal(Translator.transChoice('Foo:foo.plural.without.space', 100, {}), 'Many things', 'number = 100 returns the [1,Inf[ part of the message');

    // Message not in a domain with pluralization
    equal(Translator.transChoice('{0} Nothing|[1,Inf[ Many things', 0, {}), 'Nothing', 'number = 0 returns the {0} part of the message');
});

test('guesser', function() {
    expect(7);

    Translator.defaultDomains = [ 'Domain', 'messages' ];

    Translator.add('Domain:test', 'yop');
    Translator.add('messages:test', 'lait');
    Translator.add('Domain:foo.bar', 'baz');

    deepEqual(Translator.defaultDomains, [ 'Domain', 'messages' ], 'Default domains are well registered.');
    equal(Translator.trans('test'), 'yop', 'Returns the first guessed message corresponding to the given key');
    equal(Translator.trans('messages:test'), 'lait', 'Guesser does not impact basic usage of get');
    equal(Translator.trans('foo.bar'), 'baz', 'Returns the correct guessed message');
    equal(Translator.trans('boo.baz'), 'boo.baz', 'Returns the key as the key cannot be guessed');
    equal(Translator.trans('foo.bar', {}, 'Domain'), 'baz', 'Returns the correct guessed message');
    equal(Translator.trans('foo.bar', {}, 'messages'), 'foo.bar', 'Returns the key as the given domain is wrong');
});

test('fromJson', function () {
    expect(6);

    // accepts valid JSON string
    Translator.fromJSON('{"locale":"en","defaultDomains":["messages"],"messages": {"messages:foo":"bar"}}');
    equal(Translator.locale, 'en', 'JSON parser processes locale from valid JSON string');
    deepEqual(Translator.defaultDomains, ['messages'], 'JSON parser processes defaultDomains from valid JSON string');
    equal(Translator.trans('messages:foo'), 'bar', 'JSON parser processes messages from valid JSON string');

    // accepts object literal
    Translator.fromJSON({"locale":"pt","defaultDomains":["more_messages"],"messages": {"more_messages:moo":"mar"}});
    equal(Translator.locale, 'pt', 'JSON parser processes locale from valid object literal');
    deepEqual(Translator.defaultDomains, ['more_messages'], 'JSON parser processes defaultDomains from valid object literal');
    equal(Translator.trans('more_messages:moo'), 'mar', 'JSON parser processes messages from valid object literal');
});

test('multiple locales', function() {
    expect(5);

    // Simulate i18n/messages/en.js loading
    Translator.locale = 'en';
    Translator.defaultDomains = ["messages"];
    Translator.add("messages:symfony2.great", "I like Symfony2");
    Translator.add("messages:symfony2.powerful", "Symfony2 is powerful");

    // Simulate i18n/messages/fr.js loading
    Translator.locale = 'fr';
    Translator.defaultDomains = ["messages"];
    Translator.add("messages:symfony2.great", "J'aime Symfony2");

    // Test with locale = fr
    equal(Translator.trans('messages:symfony2.great'), "J'aime Symfony2", 'Return translation based on current locale');
    ok(!Translator.has('messages:symfony2.powerful'), 'Translation set for another locale is not available for current locale');

    // Test with locale = en
    Translator.locale = 'en';
    equal(Translator.trans('messages:symfony2.great'), "I like Symfony2", 'Return translation based on previous locale');
    ok(Translator.has('messages:symfony2.powerful'), 'Translation set for previous locale is still available');
    equal(Translator.trans('messages:symfony2.powerful'), "Symfony2 is powerful", 'Return translation based on previous locale');
});
