<?php
/**
 * Created by Aidyn Bakenov.
 * Email: aidyn.bakenov@yandex.kz
 * 11.02.2024 23:19
 */

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid())
{
	return;
}

Loc::loadMessages(__FILE__);

global $APPLICATION;

if ($ex = $APPLICATION->GetException())
{
	CAdminMessage::ShowMessage(array(
		'TYPE' => 'ERROR',
		'MESSAGE' => Loc::getMessage('MOD_UNINST_ERR'),
		'DETAILS' => $ex->GetString(),
		'HTML' => true
	));
}
else
{
	CAdminMessage::ShowNote(Loc::getMessage('MOD_UNINST_OK'));
}
?>
<form action="<?= $APPLICATION->GetCurPage(); ?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
	<input type="submit" name="" value="<?= Loc::getMessage('MOD_BACK') ?>">
</form>