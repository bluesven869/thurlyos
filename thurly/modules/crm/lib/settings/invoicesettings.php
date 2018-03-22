<?php
namespace Thurly\Crm\Settings;
use Thurly\Main;
use Thurly\Crm\Integration\ThurlyOSManager;
use Thurly\Main\ModuleManager;
class InvoiceSettings
{
	const VIEW_LIST = 1;
	const VIEW_WIDGET = 2;
	const VIEW_KANBAN = 3;

	/** @var LeadSettings  */
	private static $current = null;
	/** @var bool */
	private static $messagesLoaded = false;
	/** @var array */
	private static $descriptions = null;
	/** @var BooleanSetting  */
	private $isOpened = null;
	/** @var BooleanSetting  */
	private $isEnableSign = null;
	/** @var IntegerSetting */
	private $defaultListView = null;

	function __construct()
	{
		$this->defaultListView = new IntegerSetting('invoice_default_list_view', self::VIEW_KANBAN);
		$this->isOpened = new BooleanSetting('invoice_opened_flag', true);
		$this->isEnableSign = new BooleanSetting('invoice_enable_public_b24_sign', true);
	}
	/**
	 * Get current instance
	 * @return LeadSettings
	 */
	public static function getCurrent()
	{
		if(self::$current === null)
		{
			self::$current = new InvoiceSettings();
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
	 * Check the possibility to disable a sign
	 * @return bool
	 */
	public static function allowDisableSign()
	{
		return (!(ModuleManager::isModuleInstalled('thurlyos')) || ThurlyOSManager::isPaidAccount());
	}
	/**
	 * Get value of flag 'ENABLED_PUBLIC_B24_SIGN'
	 * @return bool
	 */
	public function getEnableSignFlag()
	{
		return $this->isEnableSign->get();
	}
	/**
	 * Set value of flag 'ENABLED_PUBLIC_B24_SIGN'
	 * @param bool $enabled Opened Flag.
	 * @return void
	 */
	public function setEnableSignFlag($enabled)
	{
		$this->isEnableSign->set($enabled);
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
				self::VIEW_LIST => GetMessage('CRM_INVOICE_SETTINGS_VIEW_LIST'),
				self::VIEW_WIDGET => GetMessage('CRM_INVOICE_SETTINGS_VIEW_WIDGET'),
				self::VIEW_KANBAN => GetMessage('CRM_INVOICE_SETTINGS_VIEW_KANBAN')
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