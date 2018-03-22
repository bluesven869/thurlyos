<?php
namespace Thurly\MessageService\Sender\Sms;

use Thurly\Main\Application;
use Thurly\Main\Config\Option;
use Thurly\Main\Error;
use Thurly\Main\Localization\Loc;
use Thurly\Main\Result;
use Thurly\Main\Web\HttpClient;
use Thurly\Main\Web\Json;
use Thurly\Main\Loader;

use Thurly\MessageService\Sender;
use Thurly\MessageService\Sender\Result\MessageStatus;
use Thurly\MessageService\Sender\Result\SendMessage;

use Thurly\MessageService;

Loc::loadMessages(__FILE__);

class SmsRu extends Sender\BaseConfigurable
{
	public static function isSupported()
	{
		if (Loader::includeModule('thurlyos'))
		{
			$zone = \CThurlyOS::getPortalZone();
		}
		else
		{
			$zone = Application::getInstance()->getContext()->getLanguage();
		}

		return in_array($zone, array('ru', 'kz', 'by'));
	}

	public function getId()
	{
		return 'smsru';
	}

	public function getName()
	{
		return Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSRU_NAME');
	}

	public function getShortName()
	{
		return 'sms.ru';
	}

	public function getDemoBalance()
	{
		$params = array(
			'embed_id' => $this->getOption('embed_id')
		);
		$apiResult = $this->callExternalMethod('my/free', $params);

		$balance = array(
			'total_free' => 0,
			'used_today' => 0,
			'available_today' => 0
		);

		if ($apiResult->isSuccess())
		{
			$balanceData = $apiResult->getData();
			$balance['total_free'] = (int)$balanceData['total_free'];
			$balance['used_today'] = (int)$balanceData['used_today'];
			$balance['available_today'] = max(0, $balance['total_free'] - $balance['used_today']);
		}

		return $balance;
	}

	public function getFromList()
	{
		$from = $this->getOption('from_list');
		return is_array($from) ? $from : array();
	}

	public function getDefaultFrom()
	{
		$fromList = $this->getFromList();
		$from = $fromList[0]['id'];
		//Try to find alphanumeric from
		foreach ($fromList as $item)
		{
			if (!preg_match('#^[0-9]+$#', $item['id']))
			{
				$from = $item['id'];
				break;
			}
		}
		return $from;
	}

	public function setDefaultFrom($from)
	{
		//$from = (string)$from;
		//$this->setOption('default_from', $from);
		return $this;
	}

	public function isConfirmed()
	{
		return ($this->getOption('is_confirmed') === true);
	}

	public function isRegistered()
	{
		return ($this->getOption('embed_id') !== null);
	}

	public function register(array $fields)
	{
		$userPhone = \NormalizePhone($fields['user_phone']);
		$params = array(
			'user_phone' => $userPhone,
			'user_firstname' => $fields['user_firstname'],
			'user_lastname' => $fields['user_lastname'],
			'user_email' => $fields['user_email'],
			'embed_partner' => $this->getEmbedPartner(),
			'embed_hash' => $this->getEmbedHash($userPhone)
		);

		$result = $this->callExternalMethod('embed/register', $params);
		if ($result->isSuccess())
		{
			$data = $result->getData();

			$this->setOption('embed_id', $data['embed_id']);
			$this->setOption('user_phone', $userPhone);
			if (!empty($params['user_firstname']))
			{
				$this->setOption('user_firstname', $params['user_firstname']);
			}
			if (!empty($params['user_lastname']))
			{
				$this->setOption('user_lastname', $params['user_lastname']);
			}
			if (!empty($params['user_email']))
			{
				$this->setOption('user_email', $params['user_email']);
			}

			if (!empty($data['confirmed']))
			{
				$this->setOption('is_confirmed', true);
			}
		}

		return $result;
	}

	/**
	 * @return array [
	 * 	'phone' => '',
	 *  'firstName' => '',
	 *  'lastName' => '',
	 *  'email' => ''
	 * ]
	 */
	public function getOwnerInfo()
	{
		return array(
			'phone' => $this->getOption('user_phone'),
			'firstName' => $this->getOption('user_firstname'),
			'lastName' => $this->getOption('user_lastname'),
			'email' => $this->getOption('user_email')
		);
	}

