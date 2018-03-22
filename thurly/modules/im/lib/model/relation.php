<?php
namespace Thurly\Im\Model;

use Thurly\Main\Entity;
use Thurly\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class RelationTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CHAT_ID int mandatory
 * <li> MESSAGE_TYPE string(1) optional default 'P'
 * <li> USER_ID int mandatory
 * <li> START_ID int optional
 * <li> LAST_ID int optional
 * <li> LAST_SEND_ID int optional
 * <li> LAST_FILE_ID int optional
 * <li> LAST_READ datetime optional
 * <li> STATUS int optional
 * <li> CALL_STATUS int optional
 * <li> NOTIFY_BLOCK bool optional default 'N'
 * <li> CHAT reference to {@link \Thurly\Im\Model\MessageTable}
 * <li> START reference to {@link \Thurly\Im\Model\MessageTable}
 * <li> LAST_SEND reference to {@link \Thurly\Im\Model\MessageTable}
 * <li> LAST reference to {@link \Thurly\Im\Model\MessageTable}
 * <li> USER reference to {@link \Thurly\Main\UserTable}
 * </ul>
 *
 * @package Thurly\Im
 **/

class RelationTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_relation';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('RELATION_ENTITY_ID_FIELD'),
			),
			'CHAT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('RELATION_ENTITY_CHAT_ID_FIELD'),
			),
			'MESSAGE_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateMessageType'),
				'title' => Loc::getMessage('RELATION_ENTITY_MESSAGE_TYPE_FIELD'),
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('RELATION_ENTITY_USER_ID_FIELD'),
			),
			'START_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('RELATION_ENTITY_START_ID_FIELD'),
			),
			'LAST_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('RELATION_ENTITY_LAST_ID_FIELD'),
			),
			'LAST_SEND_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('RELATION_ENTITY_LAST_SEND_ID_FIELD'),
			),
			'LAST_FILE_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('RELATION_ENTITY_LAST_FILE_ID_FIELD'),
			),
			'LAST_READ' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('RELATION_ENTITY_LAST_READ_FIELD'),
			),
			'STATUS' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('RELATION_ENTITY_STATUS_FIELD'),
			),
			'CALL_STATUS' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('RELATION_ENTITY_CALL_STATUS_FIELD'),
			),
			'MESSAGE_STATUS' => array(
				'data_type' => 'string',
				'default_value' => IM_MESSAGE_STATUS_RECEIVED,
				'validation' => array(__CLASS__, 'validateMessageStatus'),
			),
			'NOTIFY_BLOCK' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('RELATION_ENTITY_NOTIFY_BLOCK_FIELD'),
			),
			'MANAGER' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'COUNTER' => array(
				'data_type' => 'integer',
				'default_value' => 0,
			),
			'CHAT' => array(
				'data_type' => 'Thurly\Im\Model\ChatTable',
				'reference' => array('=this.CHAT_ID' => 'ref.ID'),
			),
			'START' => array(
				'data_type' => 'Thurly\Im\Model\MessageTable',
				'reference' => array('=this.START_ID' => 'ref.ID'),
			),
			'LAST_SEND' => array(
				'data_type' => 'Thurly\Im\Model\MessageTable',
				'reference' => array('=this.LAST_SEND_ID' => 'ref.ID'),
			),
			'LAST' => array(
				'data_type' => 'Thurly\Im\Model\MessageTable',
				'reference' => array('=this.LAST_ID' => 'ref.ID'),
			),
			'USER' => array(
				'data_type' => 'Thurly\Main\User',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
		);
	}

	/**
	 * Returns validators for MESSAGE_TYPE field.
	 *
	 * @return array
	 */
	public static function validateMessageType()
	{
		return array(
			new Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for MESSAGE_STATUS field.
	 *
	 * @return array
	 */
	public static function validateMessageStatus()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}
}

class_alias("Thurly\\Im\\Model\\RelationTable", "Thurly\\Im\\RelationTable", false);