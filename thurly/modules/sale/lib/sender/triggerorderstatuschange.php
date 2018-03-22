<?

namespace Thurly\Sale\Sender;

use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class TriggerOrderStatusChange extends \Thurly\Sender\TriggerConnector
{
	public function getName()
	{
		return Loc::getMessage('sender_trigger_order_status_change_name');
	}

	public function getCode()
	{
		return "order_status_change";
	}

	public function getEventModuleId()
	{
		return 'sale';
	}

	public function getEventType()
	{
		return "OnSaleStatusOrderChange";
	}

	/** @return bool */
	public static function canBeTarget()
	{
		return true;
	}

	public function filter()
	{
		$eventData = $this->getParam('EVENT');
		$statusId = $this->getFieldValue('STATUS_ID', null);

		if(!($eventData['ENTITY'] instanceof \Thurly\Sale\Order))
		{
			return false;
		}

		if($statusId != $eventData['ENTITY']->getField('STATUS_ID'))
		{
			return false;
		}

		return $this->filterConnectorData();
	}

	public function getConnector()
	{
		$connector = new \Thurly\Sale\Sender\ConnectorOrder;
		$connector->setModuleId('sale');

		return $connector;
	}

	/** @return array */
	public function getProxyFieldsFromEventToConnector()
	{
		$eventData = $this->getParam('EVENT');
		return array('ID' => $eventData['ENTITY']->getId(), 'LID' => $this->getSiteId());
	}


	/**
	 * @return array
	 */
	public function getPersonalizeFields()
	{
		$result = array(
			'ORDER_ID' => ''
		);

		$eventData = $this->getParam('EVENT');
		if($eventData['ENTITY'] instanceof \Thurly\Sale\Order)
		{
			$result['ORDER_ID'] = $eventData['ENTITY']->getId();
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public static function getPersonalizeList()
	{
		return array(
			array(
				'CODE' => 'ORDER_ID',
				'NAME' => Loc::getMessage('sender_trigger_order_status_change_name_order_id_name'),
				'DESC' => Loc::getMessage('sender_trigger_order_status_change_name_order_id_desc')
			),
		);
	}

	public function getForm()
	{
		$statusInput = '';
		$statusDb = \Thurly\Sale\Internals\StatusLangTable::getList(array(
			'select' => array('STATUS_ID', 'NAME'),
			'filter' => array('=LID' => LANGUAGE_ID, '=STATUS_ID' => \Thurly\Sale\OrderStatus::getAllStatuses()),
			'order' => array('STATUS.SORT')
		));
		while($status = $statusDb->fetch())
		{
			$selected = $status['STATUS_ID'] == $this->getFieldValue('STATUS_ID') ? ' selected' : '';
			$statusInput .= '<option value="' . $status['STATUS_ID'] . '"' . $selected . '>'
				. htmlspecialcharsbx($status['NAME'])
				. '</option>';
		}
		$statusInput = '<select name="' . $this->getFieldName('STATUS_ID') . '">' . $statusInput . '</select>';

		return '
			<table>
				<tr>
					<td>'.Loc::getMessage('sender_trigger_order_status_change_field').': </td>
					<td>'.$statusInput.'</td>
				</tr>
			</table>
		';
	}
}