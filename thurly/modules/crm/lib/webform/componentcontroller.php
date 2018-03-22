<?php
/**
 * Thurly Framework
 * @package thurly
 * @subpackage crm
 * @copyright 2001-2016 Thurly
 */
namespace Thurly\Crm\WebForm;

use Thurly\Main\Context;
use Thurly\Main\HttpRequest;
use Thurly\Main\Web\Json;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

abstract class ComponentController
{
	protected $errors = array();
	protected $action = null;
	protected $responseData = array();
	protected $requestData = array();

	/** @var HttpRequest $request */
	protected $request = array();

	abstract protected function getActions();
	abstract protected function checkPermissions();

	protected function prepareRequestData()
	{

	}

	protected function giveResponse()
	{
		global $APPLICATION;
		$APPLICATION->restartBuffer();

		header('Content-Type:application/json; charset=UTF-8');
		echo Json::encode(
			$this->responseData + array(
				'error' => $this->hasErrors(),
				'text' => implode('<br>', $this->errors),
			)
		);

		\CMain::finalActions();
		exit;
	}

	protected function getActionCall()
	{
		return array($this, $this->action);
	}

	protected function hasErrors()
	{
		return count($this->errors) > 0;
	}

	protected function check()
	{
		if(!$this->checkPermissions())
		{
			$this->errors[] = Loc::getMessage('CRM_PERMISSION_DENIED');
		}
		if(!in_array($this->action, $this->getActions()))
		{
			$this->errors[] = 'Action "' . $this->action . '" not found.';
		}
		elseif(!check_thurly_sessid() || !$this->request->isPost())
		{
			$this->errors[] = 'Security error.';
		}
		elseif(!is_callable($this->getActionCall()))
		{
			$this->errors[] = 'Action method "' . $this->action . '" not found.';
		}

		return !$this->hasErrors();
	}

	public function exec()
	{
		$this->request = Context::getCurrent()->getRequest();
		$this->action = $this->request->get('action');

		$this->prepareRequestData();

		if($this->check())
		{
			call_user_func_array($this->getActionCall(), array($this->requestData));
		}
		$this->giveResponse();
	}
}