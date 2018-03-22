<?php

namespace Thurly\Voximplant;

use Thurly\Main\Config\Option;
use Thurly\Main\Loader;
use Thurly\Main\ModuleManager;
use Thurly\ThurlyOS;

class Limits
{
	const OPTION_INFOCALLS_LIMIT = "infocalls_limit";
	const OPTION_LAST_INFOCALLS_COUNTER_UPDATE = "infocalls_month";
	const INFOCALLS_COUNTER = "vi_infocalls";

	const OPTION_IGNORE_MAXIMUM_NUMBERS = "ignore_maximum_numbers";
	const OPTION_IGNORE_MAXIMUM_GROUPS = "ignore_maximum_groups";
	const OPTION_IGNORE_MAXIMUM_GROUP_MEMBERS = "ignore_maximum_group_members";

	/**
	 * @param string $lineMode Line mode (\CVoxImplantConfig::MODE_SIP or \CVoxImplantConfig::MODE_RENT)
	 * @return int|false
	 */
	public static function getInfocallsLimit($lineMode = '')
	{
		if(!ModuleManager::isModuleInstalled('thurlyos'))
			return false;

		if(\CVoxImplantAccount::IsPro())
		{
			return false;
		}

		if($lineMode = \CVoxImplantConfig::MODE_SIP && \CVoxImplantSip::isActive())
		{
			return false;
		}
		
		return (int)Option::get('voximplant', self::OPTION_INFOCALLS_LIMIT);
	}

	/**
	 * @param string $lineMode Line mode (\CVoxImplantConfig::MODE_SIP or \CVoxImplantConfig::MODE_RENT)
	 * @return int|false
	 */
	public static function getInfocallsLimitRemainder($lineMode = '')
	{
		$limit = self::getInfocallsLimit($lineMode);
		if($limit === false)
			return false;

		$month = (int)date('Ym');
		$previousMonth = (int)Option::get('voximplant', self::OPTION_LAST_INFOCALLS_COUNTER_UPDATE);

		if($previousMonth !== $month)
		{
			Option::set('voximplant', self::OPTION_LAST_INFOCALLS_COUNTER_UPDATE, $month);
			$counter = 0;
			\CGlobalCounter::Set(self::INFOCALLS_COUNTER, $counter, \CGlobalCounter::ALL_SITES, '',false);
		}
		else
		{
			$counter = \CGlobalCounter::GetValue(self::INFOCALLS_COUNTER, \CGlobalCounter::ALL_SITES);
		}

		$result = $limit - $counter;
		return ($result > 0 ? $result : 0);
	}

	/**
	 * @param string $lineMode Line mode (\CVoxImplantConfig::MODE_SIP or \CVoxImplantConfig::MODE_RENT)
	 * @return bool
	 */
	public static function addInfocall($lineMode = '')
	{
		$limit = self::getInfocallsLimit($lineMode);
		if($limit === false)
			return false;

		\CGlobalCounter::Increment(self::INFOCALLS_COUNTER, \CGlobalCounter::ALL_SITES);
		return true;
	}

	/**
	 * Returns maximum IVR depth according to the portal's tariff
	 * @return int|string
	 */
	public static function getIvrDepth()
	{
		if (!Loader::includeModule('thurlyos'))
			return 0;

		if(\CThurlyOS::getLicenseType() === 'team')
			return 2;
		else if(\CThurlyOS::getLicenseType() === 'crm')
			return 2;
		else
			return 0;
	}

	/**
	 * Returns maximum count of numbers, allowed to rent.
	 * @return int
	 */
	public static function getMaximumNumbers()
	{
		if (!Loader::includeModule('thurlyos'))
			return 0;

		if(Option::get('voximplant', static::OPTION_IGNORE_MAXIMUM_NUMBERS) === "Y")
			return 0;

		$licensePrefix = \CThurlyOS::getLicensePrefix();
		if(in_array($licensePrefix, static::getRussianRegions()))
			return 0;

		$licenseType = \CThurlyOS::getLicenseType();
		if($licenseType === 'project') //project
			return 1;
		elseif ($licenseType === 'tf') //project+
			return 3;
		elseif ($licenseType === 'reail') //retail+crm
			return 3;

		return 0;
	}

	/**
	 * Returns maximum allowed number of groups.
	 * @return int
	 */
	public static function getMaximumGroups()
	{
		if (!Loader::includeModule('thurlyos'))
			return 0;

		if(Option::get('voximplant', static::OPTION_IGNORE_MAXIMUM_GROUPS) === "Y")
			return 0;

		$licenseType = \CThurlyOS::getLicenseType();
		if($licenseType === 'project') //project
			return 1;
		else
			return 0;
	}

	/**
	 * Returns maximum allowed number of users in the group.
	 * @return int
	 */
	public static function getMaximumGroupMembers()
	{
		if (!Loader::includeModule('thurlyos'))
			return 0;

		if(Option::get('voximplant', static::OPTION_IGNORE_MAXIMUM_GROUP_MEMBERS) === "Y")
			return 0;

		$licenseType = \CThurlyOS::getLicenseType();
		if($licenseType === 'project') //project
			return 3;
		else
			return 0;
	}

	/**
	 * Returns array or russian license prefixes.
	 * @return array
	 */
	protected static function getRussianRegions()
	{
		return array('ru', 'ua', 'kz', 'by');
	}

	/**
	 * Returns true if current portal is able to rent one more number.
	 * @return bool
	 */
	public static function canRentNumber()
	{
		$maximumNumbers = self::getMaximumNumbers();
		if ($maximumNumbers == 0)
			return true;

		$row = \Thurly\Voximplant\ConfigTable::getList(array(
			'select' => array('CNT'),
			'filter' => array(
				'PORTAL_MODE' => \CVoxImplantConfig::MODE_RENT
			)
		))->fetch();

		$rentedNumbers = $row['CNT'];
		return $rentedNumbers < $maximumNumbers;
	}

	/**
	 * Returns true if current portal is able to create one more group.
	 * @return bool
	 */
	public static function canCreateGroup()
	{
		$maxGroups = self::getMaximumGroups();
		if ($maxGroups == 0)
			return true;

		$row = \Thurly\Voximplant\Model\QueueTable::getList(array(
			'select' => array('CNT')
		))->fetch();
		$groupCount = $row['CNT'];
		return $groupCount < $maxGroups;
	}

	public static function canSelectLine()
	{
		return \CVoxImplantAccount::IsPro();
	}

	public static function canInterceptCall()
	{
		return \CVoxImplantAccount::IsPro();
	}
}