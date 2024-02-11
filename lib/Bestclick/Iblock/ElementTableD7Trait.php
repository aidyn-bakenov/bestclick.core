<?php
/**
 * Created by Aidyn Bakenov.
 * Email: aidyn.bakenov@yandex.kz
 * 01.03.2022 11:25
 */

namespace Bestclick\Iblock;

use _CIBElement;
use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\ORM\ValueStorageEntity;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserFieldTable;
use CFile;
use CIBlockElement;
use CUser;
use CUserFieldEnum;

trait ElementTableD7Trait
{
	public static function getParentClass(): string
	{
		return get_parent_class(static::class);
	}

	#region Инфоблок

	/**
	 * Возвращает ID инфоблока
	 *
	 * @return int
	 */
	public static function getIblockId(): int
	{
		return intval(static::getEntity()->getIblock()->getId());
	}

	/**
	 * Возвращает CODE инфоблока (конвертирует из API_CODE)
	 *
	 * @return string
	 */
	public static function getIblockCode(): string
	{
		$code = static::getIblockApiCode();

		$converter = new Converter(Converter::TO_SNAKE);
		$code = $converter->process($code);
		$converter->setFormat(Converter::TO_UPPER);

		return $converter->process($code);
	}

	/**
	 * Возвращает API_CODE инфоблока
	 *
	 * @return mixed
	 */
	public static function getIblockApiCode(): string
	{
		return (string)static::getEntity()->getIblock()->get('API_CODE');
	}

	/**
	 * Возвращает VERSION инфоблока
	 *
	 * @return int
	 */
	public static function getVersion(): int
	{
		return (int)static::getEntity()->getIblock()->get('VERSION');
	}

	/**
	 * Возвращает название таблицы для однозначных свойств
	 *
	 * @return string
	 */
	public static function getSingleValueTableName(): string
	{
		$version = static::getVersion();
		return $version == 2
			? 'b_iblock_element_prop_s'.static::getIblockId()
			: 'b_iblock_element_property';
	}

	/**
	 * Возвращает название таблицы для многозначных свойств
	 *
	 * @return string
	 */
	public static function getMultiValueTableName(): string
	{
		$version = static::getVersion();
		return $version == 2
			? 'b_iblock_element_prop_m'.static::getIblockId()
			: 'b_iblock_element_property';
	}

	/**
	 * Возвращает шаблон ссылки на страницу списка элементов
	 *
	 * @return string
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getListPageTemplate(): string
	{
		$template = '';

		$iblockId = static::getIblockId();

		$rs = IblockTable::getByPrimary($iblockId, ['select' => ['LIST_PAGE_URL']]);
		if ($ob = $rs->fetch())
		{
			$template = $ob['LIST_PAGE_URL'];
		}

		return $template;
	}

	/**
	 * Возвращает ссылку на страницу списка элементов
	 *
	 * @param array $arReplace
	 * @return string
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getListPageUrl(array $arReplace = []): string
	{
		return str_replace(
			array_keys($arReplace),
			array_values($arReplace),
			static::getListPageTemplate()
		);
	}

	/**
	 * Возвращает шаблон ссылки на детальную страницу
	 *
	 * @return string
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getDetailPageTemplate(): string
	{
		$template = '';

		$iblockId = static::getIblockId();

		$rs = IblockTable::getByPrimary($iblockId, ['select' => ['DETAIL_PAGE_URL']]);
		if ($ob = $rs->fetch())
		{
			$template = $ob['DETAIL_PAGE_URL'];
		}

		return $template;
	}

	/**
	 * Возвращает ссылку на детальную страницу
	 *
	 * @param array $arReplace
	 * @return string
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getDetailPageUrl(array $arReplace = []): string
	{
		return str_replace(
			array_keys($arReplace),
			array_values($arReplace),
			static::getDetailPageTemplate()
		);
	}

	#endregion

	#region Элемент инфоблока

	/**
	 * Возвращает данные элемента по его ID
	 *
	 * @param int $documentId
	 * @return array
	 * @throws LoaderException
	 */
	public static function getElement(int $documentId): array
	{
		Loader::includeModule('iblock');

		$arElement = [];
		$rsElement = CIBlockElement::GetList([], ['ID' => $documentId, 'IBLOCK_ID' => static::getIblockId()]);
		if ($ob = $rsElement->GetNextElement())
		{
			$arElement = static::prepareElement($ob);
		}

		return $arElement;
	}

