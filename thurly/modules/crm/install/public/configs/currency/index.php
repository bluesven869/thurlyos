<? 
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php"); 
global $APPLICATION;
$APPLICATION->SetTitle(GetMessage("CRM_PAGE_TITLE"));
$APPLICATION->IncludeComponent(
	"thurly:crm.currency", 
	".default", 
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "#SITE_DIR#crm/configs/currency/",
	),
	false
);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php"); 
?>
