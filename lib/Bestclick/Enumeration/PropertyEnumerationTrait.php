<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 02.05.2023 17:25
 */

namespace Bestclick\Enumeration;

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bestclick\Object\Enumeration;
use Bestclick\Object\EnumerationItem;

trait PropertyEnumerationTrait
{
	#region Enumeration

	/**
	 * Возвращает экземпляр класса Enumeration()
	 *
	 * @return Enumeration
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getEnumeration(): Enumeration
	{
		Loader::includeModule('iblock');

		$enum = new Enumeration();

		#region Получим элементы списка
		$rsEnum = PropertyEnumerationTable::getList([
			'order' => [
				'SORT' => 'ASC',
			],
			'filter' => [
				'PROPERTY.IBLOCK_ID' => static::getIblockId(),
				'PROPERTY.CODE' => static::getPropertyCode(),
			],
			'select' => [
				'ID',
				'IBLOCK_ID' => 'PROPERTY.IBLOCK_ID',
				'PROPERTY_ID',
				'PROPERTY_CODE' => 'PROPERTY.CODE',
				'VALUE',
				'DEF',
				'SORT',
				'XML_ID',
				'TMP_ID',
			],
		])->fetchAll();
		foreach ($rsEnum as $ob)
		{
			$id = intval($ob['ID']);
			$enumItem = EnumerationItem::create(
				(string)$ob['XML_ID'],
				(string)$ob['VALUE'],
				$ob['DEF'] == 'Y',
				$ob
			);
			$enum->setItem($id, $enumItem);
		}
		#endregion

		return $enum;
	}

	#endregion
}