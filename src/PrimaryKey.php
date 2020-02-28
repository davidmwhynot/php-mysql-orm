<?php

final class PrimaryKey
{
	/**
	 * @var string $key
	 */
	private string $key;

	/**
	 * @var int|null $value
	 */
	private  ? int $value;

	/**
	 * @var Schema $schema
	 */
	private $schema;

	/**
	 * @param array $schema
	 * @param int|null $value
	 */
	public function __construct(array $schema, ?int $value = null)
	{
		$this->schema = $schema;
		$this->value = $value;

		$this->key = $schema['COLUMN_NAME'];
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function __get(string $key)
	{
		if (property_exists($this, $key))
		{
			return $this->$key;
		}
		else
		{
			throw new

			InvalidArgumentException("Invalid property name for object of class PrimaryKey: $key.");
		}
	}

	/**
	 * @param int|null $val
	 */
	public function set(?int $val) : void
	{
		$this->value = $val;
	}

	public function __toString()
	{
		return json_encode(get_object_vars($this));
	}

}
