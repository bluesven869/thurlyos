<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");

$APPLICATION->SetTitle("Site Map");

$APPLICATION->IncludeComponent("thurly:main.map", ".default", Array(
	"LEVEL"	=>	"3",
	"COL_NUM"	=>	"2",
	"SHOW_DESCRIPTION"	=>	"Y",
	"SET_TITLE"	=>	"Y",
	"CACHE_TIME"	=>	"3600"
	)
);

require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>