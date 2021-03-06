<?php

define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']) ? $_REQUEST['SITE_ID'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

use Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$request = Thurly\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Thurly\Main\Web\PostDecodeFilter);
if (!check_thurly_sessid())
{
	die();
}
$signer = new \Thurly\Main\Security\Sign\Signer;
try
{
	$params = $signer->unsign(base64_decode($request->get('signedParamsString')), 'sale.account.pay');
	$params = unserialize($params);
	$params['AJAX_DISPLAY'] = "Y";
}
catch (\Thurly\Main\Security\Sign\BadSignatureException $e)
{
	die();
}

CThurlyComponent::includeComponentClass("thurly:sale.account.pay");

$orderPayment = new SaleAccountPay();
$orderPayment->initComponent('thurly:sale.account.pay');
$orderPayment->includeComponent($params["TEMPLATE_PATH"], $params, null);

require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/epilog_after.php');
?>