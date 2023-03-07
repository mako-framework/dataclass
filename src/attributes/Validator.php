<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\dataclass\attributes;

use Attribute;

/**
 * Validator attribute.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Validator
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $property
	)
	{}

	/**
	 * Returns the validator target name.
	 */
	public function getProperty(): string
	{
		return $this->property;
	}
}
