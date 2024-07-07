# DataClass

[![Tests](https://github.com/mako-framework/dataclass/actions/workflows/tests.yml/badge.svg)](https://github.com/mako-framework/dataclass/actions/workflows/tests.yml)
[![Static analysis](https://github.com/mako-framework/dataclass/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/mako-framework/dataclass/actions/workflows/static-analysis.yml)


## Examples

### Basic usage

```php
<?php

use mako\dataclass\DataClass;

class User extends DataClass
{
	public string $username;
	public string $email;
}

// Basic instantiation

$user = new User(
	username: 'freost',
	email: 'freost@example.org',
);

// Instantiation from an array

$array = [
	'username' => 'freost',
	'email' => 'freost@example.org',
];

$user = new User(...$array);

// Instantiation from JSON

$json = <<<JSON
{
	"username": "freost",
	"email": "freost@example.org"
}
JSON;

$user = User::fromJSON($json);

$user = new User(...json_decode($json, associative: true));
```

### Validation

```php
<?php

use mako\dataclass\DataClass;
use mako\dataclass\attributes\Validator;

class User extends DataClass
{
	public string $username;
	public string $email;

	#[Validator('username')]
	protected function usernameMustNotContainSpace(string $username): string
	{
		if (str_contains($username, ' ') === true) {
			throw new ValueError('username must not contain a space');
		}

		return $username;
	}
}

// An error will now be thrown if the username property contains a space

$user = new User(
	username: 'freost',
	email: 'freost@example.org',
);
```

### Nested data classes

```php
<?php

use mako\dataclass\DataClass;
use mako\dataclass\attributes\Validator;

class Avatar extends DataClass
{
	public string $url;
}

class User extends DataClass
{
	public string $username;
	public string $email;
	public Avatar $avatar;
}

// The avatar property will be instantiated as an Avatar instance

$user = new User(
	username: 'freost',
	email: 'freost@example.org',
	avatar: ['url' => 'https://example.org/avatar.png'],
);
```

### Arrays of nested data classes

```php
<?php

use mako\dataclass\DataClass;
use mako\dataclass\attributes\ArrayOf;
use mako\dataclass\attributes\Validator;

class Avatar extends DataClass
{
	public string $url;
}

class User extends DataClass
{
	public string $username;
	public string $email;
	#[ArrayOf(Avatar::class)]
	public array $avatars;
}

// The elements of the avatars property will be instantiated as Avatar instances

$user = new User(
	username: 'freost',
	email: 'freost@example.org',
	avatars: [['url' => 'https://example.org/avatar.png']],
);
```
