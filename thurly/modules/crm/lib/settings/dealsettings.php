<?php
namespace Thurly\Crm\Settings;
use Thurly\Main;
class DealSettings
{
	const VIEW_LIST = 1;
	const VIEW_WIDGET = 2;
	const VIEW_KANBAN = 3;

	/** @var DealSettings */
	private static $current = null;
	/** @var bool */
	private static $messagesLoaded = false;
	/** @var array */
	private static $descriptions = null;
	/** @var IntegerSetting */
	private $defaultListView = null;
	/** @var BooleanSetting */
	private $enableCloseDateSync = null;
	/** @var BooleanSetting */
	private $enableProductRowExport = null;
	/** @var BooleanSetting */
	private $isOpened = null;

	function __construct()
	{
		$this->defaultListView = new IntegerSetting('deal_default_list_view', self::VIEW_KANBAN);
		$this->enableCloseDateSync = new BooleanSetting('enable_close_date_sync', true);
		$this->enableProductRowExport = new BooleanSetting('enable_deal_prod_row_export', true);
		$this->isOpened = new BooleanSetting('deal_opened_flag', true);
	}
	/**
	 * Get current instance
	 * @return DealSettings
	 */
	public static function getCurrent()
	{
		if(self::$current === null)
		{
			self::$current = new DealSettings();
		}
		return self::$current;
	}
	/**
	 * Get value of flag 'OPENED'
	 * @return bool
	 */
	public function getOpenedFlag()
	{
		return $this->isOpened->get();
	}
	/**
	 * Set value of flag 'OPENED'
	 * @param bool $opened Opened Flag.
	 * @return void
	 */
	public function setOpenedFlag($opened)
	{
		$this->isOpened->set($opened);
	}
	/**
	 * Check if synchronization of the 'CLOSEDATE' field is enabled.
	 * @return bool
	 */
	public function isCloseDateSyncEnabled()
	{
		return $this->enableCloseDateSync->get();
	}
	/**
	 * Enable synchronization of the 'CLOSEDATE' field
	 * @param bool $enabled Enabled Flag.
	 * @return void
	 */
	public function enableCloseDateSync($enabled)
	{
		$this->enableCloseDateSync->set($enabled);
	}
	/**
	 * Check if export of the product rows is enabled
	 * @return bool
	 */
	public function isProductRowExportEnabled()
	{
		return $this->enableProductRowExport->get();
	}
	/**
	 * Enable export of the product rows
	 * @param bool $enabled Enabled Flag.
	 * @return void
	 */
	public function enableProductRowExport($enabled)
	{
		$this->enableProductRowExport->set($enabled);
	}
	/**
	 * Get default list view ID
	 * @return int
	 */
	public function getDefaultListViewID()
	{
		return $this->defaultListView->get();
	}
	/**
	 * Set default list view ID
	 * @param int $viewID View ID.
	 * @return void
	 */
	public function setDefaultListViewID($viewID)
	{
		$this->defaultListView->set($viewID);
	}
	/**
	 * Get descriptions of views supported in current context
	 * @return array
	 */
	public static function getViewDescriptions()
	{
		if(!self::$descriptions)
		{
			self::includeModuleFile();

			self::$descriptions= array(
				self::VIEW_LIST => GetMessage('CRM_DEAL_SETTINGS_VIEW_LIST'),
				self::VIEW_WIDGET => GetMessage('CRM_DEAL_SETTINGS_VIEW_WIDGET'),
				self::VIEW_KANBAN => GetMessage('CRM_DEAL_SETTINGS_VIEW_KANBAN')
			);
		}
		return self::$descriptions;
	}
	/**
	 * Prepare list items for view selector
	 * @return array
	 */
	public static function prepareViewListItems()
	{
		return \CCrmEnumeration::PrepareListItems(self::getViewDescriptions());
	}
	/**
	 * Include language file
	 * @return void
	 */
	protected static function includeModuleFile()
	{
		if(self::$messagesLoaded)
		{
			return;
		}

		Main\Localization\Loc::loadMessages(__FILE__);
		self::$messagesLoaded = true;
	}
}