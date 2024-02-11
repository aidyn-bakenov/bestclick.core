<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 02.05.2023 16:48
 */

namespace Bestclick\Enumeration;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\EO_UserField;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldTable;
use CUserFieldEnum;
use Bestclick\Object\Enumeration;
use Bestclick\Object\EnumerationItem;

trait EnumerationTrait
{
	#region Пользовательские поля

	/**
	 * Возвращает пользовательское поле
	 *
	 * @param array $select
	 * @return EO_UserField
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getUserField(array $select = []): EO_UserField
	{
		if (empty($select))
		{
			$select[] = '*';
		}

		return UserFieldTable::getList([
			'order' => [
				'SORT' => 'ASC',
				'ID' => 'ASC',
			],
			'filter' => [
				'ENTITY_ID' => static::getEntityId(),
				'FIELD_NAME' => static::getFieldName(),
			],
			'select' => $select,
		])->fetchObject();
	}

	#endregion

	#region Enumeration

	/**
	 * Возвращает экземпляр класса Enumeration()
	 *
	 * @return Enumeration
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getEnumeration(): Enumeration
	{
		$enum = new Enumeration();

		#region Получим элементы списка
		$userFieldId = static::getUserField(['ID'])->getId();
		if ($userFieldId > 0)
		{
			$rsTypes = CUserFieldEnum::GetList(['SORT' => 'ASC'], ['USER_FIELD_ID' => $userFieldId]);
			while ($ob = $rsTypes->GetNext(false, false))
			{
				$id = (int)$ob['ID'];
				$enumItem = EnumerationItem::create(
					(string)$ob['XML_ID'],
					(string)$ob['VALUE'],
					$ob['DEF'] == 'Y',
					$ob
				);
				$enum->setItem($id, $enumItem);
			}
		}
		#endregion

		return $enum;
	}

	#endregion
}