<?php

namespace Sale\Handlers\DiscountPreset;


use Thurly\Iblock\SectionTable;
use Thurly\Main;
use Thurly\Main\Error;
use Thurly\Main\Localization\Loc;
use Thurly\Sale\Basket;
use Thurly\Sale\Discount\Preset\ArrayHelper;
use Thurly\Sale\Discount\Preset\BasePreset;
use Thurly\Sale\Discount\Preset\HtmlHelper;
use Thurly\Sale\Discount\Preset\Manager;
use Thurly\Sale\Discount\Preset\State;
use Thurly\Sale\Helpers\Admin\OrderEdit;
use Thurly\Sale\Internals;
use Thurly\Sale\Helpers\Admin\Blocks;
use Thurly\Sale\Order;


Loc::loadMessages(__FILE__);

class PaySystem extends BasePreset
{
	public function getTitle()
	{
		return Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_PAYSYSTEM_NAME');
	}

	public function getDescription()
	{
		return '';
	}

	/**
	 * @return int
	 */
	public function getCategory()
	{
		return Manager::CATEGORY_PAYMENT;
	}

	public function getFirstStepName()
	{
		return 'InputName';
	}

	public function processShowInputName(State $state)
	{
		return $this->processShowInputNameInternal($state);
	}

	public function processSaveInputName(State $state)
	{
		return $this->processSaveInputNameInternal($state, 'InputAmount');
	}

	protected function getPaymentSystems()
	{
		$dbRes = Internals\PaySystemActionTable::getList(array(
			'select' => array(
				'ID',
				'NAME',
				'SORT',
				'DESCRIPTION',
				'ACTIVE',
				'ACTION_FILE',
				'LOGOTIP',
			)
		));

		$result = array();
		while($paySystem = $dbRes->fetch())
		{
			$logoFileArray = \CFile::GetFileArray($paySystem['LOGOTIP']);
			$paySystem['LOGOTIP'] = \CFile::ShowImage($logoFileArray, 100, 100, "border=0", "", false);

			$result[$paySystem['ID']] = $paySystem;
		}

		return $result;
	}

	public function processShowInputAmount(State $state)
	{
		$lid = $state->get('discount_lid');
		$currency = \CSaleLang::getLangCurrency($lid);
		$paymentSystems = $this->getPaymentSystems();

		$forSelectData = array();
		foreach($paymentSystems as $id => $paymentSystem)
		{
			$forSelectData[$id] = $paymentSystem['NAME'];
		}
		Main\Type\Collection::sortByColumn($forSelectData, 'NAME', '', null, true);

		return '
			<table width="100%" border="0" cellspacing="7" cellpadding="0">
				<tbody>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . $this->getLabelDiscountValue() . ':</strong></td>
					<td class="adm-detail-content-cell-r" style="width:60%;">
						<input type="text" name="discount_value" value="' . htmlspecialcharsbx($state->get('discount_value')) . '" maxlength="100" style="width: 100px;"> <span>' . $currency . '</span>
					</td>
				</tr>
				<tr>
					<td class="adm-detail-content-cell-l" style="width:40%;"><strong>' . Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_PAYSYSTEM_PAYMENT_LABEL') . ':</strong></td>
					<td class="adm-detail-content-cell-r">
						' . HtmlHelper::generateSelect('discount_payment', $forSelectData, $state->get('discount_payment')) . '
					</td>
				</tr>
				</tbody>
			</table>
		';
	}

	public function processSaveInputAmount(State $state)
	{
		if(!trim($state->get('discount_value')))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_VALUE'));
		}

		if(!$state->get('discount_payment'))
		{
			$this->errorCollection[] = new Error(Loc::getMessage('SALE_HANDLERS_DISCOUNTPRESET_ERROR_EMPTY_PAYMENT'));
		}

		if(!$this->errorCollection->isEmpty())
		{
			return array($state, 'InputAmount');
		}

		return array($state, 'CommonSettings');
	}

	public function processShowCommonSettings(State $state)
	{
		return $this->processShowCommonSettingsInternal($state);
	}

	public function processSaveCommonSettings(State $state)
	{
		return $this->processSaveCommonSettingsInternal($state);
	}

	public function generateState(array $discountFields)
	{
		$discountFields = $this->normalizeDiscountFields($discountFields);

		$stateFields = array(
			'discount_lid' => $discountFields['LID'],
			'discount_name' => $discountFields['NAME'],
			'discount_groups' => $this->getUserGroupsByDiscount($discountFields['ID']),
			'discount_value' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.Value'),
			'discount_type' => ArrayHelper::getByPath($discountFields, 'ACTIONS.CHILDREN.0.DATA.Unit'),
			'discount_payment' => ArrayHelper::getByPath($discountFields, 'CONDITIONS.CHILDREN.0.DATA.value.0'),
		);

		return parent::generateState($discountFields)->append($stateFields);
	}

	public function generateDiscount(State $state)
	{
		return array_merge(parent::generateDiscount($state), array(
			'CONDITIONS' => array(
				'CLASS_ID' => 'CondGroup',
				'DATA' => array(
					'All' => 'AND',
					'True' => 'True',
				),
				'CHILDREN' => array(
					array(
						'CLASS_ID' => 'CondSalePaySystem',
						'DATA' => array(
							'logic' => 'Equal',
							'value' => array($state->get('discount_payment')),
						),
					),
				),
			),
			'ACTIONS' => array(
				'CLASS_ID' => 'CondGroup',
				'DATA' => array(
					'All' => 'AND',
				),
				'CHILDREN' => array(
					array(
						'CLASS_ID' => 'ActSaleBsktGrp',
						'DATA' => array(
							'Type' => $this->getTypeOfDiscount(),
							'Value' => $state->get('discount_value'),
							'Unit' => $state->get('discount_type', 'CurAll'),
							'Max' => 0,
							'All' => 'AND',
							'True' => 'True',
						),
						'CHILDREN' => array(),
					),
				),
			),
		));
	}
}