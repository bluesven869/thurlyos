<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/crm/configs/widget/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE_WIDGET"));
?><?$APPLICATION->IncludeComponent(
	"thurly:crm.widget_slot.list",
	"",
	Array(
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>