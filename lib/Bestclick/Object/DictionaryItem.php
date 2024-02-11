<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 25.05.2023 18:32
 */

namespace Bestclick\Object;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Result;

class DictionaryItem
{
	#region Свойства

	protected int $id = 0;
	protected string $code = '';
	protected string $name = '';
	protected bool $default = false;
	protected array $values = [];
	protected Dictionary $collection;

	#endregion

	#region Методы get/set

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId(int $id): void
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode(string $code): void
	{
		$this->code = $code;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @return bool
	 */
	public function isDefault(): bool
	{
		return $this->default;
	}

	/**
	 * @param bool $default
	 */
	public function setDefault(bool $default): void
	{
		$this->default = $default;
	}

	/**
	 * @return array
	 */
	public function getValues(): array
	{
		return $this->values;
	}

	/**
	 * @param array $values
	 */
	public function setValues(array $values): void
	{
		$this->values = $values;
	}

	#endregion

	#region Collection

	/**
	 * @return Dictionary
	 */
	public function getCollection(): Dictionary
	{
		return $this->collection;
	}

	/**
	 * @param Dictionary $collection
	 */
	public function setCollection(Dictionary $collection): void
	{
		$this->collection = $collection;
	}

	#endregion

	#region CollectionItem

	/**
	 * @param string $field
	 * @return mixed
	 */
	public function get(string $field)
	{
		$values = $this->getValues();
		return $values[$field];
	}

	/**
	 * @param string $field
	 * @param $value
	 * @return void
	 */
	public function set(string $field, $value): void
	{
		$values = $this->getValues();
		$values[$field] = $value;
		$this->setValues($values);
	}

	/**
	 * @param string $code
	 * @param string $name
	 * @param bool $default
	 * @param array $values
	 * @return DictionaryItem
	 */
	public static function create(string $code = '', string $name = '', bool $default = false, array $values = []): DictionaryItem
	{
		$newItem = new static();
		$newItem->setCode($code);
		$newItem->setName($name);
		$newItem->setDefault($default);
		$newItem->setValues($values);
		return $newItem;
	}

	/**
	 * @return Result
	 * @throws ArgumentOutOfRangeException
	 */
	public function delete(): Result
	{
		$collection = $this->getCollection();
		$collection->deleteItem($this->getId());
		return new Result();
	}

	#endregion
}