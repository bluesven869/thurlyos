<?php

namespace Sale\Handlers\PaySystem;

use Thurly\Main\Localization\Loc;
use Thurly\Main\Request;
use Thurly\Sale;
use Thurly\Sale\PaySystem;
use Thurly\Main\Loader;

Loader::registerAutoLoadClasses('sale', array(PaySystem\Manager::getClassNameFromPath('Bill') => 'handlers/paysystem/bill/handler.php'));
Loc::loadMessages(__FILE__);

class BillBrHandler extends BillHandler
{
	/**
	 * @param Sale\Payment $payment
	 * @param Request|null $request
	 * @return array
	 */
	protected function getPreparedParams(Sale\Payment $payment, Request $request = null)
	{
		$params = parent::getPreparedParams($payment, $request);
		$params['DATE_BILL'] = $payment->getField('DATE_BILL');

		return $params;
	}

	/**
	 * @return array
	 */
	public function getDemoParams()
	{
		$data = parent::getDemoParams();
		$data['BILLBR_COMMENT1'] = Loc::getMessage('SALE_HPS_BILL_BR_COMMENT');
		$data['BILLBR_COMMENT2'] = Loc::getMessage('SALE_HPS_BILL_BR_COMMENT_ADD');

		return $data;
	}

}