<?
if (!defined('IM_AJAX_INIT'))
{
	define("IM_AJAX_INIT", true);
	define("PUBLIC_AJAX_MODE", true);
	define("NO_KEEP_STATISTIC", "Y");
	define("NO_AGENT_STATISTIC","Y");
	define("NO_AGENT_CHECK", true);
	define("DisableEventsCheck", true);

	if (isset($_GET['action']))
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");
		if (CModule::IncludeModule('disk'))
		{
			$ufController = new Thurly\Disk\Uf\Controller();
			$ufController->setActionName($_GET['action'])->exec();
		}
		die();
	}

	require_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/include/prolog_before.php");
}

if (\Thurly\Main\Loader::includeModule("im"))
{
	echo \Thurly\Im\Common::objectEncode(Array(
		'THURLY_SESSID' => thurly_sessid(),
		'ERROR' => 'FILE_ERROR'
	));
}

CMain::FinalActions();
die();