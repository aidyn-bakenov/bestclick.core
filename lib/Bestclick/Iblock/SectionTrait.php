<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 15.09.2022 19:18
 */

namespace Bestclick\Iblock;

use Bitrix\Iblock\Model\Section;
use Bitrix\Iblock\SectionTable;

trait SectionTrait
{
	/**
	 * Возвращает сущность для работы с разделами
	 *
	 * @return SectionTable|string|null
	 */
	public static function compileSectionEntity()
	{
		$iblockId = method_exists(static::class, 'getIblockId')
			? static::getIblockId()
			: 0;
		return Section::compileEntityByIblock($iblockId);
	}
}