<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 02.05.2023 17:01
 */

namespace Bestclick\Enumeration;

use Bestclick\Object\Enumeration;

interface IPropertyEnumeration
{
	public static function getIblockId(): int;

	public static function getPropertyCode(): string;

	public static function getEnumeration(): Enumeration;
}