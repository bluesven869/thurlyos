<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/company/birthdays.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
?>
<?$APPLICATION->IncludeComponent("thurly:intranet.structure.birthday.nearest", ".default", Array(
	"STRUCTURE_PAGE"	=>	"structure.php",
	"PM_URL"	=>	"/company/personal/messages/chat/#USER_ID#/",
	"PATH_TO_CONPANY_DEPARTMENT" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"PATH_TO_VIDEO_CALL" => "/company/personal/video/#USER_ID#/",
	"STRUCTURE_FILTER"	=>	"structure",
	"NUM_USERS"	=>	"25",
	"DATE_FORMAT" => "F j, Y",
	"DATE_FORMAT_NO_YEAR" => "F j",
	"DATE_TIME_FORMAT" => "F j, Y h:i a",
	"AJAX_MODE"	=>	"N",
	"AJAX_OPTION_SHADOW"	=>	"Y",
	"AJAX_OPTION_JUMP"	=>	"N",
	"AJAX_OPTION_STYLE"	=>	"Y",
	"AJAX_OPTION_HISTORY"	=>	"N",
	"SHOW_YEAR"	=>	"M",
	"USER_PROPERTY"	=>	array(
		0	=>	"PERSONAL_PHONE",
		1	=>	"UF_DEPARTMENT",
		2	=>	"UF_PHONE_INNER",
		3	=>	"UF_SKYPE",
		4	=>	"PERSONAL_PHOTO",
	),
	"SHOW_FILTER"	=>	"Y"
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>