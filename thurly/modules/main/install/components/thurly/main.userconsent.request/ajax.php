<?php
define('STOP_STATISTICS', true);
define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
require_once($_SERVER['DOCUMENT_ROOT'].'/thurly/modules/main/include/prolog_before.php');

use Thurly\Main\Localization\Loc;
use Thurly\Main\UserConsent\Consent;
use Thurly\Main\UserConsent\Agreement;
use Thurly\Main\Text\Encoding;

Loc::loadMessages(__FILE__);

class MainUserConsentRequestAjaxController extends \Thurly\Main\UserConsent\Internals\ComponentController
{
	protected function getActions()
	{
		return array(
			'getText',
			'saveConsent',
		);
	}

	protected function checkPermissions()
	{
		return true;
	}

	protected function getAgreement()
	{
		$id = $this->request->get('id');
		$securityCode = $this->request->get('sec');
		if(!$id)
		{
			$this->errors[] = '';
			return null;
		}

		$agreement = new Agreement($id);
		if (!$agreement->isExist() || !$agreement->isActive())
		{
			$this->errors[] = '';
			return null;
		}

		$agreementData = $agreement->getData();
		if($agreementData['SECURITY_CODE'] && $securityCode != $agreementData['SECURITY_CODE'])
		{
			$this->errors[] = '';
			return null;
		}

		return $agreement;
	}

	protected function getText()
	{
		$agreement = $this->getAgreement();
		if (!$agreement)
		{
			return;
		}

		$replace = $this->request->get('replace');
		$replace = is_array($replace) ? $replace : array();
		$replace = Encoding::convertEncoding($replace, 'UTF-8', SITE_CHARSET);
		$agreement->setReplace($replace);

		$this->responseData['text'] = $agreement->getText();
	}

	protected function saveConsent()
	{
		$agreement = $this->getAgreement();
		if (!$agreement)
		{
			return;
		}

		$originatorId = $this->request->get('originatorId');
		$originatorId = $originatorId ? $originatorId : null;

		$originId = $this->request->get('originId');
		$originId = ($originatorId && $originId) ? $originId : null;

		$data = array('URL' => $this->request->get('url'));
		Consent::addByContext($agreement->getId(), $originatorId, $originId, $data);
	}
}

$controller = new MainUserConsentRequestAjaxController();
$controller->exec();