	/**
	 * @param array $fields
	 * @return Result
	 */
	public function confirmRegistration(array $fields)
	{
		$embedId = $this->getOption('embed_id');
		$params = array(
			'embed_id' => $embedId,
			'confirm' => $fields['confirm']
		);
		$result = $this->callExternalMethod('embed/confirm', $params);

		if ($result->isSuccess())
		{
			$this->setOption('is_confirmed', true);
			$callBackResult = $this->callExternalMethod('callback/add', array(
				'embed_id' => $embedId,
				'url' => $this->getCallbackUrl()
			));
			if ($callBackResult->isSuccess())
			{
				$this->setOption('callback_set', true);
			}
		}

		return $result;
	}

	public function sendConfirmationCode()
	{
		if ($this->isRegistered())
		{
			$ownerInfo = $this->getOwnerInfo();
			$result = $this->register(array(
				'user_phone' => $ownerInfo['phone'],
				'user_firstname' => $ownerInfo['firstName'],
				'user_lastname' => $ownerInfo['lastName'],
				'user_email' => $ownerInfo['email'],
			));
		}
		else
		{
			$result = new Result();
			$result->addError(new Error('Provider is not registered.'));
		}

		return $result;
	}

	public function getExternalManageUrl()
	{
		if ($this->isRegistered())
		{
			return 'https://sms.ru/?panel=login&action=login&embed_id='.$this->getOption('embed_id');
		}
		return 'https://sms.ru/?panel=login';
	}

	public function sendMessage(array $messageFields)
	{
		if (!$this->canUse())
		{
			$result = new SendMessage();
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSRU_CAN_USE_ERROR')));
			return $result;
		}

		$params = array(
			'to' => $messageFields['MESSAGE_TO'],
			'text' => $messageFields['MESSAGE_BODY'],
			'embed_id' => $this->getOption('embed_id')
		);

		if ($this->isDemo())
		{
			$params['to'] = $this->getOption('user_phone');
		}

		if ($messageFields['MESSAGE_FROM'])
		{
			$params['from'] = $messageFields['MESSAGE_FROM'];
		}

		$result = new SendMessage();
		$apiResult = $this->callExternalMethod('sms/send', $params);
		$resultData = $apiResult->getData();

		if (!$apiResult->isSuccess())
		{
			if ((int)$resultData['status_code'] == 206)
			{
				$result->setStatus(MessageService\MessageStatus::DEFERRED);
				$result->addError(new Error($this->getErrorMessage($resultData['status_code'])));
			}
			else
			{
				$result->addErrors($apiResult->getErrors());
			}
		}
		else
		{
			$smsData = current($resultData['sms']);

			if (isset($smsData['sms_id']))
			{
				$result->setExternalId($smsData['sms_id']);
			}

			if ((int)$smsData['status_code'] !== 100)
			{
				$result->addError(new Error($this->getErrorMessage($smsData['status_code'])));
			}
			elseif ((int)$smsData['status_code'] == 206)
			{
				$result->setStatus(MessageService\MessageStatus::DEFERRED);
				$result->addError(new Error($this->getErrorMessage($smsData['status_code'])));
			}
			else
			{
				$result->setAccepted();
			}
		}

		return $result;
	}

	public function getMessageStatus(array $messageFields)
	{
		$result = new MessageStatus();
		$result->setId($messageFields['ID']);
		$result->setExternalId($messageFields['EXTERNAL_ID']);

		if (!$this->canUse())
		{
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSRU_CAN_USE_ERROR')));
			return $result;
		}

		$params = array(
			'sms_id' => $result->getExternalId(),
			'embed_id' => $this->getOption('embed_id')
		);

		$apiResult = $this->callExternalMethod('sms/status', $params);
		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$resultData = $apiResult->getData();
			$smsData = current($resultData['sms']);

			$result->setStatusCode($smsData['status_code']);
			$result->setStatusText($smsData['status_text']);

			if ((int)$resultData['status_code'] !== 100)
			{
				$result->addError(new Error($this->getErrorMessage($smsData['status_code'])));
			}
		}

