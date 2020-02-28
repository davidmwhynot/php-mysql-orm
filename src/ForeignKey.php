<?php

final class ForeignKey
{
	/**
	 * @var array $constraints
	 */
	private array $constraints;

	/**
	 * @var array|null $schema
	 */
	private  ? array $schema;

	/**
	 * @var int|null $value
	 */
	private  ? int $value;

	/**
	 * @var string $key
	 */
	private string $key;

	/**
	 * @param array $constraints
	 * @param array|null $schema
	 * @param int|null $value
	 */
	public function __construct(
		array $constraints,
		?array $schema = null,
		?int $value = null
	)
	{
		$this->constraints = $constraints;
		$this->schema = $schema;
		$this->value = $value;

		$this->key = $constraints['COLUMN_NAME'];
	}

	/**
	 * Set the foreign key's value
	 *
	 * @return void
	 */
	public function set(?int $val)
	{
		$this->value = $val;
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

			InvalidArgumentException("Invalid property name for object of class ForeignKey: $key.");
		}
	}

	/**
	 * @param string $key
	 * @param mixed $val
	 */
	public function __set(string $key, $val) : void
	{
		switch ($key)
		{
			case 'value' :
				$this->value = $val;

				break;
			case 'schema':
				$this->schema = $val;

				break;
			default:
				throw new

				InvalidArgumentException("Cannot set property '$key' for object of class ForeignKey. Either the property is private or does not exist.");
		}
	}

	public function __toString()
	{
		return json_encode(get_object_vars($this), JSON_PRETTY_PRINT);
	}

}
