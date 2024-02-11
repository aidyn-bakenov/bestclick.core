<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 25.05.2023 18:32
 */

namespace Bestclick\Object;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Result;

class EnumerationItem
{
	#region Свойства

	protected int $id = 0;
	protected string $xmlId = '';
	protected string $value = '';
	protected bool $default = false;
	protected array $values = [];
	protected Enumeration $collection;

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
	public function getXmlId(): string
	{
		return $this->xmlId;
	}

	/**
	 * @param string $xmlId
	 */
	public function setXmlId(string $xmlId): void
	{
		$this->xmlId = $xmlId;
	}

	/**
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	/**
	 * @param string $value
	 */
	public function setValue(string $value): void
	{
		$this->value = $value;
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
	 * @return Enumeration
	 */
	public function getCollection(): Enumeration
	{
		return $this->collection;
	}

	/**
	 * @param Enumeration $collection
	 */
	public function setCollection(Enumeration $collection): void
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
		$full = $this->getValues();
		return $full[$field];
	}

	/**
	 * @param string $field
	 * @param $value
	 * @return void
	 */
	public function set(string $field, $value): void
	{
		$full = $this->getValues();
		$full[$field] = $value;
		$this->setValues($full);
	}

	/**
	 * @param string $xmlId
	 * @param string $value
	 * @param bool $default
	 * @param array $values
	 * @return EnumerationItem
	 */
	public static function create(string $xmlId = '', string $value = '', bool $default = false, array $values = []): EnumerationItem
	{
		$newItem = new static();
		$newItem->setXmlId($xmlId);
		$newItem->setValue($value);
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