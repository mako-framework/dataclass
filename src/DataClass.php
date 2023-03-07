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

/**
 * Data class.
 */
abstract class DataClass implements JsonSerializable
{
	/**
	 * Validator cache.
	 */
	protected static array $__cache__ = [];

	/**
	 * Constructor.
	 */
	final public function __construct(...$props)
	{
		// Cache validators and prop details

		if(array_key_exists(static::class, static::$__cache__) === false)
		{
			static::cacheValidatorsAndPropDetails();
		}

		// Check for missing required props

		if(!empty($missing = array_diff_key(static::$__cache__[static::class]['required_props'], $props)))
		{
			throw new RuntimeException(vsprintf('Missing required %s: %s.', [count($missing) > 1 ? 'properties' : 'property', implode(',', $missing)]));
		}

		// Initialize props

		foreach($props as $name => $value)
		{
			$propInfo = static::$__cache__[static::class]['prop_info'][$name];

			if($propInfo['model'] !== null)
			{
				if($propInfo['is_array'])
				{
					$models = [];

					foreach($value as $valueData)
					{
						$models[] = new $propInfo['model'](...$valueData);
					}

					$value = $models;
				}
				else
				{
					$value = new $propInfo['model'](...$value);
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
	 * Caches validators and prop details.
	 */
	final protected static function cacheValidatorsAndPropDetails(): void
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
				'model'      => null,
			];

			// Cache required props

			if($prop->hasDefaultValue() === false)
			{
				static::$__cache__[static::class]['required_props'][$propName] = $propName;
			}

			// Cache prop info

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
							static::$__cache__[static::class]['prop_info'][$propName]['model'] = $attributes[0]->newInstance()->getType();
						}
					}
				}
				else
				{
					$typeName = $type->getName();

					if(in_array(self::class, class_parents($typeName)))
					{
						static::$__cache__[static::class]['prop_info'][$propName]['model'] = $typeName;
					}
				}
			}
		}

		// Cache validators

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
	 * Returns an array representation of the model.
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
	 * Returns a stdClass representation of the model.
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
