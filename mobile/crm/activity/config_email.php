<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/thurly/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'thurly:mobile.crm.config.user_email',
	'',
	array(
		'UID' => 'mobile_crm_config_user_email',
		'SERVICE_URL_TEMPLATE' => '/mobile/ajax.php?mobile_action=crm_config_user_email&site_id=#SITE#&sessid=#SID#',
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/thurly/footer.php');
