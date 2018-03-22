<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CThurlyComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CThurlyComponent $component */
?>


<?
$pageId = "group_files";
include("util_group_menu.php");
include("util_group_profile.php");
?>

<?php
$APPLICATION->IncludeComponent(
	'thurly:disk.file.view',
	'',
	array_merge($arResult, array(
		'PATH_TO_TRASHCAN_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_TRASHCAN'], array('group_id' => $arResult['VARIABLES']['group_id'])),
		'PATH_TO_TRASHCAN_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_TRASHCAN_FILE_VIEW'], array('group_id' => $arResult['VARIABLES']['group_id'])),
		'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK'], array('group_id' => $arResult['VARIABLES']['group_id'])),
		'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_FILE'], array('group_id' => $arResult['VARIABLES']['group_id'])),
		'PATH_TO_DISK_START_BIZPROC' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_START_BIZPROC'], array('group_id' => $arResult['VARIABLES']['group_id'])),
		'PATH_TO_DISK_TASK' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_GROUP_DISK_TASK'], array('group_id' => $arResult['VARIABLES']['group_id'])),
		'PATH_TO_GROUP' => $arParams['PATH_TO_GROUP'],
		'STORAGE' => $arResult['VARIABLES']['STORAGE'],
		'FILE_ID' => $arResult['VARIABLES']['FILE_ID'],
		'RELATIVE_PATH' => $arResult['VARIABLES']['RELATIVE_PATH'],
	)),
	$component
);?>