	/**
	 * Возвращает обработанные данные элемента в виде массива
	 *
	 * @param _CIBElement $ob
	 * @return array
	 */
	public static function prepareElement(_CIBElement $ob): array
	{
		$arElement = static::getFieldsValues($ob, __METHOD__);

		$obProps = $ob->GetProperties();
		foreach ($obProps as $propCode => $obProp)
		{
			$arElement[$propCode] = static::getPropertyValue($obProp);
		}

		return $arElement;
	}

	/**
	 * Возвращает список полей для формирования результатов метода getElement()
	 *
	 * @param _CIBElement $ob
	 * @param string $method
	 * @return array
	 */
	private static function getFieldsValues(_CIBElement $ob, string $method = ''): array
	{
		$obFields = $ob->GetFields();

		$arFields = [
			'ID' => (int)$obFields['ID'],
			'NAME' => $obFields['NAME'],
			'CODE' => $obFields['CODE'],
			'ACTIVE' => $obFields['ACTIVE'],
			'IBLOCK_ID' => (int)$obFields['IBLOCK_ID'],
			'DATE_CREATE' => $obFields['DATE_CREATE'],
			'DATE_UPDATE' => $obFields['TIMESTAMP_X'],
			'CREATED_BY' => (int)$obFields['CREATED_BY'],
			'MODIFIED_BY' => (int)$obFields['MODIFIED_BY'],
			'LIST_PAGE_URL' => $obFields['LIST_PAGE_URL'],
			'DETAIL_PAGE_URL' => $obFields['DETAIL_PAGE_URL'],
			'PREVIEW_TEXT' => $obFields['PREVIEW_TEXT'],
			'PREVIEW_TEXT_TYPE' => $obFields['PREVIEW_TEXT_TYPE'],
			'DETAIL_TEXT' => $obFields['DETAIL_TEXT'],
			'DETAIL_TEXT_TYPE' => $obFields['DETAIL_TEXT_TYPE'],
		];

		$arExplode = explode('::', $method);
		if (!empty($arExplode))
		{
			$method = $arExplode[array_key_last($arExplode)];
			if ($method == 'prepareElementForCopy')
			{
				unset(
					$arFields['DATE_CREATE'],
					$arFields['DATE_UPDATE'],
					$arFields['LIST_PAGE_URL'],
					$arFields['DETAIL_PAGE_URL']
				);
			}
		}

		return $arFields;
	}

