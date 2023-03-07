<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\dataclass;

use JsonSerializable;
use mako\dataclass\attributes\ArrayOf;
use mako\dataclass\attributes\Validator;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use RuntimeException;
use stdClass;

use function array_diff_key;
use function class_parents;
use function count;
use function implode;
use function in_array;
use function vsprintf;

/**
 * Data class.
 */
abstract class DataClass implements JsonSerializable
{
	/**
	 * Property info cache.
	 */
	protected static array $__cache__ = [];

	/**
	 * Constructor.
	 */
	final public function __construct(...$props)
	{
		// Cache property details

		if(array_key_exists(static::class, static::$__cache__) === false)
		{
			static::cachePropDetails();
		}

		// Check for missing required properties

		if(!empty($missing = array_diff_key(static::$__cache__[static::class]['required_props'], $props)))
		{
			throw new RuntimeException(vsprintf('Missing required %s: %s.', [count($missing) > 1 ? 'properties' : 'property', implode(',', $missing)]));
		}

		// Initialize properties

		foreach($props as $name => $value)
		{
			$propInfo = static::$__cache__[static::class]['prop_info'][$name];

			if($propInfo['dataclass'] !== null)
			{
				if($propInfo['is_array'])
				{
					$dataclasses = [];

					foreach($value as $valueData)
					{
						$dataclasses[] = new $propInfo['dataclass'](...$valueData);
					}

					$value = $dataclasses;
				}
				else
				{
					$value = new $propInfo['dataclass'](...$value);
				}
			}
			else
			{
				foreach($propInfo['validators'] as $validator)
				{
					$value = $this->{$validator}($value);
				}
			}

			$this->$name = $value;
		}
	}

	/**
	 * Caches property details.
	 */
	final protected static function cachePropDetails(): void
	{
		static::$__cache__[static::class] = [
			'required_props' => [],
			'prop_info'      => [],
		];

		$reflection = new ReflectionClass(static::class);

		// Cache property details

		foreach($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $prop)
		{
			$propName = $prop->getName();

			static::$__cache__[static::class]['prop_info'][$propName] = [
				'validators' => [],
				'is_array'   => false,
				'dataclass'  => null,
			];

			// Cache required properties

			if($prop->hasDefaultValue() === false)
			{
				static::$__cache__[static::class]['required_props'][$propName] = $propName;
			}

			// Cache property information

			$type = $prop->getType();

			if($type instanceof ReflectionNamedType)
			{
				if($type->isBuiltin())
				{
					if($type->getName() === 'array')
					{
						static::$__cache__[static::class]['prop_info'][$propName]['is_array'] = true;

						$attributes = $prop->getAttributes(ArrayOf::class);

						if(!empty($attributes))
						{
							static::$__cache__[static::class]['prop_info'][$propName]['dataclass'] = $attributes[0]->newInstance()->getType();
						}
					}
				}
				else
				{
					$typeName = $type->getName();

					if(in_array(self::class, class_parents($typeName)))
					{
						static::$__cache__[static::class]['prop_info'][$propName]['dataclass'] = $typeName;
					}
				}
			}
		}

		// Cache property validators

		foreach($reflection->getMethods() as $method)
		{
			$attributes = $method->getAttributes(Validator::class);

			foreach($attributes as $attribute)
			{
				$property = $attribute->newInstance()->getProperty();

				static::$__cache__[static::class]['prop_info'][$property]['validators'][] = $method->getName();
			}
		}
	}

	/**
	 * Returns an array representation of the data class.
	 */
	final public function toArray(): array
	{
		$array = [];

		$reflection = new ReflectionClass(static::class);

		$props = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

		foreach($props as $prop)
		{
			$name = $prop->getName();

			$array[$name] = $this->$name;
		}

		return $array;
	}

	/**
	 * Returns a stdClass representation of the data class.
	 */
	final public function toObject(): stdClass
	{
		return (object) $this->toArray();
	}

	/**
	 * Returns the data that should be serialized to JSON.
	 */
	final public function jsonSerialize(): mixed
	{
		return $this->toArray();
	}
}
