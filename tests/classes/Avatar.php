<?php

namespace mako\dataclass\tests\classes;

use mako\dataclass\attributes\Validator;
use mako\dataclass\DataClass;
use ValueError;

class Avatar extends DataClass
{
	public string $url;

	#[Validator('url')]
	protected function validateEmail(string $url): string
	{
		if (str_starts_with($url, 'https://') === false) {
			throw new ValueError("Url must start with 'https://'.");
		}

		return $url;
	}
}