	/**
	 * Возвращает обработанное значение свойства
	 *
	 * @param array $obProp
	 * @return array|mixed
	 */
	private static function getPropertyValue(array $obProp)
	{
		$result = null;

		$value = $obProp['VALUE'];

		$propType = $obProp['PROPERTY_TYPE'];
		$userType = $obProp['USER_TYPE'];

		switch ($propType)
		{
			case 'L':
				if ($obProp['MULTIPLE'] == 'Y' && is_array($value))
				{
					$newValue = [];
					foreach ($value as $k => $v)
					{
						$newValue[] = [
							'ID' => $obProp['VALUE_ENUM_ID'][$k],
							'XML_ID' => $obProp['VALUE_XML_ID'][$k],
							'VALUE' => $v,
						];
					}
					$result = $newValue;
				}
				elseif (strlen($value) > 0)
				{
					$result = [
						'ID' => $obProp['VALUE_ENUM_ID'],
						'XML_ID' => $obProp['VALUE_XML_ID'],
						'VALUE' => $value,
					];
				}
				else
				{
					$result = $value;
				}
				break;

			case 'F':
				if ($obProp['MULTIPLE'] == 'Y' && is_array($value))
				{
					$newValue = [];
					foreach ($value as $v)
					{
						$arFile = CFile::GetFileArray($v);
						$newValue[] = [
							'ID' => $v,
							'SRC' => CFile::GetPath($v),
							'NAME' => $arFile['ORIGINAL_NAME'],
							'DESCRIPTION' => unserialize($arFile['DESCRIPTION']),
							'TIMESTAMP_X' => $arFile['TIMESTAMP_X'],
						];
					}
					$result = $newValue;
				}
				elseif (intval($value) > 0)
				{
					$arFile = CFile::GetFileArray($value);
					$result = [
						'ID' => $value,
						'SRC' => CFile::GetPath($value),
						'NAME' => $arFile['ORIGINAL_NAME'],
						'DESCRIPTION' => unserialize($arFile['DESCRIPTION']),
						'TIMESTAMP_X' => $arFile['TIMESTAMP_X'],
					];
				}
				else
				{
					$result = $value;
				}
				break;

			case 'E':
				if ($obProp['MULTIPLE'] == 'Y' && is_array($value))
				{
					$newValue = [];
					foreach ($value as $k => $v)
					{
						$newValue[$k] = (int)$v;
					}
					$result = $newValue;
				}
				else
				{
					$result = (int)$value;
				}
				break;

			default:
				if ($propType == 'S' && (is_null($userType) || $userType == 'HTML'))
				{
					#region HTML
					if ($userType == 'HTML')
					{
						if ($obProp['WITH_DESCRIPTION'] == 'Y')
						{
							if ($obProp['MULTIPLE'] == 'Y')
							{
								foreach ($value as $k => $v)
								{
									$description = htmlspecialchars_decode($obProp['DESCRIPTION'][$k]);
									$result[] = [
										'VALUE' => [
											'TYPE' => $v['TYPE'],
											'TEXT' => htmlspecialchars_decode($v['TEXT']),
										],
										'DESCRIPTION' => is_serialized($description) ? unserialize($description) : $description,
									];
								}
							}
							else
							{
								$description = htmlspecialchars_decode($obProp['DESCRIPTION']);
								$result = [
									'VALUE' => [
										'TYPE' => $value['TYPE'],
										'TEXT' => htmlspecialchars_decode($value['TEXT']),
									],
									'DESCRIPTION' => is_serialized($description) ? unserialize($description) : $description,
								];
							}
						}
						else
						{
							if ($obProp['MULTIPLE'] == 'Y')
							{
								$newValue = [];
								foreach ($value as $v)
								{
									$newValue[] = [
										'TYPE' => $v['TYPE'],
										'TEXT' => htmlspecialchars_decode($v['TEXT']),
									];
								}
								$result = $newValue;
							}
							else
							{
								$result = [
									'TYPE' => $value['TYPE'],
									'TEXT' => htmlspecialchars_decode($value['TEXT']),
								];
							}
						}
					}
					#endregion
					#region NULL
					else
					{
						if ($obProp['WITH_DESCRIPTION'] == 'Y')
						{
							if ($obProp['MULTIPLE'] == 'Y')
							{
								foreach ($value as $k => $v)
								{
									$description = htmlspecialchars_decode($obProp['DESCRIPTION'][$k]);
									$result[] = [
										'VALUE' => htmlspecialchars_decode($v),
										'DESCRIPTION' => is_serialized($description) ? unserialize($description) : $description,
									];
								}
							}
							else
							{
								$description = htmlspecialchars_decode($obProp['DESCRIPTION']);
								$result = [
									'VALUE' => htmlspecialchars_decode($value),
									'DESCRIPTION' => is_serialized($description) ? unserialize($description) : $description,
								];
							}
						}
						else
						{
							if ($obProp['MULTIPLE'] == 'Y')
							{
								$newValue = [];
								foreach ($value as $v)
								{
									$newValue[] = htmlspecialchars_decode($v);
								}
								$result = $newValue;
							}
							else
							{
								$result = htmlspecialchars_decode($value);
							}
						}
					}
					#endregion

					break;
				}

				if ($obProp['WITH_DESCRIPTION'] == 'Y')
				{
					if ($obProp['MULTIPLE'] == 'Y')
					{
						foreach ($value as $k => $v)
						{
							$description = htmlspecialchars_decode($obProp['DESCRIPTION'][$k]);
							$result[] = [
								'VALUE' => $v,
								'DESCRIPTION' => is_serialized($description) ? unserialize($description) : $description,
							];
						}
					}
					else
					{
						$description = htmlspecialchars_decode($obProp['DESCRIPTION']);
						$result = [
							'VALUE' => $value,
							'DESCRIPTION' => is_serialized($description) ? unserialize($description) : $description,
						];
					}
				}
				else
				{
					$result = $value;
				}
				break;
		}

		return $result;
	}

