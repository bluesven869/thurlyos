<?php
namespace Thurly\Crm\Agent;

use Thurly\Main;
use Thurly\Main\Config\Option;

abstract class EntityStepwiseAgent extends AgentBase
{
	/**
	 * @return EntityStepwiseAgent|null
	 */
	public static function getInstance()
	{
		return null;
	}

	//region AgentBase
	public static function doRun()
	{
		$instance = static::getInstance();
		if($instance === null)
		{
			return false;
		}

		if(!$instance->isEnabled())
		{
			return false;
		}

		$progressData = $instance->getProgressData();

		$offsetID = isset($progressData['LAST_ITEM_ID']) ? (int)($progressData['LAST_ITEM_ID']) : 0;
		$processedItemQty = isset($progressData['PROCESSED_ITEMS']) ? (int)($progressData['PROCESSED_ITEMS']) : 0;
		$totalItemQty = isset($progressData['TOTAL_ITEMS']) ? (int)($progressData['TOTAL_ITEMS']) : 0;
		if($totalItemQty <= 0)
		{
			$totalItemQty = $instance->getTotalEntityCount();
		}

		$itemIDs = $instance->getEnityIDs($offsetID, $instance->getIterationLimit());
		$itemQty = count($itemIDs);

		if($itemQty === 0)
		{
			$instance->enable(false);
			return false;
		}

		$instance->process($itemIDs);

		$processedItemQty += $itemQty;
		$progressData['LAST_ITEM_ID'] = $itemIDs[$itemQty - 1];
		$progressData['PROCESSED_ITEMS'] = $processedItemQty;
		$progressData['TOTAL_ITEMS'] = $totalItemQty;

		$instance->setProgressData($progressData);
		return true;
	}
	//endregion

	public function isEnabled()
	{
		$name = $this->getOptionName();
		return $name !== '' && Option::get('crm', $name, 'N') === 'Y';
	}
	public function enable($enable)
	{
		$name = $this->getOptionName();
		if($name === '')
		{
			return;
		}

		if(!is_bool($enable))
		{
			$enable = (bool)$enable;
		}

		if($enable === self::isEnabled())
		{
			return;
		}

		if($enable)
		{
			Option::set('crm', $name, 'Y');
		}
		else
		{
			Option::delete('crm', array('name' => $name));
		}

		$progressName = $this->getProgressOptionName();
		if($progressName !== '')
		{
			Option::delete('crm', array('name' => $progressName));
		}
	}

	public function getProgressData()
	{
		$progressName = $this->getProgressOptionName();
		if($progressName === '')
		{
			return null;
		}

		$s = Option::get('crm', $progressName,  '');
		$data = $s !== '' ? unserialize($s) : null;
		if(!is_array($data))
		{
			$data = array();
		}

		$data['LAST_ITEM_ID'] = isset($data['LAST_ITEM_ID']) ? (int)($data['LAST_ITEM_ID']) : 0;
		$data['PROCESSED_ITEMS'] = isset($data['PROCESSED_ITEMS']) ? (int)($data['PROCESSED_ITEMS']) : 0;
		$data['TOTAL_ITEMS'] = isset($data['TOTAL_ITEMS']) ? (int)($data['TOTAL_ITEMS']) : 0;

		return $data;
	}
	public function setProgressData(array $data)
	{
		$progressName = $this->getProgressOptionName();
		if($progressName !== '')
		{
			Option::set('crm', $progressName, serialize($data));
		}
	}

	public abstract function process(array $itemIDs);
	protected abstract function getOptionName();
	protected abstract function getProgressOptionName();
	protected abstract function getTotalEntityCount();
	protected abstract function getEnityIDs($offsetID, $limit);
	protected function getIterationLimit()
	{
		return 100;
	}
}