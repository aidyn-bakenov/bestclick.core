<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 09.09.2023 19:46
 */

namespace Bestclick\Event;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bestclick\Localization\Loc as BestclickLoc;
use Bestclick\Object\Settings;

class PrologHandlers
{
	/**
	 * @return void
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function onLoad(): void
	{
		$isAdminSection = Context::getCurrent()->getRequest()->isAdminSection();
		if (!$isAdminSection)
		{
			$settings = Settings::getInstance();
			$settings->findLanguage();
			$settings->setReady(true);

			if (Loader::includeModule('bestclick.translation'))
			{
				BestclickLoc::loadMessages();
			}
		}
	}
}