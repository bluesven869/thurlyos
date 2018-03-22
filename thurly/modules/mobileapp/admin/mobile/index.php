<?
require_once($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/mobileapp/include/defines.php');
require($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/main/include/prolog_admin_before.php');

if (!CModule::IncludeModule('mobileapp')) die("Module mobileapp not installed!");

$startPage = CAdminMobileMenu::getDefaultUrl( array(
						"MENU_FILE" => MOBILE_APP_MENU_FILE,
						"EVENT_NAME" => MOBILE_APP_BUILD_MENU_EVENT_NAME,
						"MOBILE_APP_INDEX_PAGE" => MOBILE_APP_ADMIN_PATH
					));

$APPLICATION->IncludeComponent("thurly:mobileapp.auth","",Array(
	"START_PAGE" => $startPage,
	"MENU_PAGE" => MOBILE_APP_ADMIN_PATH."/left.php"
),false, Array("HIDE_ICONS" => "Y"));

require($_SERVER["DOCUMENT_ROOT"] . '/thurly/modules/main/include/epilog_admin_after.php');
?>