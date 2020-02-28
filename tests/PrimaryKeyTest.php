<?php
declare (strict_types = 1);

use PHPUnit\Framework\TestCase;

final class PrimaryKeyTest extends TestCase
{
	public function testCanBeCreated(): void
	{
		$pkNoValue = new PrimaryKey(array('COLUMN_NAME' => 'test_id'));
		$pkWithValue = new PrimaryKey(array('COLUMN_NAME' => 'test_id'), 1);

		$this->assertInstanceOf(PrimaryKey::class, $pkNoValue);
		$this->assertInstanceOf(PrimaryKey::class, $pkWithValue);
	}

	public function testCannotBeCreatedWithoutSchema(): void
	{
		$this->expectException(ArgumentCountError::class);

		$pk = new PrimaryKey();
	}

	public function testGetNoValue(): void
	{
		$pkNoValue = new PrimaryKey(array('COLUMN_NAME' => 'test_id'));

		$this->assertEquals($pkNoValue->key, 'test_id');
		$this->assertEquals($pkNoValue->value, null);
		$this->assertEquals($pkNoValue->schema, array('COLUMN_NAME' => 'test_id'));

		$this->expectException(InvalidArgumentException::class);

		$pkNoValue->foo;
	}

	public function testGetWithValue(): void
	{
		$pkWithValue = new PrimaryKey(array('COLUMN_NAME' => 'test_id'), 1);

		$this->assertEquals($pkWithValue->key, 'test_id');
		$this->assertEquals($pkWithValue->value, 1);
		$this->assertEquals($pkWithValue->schema, array('COLUMN_NAME' => 'test_id'));

		$this->expectException(InvalidArgumentException::class);

		$pkWithValue->foo;
	}

	public function testSet(): void
	{
		$pk = new PrimaryKey(array('COLUMN_NAME' => 'test_id'), 1);

		$pk->set(2);

		$this->assertEquals($pk->value, 2);
	}

	public function testToString(): void
	{
		$pkNoValue = new PrimaryKey(array('COLUMN_NAME' => 'test_id'));
		$pkWithValue = new PrimaryKey(array('COLUMN_NAME' => 'test_id'), 1);

		$this->assertEquals(strval($pkNoValue), '{"key":"test_id","value":null,"schema":{"COLUMN_NAME":"test_id"}}');
		$this->assertEquals(strval($pkWithValue), '{"key":"test_id","value":1,"schema":{"COLUMN_NAME":"test_id"}}');
	}
}
