test('api definition', function() {
  expect(4);

  ok(ExposeTranslation, 'ExposeTranslation is defined');
  ok($.isFunction(ExposeTranslation.get), 'ExposeTranslation.get is a function');
  ok($.isFunction(ExposeTranslation.has), 'ExposeTranslation.has is a function');
  ok($.isFunction(ExposeTranslation.add), 'ExposeTranslation.add is a function');
});

test('add/has methods', function() {
  expect(6);

  strictEqual(ExposeTranslation.add('foo', 'bar'), ExposeTranslation,
    'The add method returns a ExposeTranslation');
  ok(ExposeTranslation.has('foo'),
    'The has method returns true for a valid key');
  ok(!ExposeTranslation.has('bar'),
    'The has method returns false for a wrong key');
  ok(!ExposeTranslation.has(undefined),
    'The has method returns false for an undefined key');
  ok(!ExposeTranslation.has(0),
    'The has method returns false for the key 0');
  ok(!ExposeTranslation.has(''),
    'The has method returns false for an empty key');
});

test('get method', function() {
  expect(34);

  ExposeTranslation.add('Foo:foo', 'bar');
  ExposeTranslation.add('Foo:foo.with.arg', 'This is Ba %arg%');
  ExposeTranslation.add('Foo:foo.with.args', 'There are %bananas% bananas and %apples% apples.');
  ExposeTranslation.add('Bar:foo.with.replaces', '%repeat% %repeat% %repeat% !!!');
  ExposeTranslation.add('Foo:foo.plural', '{0} Nothing|[1,Inf[ Many things');
  ExposeTranslation.add('Foo:foo.plural.with.args', '{0} Nothing|{1} One thing|[2,Inf[ %count% things');
  ExposeTranslation.add('Foo:foo.plural.with.inf', ']-Inf,0[ Underground|{0} Ground 0|{1} First level|[2,Inf[ High level');
  ExposeTranslation.add('Foo:complex.plural', '{0} There is no apples|[20,Inf] There are many apples|There is one apple|a_few: There are %count% apples');
  ExposeTranslation.add('Foo:foo.plural.space.before.interval', ' {0} Nothing| [1,Inf[ Many things');
  ExposeTranslation.add('Foo:foo.plural.without.space', '{0}Nothing|[1,Inf[Many things');
  ExposeTranslation.add('Foo:empty', '');

  // Basic
  equal(ExposeTranslation.get('Foo:foo'), 'bar',
    'Returns the correct message for the given key');
  equal(ExposeTranslation.get('Foo:foo.with.arg'), 'This is Ba %arg%',
    'Returns the correct message for the given key');
  equal(ExposeTranslation.get('Foo:foo.with.args'), 'There are %bananas% bananas and %apples% apples.',
    'Returns the correct message for the given key');
  equal(ExposeTranslation.get('Foo:foo.plural'), '{0} Nothing|[1,Inf[ Many things',
    'Returns the correct message for the given key');

  equal(ExposeTranslation.get(''), '',
    'Empty key returns empty message');
  equal(ExposeTranslation.get('unknown.key'), 'unknown.key',
    'Unknown key returns the key as message');

  // Placeholders
  equal(ExposeTranslation.get('Foo:foo.with.arg', { arg: 'Bar' }), 'This is Ba Bar',
    'Returns the message with correct replaces');
  equal(ExposeTranslation.get('Foo:foo.with.args', { bananas: 10, apples: 2 }), 'There are 10 bananas and 2 apples.',
    'Returns the message with correct replaces');
  equal(ExposeTranslation.get('Bar:foo.with.replaces', { repeat: 'ah' }), 'ah ah ah !!!',
    'Returns the message with correct repeat replaces');

  // Translations
  equal(ExposeTranslation.get('Foo:foo.plural', {}, 0), 'Nothing',
    'number = 0 returns the {0} part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural', {}, 1), 'Many things',
    'number = 1 returns the [1,Inf[ part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural', {}, 100), 'Many things',
    'number = 100 returns the [1,Inf[ part of the message');

  equal(ExposeTranslation.get('Foo:foo.plural.with.args', { count: 0 }, 0), 'Nothing',
    'number = 0 returns the {0} part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural.with.args', { count: 1 }, 1), 'One thing',
    'number = 1 returns the {1} part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural.with.args', { count: 2 }, 2), '2 things',
    'number = 2 returns the [2,Inf[ part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural.with.args', { count: 100 }, 100), '100 things',
    'number = 100 returns the [2,Inf[ part of the message');

  equal(ExposeTranslation.get('Foo:foo.plural.with.inf', {}, -100000), 'Underground',
    'number = -100000 returns the ]-Inf,0] part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural.with.inf', {}, -1), 'Underground',
    'number = -1 returns the ]-Inf,0] part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural.with.inf', {}, 0), 'Ground 0',
    'number = 0 returns the {0} part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural.with.inf', {}, 1), 'First level',
    'number = 1 returns the {1} part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural.with.inf', {}, 10000), 'High level',
    'number = 1000 returns the [2,Inf[ part of the message');

  equal(ExposeTranslation.get('Foo:complex.plural', {}, 0), 'There is no apples',
    'number = 0 returns the {0} part of the message');
  equal(ExposeTranslation.get('Foo:complex.plural', {}, 1), 'There is one apple',
    'number = 1 returns the standard rule');
  equal(ExposeTranslation.get('Foo:complex.plural', { count: 9 }, 9), 'There are 9 apples',
    'number = 10 returns the "a_few" part of the message');
  equal(ExposeTranslation.get('Foo:complex.plural', {}, 20), 'There are many apples',
    'number = 20 returns the [20,Inf] part of the message');

  // Translations with spaces before intervals
  equal(ExposeTranslation.get('Foo:foo.plural.space.before.interval', {}, 0), 'Nothing',
    'number = 0 returns the {0} part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural.space.before.interval', {}, 1), 'Many things',
    'number = 1 returns the [1,Inf[ part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural.space.before.interval', {}, 100), 'Many things',
    'number = 100 returns the [1,Inf[ part of the message');

  // Translations witout spaces
  equal(ExposeTranslation.get('Foo:foo.plural.without.space', {}, 0), 'Nothing',
    'number = 0 returns the {0} part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural.without.space', {}, 1), 'Many things',
    'number = 1 returns the [1,Inf[ part of the message');
  equal(ExposeTranslation.get('Foo:foo.plural.without.space', {}, 100), 'Many things',
    'number = 100 returns the [1,Inf[ part of the message');

  // Empty string translations
  equal(ExposeTranslation.get('Foo:empty'), '',
    'An empty string translation should return the empty string and not the key.');

  // Message not in a domain with replaces
  equal(ExposeTranslation.get('Message not in the domain with %arg%',{arg: 'Bar'}), 'Message not in the domain with Bar',
    'Message not in the domain with args returns the processed message');

  // Message not in a domain with pluralization
  equal(ExposeTranslation.get('{0} Nothing|[1,Inf[ Many things', {}, 0), 'Nothing',
    'number = 0 returns the {0} part of the message');
});

test('guesser', function() {
  expect(5);

  ExposeTranslation.defaultDomains = [ 'Domain', 'messages' ];

  ExposeTranslation.add('Domain:test', 'yop');
  ExposeTranslation.add('messages:test', 'lait');
  ExposeTranslation.add('Domain:foo.bar', 'baz');

  deepEqual(ExposeTranslation.defaultDomains, [ 'Domain', 'messages' ],
    'Default domains are well registered.');

  equal(ExposeTranslation.get('test'), 'yop',
    'Returns the first guessed message corresponding to the given key');
  equal(ExposeTranslation.get('messages:test'), 'lait',
    'Guesser does not impact basic usage of get');
  equal(ExposeTranslation.get('foo.bar'), 'baz',
    'Returns the correct guessed message');
  equal(ExposeTranslation.get('boo.baz'), 'boo.baz',
    'Returns the key as the key cannot be guessed');
});
