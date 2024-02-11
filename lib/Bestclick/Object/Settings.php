<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 09.09.2023 19:53
 */

namespace Bestclick\Object;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\EO_Language;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bestclick\Base\SingletonTrait;

class Settings
{
	use SingletonTrait;

	#region Свойства

	protected bool $ready = false;
	protected EO_Language $language;
	protected array $files = [];

	#endregion

	#region Методы get/set

	/**
	 * @return bool
	 */
	public function isReady(): bool
	{
		return $this->ready;
	}

	/**
	 * @param bool $ready
	 */
	public function setReady(bool $ready): void
	{
		$this->ready = $ready;
	}

	/**
	 * @return EO_Language
	 */
	public function getLanguage(): EO_Language
	{
		return $this->language;
	}

	/**
	 * @param EO_Language $language
	 */
	public function setLanguage(EO_Language $language): void
	{
		$this->language = $language;
	}

	/**
	 * @return bool
	 */
	public function isVisuallyImpaired(): bool
	{
		return $_SESSION['VISUALLY_IMPAIRED'] === true;
	}

	/**
	 * @param bool $visuallyImpaired
	 */
	public function setVisuallyImpaired(bool $visuallyImpaired): void
	{
		if ($visuallyImpaired)
		{
			$_SESSION['VISUALLY_IMPAIRED'] = true;
		}
		else
		{
			unset($_SESSION['VISUALLY_IMPAIRED']);
		}
	}

	/**
	 * @return int
	 */
	public function getFontSize(): int
	{
		return $_SESSION['VISUALLY_IMPAIRED_FONT_SIZE'] ?? 16;
	}

	/**
	 * @param int $fontSize
	 */
	public function setFontSize(int $fontSize = 16): void
	{
		$_SESSION['VISUALLY_IMPAIRED_FONT_SIZE'] = $fontSize;
	}

	#endregion

	#region Получение данных

	/**
	 * Сохраняет текущий язык
	 *
	 * @param string $lang
	 * @return void
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function findLanguage(string $lang = ''): void
	{
		if (empty($lang))
		{
			$lang = LANGUAGE_ID;
		}

		$rs = LanguageTable::getById($lang);
		if ($ob = $rs->fetchObject())
		{
			$this->setLanguage($ob);
		}
	}

	#endregion
}