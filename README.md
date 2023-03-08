# DataClass

[![Static analysis](https://github.com/mako-framework/dataclass/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/mako-framework/dataclass/actions/workflows/static-analysis.yml)


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

$user = new User(...json_decode($json, associative: true));
```
