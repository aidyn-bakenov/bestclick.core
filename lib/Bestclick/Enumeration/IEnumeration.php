<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 02.05.2023 17:01
 */

namespace Bestclick\Enumeration;

use Bitrix\Main\EO_UserField;
use Bestclick\Object\Enumeration;

interface IEnumeration
{
	public static function getEntityId(): string;

	public static function getFieldName(): string;

	public static function getUserField(array $select = []): EO_UserField;

	public static function getEnumeration(): Enumeration;
}