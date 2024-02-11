<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 28.06.2022 16:14
 */

namespace Bestclick\Object;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ObjectNotFoundException;

class Enumeration
{
	#region Свойства

	protected array $xmlIds = [];
	protected array $values = [];
	protected int $default = 0;
	protected array $collection = [];

	#endregion

	#region Методы get/set

	/**
	 * @return array
	 */
	public function getXmlIds(): array
	{
		return $this->xmlIds;
	}

	/**
	 * @return array
	 */
	public function getValues(): array
	{
		return $this->values;
	}

	/**
	 * @return int
	 */
	public function getDefault(): int
	{
		return $this->default;
	}

	/**
	 * @param int $default
	 */
	public function setDefault(int $default): void
	{
		$this->default = $default;
	}

	/**
	 * @return array
	 */
	public function getFull(): array
	{
		$arValues = [];

		/** @var EnumerationItem $item */
		foreach ($this->getCollection() as $item)
		{
			$id = $item->getId();
			$arValues[$id] = $item->getValues();
		}

		return $arValues;
	}

	#endregion

	#region Collection

	/**
	 * @return EnumerationItem[]
	 */
	public function getCollection(): array
	{
		return $this->collection;
	}

	/**
	 * @param EnumerationItem[] $collection
	 */
	public function setCollection(array $collection): void
	{
		$this->collection = $collection;
	}

	/**
	 * @return void
	 * @throws ArgumentOutOfRangeException
	 * @throws ObjectNotFoundException
	 * @noinspection PhpUnused
	 */
	public function clearCollection(): void
	{
		foreach ($this->collection as $item)
		{
			$item->delete();
		}
	}

	#endregion

	#region CollectionItem

	/**
	 * Возвращает объект по ID элемента
	 *
	 * @param int $id
	 * @return EnumerationItem
	 */
	public function getItem(int $id): EnumerationItem
	{
		return $this->collection[$id] ?: EnumerationItem::create();
	}

	/**
	 * Возвращает объект по XML_ID элемента
	 *
	 * @param string $xmlId
	 * @return EnumerationItem
	 * @noinspection PhpUnused
	 */
	public function getItemByXmlId(string $xmlId): EnumerationItem
	{
		$id = (int)array_search($xmlId, $this->xmlIds);
		return $this->getItem($id);
	}

	/**
	 * Добавляет новый элемент
	 *
	 * @param int $id
	 * @param EnumerationItem $item
	 * @return void
	 */
	public function setItem(int $id, EnumerationItem $item): void
	{
		$this->xmlIds[$id] = $item->getXmlId();
		$this->values[$id] = $item->getValue();
		if ($item->isDefault())
		{
			$this->default = $id;
		}

		$item->setId($id);
		$this->collection[$id] = $item;
		$item->setCollection($this);
	}

	/**
	 * Удаляет элемент
	 *
	 * @param int $id
	 * @return mixed
	 * @throws ArgumentOutOfRangeException
	 */
	public function deleteItem(int $id): EnumerationItem
	{
		if (!isset($this->collection[$id]))
		{
			throw new ArgumentOutOfRangeException('collection item index wrong');
		}

		/** @var EnumerationItem $oldItem */
		$oldItem = $this->collection[$id];
		unset(
			$this->xmlIds[$id],
			$this->values[$id],
			$this->collection[$id]
		);

		if ($id == $this->getDefault())
		{
			$this->setDefault(0);
		}

		return $oldItem;
	}

	#endregion

	#region Методы проверки

	/**
	 * Проверяет наличие элемента по ID
	 *
	 * @param int $id
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function has(int $id): bool
	{
		$full = $this->getFull();
		return isset($full[$id]);
	}

	/**
	 * Проверяет наличие элемента по XML_ID
	 *
	 * @param string $xmlId
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function hasXmlId(string $xmlId): bool
	{
		$xmlIds = $this->getXmlIds();
		return in_array($xmlId, $xmlIds);
	}

	/**
	 * Возвращает количество элементов списка
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->getCollection());
	}

	#endregion

	#region Методы приведения данных к формату

	/**
	 * Возвращает в формате массива
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'XML_ID' => $this->getXmlIds(),
			'VALUE' => $this->getValues(),
			'DEFAULT' => $this->getDefault(),
			'FULL' => $this->getFull(),
		];
	}

	#endregion
}