<?php

class BaseModel
{

	/**
	 * @var PDO $_pdo database connection object
	 */
	protected $_pdo;

	/**
	 * @var string $_table name of the table that this model represents
	 */
	protected $_table;

	/**
	 * @var PrimaryKey $_pk the table's primary key
	 */
	protected $_pk;

	/**
	 * @var string[] $_keys the names of the table's columns (not including the
	 * primary key's column's name)
	 */
	protected $_keys;

	/**
	 * @var Schema[] $_schema metadata for the table's columns
	 */
	protected $_schema;

	/**
	 * @var mixed[] $_pristine should mirror the values that are currently
	 * stored in the database for this record
	 */
	protected $_pristine;

	/**
	 * @var ForeignKey[] $_fks array of the model's foreign keys
	 */
	protected $_fks;

	/**
	 * @var string[] $_fks_col_names array of the column names of the model's
	 * foreign keys
	 */
	protected $_fks_col_names;

	// list of data types we use
	// TODO: make sure all of these are covered
	/*
	 * +------------+
	 * | data_type  |
	 * +------------+
	 * | int        | --
	 * | tinyint    | --
	 * | timestamp  | -?
	 * | smallint   | --
	 * | varchar    | --
	 * | datetime   | -?
	 * | text       | -?
	 * | decimal    |
	 * | double     |
	 * | longblob   |
	 * | mediumtext | -?
	 * | bigint     | --
	 * | mediumint  | --
	 * | char       | -?
	 * | blob       |
	 * | date       | -?
	 * | tinytext   | -?
	 * +------------+
	 */

	protected const INT_DATATYPES = array('int', 'tinyint', 'smallint',
		'bigint', 'mediumint');
	protected const STRING_DATATYPES = array('varchar', 'timestamp',
		'text', 'mediumtext', 'date', 'tinytext');

	/**
	 * @param string $table
	 * @param int $pk
	 */
	public function __construct(string $table, int $pk = null)
	{
		// initialize values
		$this->_table = $table;
		$this->_pk = null;
		$this->_keys = array();
		$this->_schema = array();
		$this->_pristine = array();
		$this->_fks = array();
		$this->_fks_col_names = array();

		// establish database connection
		$dbc = new Database();
		$this->_pdo = $dbc->getConnection();

		// get table metadata
		$schema = $dbc->getTableSchema($table);
		$foreign_keys = $dbc->getForeignKeys($table);

		foreach ($foreign_keys as $fk)
		{
			$this->_fks[$fk['COLUMN_NAME']] = new ForeignKey($fk);

			array_push($this->_fks_col_names,
				$fk['CONSTRAINT_NAME']);
			$fk_field_name = $fk['CONSTRAINT_NAME'];
			$this->$fk_field_name = null;
		}

		// set the model's properties based on the schema
		foreach ($schema as $field)
		{
			$key = $field['COLUMN_NAME'];

			if ($field['COLUMN_KEY'] === 'PRI')
			{
				$this->_pk = new PrimaryKey($field);
			}
			else if (in_array($key, array_keys($this->_fks)))
			{
				$this->_fks[$key]->schema = $field;
			}
			else
			{
				$this->$key = null;
				$this->_schema[$key] = $field;
			}
		}

		$this->_keys = array_keys($this->_schema);

		if (!is_null($pk))
		{
			// fetch the existing record if a primary key was provided and
			// update the model's properties to refelect the new data
			$fields = $this->get($pk);

			foreach ($fields as $key => $val)
			{
				if ($key === $this->_pk->key)
				{
					$this->_pk->set($val);
				}
				else if (in_array($key, array_keys($this
					->_fks)))
				{
					$fk = $this->_fks[$key];
					$fk_field_name = $fk

						->constraints['CONSTRAINT_NAME'];
					$fk_table_name = $fk

						->constraints['REFERENCED_TABLE_NAME'];

					$this->_fks[$key]->value = $val;

					$this->$fk_field_name = new
					Model($fk_table_name, $val);

				}
				else
				{
					$this->$key = $val;
				}
			}
		}

		// store the inital (unchanged) values for this record
		foreach ($this->_keys as $key)
		{
			$this->_pristine[$key] = $this->$key;
		}
	}

	public function __destruct()
	{
		$this->_pdo = null;
	}

	/**
	 * get a row
	 * @param int $pk primary key of the row to get
	 * @return array data for the row
	 */
	private function get(int $pk): array
	{
		$_pk = $this->_pk->key;

		$query = $this->_pdo->prepare(
			"SELECT * FROM $this->_table WHERE $_pk = :pk LIMIT 1"
		);
		$query->execute([':pk' => $pk]);

		$results = $query->fetch(PDO::FETCH_ASSOC);

		foreach ($results as $key => $val)
		{
			$datatype = null;

			if ($key === $_pk)
			{
				// $this->_pk->set($val);
				$datatype = $this->_pk->schema['DATA_TYPE'];
			}
			else if (in_array($key, array_keys($this->_fks)))
			{
				$datatype = $this->_fks[$key]
					->schema['DATA_TYPE'];
			}
			else
			{
				$datatype = $this->_schema[$key]['DATA_TYPE'];
			}

			// TODO: special cases for additional datatypes
			if (in_array($datatype, Model::INT_DATATYPES))
			{
				$results[$key] = (int) $val;
			}
			else
			{
				$results[$key] = $val;
			}
		}

		return $results;
	}

