<?
define("NOT_CHECK_FILE_PERMISSIONS", true);
define("BX_MOBILE_LOG", true);

require($_SERVER["DOCUMENT_ROOT"] . "/thurly/modules/main/include/prolog_before.php");
header("Content-Type: application/x-javascript");
CModule::IncludeModule("mobileapp");
$platform = strtolower(CMobile::getInstance()->getPlatform());
echo \Thurly\MobileApp\Designer\Manager::getConfigJSON("#code#", $platform);
die();

