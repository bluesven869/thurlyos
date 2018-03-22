<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/services/subscr_edit.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));
?>

<p><?=GetMessage("SERVICES_INFO")?></p>

<?$APPLICATION->IncludeComponent(
	"thurly:subscribe.simple",
	"",
	Array(
		"AJAX_MODE" => "N", 
		"SHOW_HIDDEN" => "N", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600", 
		"SET_TITLE" => "N", 
		"AJAX_OPTION_SHADOW" => "Y", 
		"AJAX_OPTION_JUMP" => "N", 
		"AJAX_OPTION_STYLE" => "Y", 
		"AJAX_OPTION_HISTORY" => "N" 
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>