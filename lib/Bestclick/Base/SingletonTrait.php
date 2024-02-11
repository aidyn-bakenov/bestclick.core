<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 10.09.2023 18:12
 */

namespace Bestclick\Base;

use Exception;

trait SingletonTrait
{
	#region Свойства

	private static array $instances = [];

	#endregion

	#region Instance

	/**
	 * Возвращает единственный экземпляр класса
	 *
	 * @return self
	 */
	public static function getInstance(): self
	{
		$cls = static::class;
		if (!isset(self::$instances[$cls]))
		{
			self::$instances[$cls] = new static();
		}
		return self::$instances[$cls];
	}

	/**
	 * Синглтон всегда должен быть закрытым, чтобы запретить использование оператора "new"
	 */
	protected function __construct() {}

	/**
	 * Синглтон не может быть клонирован
	 */
	protected function __clone() {}

	/**
	 * Синглтон не может быть восстановлен
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function __wakeup()
	{
		throw new Exception('Cannot unserialize a singleton.');
	}

	#endregion
}