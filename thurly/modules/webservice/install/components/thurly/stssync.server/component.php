<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Thurly vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CThurlyComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */


$arDefaultUrlTemplates404 = array(
	"endpoint" => "#user_id#/#ap#/_vti_bin/lists.asmx",
	"redirect_item" => "#user_id#/#ap#/#path#/DispForm.aspx",
	"redirect_index" => "#user_id#/#ap#/#path#/"
);

$arDefaultVariableAliases404 = array();
$arDefaultVariableAliases = array();

$arComponentVariables = array(
	"user_id", "ap", "path"
);

$arVariables = array();

$componentPage = 'redirect_index';

if($arParams["SEF_MODE"] == "Y")
{
	$engine = new CComponentEngine($this);
	$engine->addGreedyPart("#path#");

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = $engine->guessComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
}
else
{
	ShowError('Non-SEF mode is not supported by thurly:stssync.server component');
}

if($componentPage !== 'endpoint')
{
	$componentPage = 'redirect_index';
}

if($componentPage == 'endpoint')
{
	if(!isset($arVariables['user_id']) || !isset($arVariables['ap']) || intval($arVariables['user_id']) <= 0 || strlen($arVariables['ap']) <= 0)
	{
		die('wrong request');
	}

	if(!\Thurly\Main\Loader::includeModule('webservice'))
	{
		die('Webservice module not installed');
	}

	\Thurly\WebService\StsSync::checkAuth($arVariables['user_id'], $arVariables['ap']);

	$APPLICATION->IncludeComponent(
		"thurly:webservice.server",
		"",
		array(
			'WEBSERVICE_NAME' => $arParams['WEBSERVICE_NAME'],
			'WEBSERVICE_CLASS' => $arParams['WEBSERVICE_CLASS'],
			'WEBSERVICE_MODULE' => $arParams['WEBSERVICE_MODULE'],
		),
		null, array('HIDE_ICONS' => 'Y')
	);

	CMain::FinalActions();
	die();
}
else
{
	$redirectUrl = '/';
	if(empty($arParams['REDIRECT_PATH']) || empty($_REQUEST['ID']))
	{
		if(!empty($arVariables['path']))
		{
			$redirectUrl = $arVariables['path'].'/';
		}
		else
		{
			$redirectUrl = '';
		}
	}
	else
	{
		$redirectUrl = str_replace(
			array('#ID#', '#PATH#'),
			array(intval($_REQUEST['ID']), $arVariables['path']),
			$arParams['REDIRECT_PATH']
		);
	}

	$redirectUrl = str_replace('.php/', '.php', $redirectUrl);
	$redirectUrl = '/'.ltrim($redirectUrl, '/');

	LocalRedirect($redirectUrl);
}