	/**
	 * @param $col string name of the column to validate
	 * @return array validation results
	 */
	private function _validateColumn(string $col): array
	{
		$result = array('valid' => true, 'useDefault' => false);

		$isValueNull = is_null($this->$col);
		$isColumnNotNullable = $this->_schema[$col]['IS_NULLABLE'] ===
			'NO';
		$columnHasDefault = array_key_exists('COLUMN_DEFAULT', $this
				->_schema[$col]);

			$columnDataType = $this->_schema[$col]['DATA_TYPE'];
			$valueDataType = gettype($this->$col);

		if ($isValueNull && $isColumnNotNullable)
		{
			// null value validation rules
			if ($columnHasDefault)
			{
				$columnDefault = $this
					->_schema[$col]['COLUMN_DEFAULT'];

				if (is_null($columnDefault))
				{
					// if the column is null, is not nullable, and has a default but the default is null, it is invalid
					$result['valid'] = false;
				}
				else
				{
					// if the column is null, is not nullable, and has a default that is not null, use the default
					$result['useDefault'] = true;
				}
			}
			else
			{
				// if the column is null, is not nullable, and the column has no default, it is invalid
				$result['valid'] = false;
			}
		}
		else
		{
			// data type validation rules
			if (in_array($columnDataType, Model::INT_DATATYPES))
			{
				if ($valueDataType !== 'integer')
				{
					$result['valid'] = false;
				}
			}
			else if (in_array($columnDataType,
				Model::STRING_DATATYPES))
			{
				if ($valueDataType !== 'string')
				{
					$result['valid'] = false;
				}
			}
		}

		return $result;
	}

	/**
	 * @return bool indication of the success of the save operation
	 */
	public function save(): bool
	{
		$vals = array();

		// validate data before saving
		foreach ($this->_keys as $key)
		{
			$validationResult = $this->_validateColumn($key);

			if ($validationResult['valid'])
			{
				if ($validationResult['useDefault'])
				{
					array_push($vals, 'default');
				}
				else
				{
					array_push($vals, $this->$key);
				}
			}
			else
			{
				throw new

				Exception("Invalid field. Please refer to the table's schema for column $key.");
			}
		}

		if (is_null($this->_pk->value))
		{
			// primary key is null, so create a new record
			$stmt = "INSERT INTO $this->_table (" . join(', ',
				$this->_keys) . ') VALUES (' . join(', ',
				array_fill(0, sizeof($vals), '?')) .
				')';

			$query = $this->_pdo->prepare($stmt);
			$query->execute($vals);

			$results = $query->rowCount();

			return $results === 1;
		}
		else
		{
			// primary key is not null, so update an existing record
			$_pk = $this->_pk->key;

			// determine which fields changed from their original values and only update those
			$modified_keys_formatted = array();
			$modified_values_formatted = array();
			$numValuesToUpdate = 0;

			foreach ($this->_keys as $key)
			{
				if ($this->_pristine[$key] !== $this->$key)
				{
					array_push($modified_keys_formatted,
						"$key = :$key");

					$modified_values_formatted[":$key"] =
					$this->$key;

					++$numValuesToUpdate;
				}
			}

			if ($numValuesToUpdate === 0)
			{
				return false;
			}

			$stmt = "UPDATE $this->_table SET " . join(', ',
				$modified_keys_formatted) .
				" WHERE $_pk = :pk LIMIT 1";
			$bind_params = array_merge($modified_values_formatted,
				[':pk' => $this->_pk->value]);

			$query = $this->_pdo->prepare($stmt);
			$query->execute($bind_params);

			$result = $query->rowCount();

			return $result === 1;
		}
	}

	/**
	 * @param string $key name of property to get
	 * @return mixed value of the property
	 */
	public function __get(string $key)
	{
		if (property_exists($this, $key))
		{
			return $this->$key;
		}
		else
		{
			if ($key === $this->_pk->key)
			{
				return $this->_pk->value;
			}
			else
			{
				return null;
			}
		}
	}

	/**
	 * @param string $key name of the property to set
	 * @param mixed $val value to assign to the property
	 */
	public function __set(string $key, $val)
	{
		if ($key[0] !== '_')
		{
			$this->$key = $val;
		}
		else
		{
			throw new

			Exception("Cannot set protected property of Model: $key");
		}
	}

	/**
	 * @return string information about the contents of this BaseModel
	 * @codeCoverageIgnore
	 */
	public function dump()
	{
		$output = '';
		foreach ($this->_fks as $fk)
		{
			$output = $output . "fk: $fk<br />";
		}

		$output = $output . "<br />pk: $this->_pk<br /><br />json: " .
		json_encode(get_object_vars($this), JSON_PRETTY_PRINT);

		return $output;
	}

	/**
	 * @return string information about the data in this BaseModel
	 * @codeCoverageIgnore
	 */
	public function dumpData()
	{
		return json_encode($this, JSON_PRETTY_PRINT);
	}

	public function __toString()
	{
		// return json_encode($this, JSON_PRETTY_PRINT);
		return json_encode($this);
	}
}
