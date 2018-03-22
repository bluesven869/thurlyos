<?php
namespace Thurly\Calendar;

use Thurly\Main,
	Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class PushTable
 *
 * Fields:
 * <ul>
 * <li> ENTITY_TYPE string(24) mandatory
 * <li> ENTITY_ID int mandatory
 * <li> CHANNEL_ID string(128) mandatory
 * <li> RESOURCE_ID string(128) mandatory
 * <li> EXPIRES datetime mandatory
 * <li> NOT_PROCESSED bool optional default 'N'
 * <li> FIRST_PUSH_DATE datetime optional
 * </ul>
 *
 * @package Thurly\Calendar
 **/

class PushTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_push';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ENTITY_TYPE' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateEntityType'),
				'title' => Loc::getMessage('PUSH_ENTITY_ENTITY_TYPE_FIELD'),
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('PUSH_ENTITY_ENTITY_ID_FIELD'),
			),
			'CHANNEL_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateChannelId'),
				'title' => Loc::getMessage('PUSH_ENTITY_CHANNEL_ID_FIELD'),
			),
			'RESOURCE_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateResourceId'),
				'title' => Loc::getMessage('PUSH_ENTITY_RESOURCE_ID_FIELD'),
			),
			'EXPIRES' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('PUSH_ENTITY_EXPIRES_FIELD'),
			),
			'NOT_PROCESSED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('PUSH_ENTITY_NOT_PROCESSED_FIELD'),
			),
			'FIRST_PUSH_DATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('PUSH_ENTITY_FIRST_PUSH_DATE_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for ENTITY_TYPE field.
	 *
	 * @return array
	 */
	public static function validateEntityType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 24),
		);
	}
	/**
	 * Returns validators for CHANNEL_ID field.
	 *
	 * @return array
	 */
	public static function validateChannelId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 128),
		);
	}
	/**
	 * Returns validators for RESOURCE_ID field.
	 *
	 * @return array
	 */
	public static function validateResourceId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 128),
		);
	}
}