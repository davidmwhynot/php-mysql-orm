<?php
// declare (strict_types = 1);

use PHPUnit\Framework\TestCase;

include dirname(__DIR__) . '/env.php';

final class ModelTest extends TestCase
{
	public function testCanBeCreated(): void
	{
		$modelNoPK = new Model('federation');
		$modelPK = new Model('federation', 1);

		$this->assertInstanceOf(Model::class, $modelNoPK);
		$this->assertInstanceOf(Model::class, $modelPK);
	}

	public function testCannotBeCreatedWithoutTableName(): void
	{
		$this->expectException(ArgumentCountError::class);

		$model = new Model();
	}

	public function testCannotBeCreatedWithInvalidTableName(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$model = new Model('foo', 1);
	}

	public function testCanBeUpdated(): void
	{
		$model = new Model('federation', 1);

		$reversedName = strrev($model->fed_name);

		$model->fed_name = $reversedName;

		$result = $model->save();

		$this->assertTrue($result);

		$newModel = new Model('federation', 1);

		$this->assertEquals($reversedName, $newModel->fed_name);
	}

	public function testUnchangedModelUpdateReturnsFalse(): void
	{
		$model = new Model('federation', 1);

		$result = $model->save();

		$this->assertFalse($result);
	}

	public function testCannotBeInsertedIfMissingRequiredFields(): void
	{
		$this->expectException(Exception::class);

		$model = new Model('federation');

		$model->save();

		$model = new Model('federation', 1);

		$model->fed_status = 'abc';

		$model->save();
	}

	public function testCannotBeUpdatedWithInvalidDatatypeInt(): void
	{
		$this->expectException(Exception::class);

		$model = new Model('federation', 1);

		$model->fed_name = 1;

		$model->save();
	}

	public function testCanBeInserted(): void
	{
		$model = new Model('fed_log');

		$model->fed_log_fed_id_f = 1;
		$model->fed_log_originator_empire_id_f = 1;
		$model->fed_log_entry_qualifier = 1;
		$model->fed_log_entry_id_f = 1;

		$this->assertTrue($model->save());
	}

	public function testCannotBeInsertedWithInvalidDatatypeString(): void
	{
		$this->expectException(Exception::class);

		$model = new Model('fed_log');

		$model->fed_log_fed_id_f = 1;
		$model->fed_log_originator_empire_id_f = 'abc';
		$model->fed_log_entry_qualifier = 1;
		$model->fed_log_entry_id_f = 1;

		$model->save();
	}

	public function testCannotBeInsertedWithInvalidDatatypeInt(): void
	{
		$this->expectException(Exception::class);

		$model = new Model('fed_ballot');

		$model->fed_ballot_fed_id_f = 1;
		$model->fed_ballot_title = 1;
		$model->fed_ballot_proposal = 'abc';
		$model->fed_ballot_empire_proposing_id_f = 1;
		$model->fed_ballot_empire_second_id_f = 1;
		$model->fed_ballot_start = '0000-00-00 00:00:00';
		$model->fed_ballot_stop = '0000-00-01 00:00:00';
		$model->fed_ballot_votes_yes = 1;
		$model->fed_ballot_votes_no = 1;
		$model->fed_ballot_votes_abstain = 1;

		$model->save();
	}

	public function testCanGetModelFields(): void
	{
		$model = new Model('fed_log');

		$this->assertEquals($model->_table, 'fed_log');
	}

	public function testCanGetModelPrimaryKey(): void
	{
		$model = new Model('fed_log', 1);

		$this->assertEquals($model->fed_log_id, 1);
	}

	public function testGetMissingFieldReturnsNull(): void
	{
		$model = new Model('fed_log', 1);

		$this->assertEquals($model->foo, null);
	}

	public function testCannotSetPrivateField(): void
	{
		$this->expectException(Exception::class);
		$model = new Model('fed_log');

		$model->_pk = 'abc';
	}

	public function testToString(): void
	{
		$model = new Model('fed_log');

		$this->assertEquals(
			strval($model),

			'{"fed_log_fed_id_f":null,"fed_log_originator_empire_id_f":null,"fed_log_entry_qualifier":null,"fed_log_entry_id_f":null,"fed_log_entry_timestamp":null}'
		);
	}

	public function testForeignKeys(): void
	{
		$model = new BaseModel('federation', 1);

		// $model->fed_founder_empire->empire_name = 'abc123';
		// $model->fed_founder_empire->save();

		print(PHP_EOL);
		print(strval($model->_fks['fed_founder_empire_id_f']));
		print(PHP_EOL);
		// print(strval($model));

		// $this->assertTrue(true);
	}

}
