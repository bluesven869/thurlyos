<?php
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

use Thurly\Main\Localization\Loc;

if(!\Thurly\Main\Loader::includeModule('rest'))
{
	return;
}

$dbApps = \Thurly\Rest\AppTable::getList(
	array(
		'filter' => array(
			"=ACTIVE" => \Thurly\Rest\AppTable::ACTIVE,
			"=STATUS" => \Thurly\Rest\AppTable::STATUS_PAID,
		),
		'select' => array('CNT'),
		'runtime' => array(
			new \Thurly\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
		)
	)
);

$res = $dbApps->fetch();
$appsCount = $res['CNT'];

$arResult["UNINSTALLED_PAID_APPS_COUNT"] = intval($appsCount);

$arResult['ITEMS'] = array(
	array(
		"TEXT" => Loc::getMessage("MARKETPLACE_BEST"),
		"LINK" => $arParams['TOP_URL'],
		"PARAMS" => array("class" => "top"),
		"SELECTED" => $arParams["COMPONENT_PAGE"] == "top",
	),
	array(
		"TEXT" => Loc::getMessage("MARKETPLACE_CATEGORIES"),
		"LINK" => "",
		"PARAMS" => array("class" => "category"),
		"SELECTED" => $arParams["COMPONENT_PAGE"] == "category",
	),
);

if(\CRestUtil::isAdmin())
{
	$arResult['ITEMS'][] = array(
		"TEXT" => Loc::getMessage("MARKETPLACE_SHOPPING"),
		"LINK" => $arParams['BUY_URL'],
		"PARAMS" => array("class" => "sale"),
		"SELECTED" => $arParams["COMPONENT_PAGE"] == "buy",
	);
}

$arResult['ITEMS'][] = array(
	"TEXT" => Loc::getMessage("MARKETPLACE_UPDATES"),
	"LINK" => "/marketplace/updates/",
	"PARAMS" => array("class" => "updates"),
	"SELECTED" => $arParams["COMPONENT_PAGE"] == "updates",
);

$arResult['NUM_UPDATES'] = intval(\Thurly\Main\Config\Option::get("rest", "mp_num_updates", 0));

$arResult['CATEGORY_LIST'] = \Thurly\Rest\Marketplace\Client::getCategories();

$this->includeComponentTemplate();