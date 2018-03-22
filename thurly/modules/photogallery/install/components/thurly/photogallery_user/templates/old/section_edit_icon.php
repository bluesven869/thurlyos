<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
	"thurly:photogallery.section.edit.icon",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"BEHAVIOUR" => "USER",
		"USER_ALIAS" => $arResult["MENU_VARIABLES"]["USER_ALIAS"],
		"PERMISSION" => $arResult["MENU_VARIABLES"]["PERMISSION"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		
 		"ELEMENT_SORT_FIELD" => "ID", 
 		"ELEMENT_SORT_ORDER" => "ASC", 
 		
		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["section_top"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		
		"ALBUM_PHOTO_THUMBS_WIDTH"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_WIDTH"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		
		"SET_TITLE" => $arParams["SET_TITLE"], 
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"], 
		
		"THUMBS_SIZE"	=>	80 
	),
	$component
);
?>