		return $result;
	}

	public static function resolveStatus($serviceStatus)
	{
		$status = parent::resolveStatus($serviceStatus);

		switch ((int)$serviceStatus)
		{
			case 100:
				return MessageService\MessageStatus::ACCEPTED;
				break;
			case 101:
				return MessageService\MessageStatus::SENDING;
				break;
			case 102:
				return MessageService\MessageStatus::SENT;
				break;
			case 103:
				return MessageService\MessageStatus::DELIVERED;
				break;
			case 104: //timeout
			case 105: //removed by moderator
			case 106: //error on receiver`s side
			case 107: //unknown reason
			case 108: //rejected
				return MessageService\MessageStatus::UNDELIVERED;
				break;
			case 110:
				return MessageService\MessageStatus::READ;
				break;
		}

		return $status;
	}

	public function sync()
	{
		if ($this->isRegistered())
		{
			$this->loadFromList();
		}
		return $this;
	}

	private function callExternalMethod($method, $params)
	{
		$url = 'https://sms.ru/'.$method;

		$httpClient = new HttpClient(array(
			"socketTimeout" => 10,
			"streamTimeout" => 30,
			"waitResponse" => true,
		));
		$httpClient->setHeader('User-Agent', 'ThurlyOS');
		$httpClient->setCharset('UTF-8');

		$isUtf = Application::getInstance()->isUtfMode();

		if (!$isUtf)
		{
			$params = \Thurly\Main\Text\Encoding::convertEncoding($params, SITE_CHARSET, 'UTF-8');
		}
		$params['json'] = 1;

		$result = new Result();
		$answer = array();

		if ($httpClient->query(HttpClient::HTTP_POST, $url, $params) && $httpClient->getStatus() == '200')
		{
			$answer = $this->parseExternalAnswer($httpClient->getResult());
		}

		$answerCode = isset($answer['status_code']) ? (int)$answer['status_code'] : 0;

		if ($answerCode !== 100)
		{
			$result->addError(new Error($this->getErrorMessage($answerCode, $answer)));
		}
		$result->setData($answer);

		return $result;
	}

	private function parseExternalAnswer($httpResult)
	{
		try
		{
			$answer = Json::decode($httpResult);
		}
		catch (\Thurly\Main\ArgumentException $e)
		{
			$data = explode(PHP_EOL, $httpResult);
			$code = (int)array_shift($data);
			$answer = $data;
			$answer['status_code'] = $code;
			$answer['status'] = $code === 100 ? 'OK' : 'ERROR';
		}

		if (!is_array($answer) && is_numeric($answer))
		{
			$answer = array(
				'status' => $answer === 100 ? 'OK' : 'ERROR',
				'status_code' => $answer
			);
		}

		return $answer;
	}

	private function getEmbedPartner()
	{
		return Option::get('messageservice', 'smsru_partner');
	}

	private function getSecretKey()
	{
		return Option::get('messageservice', 'smsru_secret_key');
	}

	private function getEmbedHash($phoneNumber)
	{
		return md5($phoneNumber.$this->getSecretKey());
	}

	private function getErrorMessage($errorCode, $answer = null)
	{
		$message = Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSRU_ERROR_'.$errorCode);
		if (!$message && $answer && !empty($answer['errors']))
		{
			$errorCode = $answer['errors'][0]['status_code'];
			$message = Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSRU_ERROR_'.$errorCode);
			if (!$message)
			{
				$message = $answer['errors'][0]['status_text'];
			}
		}

		return $message ?: Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSRU_ERROR_OTHER');
	}

	private function loadFromList()
	{
		$params = array(
			'embed_id' => $this->getOption('embed_id')
		);
		$result = $this->callExternalMethod('my/senders', $params);

		if ($result->isSuccess())
		{
			$from = array();
			$resultData = $result->getData();
			foreach ($resultData['senders'] as $sender)
			{
				if (!empty($sender))
				{
					$from[] = array(
						'id' => $sender,
						'name' => $sender
					);
				}
			}

			$this->setOption('from_list', $from);
		}
	}
}