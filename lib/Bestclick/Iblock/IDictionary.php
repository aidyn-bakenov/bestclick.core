<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 29.03.2023 10:34
 */

namespace Bestclick\Iblock;

use Bestclick\Object\Dictionary;

interface IDictionary
{
	public static function getDictionary(): Dictionary;
}