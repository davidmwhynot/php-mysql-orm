<?php
declare (strict_types = 1);

use PHPUnit\Framework\TestCase;

final class ForeignKeyTest extends TestCase
{
	public function testCanBeCreated(): void
	{
		$fkNoValueNoSchema = new ForeignKey(array('COLUMN_NAME' =>
			'test_id'));
		$fkNoSchema = new ForeignKey(array('COLUMN_NAME' =>
			'test_id'), null, 1);
		$fk;

		$this->assertInstanceOf(ForeignKey::class, $fkNoValue);
		$this->assertInstanceOf(ForeignKey::class, $fk);
	}

	public function testCannotBeCreatedWithoutSchema(): void
	{
		$this->expectException(ArgumentCountError::class);

		$fk = new ForeignKey();
	}

	public function testGetNoValue(): void
	{
		$fkNoValue = new ForeignKey(array('COLUMN_NAME' => 'test_id'));

		$this->assertEquals($fkNoValue->key, 'test_id');
		$this->assertEquals($fkNoValue->value, null);
		$this->assertEquals($fkNoValue->schema, array('COLUMN_NAME' =>
			'test_id'));

		$this->expectException(InvalidArgumentException::class);

		$fkNoValue->foo;
	}

	public function testGetWithValue(): void
	{
		$fk = new ForeignKey(array('COLUMN_NAME' =>
			'test_id'), null, 1);

		$this->assertEquals($fk->key, 'test_id');
		$this->assertEquals($fk->value, 1);
		$this->assertEquals($fk->schema, array('COLUMN_NAME' =>
			'test_id'));

		$this->expectException(InvalidArgumentException::class);

		$fk->foo;
	}

	public function testSet(): void
	{
		$fk = new ForeignKey(array('COLUMN_NAME' => 'test_id'), null,
			1);

		$fk->set(2);

		$this->assertEquals($fk->value, 2);
	}

	public function testToString(): void
	{
		$fkNoValue = new ForeignKey(array('COLUMN_NAME' => 'test_id'));
		$fk = new ForeignKey(array('COLUMN_NAME' =>
			'test_id'), null, 1);

		$this->assertEquals(strval($fkNoValue),

			'{"key":"test_id","value":null,"schema":{"COLUMN_NAME":"test_id"}}');
		$this->assertEquals(strval($fk),

			'{"key":"test_id","value":1,"schema":{"COLUMN_NAME":"test_id"}}');
	}
}
