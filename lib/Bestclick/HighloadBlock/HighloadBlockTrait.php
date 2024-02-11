<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 27.05.2022 15:10
 */

namespace Bestclick\HighloadBlock;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldTable;

trait HighloadBlockTrait
{
	/**
	 * Пересобирает массив полей, т.к. по умолчанию присутствует только поле ID
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		$map = [];

		$fields = static::getEntity()->getFields();
		foreach ($fields as $field)
		{
			if (is_a($field, '\Bitrix\Main\ORM\Fields\ExpressionField'))
			{
				continue;
			}

			$map[$field->getName()] = [
				'autocomplete' => $field->getParameter('autocomplete'),
				'data_type' => $field->getDataType(),
				'primary' => (int)$field->getParameter('primary'),
				'required' => (int)$field->getParameter('required'),
			];
		}

		return $map;
	}

	public static function getBlockId(): int
	{
		return 0;
	}

	public static function getParentClass(): string
	{
		return get_parent_class(static::class);
	}

	#region Пользовательские поля

	/**
	 * Возвращает пользовательское поле
	 *
	 * @param string $fieldName
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getUserFieldByName(string $fieldName): array
	{
		$arUserFields = self::getUserFieldListByNames([$fieldName]);
		return $arUserFields[$fieldName];
	}

	/**
	 * Возвращает пользовательские поля
	 *
	 * @param array $arFieldNames
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getUserFieldListByNames(array $arFieldNames): array
	{
		$hlblockId = static::getBlockId();

		$arUserFields = [];

		$rs = UserFieldTable::getList([
			'order' => ['SORT' => 'ASC', 'ID' => 'ASC'],
			'filter' => ['ENTITY_ID' => 'HLBLOCK_'.$hlblockId, 'FIELD_NAME' => array_values($arFieldNames)],
			'select' => ['*'],
		])->fetchAll();
		foreach ($rs as $ob)
		{
			$arUserFields[$ob['FIELD_NAME']] = $ob;
		}

		return $arUserFields;
	}

	#endregion
}