<?php

namespace mako\dataclass\tests\classes;

use mako\dataclass\attributes\ArrayOf;
use mako\dataclass\attributes\Validator;
use mako\dataclass\DataClass;
use ValueError;

class User extends DataClass
{
	public string $name;
	public string $username;
	public string $email;
	public ?Avatar $avatar = null;
	#[ArrayOf(Link::class)]
	public array $links = [];

	#[Validator('name')]
	protected function validateName(string $name): string
	{
		return mb_convert_case($name, MB_CASE_TITLE);
	}

	#[Validator('email')]
	protected function validateEmail(string $email): string
	{
		if (str_contains($email, '@') === false) {
			throw new ValueError("Email must contain an '@'.");
		}

		return $email;
	}
}