	#endregion

	#region Значения полей

	/**
	 * Возвращает символьный код элемента по его ID
	 *
	 * @param int $id
	 * @return string
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @noinspection PhpUnused
	 */
	public static function getCodeById(int $id): string
	{
		$code = '';

		$rs = static::getByPrimary($id, ['select' => ['CODE']]);
		if ($ob = $rs->fetch())
		{
			$code = (string)$ob['CODE'];
		}

		return $code;
	}

	#endregion

	#region Свойства

	/**
	 * Возвращает все свойства инфоблока
	 *
	 * @param array $codes
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getProperties(array $codes = []): array
	{
		$iblockId = static::getIblockId();
		if (empty($iblockId))
		{
			return [];
		}

		$filter = ['IBLOCK_ID' => $iblockId];
		if (!empty($codes))
		{
			$filter['CODE'] = $codes;
		}

		$select = ['*'];

		$arProps = [];
		$rsProps = PropertyTable::getList(['filter' => $filter, 'select' => $select])->fetchAll();
		foreach ($rsProps as $ob)
		{
			$arProps[$ob['CODE']] = $ob;
		}

		return $arProps;
	}

	/**
	 * Возвращает свойство инфоблока
	 *
	 * @param string $code
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getProperty(string $code): array
	{
		$arProps = static::getProperties([$code]);
		return $arProps[$code];
	}

	/**
	 * Возвращает ID свойства
	 *
	 * @param string $code
	 * @return int
	 */
	public static function getPropertyId(string $code): int
	{
		/** @var ValueStorageEntity $entity */
		$entity = static::getEntity()
			->getField($code)
			->getRefEntity();

		return (int)str_replace('IblockProperty', '', $entity->getName());
	}

	#endregion

	#region Значения списков свойств

	/**
	 * Возвращает значения свойства типа "Список"
	 *
	 * @param string $propertyCode
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @noinspection PhpUnused
	 */
	public static function getPropertyEnumList(string $propertyCode): array
	{
		$arEnums = static::getPropertiesEnumList([$propertyCode]);
		return $arEnums[$propertyCode];
	}

	/**
	 * Возвращает значения свойств типа "Список"
	 *
	 * @param array $properties
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getPropertiesEnumList(array $properties = []): array
	{
		Loader::includeModule('iblock');

		$arEnumList = [];
		foreach ($properties as $propCode)
		{
			$arEnumList[$propCode] = ['XML_ID' => [], 'VALUE' => [], 'DEFAULT' => null, 'FULL' => []];
		}

		$filter = [];
		$filter['PROPERTY.IBLOCK_ID'] = static::getIblockId();
		if (!empty($properties))
		{
			$filter['PROPERTY.CODE'] = array_values($properties);
		}

		$rsEnum = PropertyEnumerationTable::getList([
			'order' => ['SORT' => 'ASC'],
			'filter' => $filter,
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
			$propCode = $ob['PROPERTY_CODE'];

			$arEnumList[$propCode]['XML_ID'][$id] = $ob['XML_ID'];
			$arEnumList[$propCode]['VALUE'][$id] = $ob['VALUE'];
			$arEnumList[$propCode]['FULL'][$id] = $ob;

			if ($ob['DEF'] == 'Y')
			{
				$arEnumList[$propCode]['DEFAULT'] = $id;
			}
		}

		return $arEnumList;
	}

	#endregion

	#region Значения связанных инфоблоков

	/**
	 * Возвращает элементы связанного инфоблока
	 *
	 * @param string $propertyCode
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws SystemException
	 */
	public static function getPropertyElementList(string $propertyCode): array
	{
		$arEList = static::getPropertiesElementList([$propertyCode]);
		return $arEList[$propertyCode];
	}

