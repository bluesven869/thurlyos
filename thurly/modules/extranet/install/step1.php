<?if(!check_thurly_sessid()) return;?>
<?
global $APPLICATION;

if($ex = $APPLICATION->GetException())
	echo CAdminMessage::ShowMessage(Array(
		"TYPE" => "ERROR",
		"MESSAGE" => GetMessage("MOD_INST_ERR"),
		"DETAILS" => $ex->GetString(),
		"HTML" => true,
	));
else
	echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
?>
<p>
<a href="/thurly/admin/wizard_install.php?lang=<?echo LANG?>&wizardName=thurly:extranet&<? echo thurly_sessid_get()?>"><? echo GetMessage('MOD_EXTRANET_RUN_WIZARD'); ?></a>
<p>
<form action="<?echo $APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?echo LANG?>">
	<input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
</form>