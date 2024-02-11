<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 03.06.2022 11:26
 */

namespace Bestclick\Base;

use Bitrix\Disk\Internals\Error\Error;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\GroupTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserGroupTable;
use CFile;
use CUser;

class UserHelperTable
{
	#region Пользователь

	/**
	 * Возвращает данные о пользователе
	 *
	 * @param int $userId
	 * @param array $customSelect
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getUser(int $userId, array $customSelect = []): array
	{
		$arUser = [];

		$rsUsers = static::getUsers([$userId], $customSelect);
		if (!empty($rsUsers))
		{
			$arUser = array_shift($rsUsers);
			$arUser['GROUP_LIST'] = static::getUserGroups($userId);

			$arUser['GROUP_CODES'] = [];
			foreach ($arUser['GROUP_LIST'] as $group)
			{
				if ($group['STRING_ID'])
				{
					$arUser['GROUP_CODES'][] = $group['STRING_ID'];
				}
			}
		}

		return $arUser;
	}

	/**
	 * Возвращает данные о пользователях
	 *
	 * @param array $userIds
	 * @param array $customSelect
	 * @return array
	 * @throws LoaderException
	 */
	public static function getUsers(array $userIds, array $customSelect = []): array
	{
		$arUsers = [];

		$userIds = array_unique($userIds);
		if (!empty($userIds))
		{
			$filter = [
				'ID' => implode('|', $userIds),
			];
			$arUsers = static::getByFilter($filter, $customSelect);
		}

		return $arUsers;
	}

	/**
	 * Возвращает пользователей по фильтру
	 *
	 * @param array $filter
	 * @param array $customSelect
	 * @return array
	 * @throws LoaderException
	 */
	private static function getByFilter(array $filter, array $customSelect = []): array
	{
		$arUsers = [];

		if (Loader::includeModule('main'))
		{
			$defaultSelect = [
				'FIELDS' => [
					'ID',
					'LAST_NAME',
					'NAME',
					'SECOND_NAME',
					'LOGIN',
					'EMAIL',
					'DATE_REGISTER',
					'LAST_LOGIN',
					'PERSONAL_PHONE',
					'PERSONAL_MOBILE',
					'PERSONAL_GENDER',
					'PERSONAL_WWW',
					'PERSONAL_COUNTRY',
					'PERSONAL_STATE',
					'PERSONAL_CITY',
					'PERSONAL_PHOTO',
					'WORK_DEPARTMENT',
					'WORK_POSITION',
					'WORK_PHONE',
				],
				'SELECT' => [],
			];
			if (empty($customSelect))
			{
				$select = $defaultSelect;
			}
			else
			{
				if (in_array('FULL_NAME', $customSelect))
				{
					foreach (['LAST_NAME', 'NAME', 'SECOND_NAME'] as $fieldName)
					{
						if (!in_array($fieldName, $customSelect))
						{
							$customSelect[] = $fieldName;
						}
					}
				}

				$select = [];
				foreach ($customSelect as $fieldName)
				{
					if (in_array($fieldName, $defaultSelect['FIELDS']))
					{
						$select['FIELDS'][] = $fieldName;
					}
					elseif (in_array($fieldName, $defaultSelect['SELECT']))
					{
						$select['SELECT'][] = $fieldName;
					}
				}
			}

			$rsUsers = CUser::GetList('ID', 'ASC', $filter, $select);
			while ($ob = $rsUsers->GetNext(false, false))
			{
				$user = [];

				if (isset($ob['LAST_NAME']) || isset($ob['NAME']) || isset($ob['SECOND_NAME']))
				{
					$user['FULL_NAME'] = CUser::FormatName('#LAST_NAME# #NAME# #SECOND_NAME#', $ob);
				}

				foreach ($select['FIELDS'] as $fieldName)
				{
					if (isset($ob[$fieldName]))
					{
						$value = $ob[$fieldName];

						if ($fieldName == 'ID')
						{
							$value = (int)$value;
						}
						elseif (in_array($fieldName, ['DATE_REGISTER', 'LAST_LOGIN']))
						{
							$value = is_object($value) && method_exists($value, 'format') && $value
								? $value->format('d.m.Y H:i:s')
								: $value;
						}
						elseif ($fieldName == 'PERSONAL_PHOTO' && intval($ob[$fieldName]) > 0)
						{
							$user[$fieldName] = [
								'ID' => $value,
								'SRC' => CFile::GetPath($value),
								'ALT' => $user['FULL_NAME'],
							];
						}

						if (!isset($user[$fieldName]))
						{
							$user[$fieldName] = $value;
						}
					}
				}
				foreach ($select['SELECT'] as $fieldName)
				{
					if (isset($ob[$fieldName]))
					{
						$user[$fieldName] = $ob[$fieldName];
					}
				}

				$arUsers[$ob['ID']] = $user;
			}
		}

		return $arUsers;
	}

