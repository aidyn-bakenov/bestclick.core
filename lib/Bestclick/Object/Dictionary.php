<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 28.06.2022 16:14
 */

namespace Bestclick\Object;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ObjectNotFoundException;

class Dictionary
{
	#region Свойства

	protected array $codes = [];
	protected array $names = [];
	protected int $default = 0;
	protected array $collection = [];

	#endregion

	#region Методы get/set

	/**
	 * @return array
	 */
	public function getCodes(): array
	{
		return $this->codes;
	}

	/**
	 * @return array
	 */
	public function getNames(): array
	{
		return $this->names;
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
	 * @return DictionaryItem[]
	 */
	public function getCollection(): array
	{
		return $this->collection;
	}

	/**
	 * @param DictionaryItem[] $collection
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
	 * @return DictionaryItem
	 */
	public function getItem(int $id): DictionaryItem
	{
		return $this->collection[$id] ?: DictionaryItem::create();
	}

	/**
	 * Возвращает объект по CODE элемента
	 *
	 * @param string $code
	 * @return DictionaryItem
	 * @noinspection PhpUnused
	 */
	public function getItemByCode(string $code): DictionaryItem
	{
		$id = (int)array_search($code, $this->codes);
		return $this->has($id) ? $this->getItem($id) : DictionaryItem::create();
	}

	/**
	 * Добавляет новый элемент
	 *
	 * @param int $id
	 * @param DictionaryItem $item
	 * @return void
	 */
	public function setItem(int $id, DictionaryItem $item): void
	{
		$this->codes[$id] = $item->getCode();
		$this->names[$id] = $item->getName();
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
	public function deleteItem(int $id): DictionaryItem
	{
		if (!isset($this->collection[$id]))
		{
			throw new ArgumentOutOfRangeException('collection item index wrong');
		}

		/** @var DictionaryItem $oldItem */
		$oldItem = $this->collection[$id];
		unset(
			$this->codes[$id],
			$this->names[$id],
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
		$collection = $this->getCollection();
		return isset($collection[$id]);
	}

	/**
	 * Проверяет наличие элемента по CODE
	 *
	 * @param string $code
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function hasCode(string $code): bool
	{
		$codes = $this->getCodes();
		return in_array($code, $codes);
	}

	/**
	 * Возвращает количество элементов справочника
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
			'CODE' => $this->getCodes(),
			'NAME' => $this->getNames(),
			'DEFAULT' => $this->getDefault(),
			'FULL' => $this->getFull(),
		];
	}

	#endregion
}