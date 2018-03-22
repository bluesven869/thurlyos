<?
/*
##############################################
# Thurly: SiteManager                        #
# Copyright (c) 2004 Thurly                  #
# http://www.thurly.ru                       #
# mailto:admin@thurly.ru                     #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_admin_before.php");
$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/form/include.php");
$err_mess = "File: ".__FILE__."<br>Line: ";

/***************************************************************************
						   ��������� GET | POST
****************************************************************************/

$q = CForm::GetByID($WEB_FORM_ID);
$arrForm = $q->Fetch();

$F_RIGHT = CForm::GetPermission($arrForm["ID"]);
if ($F_RIGHT<30) $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if (check_thurly_sessid()) CForm::SetMailTemplate($WEB_FORM_ID, "Y");

IncludeModuleLangFile(__FILE__);
$strNote .= GetMessage("FORM_GENERATING_FINISHED")."<br>";

/***************************************************************************
							   HTML �����
****************************************************************************/

$APPLICATION->SetTitle(GetMessage("FORM_PAGE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_popup_admin.php")
?>
<div align="center"><?=ShowNote($strNote)?>
<font class="tablebodytext">[&nbsp;<a target="_blank" href="/thurly/admin/message_admin.php?lang=<?=LANGUAGE_ID?>&find_type_id=<?=htmlspecialcharsbx($arrForm["MAIL_EVENT_TYPE"])?>&set_filter=Y" class="tablebodylink"><?=GetMessage("FORM_VIEW_TEMPLATE")?></a>&nbsp;]</font><br>
<form><input class="button" type="button" onClick="window.close()" value="<?echo GetMessage("FORM_CLOSE")?>"></form></div>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/epilog_popup_admin.php")?>