	/**
	 * Возвращает ФИО пользователя
	 *
	 * @param int $userId
	 * @return string
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getFullName(int $userId): string
	{
		$arUser = static::getUser($userId, ['ID', 'LAST_NAME', 'NAME', 'SECOND_NAME']);
		return (string)$arUser['FULL_NAME'];
	}

	/**
	 * Возвращает ФИО пользователя
	 *
	 * @param array $userIds
	 * @return array
	 * @throws LoaderException
	 * @noinspection PhpUnused
	 */
	public static function getUsersFullNames(array $userIds): array
	{
		$rsUser = static::getUsers($userIds, ['ID', 'LAST_NAME', 'NAME', 'SECOND_NAME']);

		$arUsers = [];
		foreach ($rsUser as $ob)
		{
			$arUsers[$ob['ID']] = (string)$ob['FULL_NAME'];
		}

		return $arUsers;
	}

	#endregion

	#region Группа пользователей

	/**
	 * Возвращает группы пользователя
	 *
	 * @param int $userId
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getUserGroups(int $userId): array
	{
		$arGroups = [];

		if (Loader::includeModule('main'))
		{
			$rsGroups = UserGroupTable::getList([
				'filter' => ['USER_ID' => $userId],
				'select' => ['GROUP_ID', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO', 'GROUP.STRING_ID']
			])->fetchAll();
			if ($rsGroups)
			{
				foreach ($rsGroups as $group)
				{
					$arGroups[$group['GROUP_ID']] = [
						'ID' => $group['GROUP_ID'],
						'DATE_ACTIVE_FROM' => $group['DATE_ACTIVE_FROM'],
						'DATE_ACTIVE_TO' => $group['DATE_ACTIVE_TO'],
						'STRING_ID' => $group['MAIN_USER_GROUP_GROUP_STRING_ID'],
					];
				}
			}
		}

		return $arGroups;
	}

	/**
	 * Возвращает данные о группе
	 *
	 * @param string $code
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getGroup(string $code): array
	{
		$group = [];

		if (Loader::includeModule('main'))
		{
			$group = GroupTable::getList(['filter' => ['STRING_ID' => $code]])->fetch();
		}

		return $group;
	}

	/**
	 * Возвращает пользователей группы
	 *
	 * @param string $code
	 * @param array $customSelect
	 * @return array
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function getGroupUsers(string $code, array $customSelect = []): array
	{
		$arUsers = [];

		$group = static::getGroup($code);
		if (!empty($group))
		{
			$filter = [
				'GROUPS_ID' => (int)$group['ID'],
			];
			$arUsers = static::getByFilter($filter, $customSelect);
		}

		return $arUsers;
	}

	#endregion

	#region Проверка на вхождение в группу

	/**
	 * Проверка на вхождение в группы
	 *
	 * @param int $userId
	 * @param array $groupCodes
	 * @param array $arUserGroups
	 * @return Result
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public static function inGroups(int $userId, array $groupCodes = [], array $arUserGroups = []): Result
	{
		$result = new Result();

		$arUserGroupCodes = [];

		if (empty($arUserGroups))
		{
			$arUserGroups = static::getUserGroups($userId);
		}
		if (!empty($groupCodes))
		{
			$currTimestamp = (new DateTime())->getTimestamp();

			foreach ($arUserGroups as $group)
			{
				$isActualGroup = true;

				// Проверка даты начала активности
				$dateActiveFrom = $group['DATE_ACTIVE_FROM'];
				if ($dateActiveFrom instanceof DateTime)
				{
					$activeFromTimestamp = $dateActiveFrom->getTimestamp();
					$isActualGroup = $currTimestamp > $activeFromTimestamp;
				}

				// Проверка даты окончания активности
				$dateActiveTo = $group['DATE_ACTIVE_TO'];
				if ($dateActiveTo instanceof DateTime)
				{
					$activeToTimestamp = $dateActiveTo->getTimestamp();
					$isActualGroup = $currTimestamp < $activeToTimestamp;
				}

				if ($isActualGroup && $group['STRING_ID'])
				{
					$arUserGroupCodes[] = $group['STRING_ID'];
				}
			}
		}

		if (count(array_intersect($groupCodes, $arUserGroupCodes)) == 0)
		{
			$result->addError(new Error(Loc::getMessage('USER_HELPER_USER_IS_NOT_MEMBER_OF_GROUPS')));
		}

		return $result;
	}

	#endregion
}