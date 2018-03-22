<?php
namespace Thurly\Sale;

use Thurly\Main\Entity;
use Thurly\Sale\Internals;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class BasketPropertyItem
 * @package Thurly\Sale
 */
class BasketPropertyItem extends BasketPropertyItemBase
{
	/**
	 * @return BasketPropertyItem
	 */
	protected static function createBasketPropertyItemObject()
	{
		$registry = Registry::getInstance(Registry::REGISTRY_TYPE_ORDER);
		$basketPropertyItemClassName = $registry->getBasketPropertyItemClassName();

		return new $basketPropertyItemClassName();
	}

	/**
	 * @param array $data
	 * @return Entity\AddResult
	 */
	protected function addInternal(array $data)
	{
		return Internals\BasketPropertyTable::add($data);
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return Entity\UpdateResult
	 */
	protected function updateInternal($primary, array $data)
	{
		return Internals\BasketPropertyTable::update($primary, $data);
	}

	/**
	 * @return array
	 */
	protected static function getFieldMap()
	{
		return Internals\BasketPropertyTable::getMap();
	}
}