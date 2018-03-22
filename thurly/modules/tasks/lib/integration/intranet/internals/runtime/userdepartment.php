<?
namespace Thurly\Tasks\Integration\Intranet\Internals\Runtime;

use Thurly\Main\Entity;
use Thurly\Intranet\Internals\UserSubordinationTable;
use Thurly\Intranet\Internals\UserToDepartmentTable;
use Thurly\Main\Loader;

class UserDepartment extends \Thurly\Tasks\Integration\Intranet
{
	public static function getSubordinateFilter(array $parameters = array())
	{
		if(!static::includeModule())
		{
			return array();
		}

		$rf = $parameters['REF_FIELD'];

		return array('runtime' =>
			array(
				new Entity\ReferenceField(
					'ISB',
					UserSubordinationTable::getEntity(),
					array(
						'=ref.DIRECTOR_ID' => array('?', $parameters['USER_ID']),
						'=ref.SUBORDINATE_ID' => (((string) $rf != '' ? $rf : 'this')).'.USER_ID',
					),
					array('join_type' => 'inner')
				)
			)
		);
	}

	public static function getUserPrimaryDepartmentField(array $parameters = array())
	{
		if(!static::includeModule() || !Loader::includeModule('iblock'))
		{
			return array();
		}

		$rf = $parameters['REF_FIELD'];
		$vf = $parameters['VALUE_FIELD'];

		return array('runtime' =>
			array(
				new Entity\ReferenceField(
					'PD',
					UserToDepartmentTable::getEntity(),
					array(
						'=ref.USER_ID' => (((string) $rf != '' ? $rf : 'this')).'.'.(((string) $vf != '' ? $vf : 'ID')),
						'=ref.WEIGHT' => array('?', '1'),
					),
					array('join_type' => 'inner')
				)
			)
		);
	}
}