	/**
	 * Возвращает элементы связанных инфоблоков
	 * Работает на D7
	 *
	 * @param array $properties
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws SystemException
	 */
	public static function getPropertiesElementList(array $properties = []): array
	{
		Loader::includeModule('iblock');

		$arEList = [];
		foreach ($properties as $propCode)
		{
			$arEList[$propCode] = ['CODE' => [], 'NAME' => [], 'DEFAULT' => null, 'FULL' => []];
		}

		$arLinkIBlocks = [];

		$filter = [
			'IBLOCK_ID' => static::getIblockId(),
			'PROPERTY_TYPE' => 'E',
		];
		if (!empty($properties))
		{
			$filter['CODE'] = array_values($properties);
		}

		$rsProperties = PropertyTable::getList([
			'order' => ['SORT' => 'ASC'],
			'filter' => $filter,
			'select' => ['*'],
		])->fetchAll();
		foreach ($rsProperties as $property)
		{
			if (!isset($arLinkIBlocks[$property['LINK_IBLOCK_ID']]))
			{
				$arLinkIBlocks[$property['LINK_IBLOCK_ID']] = $property['CODE'];
			}

			if ($property['DEFAULT_VALUE'])
			{
				$arEList[$property['CODE']]['DEFAULT'] = $property['DEFAULT_VALUE'];
			}
		}

		if (!empty($arLinkIBlocks))
		{
			$allElements = [];

			$rsEList = ElementTable::getList([
				'order' => ['SORT' => 'ASC'],
				'filter' => [
					'IBLOCK_ID' => array_keys($arLinkIBlocks),
					'ACTIVE' => 'Y',
				],
				'select' => [
					'ID',
					'IBLOCK_ID',
					'IBLOCK_SECTION_ID',
					'ACTIVE',
					'ACTIVE_FROM',
					'ACTIVE_TO',
					'NAME',
					'CODE',
					'SORT',
					'PREVIEW_TEXT',
					'PREVIEW_TEXT_TYPE',
					'DETAIL_TEXT',
					'DETAIL_TEXT_TYPE',
					'CREATED_BY',
					'MODIFIED_BY',
					'DATE_CREATE',
					'TIMESTAMP_X',
				],
			])->fetchAll();
			foreach ($rsEList as $ob)
			{
				$propertyCode = $arLinkIBlocks[$ob['IBLOCK_ID']];

				$id = intval($ob['ID']);
				$arEList[$propertyCode]['CODE'][$id] = $ob['CODE'];
				$arEList[$propertyCode]['NAME'][$id] = $ob['NAME'];
				$arEList[$propertyCode]['FULL'][$id] = $ob;

				if (!isset($allElements[$id]))
				{
					$allElements[$id] = $id;
				}
			}

			$rsEProps = ElementPropertyTable::getList([
				'filter' => [
					'IBLOCK_ELEMENT_ID' => array_keys($allElements),
				],
				'select' => [
					'ID',
					'IBLOCK_ID' => 'ELEMENT.IBLOCK_ID',
					'IBLOCK_ELEMENT_ID',
					'IBLOCK_PROPERTY_ID',
					'VALUE',
					'VALUE_TYPE',
					'VALUE_ENUM',
					'VALUE_NUM',
					'DESCRIPTION',
					'PROPERTY_ACTIVE' => 'PROPERTY.ACTIVE',
					'PROPERTY_NAME' => 'PROPERTY.NAME',
					'PROPERTY_CODE' => 'PROPERTY.CODE',
					'PROPERTY_SORT' => 'PROPERTY.SORT',
					'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE',
					'PROPERTY_MULTIPLE' => 'PROPERTY.MULTIPLE',
					'XML_ID' => 'PROPERTY_ENUM.XML_ID',
				],
				'runtime' => [
					new Reference(
						'PROPERTY',
						PropertyTable::class,
						Join::on('this.IBLOCK_PROPERTY_ID', 'ref.ID')
					),
					new Reference(
						'PROPERTY_ENUM',
						PropertyEnumerationTable::class,
						Join::on('this.VALUE_ENUM', 'ref.ID')
					),
				],
			])->fetchAll();
			foreach ($rsEProps as $ob)
			{
				$propertyCode = $arLinkIBlocks[$ob['IBLOCK_ID']];

				$id = (int)$ob['IBLOCK_ELEMENT_ID'];
				$code = $ob['PROPERTY_CODE'];

				$arValues =& $arEList[$propertyCode]['FULL'][$id]['PROPERTY_VALUES'][$code];
				if (!isset($arValues) || !is_array($arValues))
				{
					$arValues = [];
				}

				$vID = (int)$ob['ID'];
				$arValues[$vID] = [
					'ID' => $vID,
					'ACTIVE' => $ob['PROPERTY_ACTIVE'],
					'NAME' => $ob['PROPERTY_NAME'],
					'CODE' => $code,
					'SORT' => $ob['PROPERTY_SORT'],
					'PROPERTY_TYPE' => $ob['PROPERTY_TYPE'],
					'MULTIPLE' => $ob['PROPERTY_MULTIPLE'],
					'VALUE' => $ob['VALUE'],
					'VALUE_TYPE' => $ob['VALUE_TYPE'],
					'VALUE_ENUM' => $ob['VALUE_ENUM'],
					'VALUE_NUM' => $ob['VALUE_NUM'],
					'DESCRIPTION' => $ob['DESCRIPTION'],
					'XML_ID' => $ob['PROPERTY_TYPE'] == 'L' ? $ob['XML_ID'] : null,
				];

				$multiple = $ob['PROPERTY_MULTIPLE'] == 'Y';
				if (!$multiple)
				{
					$arValues = array_shift($arValues);
				}
			}
		}

		return $arEList;
	}

