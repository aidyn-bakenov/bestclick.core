<?php

use Bitrix\Main\Loader;

$moduleId = 'bestclick.core';

Loader::registerAutoLoadClasses($moduleId, [
	#region Подключение trait
	'Bestclick\Base\SingletonTrait' => 'lib/Bestclick/Base/SingletonTrait.php',
	'Bestclick\Enumeration\EnumerationTrait' => 'lib/Bestclick/Enumeration/EnumerationTrait.php',
	'Bestclick\Enumeration\PropertyEnumerationTrait' => 'lib/Bestclick/Enumeration/PropertyEnumerationTrait.php',
	'Bestclick\Iblock\DictionaryTrait' => 'lib/Bestclick/Iblock/DictionaryTrait.php',
	'Bestclick\Iblock\ElementTableD7Trait' => 'lib/Bestclick/Iblock/ElementTableD7Trait.php',
	'Bestclick\Iblock\SectionTrait' => 'lib/Bestclick/Iblock/SectionTrait.php',
	'Bestclick\HighloadBlock\HighloadBlockTrait' => 'lib/Bestclick/HighloadBlock/HighloadBlockTrait.php',
	'Bestclick\Workflow\BizprocTrait' => 'lib/Bestclick/Workflow/BizprocTrait.php',
	'Bestclick\Workflow\BizprocNotificationTrait' => 'lib/Bestclick/Workflow/BizprocNotificationTrait.php',
	#endregion
	#region Подключение interface
	'Bestclick\Enumeration\IEnumeration' => 'lib/Bestclick/Enumeration/IEnumeration.php',
	'Bestclick\Enumeration\IPropertyEnumeration' => 'lib/Bestclick/Enumeration/IPropertyEnumeration.php',
	'Bestclick\Iblock\IDictionary' => 'lib/Bestclick/Iblock/IDictionary.php',
	'Bestclick\Iblock\IElementTableD7' => 'lib/Bestclick/Iblock/IElementTableD7.php',
	'Bestclick\HighloadBlock\IHighloadBlock' => 'lib/Bestclick/HighloadBlock/IHighloadBlock.php',
	'Bestclick\Workflow\IWorkflowBizproc' => 'lib/Bestclick/Workflow/IWorkflowBizproc.php',
	#endregion
	#region Подключение объектов
	'Bestclick\Object\Dictionary' => 'lib/Bestclick/Object/Dictionary.php',
	'Bestclick\Object\DictionaryItem' => 'lib/Bestclick/Object/DictionaryItem.php',
	'Bestclick\Object\Enumeration' => 'lib/Bestclick/Object/Enumeration.php',
	'Bestclick\Object\EnumerationItem' => 'lib/Bestclick/Object/EnumerationItem.php',
	'Bestclick\Object\Settings' => 'lib/Bestclick/Object/Settings.php',
	#endregion
	#region Подключение контроллеров
	'Bestclick\Controller\Project' => 'lib/Bestclick/Controller/Project.php',
	'Bestclick\Controller\VisuallyImpaired' => 'lib/Bestclick/Controller/VisuallyImpaired.php',
	#endregion
	#region Подключение справочников
	#endregion
	#region Подключение highload-блоков
	#endregion
	#region Подключение классов
	'Bestclick\Base\UserHelperTable' => 'lib/Bestclick/Base/UserHelperTable.php',
	'Bestclick\Event\PrologHandlers' => 'lib/Bestclick/Event/PrologHandlers.php',
	#endregion
]);