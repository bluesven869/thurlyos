<?
/**
 * Class CheckListTable
 *
 * @package Thurly\Tasks
 **/

namespace Thurly\Tasks\Internals\Task\Template;

use Thurly\Main,
	Thurly\Main\Localization\Loc;

use Thurly\Tasks\Util\Assert;

Loc::loadMessages(__FILE__);

class CheckListTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_tasks_template_chl_item';
	}

	/**
	 * @return static
	 */
	public static function getClass()
	{
		return get_called_class();
	}

	public static function add(array $data)
	{
		if(!isset($data['SORT']))
		{
			$data['TEMPLATE_ID'] = intval($data['TEMPLATE_ID']);
			if($data['TEMPLATE_ID'] && !array_key_exists('SORT', $data))
			{
				$item = static::getList(array(
					'runtime' => array(
						'MAX_SORT' => array(
							'dat_type' => 'integer',
							'expression' => array(
								'MAX(SORT)'
							)
						)
					),
					'filter' => array(
						'=TEMPLATE_ID' => $data['TEMPLATE_ID']
					),
					'select' => array(
						'MAX_SORT'
					)
				))->fetch();

				if(intval($item['MAX_SORT']))
				{
					$data['SORT'] = intval($item['MAX_SORT']) + 1;
				}
				else
				{
					$data['SORT'] = 1;
				}
			}
		}

		return parent::add($data);
	}

	public static function getListByTemplateDependency($templateId, $parameters)
	{
		$templateId = intval($templateId);
		if(!$templateId) // getter should not throw any exception on bad parameters
			return new \Thurly\Main\DB\ArrayResult(array());

		if(!is_array($parameters))
			$parameters = array();
		if(!is_array($parameters['filter']))
			$parameters['filter'] = array();

		$parameters['filter']['@TEMPLATE_ID'] = new \Thurly\Main\DB\SqlExpression(\Thurly\Tasks\Internals\Task\Template\DependenceTable::getSubTreeSql($templateId));

		return static::getList($parameters);
	}

	/**
	 * Update list items for a certain template: add new, update passed and delete absent.
	 * This function is low-level, i.e. it disrespects any events\callbacks
	 *
	 * @param integer $templateId
	 * @param mixed[] $items
	 * @return mixed[]
	 * @throws \Thurly\Main\ArgumentException
	 */
	public static function updateForTemplate($templateId, $items = array())
	{
		$templateId = 	Assert::expectIntegerPositive($templateId, '$templateId');
		$items = 		Assert::expectArray($items, '$items');

		$existed = array();
		$res = static::getList(array(
			'filter' => array('=TEMPLATE_ID' => $templateId),
			'select' => array('ID')
		));
		while($item = $res->fetch())
		{
			$existed[$item['ID']] = true;
		}

		$results = array();
		foreach($items as $item)
		{
			$item = Assert::expectArray($item, '$items[]');
			$item['TEMPLATE_ID'] = $templateId;

			$item['TITLE'] = trim($item['TITLE']);
			/*
			if((string) $item['TITLE'] == '')
			{
				continue;
			}
			*/

			if(intval($item['ID']))
			{
				$id = $item['ID'];

				unset($item['ID']);
				unset($existed[$id]);

				$results[$id] = static::update($id, $item);
			}
			else
			{
				$addResult = static::add($item);
				if($addResult->isSuccess())
					$results[$addResult->getId()] = $addResult;
				else
					$results[] = $addResult;
			}
		}

		foreach($existed as $id => $flag)
		{
			$results[$id] = static::delete($id);
		}

		return $results;
	}

	/**
	 * Move item after other item.
	 * This function is low-level, i.e. it disrespects any events\callbacks.
	 *
	 * @param integer $selectedItemId
	 * @param integer $insertAfterItemId
	 * @throws \Thurly\Main\ArgumentException
	 */
	public static function moveAfterItem($templateId, $selectedItemId, $insertAfterItemId)
	{
		$templateId = 			Assert::expectIntegerPositive($templateId, '$templateId');
		$selectedItemId = 		Assert::expectIntegerPositive($selectedItemId, '$selectedItemId');
		$insertAfterItemId = 	Assert::expectIntegerPositive($insertAfterItemId, '$insertAfterItemId');

		$res = static::getList(array('filter' => array(
			'=TEMPLATE_ID' => $templateId
		), 'order' => array(
			'SORT' => 'asc',
			'ID' => 'asc'
		), 'select' => array(
			'ID',
			'SORT'
		)));

		$items = array($selectedItemId => 0);	// by default to first position
		$prevItemId = 0;
		$sortIndex = 1;
		while($item = $res->fetch())
		{
			if ($insertAfterItemId == $prevItemId)
				$items[$selectedItemId] = $sortIndex++;

			if ($item['ID'] != $selectedItemId)
				$items[$item['ID']] = $sortIndex++;

			$prevItemId = $item['ID'];
		}

		if ($insertAfterItemId == $prevItemId)
			$items[$selectedItemId] = $sortIndex;

		if (!empty($items))
		{
			$sql = "
				UPDATE ".static::getTableName()."
					SET
						SORT = CASE ";

			foreach ($items as $id => $sortIndex)
				$sql .= " WHEN ID = '".intval($id)."' THEN '".intval($sortIndex)."'";

			$sql .= " END

				WHERE TEMPLATE_ID = '".intval($templateId)."'";

			\Thurly\Main\HttpApplication::getConnection()->query($sql);
		}
	}

	/**
	 * Removes all checklist's items for given template.
	 * This function is low-level, i.e. it disrespects any events\callbacks
	 *
	 * @param integer $templateId
	 * @throws \Thurly\Main\ArgumentException
	 */
	public static function deleteByTemplateId($templateId)
	{
		$templateId = Assert::expectIntegerPositive($templateId, '$templateId');

		\Thurly\Main\HttpApplication::getConnection()->query("DELETE FROM ".static::getTableName()." WHERE TEMPLATE_ID = '".$templateId."'");
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
			),
			'TEMPLATE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('TASKS_TASK_TEMPLATE_ENTITY_TEMPLATE_ID_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
			),
			'TITLE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateTitle'),
				'title' => Loc::getMessage('TASKS_TASK_TEMPLATE_ENTITY_TITLE_FIELD'),
			),
			'CHECKED' => array(
				'data_type' => 'integer',
			),

			// for compatibility
			'IS_COMPLETE' => array(
				'data_type' => 'string',
				'expression' => array(
					"CASE WHEN %s = '1' THEN 'Y' ELSE 'N' END",
					"CHECKED"
				)
			),
			'SORT_INDEX' => array(
				'data_type' => 'integer',
				'expression' => array(
					"%s",
					"SORT"
				)
			),
		);
	}
	/**
	 * Returns validators for TITLE field.
	 *
	 * @return array
	 */
	public static function validateTitle()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
}