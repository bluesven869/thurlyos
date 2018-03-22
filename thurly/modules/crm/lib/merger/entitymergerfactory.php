<?php
namespace Thurly\Crm\Merger;
use Thurly\Main;
use Thurly\Crm;

class EntityMergerFactory
{
	/** Create new entity merger by specified entity type ID.
	 * @static
	 * @param int $entityTypeID Entity type ID.
	 * @param int $currentUserID Current user ID.
	 * @param bool $enablePermissionCheck Permission check flag.
	 * @return EntityMerger
	 */
	public static function create($entityTypeID, $currentUserID, $enablePermissionCheck = false)
	{
		if(!is_int($entityTypeID))
		{
			$entityTypeID = (int)$entityTypeID;
		}

		if(!\CCrmOwnerType::IsDefined($entityTypeID))
		{
			throw new Main\ArgumentException('Is not defined', 'entityTypeID');
		}

		if($entityTypeID === \CCrmOwnerType::Lead)
		{
			return new LeadMerger($currentUserID, $enablePermissionCheck);
		}
		elseif($entityTypeID === \CCrmOwnerType::Deal)
		{
			return new DealMerger($currentUserID, $enablePermissionCheck);
		}
		elseif($entityTypeID === \CCrmOwnerType::Contact)
		{
			return new ContactMerger($currentUserID, $enablePermissionCheck);
		}
		elseif($entityTypeID === \CCrmOwnerType::Company)
		{
			return new CompanyMerger($currentUserID, $enablePermissionCheck);
		}
		else
		{
			throw new Main\NotSupportedException("Entity type: '".\CCrmOwnerType::ResolveName($entityTypeID)."' is not supported in current context");
		}
	}
}