	#endregion

	#region Связанные пользователи

	/**
	 * Возвращает список пользователей, связанных с текущим элементом
	 *
	 * @param array $arElement
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getUsers(array $arElement): array
	{
		$arUsers = [];

		// Код => Многозначный (да/нет)
		$fields = [
			'CREATED_BY' => 'N',
			'MODIFIED_BY' => 'N',
		];

		// Получим список свойств типов "Привязка к пользователю" и "Привязка к работнику"
		if (Loader::includeModule('iblock'))
		{
			$rs = PropertyTable::getList([
				'order' => ['SORT' => 'ASC'],
				'filter' => [
					'IBLOCK_ID' => static::getIblockId(),
					'PROPERTY_TYPE' => 'S',
					'USER_TYPE' => ['employee', 'UserID'],
				],
				'select' => [
					'CODE',
					'MULTIPLE',
				],
			])->fetchAll();
			foreach ($rs as $ob)
			{
				$fields[$ob['CODE']] = $ob['MULTIPLE'];
			}
		}

		// Соберем ID пользователей
		foreach ($fields as $code => $multiple)
		{
			if ($multiple == 'Y')
			{
				foreach ($arElement[$code] as $userId)
				{
					if (intval($userId) > 0 && !isset($arUsers[$userId]))
					{
						$arUsers[$userId] = ['ID' => $userId];
					}
				}
			}
			else
			{
				$userId = $arElement[$code];
				if (intval($userId) > 0 && !isset($arUsers[$userId]))
				{
					$arUsers[$userId] = ['ID' => $userId];
				}
			}
		}

		// Получим список пользователей, связанных с текущим элементом
		if (Loader::includeModule('main'))
		{
			$rsUsers = CUser::GetList(
				'ID',
				'ASC',
				['ID' => implode('|', array_keys($arUsers))],
				[
					'FIELDS' => [
						'ID',
						'ACTIVE',
						'LOGIN',
						'EMAIL',
						'LAST_NAME',
						'NAME',
						'SECOND_NAME',
					],
					'SELECT' => ['UF_IIN'],
				]
			);
			while ($obUser = $rsUsers->Fetch())
			{
				$userId = intval($obUser['ID']);
				$arUsers[$userId] = $obUser;
			}
		}

		return $arUsers;
	}

	#endregion

	#region Пользовательские поля разделов

	/**
	 * Возвращает пользовательское поле разделов
	 *
	 * @param string $fieldName
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getSectionUserField(string $fieldName): array
	{
		$arUserFields = static::getSectionUserFieldList([$fieldName]);
		return $arUserFields[$fieldName];
	}

	/**
	 * Возвращает пользовательские поля разделов
	 *
	 * @param array $arFieldNames
	 * @return array
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getSectionUserFieldList(array $arFieldNames): array
	{
		$iblockId = static::getIblockId();

		$arUserFields = [];

		$rs = UserFieldTable::getList([
			'order' => ['SORT' => 'ASC', 'ID' => 'ASC'],
			'filter' => ['ENTITY_ID' => 'IBLOCK_'.$iblockId.'_SECTION', 'FIELD_NAME' => array_values($arFieldNames)],
			'select' => ['*'],
		])->fetchAll();
		foreach ($rs as $ob)
		{
			$arUserFields[$ob['FIELD_NAME']] = $ob;
		}

		return $arUserFields;
	}

	#endregion

	#region Значения списков пользовательских полей

	/**
	 * Возвращает значения пользовательского поля типа "Список"
	 *
	 * @param int $userFieldId
	 * @return array
	 */
	public static function getUserFieldEnumList(int $userFieldId): array
	{
		$arEnums = static::getUserFieldsEnumList([$userFieldId]);
		return $arEnums[$userFieldId];
	}

