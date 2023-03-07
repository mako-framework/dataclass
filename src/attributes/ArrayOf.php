<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\dataclass\attributes;

use Attribute;

/**
 * ArrayOf attribute.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ArrayOf
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $type
	)
	{}

	/**
	 * Returns the type.
	 */
	public function getType(): string
	{
		return $this->type;
	}
}
