<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/crm/configs/bp/index.php");
CModule::IncludeModule('bizproc');


$APPLICATION->SetTitle(GetMessage("TITLE"));
?><?
$APPLICATION->IncludeComponent(
	"thurly:crm.config.bp",
	"",
	Array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/crm/configs/bp/",
		"SEF_URL_TEMPLATES" => Array(
			"ENTITY_LIST_URL" => "",
			"FIELDS_LIST_URL" => "#entity_id#/",
			"FIELD_EDIT_URL" => "#entity_id#/edit/#bp_id#/"
		),
		"VARIABLE_ALIASES" => Array(
			"ENTITY_LIST_URL" => Array(),
			"FIELDS_LIST_URL" => Array(),
			"FIELD_EDIT_URL" => Array(),
		)
	)
);

?><?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>