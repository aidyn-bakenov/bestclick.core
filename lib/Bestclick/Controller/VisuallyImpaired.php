<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 24.09.2023 18:53
 */

namespace Bestclick\Controller;

use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\Controller;
use Bestclick\Object\Settings;

class VisuallyImpaired extends Controller
{
	#region Настройка контроллера

	public function configureActions(): array
	{
		return [
			'isVisuallyImpaired' => [
				'-prefilters' => [
					Authentication::class,
				],
			],
			'setVisuallyImpaired' => [
				'-prefilters' => [
					Authentication::class,
				],
			],
			'getFontSize' => [
				'-prefilters' => [
					Authentication::class,
				],
			],
			'setFontSize' => [
				'-prefilters' => [
					Authentication::class,
				],
			],
		];
	}

	#endregion

	#region Методы для получения и обработки данных

	/**
	 * Возвращает активность режима для слабовидящих
	 *
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function isVisuallyImpairedAction(): bool
	{
		return Settings::getInstance()->isVisuallyImpaired();
	}

	/**
	 * Сохраняет активность режима для слабовищяших
	 *
	 * @param string $visuallyImpaired
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function setVisuallyImpairedAction(string $visuallyImpaired): void
	{
		Settings::getInstance()->setVisuallyImpaired($visuallyImpaired == 'Y');
	}

	/**
	 * Возвращает текущий размер шрифта
	 *
	 * @return int
	 * @noinspection PhpUnused
	 */
	public function getFontSizeAction(): int
	{
		return Settings::getInstance()->getFontSize();
	}

	/**
	 * Сохраняет текущий размер шрифта
	 *
	 * @param int $fontSize
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function setFontSizeAction(int $fontSize): void
	{
		Settings::getInstance()->setFontSize($fontSize);
	}

	#endregion
}