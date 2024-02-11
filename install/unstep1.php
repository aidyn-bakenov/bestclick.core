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

$moduleId = 'bestclick.core';

?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="id" value="<?= $moduleId ?>">
    <input type="hidden" name="step" value="2">
    <input type="hidden" name="uninstall" value="Y">
    <?php CAdminMessage::ShowMessage(Loc::getMessage('MOD_UNINST_WARN')); ?>
    <input type="submit" name="" value="<?= Loc::getMessage('MOD_UNINST_DEL') ?>">
</form>