<?
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_admin_before.php');
CComponentUtil::__IncludeLang(dirname($_SERVER["SCRIPT_NAME"]), "/ajax.php");

$arResult = array();

if (!CModule::IncludeModule("thurlycloud"))
	$arResult["ERROR"] = GetMessage("BCLMMD_BC_NOT_INSTALLED");

if(!$USER->CanDoOperation("thurlycloud_monitoring") || !check_thurly_sessid())
	$arResult["ERROR"] = GetMessage("BCLMMD_ACCESS_DENIED");

if(!isset($arResult["ERROR"]))
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
	$domain = isset($_REQUEST['domain']) ? trim($_REQUEST['domain']) : '';
	$monitoring = CThurlyCloudMonitoring::getInstance();

	switch ($action)
	{
		case 'update':
			try
			{
				$result = $monitoring->startMonitoring(
					$domain,
					$_REQUEST["IS_HTTPS"]==="Y",
					$_REQUEST["LANG"],
					$_REQUEST["EMAILS"],
					$_REQUEST["TESTS"]
				);

				if ($result != "")
					$arResult["ERROR"] = $result;
			}
			catch (Exception $e)
			{
				$arResult["ERROR"] = $e->getMessage();
			}
			break;
	}

	if(isset($arResult["ERROR"]))
		$arResult["RESULT"] = "ERROR";
	else
		$arResult["RESULT"] = "OK";

}

$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');
die(json_encode($arResult));
?>
