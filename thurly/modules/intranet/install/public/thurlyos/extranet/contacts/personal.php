<?
require($_SERVER["DOCUMENT_ROOT"]."/thurly/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/intranet/public_thurlyos/extranet/contacts/personal.php");
$APPLICATION->SetTitle(GetMessage("TITLE"));
?>
<?GetGlobalID();
$componentDateTimeFormat = CIntranetUtils::getCurrentDateTimeFormat();

$APPLICATION->IncludeComponent("thurly:socialnetwork_user", ".default", array(
	"ITEM_DETAIL_COUNT" => "32",
	"ITEM_MAIN_COUNT" => "6",
	"DATE_TIME_FORMAT" => $componentDateTimeFormat,
	"NAME_TEMPLATE" => "",
	"PATH_TO_GROUP" => "/extranet/workgroups/group/#group_id#/",
	"PATH_TO_GROUP_SUBSCRIBE" => "/extranet/workgroups/group/#group_id#/subscribe/",
	"PATH_TO_GROUP_SEARCH" => "/extranet/workgroups/group/search/",
	"PATH_TO_SEARCH_EXTERNAL" => "/extranet/contacts/",
	"PATH_TO_CONPANY_DEPARTMENT" => "",
	"PATH_TO_GROUP_TASKS" => "/extranet/workgroups/group/#group_id#/tasks/",
	"PATH_TO_GROUP_TASKS_TASK" => "/extranet/workgroups/group/#group_id#/tasks/task/#action#/#task_id#/",
	"PATH_TO_GROUP_TASKS_VIEW" => "/extranet/workgroups/group/#group_id#/tasks/view/#action#/#view_id#/",
	"PATH_TO_GROUP_POST" => "/extranet/workgroups/group/#group_id#/blog/#post_id#/",
	"PATH_TO_CONPANY_DEPARTMENT" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"PATH_TO_VIDEO_CALL" => "/extranet/contacts/personal/video/#USER_ID#/",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/extranet/contacts/personal/",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "Y",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"CACHE_TIME_LONG" => "604800",
	"PATH_TO_SMILE" => "/thurly/images/socialnetwork/smile/",
	"PATH_TO_BLOG_SMILE" => "/thurly/images/blog/smile/",
	"PATH_TO_FORUM_SMILE" => "/thurly/images/forum/smile/",
	"SONET_PATH_TO_FORUM_ICON" => "/thurly/images/forum/icon/",
	"SET_TITLE" => "Y",
	"SET_NAV_CHAIN" => "Y",
	"HIDE_OWNER_IN_TITLE" => "Y",
	"USER_FIELDS_MAIN" => array(
		0 => "PERSONAL_BIRTHDAY",
		1 => "PERSONAL_GENDER",
		2 => "WORK_POSITION",
		3 => "WORK_COMPANY",
		4 => "SECOND_NAME",
	),
	"USER_PROPERTY_MAIN" => array(
		0 => "UF_DEPARTMENT",
	),
	"USER_FIELDS_CONTACT" => array(
		0 => "EMAIL",
		1 => "PERSONAL_WWW",
		2 => "PERSONAL_MOBILE",
		3 => "WORK_PHONE",
	),
	"USER_PROPERTY_CONTACT" => array(
		0 => "UF_PHONE_INNER",
		1 => "UF_SKYPE",
		2 => "UF_TWITTER",
		3 => "UF_FACEBOOK",
		4 => "UF_LINKEDIN",
		5 => "UF_XING",
	),
	"USER_FIELDS_PERSONAL" => array(
		0 => "TIME_ZONE",
	),
	"USER_PROPERTY_PERSONAL" => array(
		0 => "UF_SKILLS",
		1 => "UF_INTERESTS",
		2 => "UF_WEB_SITES",
	),
	"AJAX_LONG_TIMEOUT" => "60",
	"EDITABLE_FIELDS" => array(
		0 => "LOGIN",
		1 => "NAME",
		2 => "SECOND_NAME",
		3 => "LAST_NAME",
		4 => "EMAIL",
		5 => "PASSWORD",
		6 => "PERSONAL_BIRTHDAY",
		7 => "PERSONAL_WWW",
		8 => "PERSONAL_GENDER",
		9 => "PERSONAL_PHOTO",
		11 => "PERSONAL_MOBILE",
		13 => "PERSONAL_CITY",
		14 => "WORK_PHONE",
		15 => "UF_PHONE_INNER",
		16 => "UF_SKYPE",
		17 => "UF_TWITTER",
		18 => "UF_FACEBOOK",
		19 => "UF_LINKEDIN",
		20 => "UF_XING",
		21 => "UF_SKILLS",
		22 => "UF_INTERESTS",
		23 => "UF_WEB_SITES",
		24 => "TIME_ZONE",
		25 => "WORK_COMPANY",
		26 => "WORK_POSITION"
	),
	"SHOW_YEAR" => "M",
	"USER_FIELDS_SEARCH_SIMPLE" => array(
		0 => "WORK_COMPANY",
	),
	"USER_PROPERTIES_SEARCH_SIMPLE" => array(
	),
	"USER_FIELDS_SEARCH_ADV" => array(
		0 => "PERSONAL_CITY",
		1 => "WORK_COMPANY",
	),
	"USER_PROPERTIES_SEARCH_ADV" => array(
	),
	"SONET_USER_FIELDS_LIST" => array(
		0 => "PERSONAL_BIRTHDAY",
		1 => "PERSONAL_GENDER",
		2 => "PERSONAL_CITY",
	),
	"SONET_USER_PROPERTY_LIST" => array(
	),
	"SONET_USER_FIELDS_SEARCHABLE" => array(
	),
	"SONET_USER_PROPERTY_SEARCHABLE" => array(
	),
	"BLOG_GROUP_ID" => $GLOBAL_BLOG_GROUP[SITE_ID],
	"FORUM_ID" => $GLOBAL_FORUM_ID["USERS_AND_GROUPS"],//"#FORUM_ID#",
	"CALENDAR_ALLOW_SUPERPOSE" => "Y",
	"CALENDAR_ALLOW_RES_MEETING" => "Y",
	// "CALENDAR_IBLOCK_TYPE" => "events",
	// "CALENDAR_WEEK_HOLIDAYS" => array(
		// 0 => "5",
		// 1 => "6",
	// ),
	// "CALENDAR_YEAR_HOLIDAYS" => "1.01, 2.01, 7.01, 23.02, 8.03, 1.05, 9.05, 12.06, 4.11, 12.12",
	// "CALENDAR_WORK_TIME_START" => "9",
	// "CALENDAR_WORK_TIME_END" => "19",
	// "CALENDAR_ALLOW_SUPERPOSE" => "Y",
	// "CALENDAR_SUPERPOSE_CAL_IDS" => array(),
	// "CALENDAR_SUPERPOSE_CUR_USER_CALS" => "Y",
	// "CALENDAR_SUPERPOSE_USERS_CALS" => "Y",
	// "CALENDAR_SUPERPOSE_GROUPS_CALS" => "Y",
	// "CALENDAR_SUPERPOSE_GROUPS_IBLOCK_ID" => $GLOBAL_IBLOCK_ID["calendar_groups_extranet"],// "#CALENDAR_GROUPS_IBLOCK_ID#",
	// "CALENDAR_ALLOW_RES_MEETING" => "N",
	// "CALENDAR_ALLOW_VIDEO_MEETING" => "N",
	"TASK_FORUM_ID" => $GLOBAL_FORUM_ID["GROUPS_AND_USERS_TASKS_COMMENTS_EXTRANET"],//"#TASKS_FORUM_ID#",
	"FILES_USE_AUTH" => "Y",
	"FILE_NAME_FILE_PROPERTY" => "FILE",
	"FILES_UPLOAD_MAX_FILESIZE" => "1024",
	"FILES_UPLOAD_MAX_FILE" => "4",
	"FILES_USE_COMMENTS" => "Y",
	"FILES_FORUM_ID" => $GLOBAL_FORUM_ID["GROUPS_AND_USERS_FILES_COMMENTS"],//"#FILES_FORUM_ID#",
	"FILES_USE_CAPTCHA" => "Y",
	"FILES_USER_IBLOCK_ID" => $GLOBAL_IBLOCK_ID["user_files"],
	"PHOTO_UPLOAD_MAX_FILESIZE" => "64",
	"PHOTO_UPLOAD_MAX_FILE" => "4",
	"PHOTO_ALBUM_PHOTO_THUMBS_SIZE" => "100",
	"PHOTO_ALBUM_PHOTO_SIZE" => "100",
	"PHOTO_THUMBS_SIZE" => "120",
	"PHOTO_PREVIEW_SIZE" => "300",
	"PHOTO_WATERMARK_MIN_PICTURE_SIZE" => "200",
	"PHOTO_PATH_TO_FONT" => "",
	"PHOTO_USE_RATING" => "Y",
	"PHOTO_USE_COMMENTS" => "Y",
	"PHOTO_UPLOADER_TYPE" => "form",
	"PHOTO_FORUM_ID" => $GLOBAL_FORUM_ID["GROUPS_AND_USERS_PHOTOGALLERY_COMMENTS"],//"#PHOTOGALLERY_FORUM_ID#",
	"PHOTO_USE_CAPTCHA" => "N",
	"AJAX_OPTION_ADDITIONAL" => "",
	"SEF_URL_TEMPLATES" => array(
		"index" => "index.php",
		"user" => "user/#user_id#/",
		"user_friends" => "user/#user_id#/friends/",
		"user_friends_add" => "user/#user_id#/friends/add/",
		"user_friends_delete" => "user/#user_id#/friends/delete/",
		"user_groups" => "user/#user_id#/groups/",
		"user_groups_add" => "user/#user_id#/groups/add/",
		"group_create" => "/extranet/workgroups/create/",
		"user_profile_edit" => "user/#user_id#/edit/",
		"user_settings_edit" => "user/#user_id#/settings/",
		"user_features" => "user/#user_id#/features/",
		"group_request_group_search" => "group/#user_id#/group_search/",
		"group_request_user" => "group/#group_id#/user/#user_id#/request/",
		"search" => "search.php",
		"message_form" => "messages/form/#user_id#/",
		"message_form_mess" => "messages/form/#user_id#/#message_id#/",
		"user_ban" => "messages/ban/",
		"messages_chat" => "messages/chat/#user_id#/",
		"messages_input" => "messages/input/",
		"messages_input_user" => "messages/input/#user_id#/",
		"messages_output" => "messages/output/",
		"messages_output_user" => "messages/output/#user_id#/",
		"messages_users" => "messages/",
		"messages_users_messages" => "messages/#user_id#/",
		"log" => "log/",
		"subscribe" => "subscribe/",
		"user_subscribe" => "user/#user_id#/subscribe/",
		"user_blog" => "user/#user_id#/blog/",
		"user_blog_post_edit" => "user/#user_id#/blog/edit/#post_id#/",
		"user_blog_rss" => "user/#user_id#/blog/rss/#type#/",
		"user_blog_draft" => "user/#user_id#/blog/draft/",
		"user_blog_post" => "user/#user_id#/blog/#post_id#/",
		"user_tasks" => "user/#user_id#/tasks/",
		"user_tasks_task" => "user/#user_id#/tasks/task/#action#/#task_id#/",
		"user_tasks_view" => "user/#user_id#/tasks/view/#action#/#view_id#/",
/* modified by wladart */
		"group_files" => "group/#group_id#/files/lib/#path#/",
/* --modified by wladart */
	),
	"GROUP_THUMBNAIL_SIZE" => 100,
	"LOG_THUMBNAIL_SIZE" => 100,
	"LOG_COMMENT_THUMBNAIL_SIZE" => 100,
	"LOG_NEW_TEMPLATE" => "Y"
	),
	false
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/thurly/footer.php");?>