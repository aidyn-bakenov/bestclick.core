<?php
/**
 * Created by Aidyn Bakenov.
 * Email: aidyn.bakenov@yandex.kz
 * 11.02.2024 23:19
 */

use Bitrix\Main\EventManager;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class bestclick_core extends CModule
{
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_ID = 'bestclick.core';
	private array $exclusionAdminFiles;

	function __construct()
	{
		$this->exclusionAdminFiles = [
			'..',
			'.',
			'menu.php'
		];

		$arModuleVersion = [];

		include __DIR__.'/version.php';

		if (is_array($arModuleVersion))
		{
			$this->MODULE_VERSION = array_key_exists('VERSION', $arModuleVersion)
				? $arModuleVersion['VERSION'] : '1.0.0';
			$this->MODULE_VERSION_DATE = array_key_exists('DATE', $arModuleVersion)
				? $arModuleVersion['DATE'] : '2024-02-11 23:19:58';
		}

		$this->MODULE_NAME = Loc::getMessage('BESTCLICK_CORE_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('BESTCLICK_CORE_MODULE_DESCRIPTION');

		$this->PARTNER_NAME = Loc::getMessage('BESTCLICK_CORE_MODULE_PARTNER');
		$this->PARTNER_URI = Loc::getMessage('BESTCLICK_CORE_MODULE_PARTNER_URI');

		$this->MODULE_GROUP_RIGHTS = 'N';
	}

	/**
	 * Получение места размещения модуля
	 *
	 * @param bool $notDocumentRoot
	 * @return string
	 */
	public static function getPath(bool $notDocumentRoot = false): string
	{
		return $notDocumentRoot
			? str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__))
			: dirname(__DIR__);
	}

	/**
	 * Проверка поддержки ядра D7
	 *
	 * @return bool
	 */
	public function isVersionD7(): bool
	{
		return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
	}

	function DoInstall(): void
	{
		global $APPLICATION;

		if (!Loader::includeModule('highloadblock'))
		{
			$APPLICATION->ThrowException(Loc::getMessage('BESTCLICK_CORE_MODULE_INSTALL_ERROR_VERSION'));
		}
		elseif ($this->isVersionD7())
		{
			ModuleManager::registerModule($this->MODULE_ID);

			$this->InstallFiles();
			$this->InstallEvents();
		}
		else
		{
			$APPLICATION->ThrowException(Loc::getMessage('BESTCLICK_CORE_MODULE_INSTALL_ERROR_VERSION'));
		}

		$APPLICATION->IncludeAdminFile(
			Loc::getMessage('BESTCLICK_CORE_MODULE_INSTALL_TITLE'),
			$this->getPath().'/install/step.php'
		);
	}

	function DoUnInstall(): void
	{
		global $APPLICATION;
		$request = Application::getInstance()->getContext()->getRequest();

		if ($request['step'] < 2)
		{
			$APPLICATION->IncludeAdminFile(
				Loc::getMessage('BESTCLICK_CORE_MODULE_INSTALL_TITLE'),
				$this->getPath().'/install/unstep1.php'
			);
		}
		elseif ($request['step'] == 2)
		{
			ModuleManager::unRegisterModule($this->MODULE_ID);

			$this->UnInstallFiles();
			$this->UnInstallEvents();

			$APPLICATION->IncludeAdminFile(
				Loc::getMessage('BESTCLICK_CORE_MODULE_INSTALL_TITLE'),
				$this->getPath().'/install/unstep2.php'
			);
		}
	}

	function InstallFiles(): void
	{
		if (Directory::isDirectoryExists($path = $this->GetPath().'/install/admin'))
		{
			CopyDirFiles(
				$this->GetPath().'/install/admin',
				$_SERVER['DOCUMENT_ROOT'].'/bitrix/admin'
			);
			if ($dir = opendir($path))
			{
				while (false !== $item = readdir($dir))
				{
					if (in_array($item, $this->exclusionAdminFiles))
					{
						continue;
					}
					file_put_contents(
						$_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$item,
						'<'.'? require($_SERVER["DOCUMENT_ROOT"]."'.$this->GetPath(true).'/admin/'.$item.'"); ?'.'>'
					);
				}
				closedir($dir);
			}
		}

		if (Directory::isDirectoryExists($this->GetPath().'/install/cron'))
		{
			$newPath = '/local/php_interface/cron';

			$currFolder = $_SERVER['DOCUMENT_ROOT'];
			foreach (explode('/', $newPath) as $folder)
			{
				if (empty($folder))
				{
					continue;
				}

				$currFolder .= '/'.$folder;
				if (!Directory::isDirectoryExists($currFolder))
				{
					Directory::createDirectory($currFolder);
				}
			}

			if (Directory::isDirectoryExists($_SERVER['DOCUMENT_ROOT'].$newPath))
			{
				CopyDirFiles(
					$this->GetPath().'/install/cron',
					$_SERVER['DOCUMENT_ROOT'].$newPath,
					false,
					true
				);
			}
		}

		if (Directory::isDirectoryExists($this->GetPath().'/install/php_interface'))
		{
			$newPath = '/local/php_interface/include';

			$currFolder = $_SERVER['DOCUMENT_ROOT'];
			foreach (explode('/', $newPath) as $folder)
			{
				if (empty($folder))
				{
					continue;
				}

				$currFolder .= '/'.$folder;
				if (!Directory::isDirectoryExists($currFolder))
				{
					Directory::createDirectory($currFolder);
				}
			}

			if (Directory::isDirectoryExists($_SERVER['DOCUMENT_ROOT'].$newPath))
			{
				CopyDirFiles(
					$this->GetPath().'/install/php_interface',
					$_SERVER['DOCUMENT_ROOT'].$newPath,
					false,
					true
				);
			}
		}
	}

	function UnInstallFiles(): void
	{
		if (Directory::isDirectoryExists($path = $this->GetPath().'/install/admin'))
		{
			DeleteDirFiles(
				$_SERVER['DOCUMENT_ROOT'].$this->GetPath().'/install/admin/',
				$_SERVER['DOCUMENT_ROOT'].'/bitrix/admin'
			);
			if ($dir = opendir($path))
			{
				while (false !== $item = readdir($dir))
				{
					if (in_array($item, $this->exclusionAdminFiles))
					{
						continue;
					}
					File::deleteFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$item);
				}
				closedir($dir);
			}
		}
	}

	function InstallEvents(): void
	{
		$eventManager = EventManager::getInstance();
	}

	function UnInstallEvents(): void
	{
		$eventManager = EventManager::getInstance();
	}
}