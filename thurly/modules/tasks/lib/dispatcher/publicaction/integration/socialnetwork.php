<?
/**
 * Thurly Framework
 * @package thurly
 * @subpackage tasks
 * @copyright 2001-2016 Thurly
 * 
 * @access private
 *
 * Each method you put here you`ll be able to call as ENTITY_NAME.METHOD_NAME via AJAX and\or REST, so be careful.
 */

namespace Thurly\Tasks\Dispatcher\PublicAction\Integration;

use \Thurly\Main\ArgumentException;

final class SocialNetwork extends \Thurly\Tasks\Dispatcher\PublicAction
{
    const ALLOWED_AVATAR_SIZE = 100;

	/**
	 * Display user selector control
	 */
	public function getDestinationData($context = 'TASKS')
	{
		$validCtxs = static::getValidDestinationDataContexts();
		if(!in_array($context, $validCtxs))
		{
			$this->errors->add('INVALID_CONTEXT', 'Invalid context passed');
			return array();
		}

		return \Thurly\Tasks\Integration\SocialNetwork::getLogDestination($context, array(
			'AVATAR_WIDTH' => static::ALLOWED_AVATAR_SIZE,
			'AVATAR_HEIGHT' => static::ALLOWED_AVATAR_SIZE,
			'USE_PROJECTS' => 'Y'
        ));
	}

	public function setDestinationLast($items = array(), $context = 'TASKS')
	{
		if(empty($items))
		{
			return array();
		}

		$validCtxs = static::getValidDestinationDataContexts();
		if(!in_array($context, $validCtxs))
		{
			$this->errors->add('INVALID_CONTEXT', 'Invalid context passed');
			return array();
		}

		\Thurly\Tasks\Integration\SocialNetwork::setLogDestinationLast($items, $context);

		return array();
	}

	private static function getValidDestinationDataContexts()
	{
		// todo: there may be an event that obtains a list of valid contexts

		return array(
			'TASKS',
			'TASKS_RIGHTS'
		);
	}

	public static function getMemberList($groupId)
	{
		return \Thurly\Tasks\Integration\SocialNetwork::getMemberList($groupId);
	}
}