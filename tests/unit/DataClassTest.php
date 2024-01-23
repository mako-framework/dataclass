<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\dataclass\tests\unit;

use mako\dataclass\tests\classes\Avatar;
use mako\dataclass\tests\classes\Link;
use mako\dataclass\tests\classes\User;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ValueError;

/**
 * @group unit
 */
class DataClassTest extends TestCase
{
	/**
	 *
	 */
	public function testDataClassWithValidInput(): void
	{
		$user = new User(
			name: 'frederic',
			username: 'freost',
			email: 'freost@example.org'
		);

		$this->assertSame('Frederic', $user->name);

		$this->assertSame('freost', $user->username);

		$this->assertSame('freost@example.org', $user->email);

		$this->assertNull($user->avatar);

		$this->assertIsArray($user->links);

		$this->assertEmpty($user->links);
	}

	/**
	 *
	 */
	public function testDataClassWithInvalidInput(): void
	{
		$this->expectException(ValueError::class);

		$this->expectExceptionMessage("Email must contain an '@'.");

		new User(
			name: 'frederic',
			username: 'freost',
			email: 'freostexample.org'
		);
	}

	/**
	 *
	 */
	public function testDataClassWithMissingProperty(): void
	{
		$this->expectException(RuntimeException::class);

		$this->expectExceptionMessage('Missing required property: email.');

		new User(
			name: 'frederic',
			username: 'freost'
		);
	}

	/**
	 *
	 */
	public function testDataClassWithNestedDataClass(): void
	{
		$user = new User(
			name: 'frederic',
			username: 'freost',
			email: 'freost@example.org',
			avatar: ['url' => 'https://example.org/avatar.png'],
		);

		$this->assertInstanceOf(Avatar::class, $user->avatar);

		$this->assertSame('https://example.org/avatar.png', $user->avatar->url);
	}

	/**
	 *
	 */
	public function testDataClassWithNestedDataClassWithInvalidInput(): void
	{
		$this->expectException(ValueError::class);

		$this->expectExceptionMessage("Url must start with 'https://'.");

		new User(
			name: 'frederic',
			username: 'freost',
			email: 'freost@example.org',
			avatar: ['url' => 'http://example.org/avatar.png'],
		);
	}

	/**
	 *
	 */
	public function testDataClassWithArrayOfNestedDataClasses(): void
	{
		$user = new User(
			name: 'frederic',
			username: 'freost',
			email: 'freost@example.org',
			links: [['url' => 'https://example.org', 'description' => 'Example']],
		);

		$this->assertIsArray($user->links);

		$this->assertInstanceOf(Link::class, $user->links[0]);

		$this->assertSame('https://example.org', $user->links[0]->url);

		$this->assertSame('Example', $user->links[0]->description);
	}

	/**
	 *
	 */
	public function testInstantiationFromArray(): void
	{
		$user = new User(...[
			'name' => 'frederic',
			'username' => 'freost',
			'email' => 'freost@example.org',
		]);

		$this->assertSame('Frederic', $user->name);

		$this->assertSame('freost', $user->username);

		$this->assertSame('freost@example.org', $user->email);

		$this->assertNull($user->avatar);

		$this->assertIsArray($user->links);

		$this->assertEmpty($user->links);
	}

	/**
	 *
	 */
	public function testToArray(): void
	{
		$array = [
			'name' => 'Frederic',
			'username' => 'freost',
			'email' => 'freost@example.org',
			'avatar' => null,
			'links' => [],
		];

		$user = new User(...$array);

		$this->assertSame($array, $user->toArray());
	}

	/**
	 *
	 */
	public function testJsonSerialization(): void
	{
		$json = '{"name":"Frederic","username":"freost","email":"freost@example.org","avatar":null,"links":[]}';

		$user = new User(...json_decode($json, associative: true));

		$this->assertSame($json, json_encode($user));
	}

	/**
	 *
	 */
	public function testFromJson(): void
	{
		$json = '{"name":"Frederic","username":"freost","email":"freost@example.org","avatar":null,"links":[]}';

		$user = User::fromJSON($json);

		$this->assertSame($json, json_encode($user));
	}
}
