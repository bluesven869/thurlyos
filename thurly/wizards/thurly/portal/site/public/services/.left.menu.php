<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public/services/.left.menu.php");

$aMenuLinks = Array(
	Array(
		GetMessage("SERVICES_MENU_MEETING_ROOM"),
		"#SITE_DIR#services/index.php",
		Array("#SITE_DIR#services/res_c.php"),
		Array(),
		"CBXFeatures::IsFeatureEnabled('MeetingRoomBookingSystem')"
	),
	Array(
		GetMessage("SERVICES_MENU_IDEA"),
		"#SITE_DIR#services/idea/",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Idea')"
	),
	Array(
		GetMessage("SERVICES_MENU_LISTS"),
		"#SITE_DIR#services/lists/",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Lists')"
	),
	Array(
		GetMessage("SERVICES_MENU_REQUESTS"),
		"#SITE_DIR#services/requests/",
		Array(),
		Array(),
		(!IsModuleInstalled("form"))?"false":"CBXFeatures::IsFeatureEnabled('Requests')"
	),
	Array(
		GetMessage("SERVICES_MENU_LEARNING"),
		"#SITE_DIR#services/learning/",
		Array("/services/course.php"),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Learning')"
	),
	Array(
		GetMessage("SERVICES_MENU_WIKI"),
		"#SITE_DIR#services/wiki/",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Wiki')"
	),
	Array(
		GetMessage("SERVICES_MENU_FAQ"),
		"#SITE_DIR#services/faq/",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage("SERVICES_MENU_VOTE"),
		"#SITE_DIR#services/votes.php",
		Array("#SITE_DIR#services/vote_new.php", "#SITE_DIR#services/vote_result.php"),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Vote')"
	),
	Array(
		GetMessage("SERVICES_MENU_SUPPORT"),
		"#SITE_DIR#services/support.php?show_wizard=Y",
		Array("#SITE_DIR#services/support.php"),
		Array(),
		(!IsModuleInstalled("support"))?"false":"CBXFeatures::IsFeatureEnabled('Support')"
	),
	Array(
		GetMessage("SERVICES_MENU_LINKS"),
		"#SITE_DIR#services/links.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('WebLink')"
	),
	Array(
		GetMessage("SERVICES_MENU_SUBSCR"),
		"#SITE_DIR#services/subscr_edit.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Subscribe')"
	),
	Array(
		GetMessage("SERVICES_MENU_EVENTLIST"),
		"#SITE_DIR#services/event_list.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('EventList')"
	),
	Array(
		GetMessage("SERVICES_MENU_SALARY"),
		"#SITE_DIR#services/salary/",
		Array(),
		Array(),
		"LANGUAGE_ID == 'ru' && CBXFeatures::IsFeatureEnabled('Salary')"
	),
	Array(
		GetMessage("SERVICES_MENU_BOARD"),
		"#SITE_DIR#services/board/",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Board')"
	),
	Array(
		GetMessage("SERVICES_MENU_TELEPHONY"),
		"#SITE_DIR#services/telephony/",
		Array(),
		Array(),
		'CModule::IncludeModule("voximplant") && SITE_TEMPLATE_ID !== "thurlyos" && Thurly\Voximplant\Security\Helper::isMainMenuEnabled()'
	),
	Array(
		GetMessage("SERVICES_MENU_OPENLINES"),
		"#SITE_DIR#services/openlines/",
		Array(),
		Array(),
		'CModule::IncludeModule("imopenlines") && SITE_TEMPLATE_ID !== "thurlyos" && Thurly\ImOpenlines\Security\Helper::isMainMenuEnabled()'
	),
);
?>