	/**
	 * Возвращает значения пользовательских полей типа "Список"
	 *
	 * @param array $arUserFieldId
	 * @return array
	 */
	public static function getUserFieldsEnumList(array $arUserFieldId): array
	{
		$arEnumList = [];
		foreach ($arUserFieldId as $userFieldId)
		{
			$arEnumList[$userFieldId] = ['XML_ID' => [], 'VALUE' => [], 'DEFAULT' => null, 'FULL' => []];
		}

		$rsTypes = CUserFieldEnum::GetList(['SORT' => 'ASC'], ['USER_FIELD_ID' => array_values($arUserFieldId)]);
		while ($ob = $rsTypes->GetNext(false, false))
		{
			$id = intval($ob['ID']);
			$arEnumList[$ob['USER_FIELD_ID']]['XML_ID'][$id] = $ob['XML_ID'];
			$arEnumList[$ob['USER_FIELD_ID']]['VALUE'][$id] = $ob['VALUE'];
			$arEnumList[$ob['USER_FIELD_ID']]['FULL'][$id] = $ob;

			if ($ob['DEF'] == 'Y')
			{
				$arEnumList[$ob['USER_FIELD_ID']]['DEFAULT'] = $id;
			}
		}

		return $arEnumList;
	}

	#endregion

	#region Копирование

	/**
	 * Подготовка данных для создания копии
	 *
	 * @param _CIBElement $ob
	 * @return array
	 * @noinspection PhpUnused
	 */
	public static function prepareElementForCopy(_CIBElement $ob): array
	{
		$obFields = $ob->GetFields();
		$obProps = $ob->GetProperties();

		$arElement = static::getFieldsValues($ob, __METHOD__);

		foreach ($obProps as $propCode => $obProp)
		{
			$arElement['PROPERTY_VALUES'][$propCode] = static::preparePropertyValue($obProp);
		}

		if (isset($obProps['PROCESS_STATUS']))
		{
			$arElement['PROPERTY_VALUES']['PROCESS_STATUS'] = 0;
		}
		if (isset($obProps['PROCESS_VERSION']))
		{
			$arElement['PROPERTY_VALUES']['PROCESS_VERSION'] = intval($obProps['PROCESS_VERSION']['VALUE']) + 1;
		}
		if (isset($obProps['PREV_ELEMENT_ID']))
		{
			$arElement['PROPERTY_VALUES']['PREV_ELEMENT_ID'] = $obFields['ID'];
		}
		if (isset($obProps['PROCESS_IS_LAST']))
		{
			$arElement['PROPERTY_VALUES']['PROCESS_IS_LAST'] = 1;
		}

		return $arElement;
	}

