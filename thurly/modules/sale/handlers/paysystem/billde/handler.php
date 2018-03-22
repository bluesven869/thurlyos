<?php

namespace Sale\Handlers\PaySystem;

use Thurly\Main\Localization\Loc;
use Thurly\Main\Request;
use Thurly\Sale;
use Thurly\Main\Loader;
use Thurly\Sale\PaySystem;

Loader::registerAutoLoadClasses('sale', array(PaySystem\Manager::getClassNameFromPath('Bill') => 'handlers/paysystem/bill/handler.php'));
Loc::loadMessages(__FILE__);

class BillDeHandler extends BillHandler
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
		$data['BILLDE_COMMENT1'] = Loc::getMessage('SALE_HPS_BILL_DE_COMMENT');
		$data['BILLDE_COMMENT2'] = Loc::getMessage('SALE_HPS_BILL_DE_COMMENT_ADD');

		return $data;
	}
}