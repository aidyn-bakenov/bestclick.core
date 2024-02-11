<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 30.06.2022 12:10
 */

namespace Bestclick\Iblock;

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\SystemException;
use Bestclick\Object\Dictionary;
use Bestclick\Object\DictionaryItem;
use Bestclick\Object\Enumeration;
use Bestclick\Object\EnumerationItem;

trait DictionaryTrait
{
	#region Dictionary

	/**
	 * Возвращает экземпляр класса Dictionary
	 *
	 * @param array $parameters
	 * @param string $lang
	 * @return Dictionary
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getDictionary(array $parameters = [], string $lang = ''): Dictionary
	{
		if (empty($lang))
		{
			$lang = Application::getInstance()->getContext()->getLanguage();
		}

		$codes = [];
		$names = [];
		$full = [];

		#region Получим список свойств справочника
		$arProps = [];
		$arPropsByTypes = [];
		if (method_exists(static::class, 'getIblockId'))
		{
			$rsProps = PropertyTable::getList([
				'filter' => [
					'IBLOCK_ID' => static::getIblockId(),
				],
			])->fetchAll();
			foreach ($rsProps as $obProp)
			{
				$propCode = $obProp['CODE'];
				$arProps[$propCode] = $obProp;

				$propType = $obProp['PROPERTY_TYPE'];
				$arPropsByTypes[$propType][] = $obProp;
			}
		}
		#endregion
		#region Получим значения списков
		$enum = new Enumeration();
		if (!empty($arPropsByTypes['L']))
		{
			$listPropsIds = [];
			foreach ($arPropsByTypes['L'] as $obProp)
			{
				$listPropsIds[] = (int)$obProp['ID'];
			}

			$rs = PropertyEnumerationTable::getList([
				'order' => [
					'SORT' => 'ASC',
					'ID' => 'ASC',
				],
				'filter' => [
					'PROPERTY_ID' => $listPropsIds,
				],
				'select' => [
					'ID',
					'PROPERTY_ID',
					'VALUE',
					'DEF',
					'SORT',
					'XML_ID',
				],
				'cache' => [
					'ttl' => 3600
				],
			])->fetchAll();
			foreach ($rs as $ob)
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
		#region Заполним параметры по умолчанию

		#region order
		if (empty($parameters['order']))
		{
			$parameters['order'] = [
				'SORT' => 'ASC',
			];
		}
		#endregion
		#region filter
		if (empty($parameters['filter']))
		{
			$parameters['filter'] = [
				'=ACTIVE' => 'Y',
			];
		}
		#endregion
		#region select
		$customSelect = [
			'ID',
			'CODE',
			'NAME',
			'SORT',
		];
		foreach ($arProps as $propCode => $obProp)
		{
			if ($obProp['MULTIPLE'] == 'Y' && in_array($obProp['PROPERTY_TYPE'], ['E', 'L']))
			{
				$parameters['runtime'][] = new ExpressionField(
					$propCode.'_CONCAT',
					"GROUP_CONCAT(DISTINCT %s SEPARATOR '|')",
					$propCode.'.VALUE'
				);
				$customSelect[] = $propCode.'_CONCAT';
			}
			else
			{
				if ($obProp['WITH_DESCRIPTION'] == 'Y')
				{
					$customSelect[$propCode.'_DESCRIPTION'] = $propCode.'.DESCRIPTION';
				}
				$customSelect[$propCode.'_VALUE'] = $propCode.'.VALUE';
			}
		}
		if (!empty($parameters['select']))
		{
			foreach ($parameters['select'] as $k => $v)
			{
				if (is_string($k) && !in_array($k, $customSelect) && !in_array($v, $customSelect))
				{
					$customSelect[$k] = $v;
				}
				elseif (is_int($k) && !in_array($v, $customSelect))
				{
					$customSelect[] = $v;
				}
			}
		}
		$parameters['select'] = $customSelect;
		#endregion
		#region cache
		if (empty($parameters['cache']))
		{
			$parameters['cache'] = [
				'ttl' => 3600,
			];
		}
		#endregion

		#endregion

		$langUpper = strtoupper($lang);

		$rs = static::getList($parameters)->fetchAll();
		foreach ($rs as $ob)
		{
			$id = $ob['ID'];

			if (isset($full[$id]))
			{
				$newItem = $full[$id];
			}
			else
			{
				$ob['NAME'] = $ob[$langUpper.'_NAME_VALUE'] ?: $ob['NAME'];
				$newItem = $ob;
			}

			foreach ($arProps as $propCode => $obProp)
			{
				$concat = $ob[$propCode.'_CONCAT'];
				if (isset($concat) && is_string($concat))
				{
					$values = explode('|', $concat);

					if (!isset($newItem[$propCode]))
					{
						$newItem[$propCode] = [];
					}

					$isExists = false;
					if (!empty($newItem[$propCode]))
					{
						foreach ($newItem[$propCode] as $v)
						{
							if (in_array($v, $values))
							{
								$isExists = true;
								break;
							}
						}
					}
					if (!$isExists)
					{
						$newItem[$propCode] = array_merge($newItem[$propCode], $values);
						$newItem[$propCode] = array_unique($newItem[$propCode]);
					}
				}

				$value = $ob[$propCode.'_VALUE'];
				if (isset($value))
				{
					if ($obProp['WITH_DESCRIPTION'] == 'Y')
					{
						$description = htmlspecialchars_decode($ob[$propCode.'_DESCRIPTION'] ?: '');
						$description = is_serialized($description) ? unserialize($description) : $description;

						if ($obProp['MULTIPLE'] == 'Y')
						{
							$isExists = false;
							if (isset($newItem[$propCode]) && is_array($newItem[$propCode]))
							{
								foreach ($newItem[$propCode] as $v)
								{
									if ($value == $v['VALUE'] && $description == $v['DESCRIPTION'])
									{
										$isExists = true;
										break;
									}
								}
							}
							if (!$isExists)
							{
								$newItem[$propCode][] = [
									'VALUE' => $value,
									'DESCRIPTION' => $description,
								];
							}
						}
						else
						{
							$newItem[$propCode] = [
								'VALUE' => $value,
								'DESCRIPTION' => $description,
							];
						}
					}
					else
					{
						if ($obProp['MULTIPLE'] == 'Y')
						{
							$isExists = false;
							if (isset($newItem[$propCode]) && is_array($newItem[$propCode]))
							{
								foreach ($newItem[$propCode] as $v)
								{
									if (($obProp['PROPERTY_TYPE'] == 'L' && $value == $v['VALUE']) || $value == $v)
									{
										$isExists = true;
										break;
									}
								}
							}
							if (!$isExists)
							{
								if ($obProp['PROPERTY_TYPE'] == 'L')
								{
									$newItem[$propCode][] = [
										'ID' => $ob[$propCode.'_VALUE_ENUM_ID'],
										'XML_ID' => $ob[$propCode.'_VALUE_XML_ID'],
										'VALUE' => $value,
									];
								}
								else
								{
									$newItem[$propCode][] = $value;
								}
							}
						}
						else
						{
							if ($obProp['PROPERTY_TYPE'] == 'L')
							{
								$newItem[$propCode] = [
									'ID' => $ob[$propCode.'_VALUE_ENUM_ID'],
									'XML_ID' => $ob[$propCode.'_VALUE_XML_ID'],
									'VALUE' => $value,
								];
							}
							else
							{
								$newItem[$propCode] = $value;
							}
						}
					}
				}

				unset(
					$newItem[$propCode.'_CONCAT'],
					$newItem[$propCode.'_VALUE'],
					$newItem[$propCode.'_DESCRIPTION']
				);
			}

			if (!isset($codes[$id]))
			{
				$codes[$id] = $ob['CODE'];
			}
			if (!isset($names[$id]))
			{
				$names[$id] = $ob['NAME'];
			}

			$full[$id] = $newItem;
		}

		$dict = new Dictionary();
		foreach ($full as $id => $item)
		{
			foreach ($arProps as $propCode => $obProp)
			{
				if ($obProp['PROPERTY_TYPE'] == 'L')
				{
					if ($obProp['MULTIPLE'] == 'Y')
					{
						$values = [];
						foreach ($item[$propCode] as $value)
						{
							$values[] = [
								'ID' => $enum->getItem($value)->get('ID'),
								'XML_ID' => $enum->getItem($value)->getXmlId(),
								'DEF' => $enum->getItem($value)->isDefault(),
								'VALUE' => $enum->getItem($value)->getValue(),
							];
						}
						$item[$propCode] = $values;
					}
					else
					{
						$value = (int)$item[$propCode]['VALUE'];
						$item[$propCode] = [
							'ID' => $enum->getItem($value)->get('ID'),
							'XML_ID' => $enum->getItem($value)->getXmlId(),
							'DEF' => $enum->getItem($value)->isDefault(),
							'VALUE' => $enum->getItem($value)->getValue(),
						];
					}
				}
				else
				{
					$obProp = array_merge($obProp, [
						'VALUE' => $item[$propCode],
					]);
					$item[$propCode] = static::getPropertyValue($obProp);
				}
			}

			$dictItem = DictionaryItem::create(
				$codes[$id],
				$names[$id],
				false,
				$item
			);
			$dict->setItem($id, $dictItem);
		}

		return $dict;
	}

	#endregion
}