	private static function preparePropertyValue(array $obProp)
	{
		$value = $obProp['VALUE'];

		switch ($obProp['PROPERTY_TYPE'])
		{
			case 'S':
				if ($obProp['WITH_DESCRIPTION'] == 'Y')
				{
					if ($obProp['MULTIPLE'] == 'Y')
					{
						$newValue = [];
						if (is_array($value))
						{
							foreach ($value as $k => $v)
							{
								$newValue[] = [
									'VALUE' => htmlspecialchars_decode($v),
									'DESCRIPTION' => htmlspecialchars_decode($obProp['DESCRIPTION'][$k]),
								];
							}
						}
						$result = $newValue;
					}
					else
					{
						$result = [
							'VALUE' => htmlspecialchars_decode($value),
							'DESCRIPTION' => htmlspecialchars_decode($obProp['DESCRIPTION']),
						];
					}
				}
				else
				{
					$newValue = [];
					if (is_array($value))
					{
						foreach ($value as $v)
						{
							$newValue[] = htmlspecialchars_decode($v);
						}
						$result = $newValue;
					}
					else
					{
						$result = htmlspecialchars_decode($value);
					}

				}
				break;

			case 'L':
				if ($obProp['MULTIPLE'] == 'Y')
				{
					$newValue = [];
					if (is_array($obProp['VALUE_ENUM_ID']))
					{
						foreach ($obProp['VALUE_ENUM_ID'] as $k => $v)
						{
							$newValue[] = [
								'VALUE' => $v,
								'DESCRIPTION' => htmlspecialchars_decode($obProp['DESCRIPTION'][$k]),
							];
						}
					}
					$result = $newValue;
				}
				else
				{
					$result = [
						'VALUE' => $obProp['VALUE_ENUM_ID'],
						'DESCRIPTION' => htmlspecialchars_decode($obProp['DESCRIPTION']),
					];
				}
				break;

			case 'F':
				if ($obProp['MULTIPLE'] == 'Y')
				{
					$newValue = [];
					if (is_array($value))
					{
						if ($obProp['WITH_DESCRIPTION'] == 'Y')
						{
							foreach ($value as $k => $v)
							{
								$newValue[$k] = [
									'VALUE' => CFile::CopyFile($v),
									'DESCRIPTION' => htmlspecialchars_decode($obProp['DESCRIPTION'][$k]),
								];
							}
						}
						else
						{
							foreach ($value as $k => $v)
							{
								$newValue[$k] = CFile::CopyFile($v);
							}
						}
					}
					$result = $newValue;
				}
				else
				{
					if ($obProp['WITH_DESCRIPTION'] == 'Y')
					{
						$result = [
							'VALUE' => CFile::CopyFile($value),
							'DESCRIPTION' => htmlspecialchars_decode($obProp['DESCRIPTION']),
						];
					}
					else
					{
						$result = CFile::CopyFile($value);
					}
				}
				break;

			default:
				if ($obProp['WITH_DESCRIPTION'] == 'Y')
				{
					if ($obProp['MULTIPLE'] == 'Y')
					{
						$newValue = [];
						if (is_array($value))
						{
							foreach ($value as $k => $v)
							{
								$newValue[] = [
									'VALUE' => $v,
									'DESCRIPTION' => htmlspecialchars_decode($obProp['DESCRIPTION'][$k]),
								];
							}
						}
						$result = $newValue;
					}
					else
					{
						$result = [
							'VALUE' => $value,
							'DESCRIPTION' => htmlspecialchars_decode($obProp['DESCRIPTION']),
						];
					}
				}
				else
				{
					$result = $value;
				}
				break;
		}

		return $result;
	}

	#endregion

	#region Дочерние инфоблоки

	/**
	 * Возвращает список дочерних инфоблоков в формате
	 * 'КОД' => [
	 *   'PROPERTY_CODE' => 'КОД_СВОЙСТВА',
	 *   'CLASS' => НазваниеКласса::class,
	 * ],
	 *
	 * @return array
	 */
	public static function getChildIblockList(): array
	{
		return [];
	}

	#endregion
}