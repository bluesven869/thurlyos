<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

$isBizProcInstalled = IsModuleInstalled('bizproc');
if ($isBizProcInstalled)
{
	if (!CModule::IncludeModule('bizproc'))
	{
		ShowError(GetMessage('BIZPROC_MODULE_NOT_INSTALLED'));
		return;
	}
	elseif (!CBPRuntime::isFeatureEnabled())
		$isBizProcInstalled = false;
}

if(!CAllCrmInvoice::installExternalEntities())
	return;
if(!CCrmQuote::LocalComponentCausedUpdater())
	return;

if (!CModule::IncludeModule('currency'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED_CURRENCY'));
	return;
}
if (!CModule::IncludeModule('catalog'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED_CATALOG'));
	return;
}
if (!CModule::IncludeModule('sale'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED_SALE'));
	return;
}

/** @global CMain $APPLICATION */
global $USER_FIELD_MANAGER, $USER, $APPLICATION, $DB;

use Thurly\Main;
use Thurly\Crm\Category\DealCategory;
use Thurly\Crm\Settings\HistorySettings;
use Thurly\Crm\WebForm\Manager as WebFormManager;
use Thurly\Crm\Settings\LayoutSettings;

$userPermissions = CCrmPerms::GetCurrentUserPermissions();
if (!CCrmDeal::CheckReadPermission(0, $userPermissions))
{
	ShowError(GetMessage('CRM_PERMISSION_DENIED'));
	return;
}

$CCrmDeal = new CCrmDeal(false);
$CCrmBizProc = new CCrmBizProc('DEAL');

$userID = CCrmSecurityHelper::GetCurrentUserID();
$isAdmin = CCrmPerms::IsAdmin();

$arResult['CURRENT_USER_ID'] = CCrmSecurityHelper::GetCurrentUserID();
$arResult['PATH_TO_DEAL_LIST'] = $arParams['PATH_TO_DEAL_LIST'] = CrmCheckPath('PATH_TO_DEAL_LIST', $arParams['PATH_TO_DEAL_LIST'], $APPLICATION->GetCurPage());
$arResult['PATH_TO_DEAL_WIDGET'] = $arParams['PATH_TO_DEAL_WIDGET'] = CrmCheckPath('PATH_TO_DEAL_WIDGET', $arParams['PATH_TO_DEAL_WIDGET'], $APPLICATION->GetCurPage());
$arResult['PATH_TO_DEAL_KANBAN'] = $arParams['PATH_TO_DEAL_KANBAN'] = CrmCheckPath('PATH_TO_DEAL_KANBAN', $arParams['PATH_TO_DEAL_KANBAN'], $currentPage);
$arParams['PATH_TO_DEAL_CATEGORY'] = CrmCheckPath('PATH_TO_DEAL_CATEGORY', $arParams['PATH_TO_DEAL_CATEGORY'], $APPLICATION->GetCurPage().'?category_id=#category_id#');
$arParams['IS_RECURRING'] = isset($arParams['IS_RECURRING']) ? $arParams['IS_RECURRING'] : 'N';

if ($arParams['IS_RECURRING'] == 'Y')
{
	$arParams['PATH_TO_DEAL_CATEGORY'] = CrmCheckPath('PATH_TO_DEAL_RECUR_CATEGORY', $arParams['PATH_TO_DEAL_RECUR_CATEGORY'], $APPLICATION->GetCurPage().'?category_id=#category_id#');
}
$arParams['PATH_TO_DEAL_WIDGETCATEGORY'] = CrmCheckPath('PATH_TO_DEAL_WIDGETCATEGORY', $arParams['PATH_TO_DEAL_WIDGETCATEGORY'], $APPLICATION->GetCurPage().'?category_id=#category_id#');
$arParams['PATH_TO_DEAL_KANBANCATEGORY'] = CrmCheckPath('PATH_TO_DEAL_KANBANCATEGORY', $arParams['PATH_TO_DEAL_KANBANCATEGORY'], $APPLICATION->GetCurPage().'?category_id=#category_id#');//!!!

$arParams['PATH_TO_DEAL_DETAILS'] = CrmCheckPath('PATH_TO_DEAL_DETAILS', $arParams['PATH_TO_DEAL_DETAILS'], $APPLICATION->GetCurPage().'?deal_id=#deal_id#&details');
$arParams['PATH_TO_DEAL_SHOW'] = CrmCheckPath('PATH_TO_DEAL_SHOW', $arParams['PATH_TO_DEAL_SHOW'], $APPLICATION->GetCurPage().'?deal_id=#deal_id#&show');
$arParams['PATH_TO_DEAL_EDIT'] = CrmCheckPath('PATH_TO_DEAL_EDIT', $arParams['PATH_TO_DEAL_EDIT'], $APPLICATION->GetCurPage().'?deal_id=#deal_id#&edit');
$arParams['PATH_TO_QUOTE_EDIT'] = CrmCheckPath('PATH_TO_QUOTE_EDIT', $arParams['PATH_TO_QUOTE_EDIT'], $APPLICATION->GetCurPage().'?quote_id=#quote_id#&edit');
$arParams['PATH_TO_INVOICE_EDIT'] = CrmCheckPath('PATH_TO_INVOICE_EDIT', $arParams['PATH_TO_INVOICE_EDIT'], $APPLICATION->GetCurPage().'?invoice_id=#invoice_id#&edit');
$arParams['PATH_TO_COMPANY_SHOW'] = CrmCheckPath('PATH_TO_COMPANY_SHOW', $arParams['PATH_TO_COMPANY_SHOW'], $APPLICATION->GetCurPage().'?company_id=#company_id#&show');
$arParams['PATH_TO_CONTACT_SHOW'] = CrmCheckPath('PATH_TO_CONTACT_SHOW', $arParams['PATH_TO_CONTACT_SHOW'], $APPLICATION->GetCurPage().'?contact_id=#contact_id#&show');
$arParams['PATH_TO_USER_PROFILE'] = CrmCheckPath('PATH_TO_USER_PROFILE', $arParams['PATH_TO_USER_PROFILE'], '/company/personal/user/#user_id#/');
$arParams['PATH_TO_USER_BP'] = CrmCheckPath('PATH_TO_USER_BP', $arParams['PATH_TO_USER_BP'], '/company/personal/bizproc/');
$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);
$arResult['PATH_TO_CURRENT_LIST'] = ($arParams['IS_RECURRING'] !== 'Y') ? $arParams['PATH_TO_DEAL_LIST'] : $arParams['PATH_TO_DEAL_RECUR'];
$arParams['ADD_EVENT_NAME'] = isset($arParams['ADD_EVENT_NAME']) ? $arParams['ADD_EVENT_NAME'] : '';
$arResult['ADD_EVENT_NAME'] = $arParams['ADD_EVENT_NAME'] !== ''
	? preg_replace('/[^a-zA-Z0-9_]/', '', $arParams['ADD_EVENT_NAME']) : '';

$arResult['IS_AJAX_CALL'] = isset($_REQUEST['AJAX_CALL']) || isset($_REQUEST['ajax_request']) || !!CAjax::GetSession();
$arResult['SESSION_ID'] = thurly_sessid();
$arResult['NAVIGATION_CONTEXT_ID'] = isset($arParams['NAVIGATION_CONTEXT_ID']) ? $arParams['NAVIGATION_CONTEXT_ID'] : '';
$arResult['PRESERVE_HISTORY'] = isset($arParams['PRESERVE_HISTORY']) ? $arParams['PRESERVE_HISTORY'] : false;

$arResult['HAVE_CUSTOM_CATEGORIES'] = DealCategory::isCustomized();
$arResult['PERMITTED_CATEGORY_IDS'] = CCrmDeal::GetPermittedToReadCategoryIDs($userPermissions);
$arResult['CATEGORY_ID'] = isset($arParams['CATEGORY_ID']) ? (int)$arParams['CATEGORY_ID'] : -1;
$arResult['ENABLE_SLIDER'] = \Thurly\Crm\Settings\LayoutSettings::getCurrent()->isSliderEnabled();

if(LayoutSettings::getCurrent()->isSimpleTimeFormatEnabled())
{
	$arResult['TIME_FORMAT'] = array(
		'tommorow' => 'tommorow',
		's' => 'sago',
		'i' => 'iago',
		'H3' => 'Hago',
		'today' => 'today',
		'yesterday' => 'yesterday',
		//'d7' => 'dago',
		'-' => Main\Type\DateTime::convertFormatToPhp(FORMAT_DATE)
	);
}
else
{
	$arResult['TIME_FORMAT'] = preg_replace('/:s$/', '', Main\Type\DateTime::convertFormatToPhp(FORMAT_DATETIME));
}

$arResult['CALL_LIST_UPDATE_MODE'] = isset($_REQUEST['call_list_context']) && isset($_REQUEST['call_list_id']) && IsModuleInstalled('voximplant');
$arResult['CALL_LIST_CONTEXT'] = (string)$_REQUEST['call_list_context'];
$arResult['CALL_LIST_ID'] = (int)$_REQUEST['call_list_id'];
if($arResult['CALL_LIST_UPDATE_MODE'])
{
	AddEventHandler('crm', 'onCrmDealListItemBuildMenu', array('\Thurly\Crm\CallList\CallList', 'handleOnCrmDealListItemBuildMenu'));
}

if($arResult['CATEGORY_ID'] >= 0)
{
	$arResult['PATH_TO_DEAL_CATEGORY'] = CComponentEngine::makePathFromTemplate(
		$arParams['PATH_TO_DEAL_CATEGORY'],
		array('category_id' => $arResult['CATEGORY_ID'])
	);
	$arResult['PATH_TO_DEAL_KANBANCATEGORY'] = CComponentEngine::makePathFromTemplate(
		$arParams['PATH_TO_DEAL_KANBANCATEGORY'],
		array('category_id' => $arResult['CATEGORY_ID'])
	);
	$arResult['PATH_TO_DEAL_WIDGETCATEGORY'] = CComponentEngine::makePathFromTemplate(
		$arParams['PATH_TO_DEAL_WIDGETCATEGORY'],
		array('category_id' => $arResult['CATEGORY_ID'])
	);
}

CCrmDeal::PrepareConversionPermissionFlags(0, $arResult, $userPermissions);
if($arResult['CAN_CONVERT'])
{
	$config = \Thurly\Crm\Conversion\DealConversionConfig::load();
	if($config === null)
	{
		$config = \Thurly\Crm\Conversion\DealConversionConfig::getDefault();
	}

	$arResult['CONVERSION_CONFIG'] = $config;
}

CUtil::InitJSCore(array('ajax', 'tooltip'));

$arResult['GADGET'] = 'N';
if (isset($arParams['GADGET_ID']) && strlen($arParams['GADGET_ID']) > 0)
{
	$arResult['GADGET'] = 'Y';
	$arResult['GADGET_ID'] = $arParams['GADGET_ID'];
}
$isInGadgetMode = $arResult['GADGET'] === 'Y';

$arFilter = $arSort = array();
$bInternal = false;
$arResult['FORM_ID'] = isset($arParams['FORM_ID']) ? $arParams['FORM_ID'] : '';
$arResult['TAB_ID'] = isset($arParams['TAB_ID']) ? $arParams['TAB_ID'] : '';

if($arResult['CATEGORY_ID'] >= 0)
{
	$arFilter['CATEGORY_ID'] = $arResult['CATEGORY_ID'];
}

if (!empty($arParams['INTERNAL_FILTER']) || $isInGadgetMode)
	$bInternal = true;
$arResult['INTERNAL'] = $bInternal;
if (!empty($arParams['INTERNAL_FILTER']) && is_array($arParams['INTERNAL_FILTER']))
{
	if(empty($arParams['GRID_ID_SUFFIX']))
	{
		$arParams['GRID_ID_SUFFIX'] = $this->GetParent() !== null ? strtoupper($this->GetParent()->GetName()) : '';
	}

	$arFilter = $arParams['INTERNAL_FILTER'];
}

if (!empty($arParams['INTERNAL_SORT']) && is_array($arParams['INTERNAL_SORT']))
	$arSort = $arParams['INTERNAL_SORT'];

$enableWidgetFilter = !$bInternal && isset($_REQUEST['WG']) && strtoupper($_REQUEST['WG']) === 'Y';
if($enableWidgetFilter)
{
	$dataSourceFilter = null;

	$dataSourceName = isset($_REQUEST['DS']) ? $_REQUEST['DS'] : '';
	if($dataSourceName !== '')
	{
		$dataSource = null;
		try
		{
			$dataSource = Thurly\Crm\Widget\Data\DataSourceFactory::create(array('name' => $dataSourceName), $userID, true);
		}
		catch(Thurly\Main\NotSupportedException $e)
		{
		}

		try
		{
			$dataSourceFilter = $dataSource ? $dataSource->prepareEntityListFilter($_REQUEST) : null;
		}
		catch(Thurly\Main\ArgumentException $e)
		{
		}
		catch(Thurly\Main\InvalidOperationException $e)
		{
		}
	}

	if(is_array($dataSourceFilter) && !empty($dataSourceFilter))
	{
		$arFilter = $dataSourceFilter;
	}
	else
	{
		$enableWidgetFilter = false;
	}
}

$enableCounterFilter = false;
if(!$bInternal && isset($_REQUEST['counter']))
{
	$counterTypeID = Thurly\Crm\Counter\EntityCounterType::resolveID($_REQUEST['counter']);
	$counter = null;
	if(Thurly\Crm\Counter\EntityCounterType::isDefined($counterTypeID))
	{
		try
		{
			$counter = Thurly\Crm\Counter\EntityCounterFactory::create(
				CCrmOwnerType::Deal,
				$counterTypeID,
				$userID,
				array_merge(
					Thurly\Crm\Counter\EntityCounter::internalizeExtras($_REQUEST),
					array('DEAL_CATEGORY_ID' => $arResult['CATEGORY_ID'])
				)
			);

			$arFilter = $counter->prepareEntityListFilter(
				array(
					'MASTER_ALIAS' => CCrmDeal::TABLE_ALIAS,
					'MASTER_IDENTITY' => 'ID'
				)
			);
			$enableCounterFilter = !empty($arFilter);
		}
		catch(Thurly\Main\NotSupportedException $e)
		{
		}
		catch(Thurly\Main\ArgumentException $e)
		{
		}
	}
}

$arResult['IS_EXTERNAL_FILTER'] = ($enableWidgetFilter || $enableCounterFilter);

$sExportType = '';
if (!empty($_REQUEST['type']))
{
	$sExportType = strtolower(trim($_REQUEST['type']));
	if (!in_array($sExportType, array('csv', 'excel')))
		$sExportType = '';
}
if (!empty($sExportType) && !CCrmDeal::CheckExportPermission($userPermissions))
{
	ShowError(GetMessage('CRM_PERMISSION_DENIED'));
	return;
}

$isInExportMode = $sExportType !== '';

$CCrmUserType = new CCrmUserType($USER_FIELD_MANAGER, CCrmDeal::$sUFEntityID);

if ($arParams['IS_RECURRING'] === 'Y')
{
	$arResult['GRID_ID'] = 'CRM_DEAL_RECUR_LIST_V12'.($bInternal && !empty($arParams['GRID_ID_SUFFIX']) ? '_'.$arParams['GRID_ID_SUFFIX'] : '');
}
else
{
	$arResult['GRID_ID'] = 'CRM_DEAL_LIST_V12'.($bInternal && !empty($arParams['GRID_ID_SUFFIX']) ? '_'.$arParams['GRID_ID_SUFFIX'] : '');
}

$arResult['TYPE_LIST'] = CCrmStatus::GetStatusListEx('DEAL_TYPE');
// Please, uncomment if required
//$arResult['CURRENCY_LIST'] = CCrmCurrencyHelper::PrepareListItems();
$arResult['EVENT_LIST'] = CCrmStatus::GetStatusListEx('EVENT_TYPE');
$arResult['CLOSED_LIST'] = array('Y' => GetMessage('MAIN_YES'), 'N' => GetMessage('MAIN_NO'));
$arResult['WEBFORM_LIST'] = WebFormManager::getListNames();
$arResult['FILTER'] = array();
$arResult['FILTER2LOGIC'] = array();
$arResult['FILTER_PRESETS'] = array();
$arResult['PERMS']['ADD'] = CCrmDeal::CheckCreatePermission($userPermissions);
$arResult['PERMS']['WRITE'] = CCrmDeal::CheckUpdatePermission(0, $userPermissions);
$arResult['PERMS']['DELETE'] = CCrmDeal::CheckDeletePermission(0, $userPermissions);

$arResult['AJAX_MODE'] = isset($arParams['AJAX_MODE']) ? $arParams['AJAX_MODE'] : ($arResult['INTERNAL'] ? 'N' : 'Y');
$arResult['AJAX_ID'] = isset($arParams['AJAX_ID']) ? $arParams['AJAX_ID'] : '';
$arResult['AJAX_OPTION_JUMP'] = isset($arParams['AJAX_OPTION_JUMP']) ? $arParams['AJAX_OPTION_JUMP'] : 'N';
$arResult['AJAX_OPTION_HISTORY'] = isset($arParams['AJAX_OPTION_HISTORY']) ? $arParams['AJAX_OPTION_HISTORY'] : 'N';
$arResult['EXTERNAL_SALES'] = CCrmExternalSaleHelper::PrepareListItems();

//region Setup of group action 'Change stage'
$effectiveCategoryID = -1;
if($arResult['CATEGORY_ID'] >= 0)
{
	$effectiveCategoryID = $arResult['CATEGORY_ID'];
}
if(!$arResult['HAVE_CUSTOM_CATEGORIES'])
{
	$effectiveCategoryID = 0;
}
elseif(count($arResult['PERMITTED_CATEGORY_IDS']) === 1)
{
	$effectiveCategoryID = $arResult['PERMITTED_CATEGORY_IDS'][0];
}
elseif($effectiveCategoryID < 0 && isset($arFilter['CATEGORY_ID']))
{
	if(!is_array($arFilter['CATEGORY_ID']))
	{
		$effectiveCategoryID = (int)$arFilter['CATEGORY_ID'];
	}
	elseif(count($arFilter['CATEGORY_ID']) === 1)
	{
		$effectiveCategoryID = (int)($arFilter['CATEGORY_ID'][0]);
	}
}

if($effectiveCategoryID >= 0)
{
	$arResult['CATEGORY_STAGE_LIST'] = DealCategory::getStageList($effectiveCategoryID);
}
else
{
	$arResult['CATEGORY_STAGE_GROUPS'] = DealCategory::getStageGroupInfos();
}
$arResult['EFFECTIVE_CATEGORY_ID'] = $effectiveCategoryID;
//endregion

$arResult['CATEGORY_LIST'] = DealCategory::prepareSelectListItems($arResult['PERMITTED_CATEGORY_IDS']);

if (!$bInternal)
{
	$arResult['FILTER2LOGIC'] = array('TITLE', 'COMMENTS');
	$arResult['FILTER'] = array(
		array('id' => 'ID', 'name' => GetMessage('CRM_COLUMN_ID')),
		array('id' => 'TITLE', 'name' => GetMessage('CRM_COLUMN_TITLE')),
		array(
			'id' => 'ASSIGNED_BY_ID',
			'name' => GetMessage('CRM_COLUMN_ASSIGNED_BY'),
			'default' => true,
			'params' => array('multiple' => 'Y'),
			'type' => 'custom_entity',
			'selector' => array(
				'TYPE' => 'user',
				'DATA' => array('ID' => 'assigned_by', 'FIELD_ID' => 'ASSIGNED_BY_ID')
			)
		),
		array('id' => 'OPPORTUNITY', 'name' => GetMessage('CRM_COLUMN_OPPORTUNITY'), 'default' => true, 'type' => 'number'),
		array('id' => 'CURRENCY_ID', 'name' => GetMessage('CRM_COLUMN_CURRENCY_ID'), 'type' => 'list', 'items' => array('' => '') + CCrmCurrencyHelper::PrepareListItems())
	);

	$filterCategoryID = -1;
	if($arResult['CATEGORY_ID'] >= 0)
	{
		$filterCategoryID = $arResult['CATEGORY_ID'];
	}

	if($filterCategoryID < 0)
	{
		if(count($arResult['PERMITTED_CATEGORY_IDS']) === 1)
		{
			$filterCategoryID = $arResult['PERMITTED_CATEGORY_IDS'][0];
		}
	}

	if($filterCategoryID >= 0)
	{
		$arResult['FILTER'][] = array('id' => 'STAGE_ID', 'name' => GetMessage('CRM_COLUMN_STAGE_ID'), 'type' => 'list', 'items'=> DealCategory::getStageList($filterCategoryID), 'params' => array('multiple' => 'Y'));
	}

	if($arResult['CATEGORY_ID'] < 0)
	{
		$arResult['FILTER'][] = array('id' => 'CATEGORY_ID', 'name' => GetMessage('CRM_COLUMN_CATEGORY_ID'), 'type' => 'list', 'items' => DealCategory::prepareSelectListItems($arResult['PERMITTED_CATEGORY_IDS']), 'params' => array('multiple' => 'Y'));
	}

	$arResult['FILTER'][] = Thurly\Crm\PhaseSemantics::getListFilterInfo(CCrmOwnerType::Deal, array('id' => 'STAGE_SEMANTIC_ID', 'name' => GetMessage('CRM_COLUMN_STAGE_SEMANTIC_ID'), 'default' => true, 'params' => array('multiple' => 'Y')));

	if ($arParams['IS_RECURRING'] !== 'Y')
	{
		$arResult['FILTER'][] = \Thurly\Crm\Counter\EntityCounterType::getListFilterInfo(
			array(
				'id' => 'ACTIVITY_COUNTER',
				'name' => GetMessage('CRM_COLUMN_ACTIVITY_COUNTER_V2'),
				'default' => true,
				'params' => array('multiple' => 'Y')
			),
			array('ENTITY_TYPE_ID' => CCrmOwnerType::Deal)
		);
	}

	$arResult['FILTER'] = array_merge(
		$arResult['FILTER'],
		array(
			array('id' => 'IS_NEW', 'name' => GetMessage('CRM_COLUMN_IS_NEW'), 'type' => 'checkbox'),
			array('id' => 'PROBABILITY', 'name' => GetMessage('CRM_COLUMN_PROBABILITY'), 'type' => 'number'),
			array('id' => 'BEGINDATE', 'name' => GetMessage('CRM_COLUMN_BEGINDATE'), 'type' => 'date'),
		)
	);
	if ($arParams['IS_RECURRING'] !== 'Y')
	{
		$arResult['FILTER'][] = array('id' => 'CLOSEDATE', 'name' => GetMessage('CRM_COLUMN_CLOSEDATE'), 'default' => true, 'type' => 'date');
	}

	$arResult['FILTER'] = array_merge(
		$arResult['FILTER'],
		array(
			array(
				'id' => 'CONTACT_ID',
				'name' => GetMessage('CRM_COLUMN_CONTACT_LIST'),
				'default' => true,
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'crm_entity',
					'DATA' => array(
						'ID' => 'contact',
						'FIELD_ID' => 'CONTACT_ID',
						'FIELD_ALIAS' => 'ASSOCIATED_CONTACT_ID',
						'ENTITY_TYPE_NAMES' => array(CCrmOwnerType::ContactName),
						'IS_MULTIPLE' => true
					)
				)
			),
			array('id' => 'CONTACT_FULL_NAME', 'name' => GetMessage('CRM_COLUMN_CONTACT_FULL_NAME')),
			array(
				'id' => 'COMPANY_ID',
				'name' => GetMessage('CRM_COLUMN_COMPANY_LIST'),
				'default' => true,
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'crm_entity',
					'DATA' => array(
						'ID' => 'company',
						'FIELD_ID' => 'COMPANY_ID',
						'ENTITY_TYPE_NAMES' => array(CCrmOwnerType::CompanyName),
						'IS_MULTIPLE' => false
					)
				)
			),
			array('id' => 'COMPANY_TITLE', 'name' => GetMessage('CRM_COLUMN_COMPANY_TITLE')),
			array('id' => 'COMMENTS', 'name' => GetMessage('CRM_COLUMN_COMMENTS')),
			array('id' => 'TYPE_ID', 'params' => array('multiple' => 'Y'), 'name' => GetMessage('CRM_COLUMN_TYPE_ID'),  'type' => 'list', 'items' => CCrmStatus::GetStatusList('DEAL_TYPE')),
			array('id' => 'CLOSED', 'name' => GetMessage('CRM_COLUMN_CLOSED'), 'type' => 'checkbox'),
			array('id' => 'DATE_CREATE', 'name' => GetMessage('CRM_COLUMN_DATE_CREATE'), 'type' => 'date'),
			array(
				'id' => 'CREATED_BY_ID',
				'name' => GetMessage('CRM_COLUMN_CREATED_BY'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array('ID' => 'created_by', 'FIELD_ID' => 'CREATED_BY_ID')
				)
			),
			array('id' => 'DATE_MODIFY', 'name' => GetMessage('CRM_COLUMN_DATE_MODIFY'), 'type' => 'date'),
			array(
				'id' => 'MODIFY_BY_ID',
				'name' => GetMessage('CRM_COLUMN_MODIFY_BY'),
				'type' => 'custom_entity',
				'selector' => array(
					'TYPE' => 'user',
					'DATA' => array('ID' => 'modify_by', 'FIELD_ID' => 'MODIFY_BY_ID')
				)
			),
			array(
				'id' => 'ORIGINATOR_ID',
				'name' => GetMessage('CRM_COLUMN_BINDING'),
				'type' => 'list',
				'items' => array('' => GetMessage('CRM_COLUMN_ALL')) + $arResult['EXTERNAL_SALES']
			),
		)
	);

	if ($arParams['IS_RECURRING'] !== 'Y')
	{
		$arResult['FILTER'] = array_merge(
			$arResult['FILTER'],
			array(
				array('id' => 'CLOSED', 'name' => GetMessage('CRM_COLUMN_CLOSED'), 'type' => 'checkbox'),
				array('id' => 'EVENT_DATE', 'name' => GetMessage('CRM_COLUMN_EVENT_DATE_FILTER'), 'type' => 'date'),
				array('id' => 'EVENT_ID', 'name' => GetMessage('CRM_COLUMN_EVENT_ID_FILTER'), 'type' => 'list', 'items' => array('' => '') + CCrmStatus::GetStatusList('EVENT_TYPE')),
				array(
					'id' => 'PRODUCT_ROW_PRODUCT_ID',
					'name' => GetMessage('CRM_COLUMN_PRODUCT'),
					'type' => 'custom_entity',
					'selector' => array(
						'TYPE' => 'crm_entity',
						'DATA' => array(
							'ID' => 'product',
							'FIELD_ID' => 'PRODUCT_ROW_PRODUCT_ID',
							'ENTITY_TYPE_NAMES' => array('PRODUCT'),
							'IS_MULTIPLE' => false
						)
					)
				),
				array('id' => 'WEBFORM_ID', 'params' => array('multiple' => 'Y'), 'name' => GetMessage('CRM_COLUMN_WEBFORM'), 'type' => 'list', 'items' => WebFormManager::getListNames())
			)
		);
	}
	else
	{
		$arResult['FILTER'] = array_merge(
			$arResult['FILTER'],
			array(
				array('id' => 'CRM_DEAL_RECURRING_ACTIVE', 'name' => GetMessage('CRM_COLUMN_RECURRING_ACTIVE'), 'default' => true, 'type' => 'checkbox'),
				array('id' => 'CRM_DEAL_RECURRING_NEXT_EXECUTION', 'name' => GetMessage('CRM_COLUMN_RECURRING_NEXT_EXECUTION'), 'default' => true, 'type' => 'date'),
				array('id' => 'CRM_DEAL_RECURRING_LIMIT_DATE', 'name' => GetMessage('CRM_COLUMN_LIMIT_DATE'), 'type' => 'date'),
				array('id' => 'CRM_DEAL_RECURRING_COUNTER_REPEAT', 'name' => GetMessage('CRM_COLUMN_RECURRING_COUNTER_REPEAT'), 'type' => 'number')
			)
		);
	}

	if ($arParams['IS_RECURRING'] !== 'Y')
	{
		$utmList = \Thurly\Crm\UtmTable::getCodeNames();
		foreach ($utmList as $utmCode => $utmName)
		{
			$arResult['FILTER'][] = array(
				'id' => $utmCode,
				'name' => $utmName
			);
		}

		$CCrmUserType->PrepareListFilterFields($arResult['FILTER'], $arResult['FILTER2LOGIC']);
	}

	$currentUserID = $arResult['CURRENT_USER_ID'];
	$currentUserName = CCrmViewHelper::GetFormattedUserName($currentUserID, $arParams['NAME_TEMPLATE']);
	if ($arParams['IS_RECURRING'] !== 'Y')
	{
		$arResult['FILTER_PRESETS'] = array(
			'filter_in_work' => array(
				'name' => GetMessage('CRM_PRESET_IN_WORK'),
				'default' => true,
				'fields' => array('STAGE_SEMANTIC_ID' => array(Thurly\Crm\PhaseSemantics::PROCESS))
			),
			'filter_my' => array(
				'name' => GetMessage('CRM_PRESET_MY'),
				'fields' => array(
					'ASSIGNED_BY_ID_name' => $currentUserName,
					'ASSIGNED_BY_ID' => $currentUserID,
					'STAGE_SEMANTIC_ID' => array(Thurly\Crm\PhaseSemantics::PROCESS)
				)
			),
			'filter_won' => array(
				'name' => GetMessage('CRM_PRESET_WON'),
				'fields' => array('STAGE_SEMANTIC_ID' => array(Thurly\Crm\PhaseSemantics::SUCCESS))
			),
		);
	}
}

$arResult['HEADERS'] = array(
	array('id' => 'ID', 'name' => GetMessage('CRM_COLUMN_ID'), 'sort' => 'id', 'first_order' => 'desc', 'width' => 60, 'editable' => false, 'type' => 'int', 'class' => 'minimal'),
	array('id' => 'DEAL_SUMMARY', 'name' => GetMessage('CRM_COLUMN_DEAL'), 'sort' => 'title', 'width' => 200, 'default' => true, 'editable' => true),
	array('id' => 'CATEGORY_ID', 'name' => GetMessage('CRM_COLUMN_CATEGORY_ID'), 'sort' => 'category_id', 'default' => false),
);

if ($arParams['IS_RECURRING'] !== 'Y')
{
	if($arResult['CATEGORY_ID'] >= 0)
	{
		$arResult['HEADERS'][] = array(
			'id' => 'STAGE_ID',
			'name' => GetMessage('CRM_COLUMN_STAGE_ID'),
			'sort' => 'stage_sort',
			'width' => 200,
			'default' => true,
			'prevent_default' => false,
			'type' => 'list',
			'editable' => array('items' => $arResult['CATEGORY_STAGE_LIST'])
		);
	}
	else
	{
		$arResult['HEADERS'][] = array(
			'id' => 'STAGE_ID',
			'name' => GetMessage('CRM_COLUMN_STAGE_ID'),
			'sort' => 'stage_sort',
			'width' => 200,
			'default' => true,
			'prevent_default' => false,
			'editable' => false
		);
	}
}

// Dont display activities in INTERNAL mode.
if(!$bInternal && $arParams['IS_RECURRING'] !== 'Y')
{
	$arResult['HEADERS'][] = array(
		'id' => 'ACTIVITY_ID',
		'name' => GetMessage('CRM_COLUMN_ACTIVITY'),
		'sort' => 'nearest_activity',
		'default' => true,
		'prevent_default' => false
	);
}

if ($arParams['IS_RECURRING'] === 'Y')
{
	$arResult['HEADERS'] = array_merge(
		$arResult['HEADERS'],
		array(
			array('id' => 'DEAL_CLIENT', 'name' => GetMessage('CRM_COLUMN_CLIENT'), 'sort' => 'deal_client', 'default' => true, 'editable' => false),
			array('id' => 'CRM_DEAL_RECURRING_ACTIVE', 'name' => GetMessage('CRM_COLUMN_RECURRING_ACTIVE_TITLE'), 'sort' => 'active', 'default' => true, 'editable' => false, 'type'=>'checkbox'),
			array('id' => 'CRM_DEAL_RECURRING_COUNTER_REPEAT', 'name' => GetMessage('CRM_COLUMN_RECURRING_COUNTER_REPEAT'), 'sort' => 'counter_repeat', 'default' => true, 'editable' => false),
			array('id' => 'CRM_DEAL_RECURRING_NEXT_EXECUTION', 'name' => GetMessage('CRM_COLUMN_RECURRING_NEXT_EXECUTION'), 'sort' => 'next_execution', 'default' => true, 'editable' => false),

			array('id' => 'CRM_DEAL_RECURRING_START_DATE', 'name' => GetMessage('CRM_COLUMN_START_DATE'), 'sort' => 'start_date', 'editable' => false),
			array('id' => 'CRM_DEAL_RECURRING_LIMIT_DATE', 'name' => GetMessage('CRM_COLUMN_LIMIT_DATE'), 'sort' => 'limit_date', 'editable' => false),
			array('id' => 'CRM_DEAL_RECURRING_LIMIT_REPEAT', 'name' => GetMessage('CRM_COLUMN_LIMIT_REPEAT'), 'sort' => 'limit_repeat', 'editable' => false),

			array('id' => 'DEAL_CLIENT', 'name' => GetMessage('CRM_COLUMN_CLIENT'), 'sort' => 'deal_client', 'default' => true, 'editable' => false),
			array('id' => 'PROBABILITY', 'name' => GetMessage('CRM_COLUMN_PROBABILITY'), 'sort' => 'probability', 'first_order' => 'desc', 'editable' => true, 'align' => 'right'),
			array('id' => 'SUM', 'name' => GetMessage('CRM_COLUMN_SUM'), 'sort' => 'opportunity_account', 'first_order' => 'desc', 'default' => true, 'editable' => false, 'align' => 'right'),
			array('id' => 'ASSIGNED_BY', 'name' => GetMessage('CRM_COLUMN_ASSIGNED_BY'), 'sort' => 'assigned_by', 'default' => true, 'editable' => false, 'class' => 'username'),
			array('id' => 'ORIGINATOR_ID', 'name' => GetMessage('CRM_COLUMN_BINDING'), 'sort' => false, 'editable' => array('items' => $arResult['EXTERNAL_SALES']), 'type' => 'list'),

			array('id' => 'TITLE', 'name' => GetMessage('CRM_COLUMN_TITLE'), 'sort' => 'title', 'editable' => true),
			array('id' => 'TYPE_ID', 'name' => GetMessage('CRM_COLUMN_TYPE_ID'), 'sort' => 'type_id', 'editable' => array('items' => CCrmStatus::GetStatusList('DEAL_TYPE')), 'type' => 'list'),
			array('id' => 'OPPORTUNITY', 'name' => GetMessage('CRM_COLUMN_OPPORTUNITY'), 'sort' => 'opportunity', 'first_order' => 'desc', 'editable' => true, 'align' => 'right'),
			array('id' => 'CURRENCY_ID', 'name' => GetMessage('CRM_COLUMN_CURRENCY_ID'), 'sort' => 'currency_id', 'editable' => array('items' => CCrmCurrencyHelper::PrepareListItems()), 'type' => 'list'),
			array('id' => 'COMPANY_ID', 'name' => GetMessage('CRM_COLUMN_COMPANY_ID'), 'sort' => 'company_id', 'editable' => false),
			array('id' => 'CONTACT_ID', 'name' => GetMessage('CRM_COLUMN_CONTACT_ID'), 'sort' => 'contact_full_name', 'editable' => false),

			array('id' => 'CLOSED', 'name' => GetMessage('CRM_COLUMN_CLOSED'), 'sort' => 'closed', 'align' => 'center', 'editable' => array('items' => array('' => '', 'Y' => GetMessage('MAIN_YES'), 'N' => GetMessage('MAIN_NO'))), 'type' => 'list'),
			array('id' => 'DATE_CREATE', 'name' => GetMessage('CRM_COLUMN_DATE_CREATE'), 'sort' => 'date_create', 'first_order' => 'desc', 'default' => false, 'class' => 'date'),
			array('id' => 'CREATED_BY', 'name' => GetMessage('CRM_COLUMN_CREATED_BY'), 'sort' => 'created_by', 'editable' => false, 'class' => 'username'),
			array('id' => 'DATE_MODIFY', 'name' => GetMessage('CRM_COLUMN_DATE_MODIFY'), 'sort' => 'date_modify', 'first_order' => 'desc', 'class' => 'date'),
			array('id' => 'MODIFY_BY', 'name' => GetMessage('CRM_COLUMN_MODIFY_BY'), 'sort' => 'modify_by', 'editable' => false, 'class' => 'username'),
			array('id' => 'BEGINDATE', 'name' => GetMessage('CRM_COLUMN_BEGINDATE'), 'sort' => 'begindate', 'editable' => true, 'type' => 'date', 'class' => 'date'),
			array('id' => 'PRODUCT_ID', 'name' => GetMessage('CRM_COLUMN_PRODUCT_ID'), 'sort' => false, 'default' => $isInExportMode, 'editable' => false, 'type' => 'list'),
			array('id' => 'COMMENTS', 'name' => GetMessage('CRM_COLUMN_COMMENTS'), 'sort' => false /*because of MSSQL*/, 'editable' => false)
		)
	);
}
else
{
	$arResult['HEADERS'] = array_merge(
		$arResult['HEADERS'],
		array(
			array('id' => 'DEAL_CLIENT', 'name' => GetMessage('CRM_COLUMN_CLIENT'), 'sort' => 'deal_client', 'default' => true, 'editable' => false),
			array('id' => 'PROBABILITY', 'name' => GetMessage('CRM_COLUMN_PROBABILITY'), 'sort' => 'probability', 'first_order' => 'desc', 'editable' => true, 'align' => 'right'),
			array('id' => 'SUM', 'name' => GetMessage('CRM_COLUMN_SUM'), 'sort' => 'opportunity_account', 'first_order' => 'desc', 'default' => true, 'editable' => false, 'align' => 'right'),
			array('id' => 'ASSIGNED_BY', 'name' => GetMessage('CRM_COLUMN_ASSIGNED_BY'), 'sort' => 'assigned_by', 'default' => true, 'editable' => false, 'class' => 'username'),
			array('id' => 'ORIGINATOR_ID', 'name' => GetMessage('CRM_COLUMN_BINDING'), 'sort' => false, 'editable' => array('items' => $arResult['EXTERNAL_SALES']), 'type' => 'list'),

			array('id' => 'TITLE', 'name' => GetMessage('CRM_COLUMN_TITLE'), 'sort' => 'title', 'editable' => true),
			array('id' => 'TYPE_ID', 'name' => GetMessage('CRM_COLUMN_TYPE_ID'), 'sort' => 'type_id', 'editable' => array('items' => CCrmStatus::GetStatusList('DEAL_TYPE')), 'type' => 'list'),
			array('id' => 'OPPORTUNITY', 'name' => GetMessage('CRM_COLUMN_OPPORTUNITY'), 'sort' => 'opportunity', 'first_order' => 'desc', 'editable' => true, 'align' => 'right'),
			array('id' => 'CURRENCY_ID', 'name' => GetMessage('CRM_COLUMN_CURRENCY_ID'), 'sort' => 'currency_id', 'editable' => array('items' => CCrmCurrencyHelper::PrepareListItems()), 'type' => 'list'),
			array('id' => 'COMPANY_ID', 'name' => GetMessage('CRM_COLUMN_COMPANY_ID'), 'sort' => 'company_id', 'editable' => false),
			array('id' => 'CONTACT_ID', 'name' => GetMessage('CRM_COLUMN_CONTACT_ID'), 'sort' => 'contact_full_name', 'editable' => false),

			array('id' => 'CLOSED', 'name' => GetMessage('CRM_COLUMN_CLOSED'), 'sort' => 'closed', 'align' => 'center', 'editable' => array('items' => array('' => '', 'Y' => GetMessage('MAIN_YES'), 'N' => GetMessage('MAIN_NO'))), 'type' => 'list'),
			array('id' => 'DATE_CREATE', 'name' => GetMessage('CRM_COLUMN_DATE_CREATE'), 'sort' => 'date_create', 'first_order' => 'desc', 'default' => true, 'class' => 'date'),
			array('id' => 'CREATED_BY', 'name' => GetMessage('CRM_COLUMN_CREATED_BY'), 'sort' => 'created_by', 'editable' => false, 'class' => 'username'),
			array('id' => 'DATE_MODIFY', 'name' => GetMessage('CRM_COLUMN_DATE_MODIFY'), 'sort' => 'date_modify', 'first_order' => 'desc', 'class' => 'date'),
			array('id' => 'MODIFY_BY', 'name' => GetMessage('CRM_COLUMN_MODIFY_BY'), 'sort' => 'modify_by', 'editable' => false, 'class' => 'username'),
			array('id' => 'BEGINDATE', 'name' => GetMessage('CRM_COLUMN_BEGINDATE'), 'sort' => 'begindate', 'editable' => true, 'type' => 'date', 'class' => 'date'),
			array('id' => 'CLOSEDATE', 'name' => GetMessage('CRM_COLUMN_CLOSEDATE'), 'sort' => 'closedate', 'editable' => true, 'type' => 'date'),
			array('id' => 'PRODUCT_ID', 'name' => GetMessage('CRM_COLUMN_PRODUCT_ID'), 'sort' => false, 'default' => $isInExportMode, 'editable' => false, 'type' => 'list'),
			array('id' => 'COMMENTS', 'name' => GetMessage('CRM_COLUMN_COMMENTS'), 'sort' => false /*because of MSSQL*/, 'editable' => false),
			array('id' => 'EVENT_DATE', 'name' => GetMessage('CRM_COLUMN_EVENT_DATE'), 'sort' => 'event_date', 'default' => false),
			array('id' => 'EVENT_ID', 'name' => GetMessage('CRM_COLUMN_EVENT_ID'), 'sort' => 'event_id', 'editable' => array('items' => CCrmStatus::GetStatusList('EVENT_TYPE')), 'type' => 'list'),
			array('id' => 'EVENT_DESCRIPTION', 'name' => GetMessage('CRM_COLUMN_EVENT_DESCRIPTION'), 'sort' => false, 'editable' => false),
			array('id' => 'WEBFORM_ID', 'name' => GetMessage('CRM_COLUMN_WEBFORM'), 'sort' => 'webform_id', 'type' => 'list')
		)
	);
}
if ($arParams['IS_RECURRING'] !== 'Y')
{
	$utmList = \Thurly\Crm\UtmTable::getCodeNames();
	foreach ($utmList as $utmCode => $utmName)
	{
		$arResult['HEADERS'][] = array(
			'id' => $utmCode,
			'name' => $utmName,
			'sort' => false, 'default' => $isInExportMode, 'editable' => false
		);
	}
}

$CCrmUserType->ListAddHeaders($arResult['HEADERS']);

$arBPData = array();
if ($isBizProcInstalled)
{
	$arBPData = CBPDocument::GetWorkflowTemplatesForDocumentType(array('crm', 'CCrmDocumentDeal', 'DEAL'));
	$arDocumentStates = CBPDocument::GetDocumentStates(
		array('crm', 'CCrmDocumentDeal', 'DEAL'),
		null
	);
	foreach ($arBPData as $arBP)
	{
		if (!CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::ViewWorkflow,
			$userID,
			array('crm', 'CCrmDocumentDeal', 'DEAL'),
			array(
				'UserGroups' => $CCrmBizProc->arCurrentUserGroups,
				'DocumentStates' => $arDocumentStates,
				'WorkflowTemplateId' => $arBP['ID'],
				'UserIsAdmin' => $isAdmin
			)
		))
		{
			continue;
		}
		$arResult['HEADERS'][] = array('id' => 'BIZPROC_'.$arBP['ID'], 'name' => $arBP['NAME'], 'sort' => false, 'editable' => false);
	}

	if ($arBPData)
	{
		CJSCore::Init('bp_starter');
	}
}

//region Try to extract user action data
// We have to extract them before call of CGridOptions::GetFilter() or the custom filter will be corrupted.
$actionData = array(
	'METHOD' => $_SERVER['REQUEST_METHOD'],
	'ACTIVE' => false
);

if(check_thurly_sessid())
{
	$postAction = 'action_button_'.$arResult['GRID_ID'];
	$getAction = 'action_'.$arResult['GRID_ID'];
	//We need to check grid 'controls'
	$controls = isset($_POST['controls']) && is_array($_POST['controls']) ? $_POST['controls'] : array();
	if ($actionData['METHOD'] == 'POST' && (isset($controls[$postAction]) || isset($_POST[$postAction])))
	{
		CUtil::JSPostUnescape();

		$actionData['ACTIVE'] = true;

		if(isset($controls[$postAction]))
		{
			$actionData['NAME'] = $controls[$postAction];
		}
		else
		{
			$actionData['NAME'] = $_POST[$postAction];
			unset($_POST[$postAction], $_REQUEST[$postAction]);
		}

		$allRows = 'action_all_rows_'.$arResult['GRID_ID'];
		$actionData['ALL_ROWS'] = false;
		if(isset($controls[$allRows]))
		{
			$actionData['ALL_ROWS'] = $controls[$allRows] == 'Y';
		}
		elseif(isset($_POST[$allRows]))
		{
			$actionData['ALL_ROWS'] = $_POST[$allRows] == 'Y';
			unset($_POST[$allRows], $_REQUEST[$allRows]);
		}

		if(isset($_POST['rows']) && is_array($_POST['rows']))
		{
			$actionData['ID'] = $_POST['rows'];
		}
		elseif(isset($_POST['ID']))
		{
			$actionData['ID'] = $_POST['ID'];
			unset($_POST['ID'], $_REQUEST['ID']);
		}

		if(isset($_POST['FIELDS']))
		{
			$actionData['FIELDS'] = $_POST['FIELDS'];
			unset($_POST['FIELDS'], $_REQUEST['FIELDS']);
		}

		if(isset($_POST['ACTION_STAGE_ID']) || isset($controls['ACTION_STAGE_ID']))
		{
			if(isset($_POST['ACTION_STAGE_ID']))
			{
				$actionData['STAGE_ID'] = trim($_POST['ACTION_STAGE_ID']);
				unset($_POST['ACTION_STAGE_ID'], $_REQUEST['ACTION_STAGE_ID']);
			}
			else
			{
				$actionData['STAGE_ID'] = trim($controls['ACTION_STAGE_ID']);
			}
		}

		if(isset($_POST['ACTION_CATEGORY_ID']) || isset($controls['ACTION_CATEGORY_ID']))
		{
			if(isset($_POST['ACTION_CATEGORY_ID']))
			{
				$actionData['CATEGORY_ID'] = intval($_POST['ACTION_CATEGORY_ID']);
				unset($_POST['ACTION_CATEGORY_ID'], $_REQUEST['ACTION_CATEGORY_ID']);
			}
			else
			{
				$actionData['CATEGORY_ID'] = intval($controls['ACTION_CATEGORY_ID']);
			}
		}

		if(isset($_POST['ACTION_ASSIGNED_BY_ID']) || isset($controls['ACTION_ASSIGNED_BY_ID']))
		{
			$assignedByID = 0;
			if(isset($_POST['ACTION_ASSIGNED_BY_ID']))
			{
				if(!is_array($_POST['ACTION_ASSIGNED_BY_ID']))
				{
					$assignedByID = intval($_POST['ACTION_ASSIGNED_BY_ID']);
				}
				elseif(count($_POST['ACTION_ASSIGNED_BY_ID']) > 0)
				{
					$assignedByID = intval($_POST['ACTION_ASSIGNED_BY_ID'][0]);
				}
				unset($_POST['ACTION_ASSIGNED_BY_ID'], $_REQUEST['ACTION_ASSIGNED_BY_ID']);
			}
			else
			{
				$assignedByID = (int)$controls['ACTION_ASSIGNED_BY_ID'];
			}

			$actionData['ASSIGNED_BY_ID'] = $assignedByID;
		}

		if(isset($_POST['ACTION_OPENED']) || isset($controls['ACTION_OPENED']))
		{
			if(isset($_POST['ACTION_OPENED']))
			{
				$actionData['OPENED'] = strtoupper($_POST['ACTION_OPENED']) === 'Y' ? 'Y' : 'N';
				unset($_POST['ACTION_OPENED'], $_REQUEST['ACTION_OPENED']);
			}
			else
			{
				$actionData['OPENED'] = strtoupper($controls['ACTION_OPENED']) === 'Y' ? 'Y' : 'N';
			}
		}

		$actionData['AJAX_CALL'] = $arResult['IS_AJAX_CALL'];
	}
	elseif ($actionData['METHOD'] == 'GET' && isset($_GET[$getAction]))
	{
		$actionData['ACTIVE'] = check_thurly_sessid();

		$actionData['NAME'] = $_GET[$getAction];
		unset($_GET[$getAction], $_REQUEST[$getAction]);

		if(isset($_GET['ID']))
		{
			$actionData['ID'] = $_GET['ID'];
			unset($_GET['ID'], $_REQUEST['ID']);
		}

		$actionData['AJAX_CALL'] = $arResult['IS_AJAX_CALL'];
	}
}
//endregion

// HACK: for clear filter by CREATED_BY_ID, MODIFY_BY_ID and ASSIGNED_BY_ID
if($_SERVER['REQUEST_METHOD'] === 'GET')
{
	if(isset($_REQUEST['CREATED_BY_ID_name']) && $_REQUEST['CREATED_BY_ID_name'] === '')
	{
		$_REQUEST['CREATED_BY_ID'] = $_GET['CREATED_BY_ID'] = array();
	}

	if(isset($_REQUEST['MODIFY_BY_ID_name']) && $_REQUEST['MODIFY_BY_ID_name'] === '')
	{
		$_REQUEST['MODIFY_BY_ID'] = $_GET['MODIFY_BY_ID'] = array();
	}

	if(isset($_REQUEST['ASSIGNED_BY_ID_name']) && $_REQUEST['ASSIGNED_BY_ID_name'] === '')
	{
		$_REQUEST['ASSIGNED_BY_ID'] = $_GET['ASSIGNED_BY_ID'] = array();
	}
}

if (intval($arParams['DEAL_COUNT']) <= 0)
	$arParams['DEAL_COUNT'] = 20;

$arNavParams = array(
	'nPageSize' => $arParams['DEAL_COUNT']
);

$gridOptions = new \Thurly\Main\Grid\Options($arResult['GRID_ID'], $arResult['FILTER_PRESETS']);
$filterOptions = new \Thurly\Main\UI\Filter\Options($arResult['GRID_ID'], $arResult['FILTER_PRESETS']);
$arNavParams = $gridOptions->GetNavParams($arNavParams);
$arNavParams['bShowAll'] = false;

if(!$arResult['IS_EXTERNAL_FILTER'])
{
	$arFilter += $filterOptions->getFilter($arResult['FILTER']);
}

if(isset($arFilter['CLOSEDATE_datesel']) && $arFilter['CLOSEDATE_datesel'] === 'days' && isset($arFilter['CLOSEDATE_from']))
{
	//Issue #58007 - limit max CLOSEDATE
	$arFilter['CLOSEDATE_to'] = ConvertTimeStamp(strtotime(date("Y-m-d", time())));
}

$CCrmUserType->PrepareListFilterValues($arResult['FILTER'], $arFilter, $arResult['GRID_ID']);
$USER_FIELD_MANAGER->AdminListAddFilter(CCrmDeal::$sUFEntityID, $arFilter);

// converts data from filter
if(isset($arFilter['FIND']))
{
	if(is_string($arFilter['FIND']))
	{
		$find = trim($arFilter['FIND']);
		if($find !== '')
		{
			$arFilter['SEARCH_CONTENT'] = $find;
		}
	}
	unset($arFilter['FIND']);
}

$arFilter['=IS_RECURRING'] = ($arParams['IS_RECURRING'] === 'Y') ? "Y" : 'N';

//region Activity Counter Filter
if(isset($arFilter['ACTIVITY_COUNTER']))
{
	if(is_array($arFilter['ACTIVITY_COUNTER']))
	{
		$counterTypeID = Thurly\Crm\Counter\EntityCounterType::joinType(
			array_filter($arFilter['ACTIVITY_COUNTER'], 'is_numeric')
		);
	}
	else
	{
		$counterTypeID = (int)$arFilter['ACTIVITY_COUNTER'];
	}

	$counter = null;
	if(Thurly\Crm\Counter\EntityCounterType::isDefined($counterTypeID))
	{
		$counterUserIDs = array();
		if(isset($arFilter['ASSIGNED_BY_ID']))
		{
			if(is_array($arFilter['ASSIGNED_BY_ID']))
			{
				$counterUserIDs = array_filter($arFilter['ASSIGNED_BY_ID'], 'is_numeric');
			}
			elseif($arFilter['ASSIGNED_BY_ID'] > 0)
			{
				$counterUserIDs[] = $arFilter['ASSIGNED_BY_ID'];
			}
		}

		try
		{
			$counter = Thurly\Crm\Counter\EntityCounterFactory::create(
				CCrmOwnerType::Deal,
				$counterTypeID,
				0,
				array_merge(
					Thurly\Crm\Counter\EntityCounter::internalizeExtras($_REQUEST),
					array('DEAL_CATEGORY_ID' => $arResult['CATEGORY_ID'])
				)
			);

			$arFilter += $counter->prepareEntityListFilter(
				array(
					'MASTER_ALIAS' => CCrmDeal::TABLE_ALIAS,
					'MASTER_IDENTITY' => 'ID',
					'USER_IDS' => $counterUserIDs
				)
			);
			unset($arFilter['ASSIGNED_BY_ID']);
		}
		catch(Thurly\Main\NotSupportedException $e)
		{
		}
		catch(Thurly\Main\ArgumentException $e)
		{
		}
	}
}
//endregion

CCrmEntityHelper::PrepareMultiFieldFilter($arFilter, array(), '=%', false);
$arImmutableFilters = array(
	'FM', 'ID',
	'ASSIGNED_BY_ID', 'ASSIGNED_BY_ID_value',
	'CATEGORY_ID', 'CURRENCY_ID',
	'CONTACT_ID', 'CONTACT_ID_value', 'ASSOCIATED_CONTACT_ID',
	'COMPANY_ID', 'COMPANY_ID_value',
	'STAGE_SEMANTIC_ID',
	'CREATED_BY_ID', 'CREATED_BY_ID_value',
	'MODIFY_BY_ID', 'MODIFY_BY_ID_value',
	'PRODUCT_ROW_PRODUCT_ID', 'PRODUCT_ROW_PRODUCT_ID_value',
	'WEBFORM_ID',
	'SEARCH_CONTENT',
	'PRODUCT_ID', 'TYPE_ID', 'STAGE_ID', 'COMPANY_ID', 'CONTACT_ID',
	'FILTER_ID', 'FILTER_APPLIED', 'PRESET_ID'
);

foreach ($arFilter as $k => $v)
{
	if(in_array($k, $arImmutableFilters, true))
	{
		continue;
	}

	$arMatch = array();
	if($k === 'ORIGINATOR_ID')
	{
		// HACK: build filter by internal entities
		$arFilter['=ORIGINATOR_ID'] = $v !== '__INTERNAL' ? $v : null;
		unset($arFilter[$k]);
	}
	elseif (preg_match('/(.*)_from$/i'.BX_UTF_PCRE_MODIFIER, $k, $arMatch))
	{
		\Thurly\Crm\UI\Filter\Range::prepareFrom($arFilter, $arMatch[1], $v);
	}
	elseif (preg_match('/(.*)_to$/i'.BX_UTF_PCRE_MODIFIER, $k, $arMatch))
	{
		if ($v != '' && ($arMatch[1] == 'DATE_CREATE' || $arMatch[1] == 'DATE_MODIFY') && !preg_match('/\d{1,2}:\d{1,2}(:\d{1,2})?$/'.BX_UTF_PCRE_MODIFIER, $v))
		{
			$v = CCrmDateTimeHelper::SetMaxDayTime($v);
		}
		\Thurly\Crm\UI\Filter\Range::prepareTo($arFilter, $arMatch[1], $v);
	}
	elseif (in_array($k, $arResult['FILTER2LOGIC']))
	{
		// Bugfix #26956 - skip empty values in logical filter
		$v = trim($v);
		if($v !== '')
		{
			$arFilter['?'.$k] = $v;
		}
		unset($arFilter[$k]);
	}
	elseif ($k != 'ID' && $k != 'LOGIC' && $k != '__INNER_FILTER' && $k != '__JOINS' && $k != '__CONDITIONS' && strpos($k, 'UF_') !== 0 && preg_match('/^[^\=\%\?\>\<]{1}/', $k) === 1)
	{
		$arFilter['%'.$k] = $v;
		unset($arFilter[$k]);
	}
}

\Thurly\Crm\UI\Filter\EntityHandler::internalize($arResult['FILTER'], $arFilter);

// POST & GET actions processing -->
if($actionData['ACTIVE'])
{
	if ($actionData['METHOD'] == 'POST')
	{
		if($actionData['NAME'] == 'delete')
		{
			if ((isset($actionData['ID']) && is_array($actionData['ID'])) || $actionData['ALL_ROWS'])
			{
				$arFilterDel = array();
				if (!$actionData['ALL_ROWS'])
				{
					$arFilterDel = array('ID' => $actionData['ID']);
				}
				else
				{
					// Fix for issue #26628
					$arFilterDel += $arFilter;
				}

				$categories = array();
				$obRes = CCrmDeal::GetListEx(array(), $arFilterDel, false, false, array('ID', 'CATEGORY_ID'));
				while($arDeal = $obRes->Fetch())
				{
					$categoryID = isset($arDeal['CATEGORY_ID']) ? (int)$arDeal['CATEGORY_ID'] : 0;
					if(!isset($categories[$categoryID]))
					{
						$categories[$categoryID] = array();
					}

					$categories[$categoryID][] = $arDeal['ID'];
				}

				foreach($categories as $categoryID => $IDs)
				{
					$entityAttrs = CCrmDeal::GetPermissionAttributes($IDs, $categoryID);
					foreach($IDs as $ID)
					{
						if (!CCrmDeal::CheckDeletePermission($ID, $userPermissions, $categoryID, array('ENTITY_ATTRS' => $entityAttrs)))
						{
							continue;
						}

						$DB->StartTransaction();

						if ($CCrmBizProc->Delete($ID, $entityAttrs, array('DealCategoryId' => $categoryID))
							&& $CCrmDeal->Delete($ID, array('PROCESS_BIZPROC' => false)))
						{
							$DB->Commit();
						}
						else
						{
							$DB->Rollback();
						}
					}
				}
			}
		}
		elseif($actionData['NAME'] == 'edit')
		{
			if(isset($actionData['FIELDS']) && is_array($actionData['FIELDS']))
			{
				foreach($actionData['FIELDS'] as $ID => $arSrcData)
				{
					if (!CCrmDeal::CheckUpdatePermission($ID, $userPermissions))
					{
						continue;
					}

					$arUpdateData = array();
					reset($arResult['HEADERS']);
					foreach ($arResult['HEADERS'] as $arHead)
					{
						if (isset($arHead['editable']) && (is_array($arHead['editable']) || $arHead['editable'] === true) && isset($arSrcData[$arHead['id']]))
						{
							$arUpdateData[$arHead['id']] = $arSrcData[$arHead['id']];
						}
						if(isset($arUpdateData['DEAL_SUMMARY']))
						{
							if(!isset($arUpdateData['TITLE']))
							{
								$arUpdateData['TITLE'] = $arUpdateData['DEAL_SUMMARY'];
							}
							unset($arUpdateData['DEAL_SUMMARY']);
						}
					}
					if (!empty($arUpdateData))
					{
						$DB->StartTransaction();

						if($CCrmDeal->Update($ID, $arUpdateData))
						{
							$DB->Commit();

							$arErrors = array();
							CCrmBizProcHelper::AutoStartWorkflows(
								CCrmOwnerType::Deal,
								$ID,
								CCrmBizProcEventType::Edit,
								$arErrors
							);
						}
						else
						{
							$DB->Rollback();
						}
					}
				}
			}
		}
		elseif($actionData['NAME'] == 'tasks')
		{
			if (isset($actionData['ID']) && is_array($actionData['ID']))
			{
				$arTaskID = array();
				foreach($actionData['ID'] as $ID)
				{
					$arTaskID[] = 'D_'.$ID;
				}

				$APPLICATION->RestartBuffer();

				$taskUrl = CHTTP::urlAddParams(
					CComponentEngine::MakePathFromTemplate(
						COption::GetOptionString('tasks', 'paths_task_user_edit', ''),
						array(
							'task_id' => 0,
							'user_id' => $userID
						)
					),
					array(
						'UF_CRM_TASK' => implode(';', $arTaskID),
						'TITLE' => urlencode(GetMessage('CRM_TASK_TITLE_PREFIX')),
						'TAGS' => urlencode(GetMessage('CRM_TASK_TAG')),
						'back_url' => urlencode($arParams['PATH_TO_DEAL_LIST'])
					)
				);
				if ($actionData['AJAX_CALL'])
				{
					echo '<script> parent.window.location = "'.CUtil::JSEscape($taskUrl).'";</script>';
					exit();
				}
				else
				{
					LocalRedirect($taskUrl);
				}
			}
		}
		elseif($actionData['NAME'] == 'set_stage')
		{
			if(isset($actionData['STAGE_ID']) && $actionData['STAGE_ID'] != '') // Fix for issue #26628
			{
				$stageID = $actionData['STAGE_ID'];
				$categoryID = DealCategory::resolveFromStageID($stageID);

				$arIDs = array();
				if ($actionData['ALL_ROWS'])
				{
					$arActionFilter = $arFilter;
					$arActionFilter['CHECK_PERMISSIONS'] = 'N'; // Ignore 'WRITE' permission - we will check it before update.

					$dbRes = CCrmDeal::GetListEx(
						array(),
						$arActionFilter,
						false,
						false,
						array('ID', 'CATEGORY_ID')
					);
					while($arDeal = $dbRes->Fetch())
					{
						if($arDeal['CATEGORY_ID'] == $categoryID)
						{
							$arIDs[] = $arDeal['ID'];
						}
					}
				}
				elseif (isset($actionData['ID']) && is_array($actionData['ID']))
				{
					$dbRes = CCrmDeal::GetListEx(
						array(),
						array('@ID' => $actionData['ID'], 'CHECK_PERMISSIONS' => 'N'),
						false,
						false,
						array('ID', 'CATEGORY_ID')
					);
					while($arDeal = $dbRes->Fetch())
					{
						if($arDeal['CATEGORY_ID'] == $categoryID)
						{
							$arIDs[] = $arDeal['ID'];
						}
					}
				}

				$arEntityAttr = $userPermissions->GetEntityAttr('DEAL', $arIDs);
				foreach($arIDs as $ID)
				{
					if(!CCrmDeal::CheckUpdatePermission($ID, $userPermissions, $categoryID))
					{
						continue;
					}

					$DB->StartTransaction();

					$arUpdateData = array('STAGE_ID' => $stageID);
					if($CCrmDeal->Update($ID, $arUpdateData, true, true, array('DISABLE_USER_FIELD_CHECK' => true)))
					{
						$DB->Commit();

						$arErrors = array();
						CCrmBizProcHelper::AutoStartWorkflows(
							CCrmOwnerType::Deal,
							$ID,
							CCrmBizProcEventType::Edit,
							$arErrors
						);

						//Region automation
						\Thurly\Crm\Automation\Factory::runOnStatusChanged(\CCrmOwnerType::Deal, $ID);
						//end region
					}
					else
					{
						$DB->Rollback();
					}
				}
			}
		}
		elseif($actionData['NAME'] == 'assign_to')
		{
			if(isset($actionData['ASSIGNED_BY_ID']))
			{
				$arIDs = array();
				if ($actionData['ALL_ROWS'])
				{
					$arActionFilter = $arFilter;
					$arActionFilter['CHECK_PERMISSIONS'] = 'N'; // Ignore 'WRITE' permission - we will check it before update.
					$dbRes = CCrmDeal::GetListEx(array(), $arActionFilter, false, false, array('ID'));
					while($arDeal = $dbRes->Fetch())
					{
						$arIDs[] = $arDeal['ID'];
					}
				}
				elseif (isset($actionData['ID']) && is_array($actionData['ID']))
				{
					$arIDs = $actionData['ID'];
				}

				foreach($arIDs as $ID)
				{
					if (!CCrmDeal::CheckUpdatePermission($ID, $userPermissions))
					{
						continue;
					}

					$DB->StartTransaction();

					$arUpdateData = array(
						'ASSIGNED_BY_ID' => $actionData['ASSIGNED_BY_ID']
					);

					if($CCrmDeal->Update($ID, $arUpdateData, true, true, array('DISABLE_USER_FIELD_CHECK' => true)))
					{
						$DB->Commit();

						$arErrors = array();
						CCrmBizProcHelper::AutoStartWorkflows(
							CCrmOwnerType::Deal,
							$ID,
							CCrmBizProcEventType::Edit,
							$arErrors
						);
					}
					else
					{
						$DB->Rollback();
					}
				}
			}
		}
		elseif($actionData['NAME'] == 'mark_as_opened')
		{
			if(isset($actionData['OPENED']) && $actionData['OPENED'] != '')
			{
				$isOpened = strtoupper($actionData['OPENED']) === 'Y' ? 'Y' : 'N';
				$arIDs = array();
				if ($actionData['ALL_ROWS'])
				{
					$arActionFilter = $arFilter;
					$arActionFilter['CHECK_PERMISSIONS'] = 'N'; // Ignore 'READ' permission - we will check it before update.

					$dbRes = CCrmDeal::GetListEx(
						array(),
						$arActionFilter,
						false,
						false,
						array('ID', 'CATEGORY_ID', 'OPENED')
					);

					while($arDeal = $dbRes->Fetch())
					{
						if(isset($arDeal['OPENED']) && $arDeal['OPENED'] === $isOpened)
						{
							continue;
						}

						$ID = (int)$arDeal['ID'];
						$categoryID = isset($arDeal['CATEGORY_ID']) ? (int)$arDeal['CATEGORY_ID'] : 0;
						if(CCrmDeal::CheckUpdatePermission($ID, $userPermissions, $categoryID))
						{
							$arIDs[] = $ID;
						}
					}
				}
				elseif (isset($actionData['ID']) && is_array($actionData['ID']))
				{
					$dbRes = CCrmDeal::GetListEx(
						array(),
						array('@ID'=> $actionData['ID'], 'CHECK_PERMISSIONS' => 'N'),
						false,
						false,
						array('ID', 'CATEGORY_ID', 'OPENED')
					);

					while($arDeal = $dbRes->Fetch())
					{
						if(isset($arDeal['OPENED']) && $arDeal['OPENED'] === $isOpened)
						{
							continue;
						}

						$ID = (int)$arDeal['ID'];
						$categoryID = isset($arDeal['CATEGORY_ID']) ? (int)$arDeal['CATEGORY_ID'] : 0;
						if(CCrmDeal::CheckUpdatePermission($ID, $userPermissions, $categoryID))
						{
							$arIDs[] = $ID;
						}
					}
				}

				foreach($arIDs as $ID)
				{
					$DB->StartTransaction();
					$arUpdateData = array('OPENED' => $isOpened);
					if($CCrmDeal->Update($ID, $arUpdateData, true, true, array('DISABLE_USER_FIELD_CHECK' => true)))
					{
						$DB->Commit();

						CCrmBizProcHelper::AutoStartWorkflows(
							CCrmOwnerType::Deal,
							$ID,
							CCrmBizProcEventType::Edit,
							$arErrors
						);
					}
					else
					{
						$DB->Rollback();
					}
				}
			}
		}
		elseif($actionData['NAME'] == 'refresh_account')
		{
			$arIDs = array();
			if ($actionData['ALL_ROWS'])
			{
				$arActionFilter = $arFilter;
				$arActionFilter['CHECK_PERMISSIONS'] = 'N'; // Ignore 'READ' permission - we will check it before update.

				$dbRes = CCrmDeal::GetListEx(
					array(),
					$arActionFilter,
					false,
					false,
					array('ID', 'CATEGORY_ID')
				);

				while($arDeal = $dbRes->Fetch())
				{
					$ID = (int)$arDeal['ID'];
					$categoryID = isset($arDeal['CATEGORY_ID']) ? (int)$arDeal['CATEGORY_ID'] : 0;
					if(CCrmDeal::CheckUpdatePermission($ID, $userPermissions, $categoryID))
					{
						$arIDs[] = $ID;
					}
				}
			}
			elseif (isset($actionData['ID']) && is_array($actionData['ID']))
			{
				$dbRes = CCrmDeal::GetListEx(
					array(),
					array('@ID'=> $actionData['ID'], 'CHECK_PERMISSIONS' => 'N'),
					false,
					false,
					array('ID', 'CATEGORY_ID')
				);

				while($arDeal = $dbRes->Fetch())
				{
					$ID = (int)$arDeal['ID'];
					$categoryID = isset($arDeal['CATEGORY_ID']) ? (int)$arDeal['CATEGORY_ID'] : 0;
					if(CCrmDeal::CheckUpdatePermission($ID, $userPermissions, $categoryID))
					{
						$arIDs[] = $ID;
					}
				}
			}

			CCrmDeal::RefreshAccountingData($arIDs);
		}
		elseif($actionData['NAME'] == 'move_to_category')
		{
			if(isset($actionData['CATEGORY_ID']) && $actionData['CATEGORY_ID'] >= 0 && $arResult['CATEGORY_ID'] >= 0)
			{
				$categoryID = $arResult['CATEGORY_ID'];
				$newCategoryID = $actionData['CATEGORY_ID'];

				$arIDs = array();
				if ($actionData['ALL_ROWS'])
				{
					$arActionFilter = $arFilter;
					if(!isset($arActionFilter['CATEGORY_ID']))
					{
						$arActionFilter['CATEGORY_ID'] = $categoryID;
					}
					$arActionFilter['CHECK_PERMISSIONS'] = 'N'; // Ignore 'WRITE' permission - we will check it before update.

					$dbRes = CCrmDeal::GetListEx(
						array(),
						$arActionFilter,
						false,
						false,
						array('ID')
					);
					while($arDeal = $dbRes->Fetch())
					{
						$arIDs[] = $arDeal['ID'];
					}
				}
				elseif (isset($actionData['ID']) && is_array($actionData['ID']))
				{
					$dbRes = CCrmDeal::GetListEx(
						array(),
						array('@ID' => $actionData['ID'], 'CHECK_PERMISSIONS' => 'N'),
						false,
						false,
						array('ID', 'CATEGORY_ID')
					);
					while($arDeal = $dbRes->Fetch())
					{
						if($arDeal['CATEGORY_ID'] == $categoryID)
						{
							$arIDs[] = $arDeal['ID'];
						}
					}
				}

				$hasErrors = false;
				foreach($arIDs as $ID)
				{
					if(!CCrmDeal::CheckUpdatePermission($ID, $userPermissions, $categoryID))
					{
						continue;
					}

					$DB->StartTransaction();
					try
					{
						$error = CCrmDeal::MoveToCategory($ID, $newCategoryID);
						if($error !== \Thurly\Crm\Category\DealCategoryChangeError::NONE)
						{
							$hasErrors = true;
							continue;
						}
						Thurly\Crm\Automation\Factory::runOnStatusChanged(CCrmOwnerType::Deal, $ID);
						$DB->Commit();
					}
					catch(Exception $e)
					{
						$DB->Rollback();
					}
				}

				if($hasErrors)
				{
					$arResult['ERRORS'][] = array(
						'TITLE' => GetMessage('CRM_MOVE_TO_CATEGORY_ERROR_TITLE'),
						'TEXT' => GetMessage('CRM_MOVE_TO_CATEGORY_ERROR_TEXT')
					);
				}
			}
		}
		if (!$actionData['AJAX_CALL'])
		{
			LocalRedirect($arParams['PATH_TO_CURRENT_LIST']);
		}
	}
	else//if ($actionData['METHOD'] == 'GET')
	{
		if ($actionData['NAME'] == 'delete' && isset($actionData['ID']))
		{
			$ID = (int)$actionData['ID'];
			$categoryID = CCrmDeal::GetCategoryID($ID);
			$entityAttrs = CCrmDeal::GetPermissionAttributes(array($ID), $categoryID);
			if(CCrmDeal::CheckDeletePermission($ID, $userPermissions, -1, array('ENTITY_ATTRS' => $entityAttrs)))
			{
				$DB->StartTransaction();

				if($CCrmBizProc->Delete($ID, $entityAttrs, array('DealCategoryId' => $categoryID))
					&& $CCrmDeal->Delete($ID, array('PROCESS_BIZPROC' => false)))
				{
					$DB->Commit();
				}
				else
				{
					$DB->Rollback();
				}
			}
		}

		if (!$actionData['AJAX_CALL'])
		{
			if($bInternal)
			{
				LocalRedirect('?'.$arParams['FORM_ID'].'_active_tab=tab_deal');
			}
			elseif($arResult['CATEGORY_ID'] >= 0)
			{
				LocalRedirect(
					CComponentEngine::makePathFromTemplate(
						$arParams['PATH_TO_DEAL_CATEGORY'],
						array('category_id' => $arResult['CATEGORY_ID'])
					)
				);
			}
			else
			{
				LocalRedirect($arParams['PATH_TO_CURRENT_LIST']);
			}
		}
	}
}
// <-- POST & GET actions processing
$_arSort = $gridOptions->GetSorting(array(
	'sort' => array('date_create' => 'desc'),
	'vars' => array('by' => 'by', 'order' => 'order')
));
$arResult['SORT'] = !empty($arSort) ? $arSort : $_arSort['sort'];
$arResult['SORT_VARS'] = $_arSort['vars'];

// Remove column for deleted UF
$arSelect = $gridOptions->GetVisibleColumns();

if ($CCrmUserType->NormalizeFields($arSelect))
	$gridOptions->SetVisibleColumns($arSelect);

$arResult['ENABLE_BIZPROC'] = $arResult['IS_BIZPROC_AVAILABLE'] = $isBizProcInstalled;
$arResult['ENABLE_TASK'] = IsModuleInstalled('tasks');
if($arResult['ENABLE_TASK'])
{
	$arResult['TASK_CREATE_URL'] = CHTTP::urlAddParams(
		CComponentEngine::MakePathFromTemplate(
			COption::GetOptionString('tasks', 'paths_task_user_edit', ''),
			array(
				'task_id' => 0,
				'user_id' => $userID
			)
		),
		array(
			'UF_CRM_TASK' => '#ENTITY_KEYS#',
			'TITLE' => urlencode(GetMessage('CRM_TASK_TITLE_PREFIX')),
			'TAGS' => urlencode(GetMessage('CRM_TASK_TAG')),
			'back_url' => urlencode($arParams['PATH_TO_DEAL_LIST'])
		)
	);
}
// Fill in default values if empty
if (empty($arSelect))
{
	foreach ($arResult['HEADERS'] as $arHeader)
	{
		if ($arHeader['default'])
		{
			$arSelect[] = $arHeader['id'];
		}
	}

	//Disable bizproc fields processing
	$arResult['ENABLE_BIZPROC'] = false;
}
else
{
	if($arResult['ENABLE_BIZPROC'])
	{
		//Check if bizproc fields selected
		$hasBizprocFields = false;
		foreach($arSelect as &$fieldName)
		{
			if(substr($fieldName, 0, 8) === 'BIZPROC_')
			{
				$hasBizprocFields = true;
				break;
			}
		}
		$arResult['ENABLE_BIZPROC'] = $hasBizprocFields;
	}
	unset($fieldName);
}

$arSelectedHeaders = $arSelect;

if(!in_array('TITLE', $arSelect, true))
{
	//Is required for activities management
	$arSelect[] = 'TITLE';
}

if(in_array('CREATED_BY', $arSelect, true))
{
	$addictFields = array(
		'CREATED_BY_LOGIN', 'CREATED_BY_NAME', 'CREATED_BY_LAST_NAME', 'CREATED_BY_SECOND_NAME'
	);
	$arSelect = array_merge($arSelect,$addictFields);
	unset($addictFields);
}

if(in_array('MODIFY_BY', $arSelect, true))
{
	$addictFields = array(
		'MODIFY_BY_LOGIN', 'MODIFY_BY_NAME', 'MODIFY_BY_LAST_NAME', 'MODIFY_BY_SECOND_NAME'
	);
	$arSelect = array_merge($arSelect,$addictFields);
	unset($addictFields);
}

if(in_array('DEAL_SUMMARY', $arSelect, true))
{
	//$arSelect[] = 'TITLE';
	$arSelect[] = 'TYPE_ID';
}

if(in_array('ACTIVITY_ID', $arSelect, true))
{
	$arSelect[] = 'ACTIVITY_TIME';
	$arSelect[] = 'ACTIVITY_SUBJECT';
	$arSelect[] = 'C_ACTIVITY_ID';
	$arSelect[] = 'C_ACTIVITY_TIME';
	$arSelect[] = 'C_ACTIVITY_SUBJECT';
	$arSelect[] = 'C_ACTIVITY_RESP_ID';
	$arSelect[] = 'C_ACTIVITY_RESP_LOGIN';
	$arSelect[] = 'C_ACTIVITY_RESP_NAME';
	$arSelect[] = 'C_ACTIVITY_RESP_LAST_NAME';
	$arSelect[] = 'C_ACTIVITY_RESP_SECOND_NAME';
}

if(in_array('SUM', $arSelect, true))
{
	$arSelect[] = 'OPPORTUNITY';
	$arSelect[] = 'CURRENCY_ID';
}

$addictFields = array();

if(in_array('DEAL_CLIENT', $arSelect, true))
{
	$addictFields = array(
		'CONTACT_ID', 'COMPANY_ID', 'COMPANY_TITLE', 'CONTACT_HONORIFIC',
		'CONTACT_NAME', 'CONTACT_SECOND_NAME','CONTACT_LAST_NAME'
	);
}
else
{
	if(in_array('CONTACT_ID', $arSelect, true))
	{
		$addictFields = array(
			'CONTACT_ID', 'CONTACT_HONORIFIC', 'CONTACT_NAME', 'CONTACT_SECOND_NAME','CONTACT_LAST_NAME'
		);
	}
	if(in_array('COMPANY_ID', $arSelect, true))
	{
		$arSelect[] = 'COMPANY_TITLE';
	}
}

$arSelect = array_merge($arSelect, $addictFields);
unset($addictFields);

// Always need to remove the menu items
if (!in_array('STAGE_ID', $arSelect))
	$arSelect[] = 'STAGE_ID';

if (!in_array('CATEGORY_ID', $arSelect))
	$arSelect[] = 'CATEGORY_ID';

if (!in_array('STAGE_SEMANTIC_ID', $arSelect))
	$arSelect[] = 'STAGE_SEMANTIC_ID';

// For bizproc
if (!in_array('ASSIGNED_BY', $arSelect))
	$arSelect[] = 'ASSIGNED_BY';

if (!in_array('ASSIGNED_BY_ID', $arSelect))
	$arSelect[] = 'ASSIGNED_BY_ID';

// For preparing user html
if (!in_array('ASSIGNED_BY_LOGIN', $arSelect))
	$arSelect[] =  'ASSIGNED_BY_LOGIN';

if (!in_array('ASSIGNED_BY_NAME', $arSelect))
	$arSelect[] =  'ASSIGNED_BY_NAME';

if (!in_array('ASSIGNED_BY_LAST_NAME', $arSelect))
	$arSelect[] =  'ASSIGNED_BY_LAST_NAME';

if (!in_array('ASSIGNED_BY_SECOND_NAME', $arSelect))
	$arSelect[] =  'ASSIGNED_BY_SECOND_NAME';

// ID must present in select
if(!in_array('ID', $arSelect))
{
	$arSelect[] = 'ID';
}

if ($isInExportMode)
{
	$productHeaderIndex = array_search('PRODUCT_ID', $arSelectedHeaders, true);
	$productRowsEnabled = \Thurly\Crm\Settings\DealSettings::getCurrent()->isProductRowExportEnabled();

	if($productHeaderIndex <= 0 && $productRowsEnabled)
	{
		$arSelectedHeaders[] = 'PRODUCT_ID';
	}
	elseif($productHeaderIndex > 0 && !$productRowsEnabled)
	{
		unset($arSelectedHeaders[$productHeaderIndex]);
		$arSelectedHeaders = array_values($arSelectedHeaders);
	}

	CCrmComponentHelper::PrepareExportFieldsList(
		$arSelectedHeaders,
		array(
			'DEAL_SUMMARY' => array(
				'TITLE',
				'TYPE_ID'
			),
			'DEAL_CLIENT' => array(
				'CONTACT_ID',
				'COMPANY_ID'
			),
			'SUM' => array(
				'OPPORTUNITY',
				'CURRENCY_ID'
			),
			'ACTIVITY_ID' => array()
		)
	);

	if(!in_array('ID', $arSelectedHeaders))
	{
		$arSelectedHeaders[] = 'ID';
	}

	if(!in_array('CATEGORY_ID', $arSelectedHeaders))
	{
		$arSelectedHeaders[] = 'CATEGORY_ID';
	}

	$arResult['SELECTED_HEADERS'] = $arSelectedHeaders;
}

$nTopCount = false;
if ($isInGadgetMode)
{
	$arSelect = array(
		'DATE_CREATE', 'TITLE', 'STAGE_ID', 'TYPE_ID',
		'OPPORTUNITY', 'CURRENCY_ID', 'COMMENTS',
		'CONTACT_ID',  'CONTACT_HONORIFIC', 'CONTACT_NAME', 'CONTACT_SECOND_NAME',
		'CONTACT_LAST_NAME', 'COMPANY_ID', 'COMPANY_TITLE'
	);
	$nTopCount = $arParams['DEAL_COUNT'];
}

if($nTopCount > 0)
{
	$arNavParams['nTopCount'] = $nTopCount;
}

if ($isInExportMode)
	$arFilter['PERMISSION'] = 'EXPORT';

// HACK: Make custom sort for ASSIGNED_BY field
$arSort = $arResult['SORT'];
if(isset($arSort['assigned_by']))
{
	$arSort['assigned_by_last_name'] = $arSort['assigned_by'];
	$arSort['assigned_by_name'] = $arSort['assigned_by'];
	$arSort['assigned_by_login'] = $arSort['assigned_by'];
	unset($arSort['assigned_by']);
}

$arOptions = $arExportOptions = array('FIELD_OPTIONS' => array('ADDITIONAL_FIELDS' => array()));
if(in_array('ACTIVITY_ID', $arSelect, true))
{
	$arOptions['FIELD_OPTIONS']['ADDITIONAL_FIELDS'][] = 'ACTIVITY';
	$arExportOptions['FIELD_OPTIONS']['ADDITIONAL_FIELDS'][] = 'ACTIVITY';
}
if(isset($arSort['stage_sort']))
{
	$arOptions['FIELD_OPTIONS']['ADDITIONAL_FIELDS'][] = 'STAGE_SORT';
	$arExportOptions['FIELD_OPTIONS']['ADDITIONAL_FIELDS'][] = 'STAGE_SORT';

	if($arResult['CATEGORY_ID'] > 0)
	{
		$arOptions['FIELD_OPTIONS']['CATEGORY_ID'] = $arResult['CATEGORY_ID'];
		$arExportOptions['FIELD_OPTIONS']['CATEGORY_ID'] = $arResult['CATEGORY_ID'];
	}
}

if(isset($arSort['contact_full_name']))
{
	$arSort['contact_last_name'] = $arSort['contact_full_name'];
	$arSort['contact_name'] = $arSort['contact_full_name'];
	unset($arSort['contact_full_name']);
}
if(isset($arSort['deal_client']))
{
	$arSort['contact_last_name'] = $arSort['deal_client'];
	$arSort['contact_name'] = $arSort['deal_client'];
	$arSort['company_title'] = $arSort['deal_client'];
	unset($arSort['deal_client']);
}

if(isset($arParams['IS_EXTERNAL_CONTEXT']))
{
	$arOptions['IS_EXTERNAL_CONTEXT'] = $arParams['IS_EXTERNAL_CONTEXT'];
}

//FIELD_OPTIONS
$arSelect = array_unique($arSelect, SORT_STRING);

$arResult['DEAL'] = array();
$arResult['DEAL_ID'] = array();
$arResult['CATEGORIES'] = array();
$arResult['DEAL_UF'] = array();

//region Navigation data initialization
$pageNum = 0;
$pageSize = !$isInExportMode
	? (int)(isset($arNavParams['nPageSize']) ? $arNavParams['nPageSize'] : $arParams['DEAL_COUNT']) : 0;
$enableNextPage = false;
if(isset($_REQUEST['apply_filter']) && $_REQUEST['apply_filter'] === 'Y')
{
	$pageNum = 1;
}
elseif($pageSize > 0 && isset($_REQUEST['page']))
{
	$pageNum = (int)$_REQUEST['page'];
	if($pageNum < 0)
	{
		//Backward mode
		$offset = -($pageNum + 1);
		$total = CCrmDeal::GetListEx(array(), $arFilter, array());
		$pageNum = (int)(ceil($total / $pageSize)) - $offset;
		if($pageNum <= 0)
		{
			$pageNum = 1;
		}
	}
}

if($pageNum > 0)
{
	if(!isset($_SESSION['CRM_PAGINATION_DATA']))
	{
		$_SESSION['CRM_PAGINATION_DATA'] = array();
	}
	$_SESSION['CRM_PAGINATION_DATA'][$arResult['GRID_ID']] = array('PAGE_NUM' => $pageNum, 'PAGE_SIZE' => $pageSize);
}
else
{
	if(!$bInternal
		&& !(isset($_REQUEST['clear_nav']) && $_REQUEST['clear_nav'] === 'Y')
		&& isset($_SESSION['CRM_PAGINATION_DATA'])
		&& isset($_SESSION['CRM_PAGINATION_DATA'][$arResult['GRID_ID']])
	)
	{
		$paginationData = $_SESSION['CRM_PAGINATION_DATA'][$arResult['GRID_ID']];
		if(isset($paginationData['PAGE_NUM'])
			&& isset($paginationData['PAGE_SIZE'])
			&& $paginationData['PAGE_SIZE'] == $pageSize
		)
		{
			$pageNum = (int)$paginationData['PAGE_NUM'];
		}
	}

	if($pageNum <= 0)
	{
		$pageNum  = 1;
	}
}
//endregion

if ($arParams['IS_RECURRING'] === 'Y')
{
	$arOptions['FIELD_OPTIONS']['ADDITIONAL_FIELDS'][] = 'RECURRING';
	$recurringSortedFields = array('active', 'counter_repeat', 'next_execution', 'start_date', 'limit_date', 'limit_repeat');
	foreach ($recurringSortedFields as $fieldName)
	{
		if (isset($arSort[$fieldName]))
		{
			$arSort['crm_deal_recurring_'.$fieldName] = $arSort[$fieldName];
			unset($arSort[$fieldName]);
		}
	}
}

if(!isset($arSort['nearest_activity']))
{
	if ($isInGadgetMode && isset($arNavParams['nTopCount']))
	{
		$navListOptions = array_merge($arOptions, array('QUERY_OPTIONS' => array('LIMIT' => $arNavParams['nTopCount'])));
	}
	else
	{
		$navListOptions = $isInExportMode
			? $arExportOptions
			: array_merge(
				$arOptions,
				array('QUERY_OPTIONS' => array('LIMIT' => $pageSize + 1, 'OFFSET' => $pageSize * ($pageNum - 1)))
			);
	}

	$dbResult = CCrmDeal::GetListEx(
		$arSort,
		$arFilter,
		false,
		false,
		$arSelect,
		$navListOptions
	);

	$qty = 0;
	while($arDeal = $dbResult->GetNext())
	{
		if($pageSize > 0 && ++$qty > $pageSize)
		{
			$enableNextPage = true;
			break;
		}

		$arResult['DEAL'][$arDeal['ID']] = $arDeal;
		$arResult['DEAL_ID'][$arDeal['ID']] = $arDeal['ID'];
		$arResult['DEAL_UF'][$arDeal['ID']] = array();

		$categoryID = isset($arDeal['CATEGORY_ID']) ? (int)$arDeal['CATEGORY_ID'] : 0;
		if(!isset($arResult['CATEGORIES'][$categoryID]))
		{
			$arResult['CATEGORIES'][$categoryID] = array();
		}
		$arResult['CATEGORIES'][$categoryID][] = $arDeal['ID'];
	}

	//region Navigation data storing
	$arResult['PAGINATION'] = array('PAGE_NUM' => $pageNum, 'ENABLE_NEXT_PAGE' => $enableNextPage);
	$arResult['DB_FILTER'] = $arFilter;

	if(!isset($_SESSION['CRM_GRID_DATA']))
	{
		$_SESSION['CRM_GRID_DATA'] = array();
	}
	$_SESSION['CRM_GRID_DATA'][$arResult['GRID_ID']] = array('FILTER' => $arFilter);
	//endregion
}
else
{
	$navListOptions = $isInExportMode
		? $arExportOptions
		: array_merge(
			$arOptions,
			array('QUERY_OPTIONS' => array('LIMIT' => $pageSize + 1, 'OFFSET' => $pageSize * ($pageNum - 1)))
		);

	$navDbResult = CCrmActivity::GetEntityList(
		CCrmOwnerType::Deal,
		$userID,
		$arSort['nearest_activity'],
		$arFilter,
		false,
		$navListOptions
	);

	$qty = 0;
	while($arDeal = $navDbResult->Fetch())
	{
		if($pageSize > 0 && ++$qty > $pageSize)
		{
			$enableNextPage = true;
			break;
		}

		$arResult['DEAL'][$arDeal['ID']] = $arDeal;
		$arResult['DEAL_ID'][$arDeal['ID']] = $arDeal['ID'];
		$arResult['DEAL_UF'][$arDeal['ID']] = array();
	}

	//region Navigation data storing
	$arResult['PAGINATION'] = array('PAGE_NUM' => $pageNum, 'ENABLE_NEXT_PAGE' => $enableNextPage);
	$arResult['DB_FILTER'] = $arFilter;
	if(!isset($_SESSION['CRM_GRID_DATA']))
	{
		$_SESSION['CRM_GRID_DATA'] = array();
	}
	$_SESSION['CRM_GRID_DATA'][$arResult['GRID_ID']] = array('FILTER' => $arFilter);
	//endregion

	$entityIDs = array_keys($arResult['DEAL']);
	if(!empty($entityIDs))
	{
		//Permissions are already checked.
		$dbResult = CCrmDeal::GetListEx(
			$arSort,
			array('@ID' => $entityIDs, 'CHECK_PERMISSIONS' => 'N'),
			false,
			false,
			$arSelect,
			$arOptions
		);
		while($arDeal = $dbResult->GetNext())
		{
			$arResult['DEAL'][$arDeal['ID']] = $arDeal;

			$categoryID = isset($arDeal['CATEGORY_ID']) ? (int)$arDeal['CATEGORY_ID'] : 0;
			if(!isset($arResult['CATEGORIES'][$categoryID]))
			{
				$arResult['CATEGORIES'][$categoryID] = array();
			}
			$arResult['CATEGORIES'][$categoryID][] = $arDeal['ID'];
		}
	}
}

$arResult['PAGINATION']['URL'] = $APPLICATION->GetCurPageParam('', array('apply_filter', 'clear_filter', 'save', 'page', 'sessid', 'internal'));
$enableExportEvent = $isInExportMode && HistorySettings::getCurrent()->isExportEventEnabled();
$now = time() + CTimeZone::GetOffset();
$aclivitylessItems = array();
foreach($arResult['DEAL'] as &$arDeal)
{
	$entityID = $arDeal['ID'];
	if($enableExportEvent)
	{
		CCrmEvent::RegisterExportEvent(CCrmOwnerType::Deal, $entityID, $userID);
	}

	$arDeal['CLOSEDATE'] = !empty($arDeal['CLOSEDATE']) ? CCrmComponentHelper::TrimDateTimeString(ConvertTimeStamp(MakeTimeStamp($arDeal['CLOSEDATE']), 'SHORT', SITE_ID)) : '';
	$arDeal['BEGINDATE'] = !empty($arDeal['BEGINDATE']) ? CCrmComponentHelper::TrimDateTimeString(ConvertTimeStamp(MakeTimeStamp($arDeal['BEGINDATE']), 'SHORT', SITE_ID)) : '';
	$arDeal['EVENT_DATE'] = !empty($arDeal['EVENT_DATE']) ? CCrmComponentHelper::TrimDateTimeString(ConvertTimeStamp(MakeTimeStamp($arDeal['EVENT_DATE']), 'SHORT', SITE_ID)) : '';
	$arDeal['~CLOSEDATE'] = $arDeal['CLOSEDATE'];
	$arDeal['~BEGINDATE'] = $arDeal['BEGINDATE'];
	$arDeal['~EVENT_DATE'] = $arDeal['EVENT_DATE'];

	$currencyID =  isset($arDeal['~CURRENCY_ID']) ? $arDeal['~CURRENCY_ID'] : CCrmCurrency::GetBaseCurrencyID();
	$arDeal['~CURRENCY_ID'] = $currencyID;
	$arDeal['CURRENCY_ID'] = htmlspecialcharsbx($currencyID);

	$arDeal['FORMATTED_OPPORTUNITY'] = CCrmCurrency::MoneyToString($arDeal['~OPPORTUNITY'], $arDeal['~CURRENCY_ID']);

	$arDeal['PATH_TO_DEAL_DETAILS'] = CComponentEngine::MakePathFromTemplate(
		$arParams['PATH_TO_DEAL_DETAILS'],
		array('deal_id' => $entityID)
	);

	if($arResult['ENABLE_SLIDER'])
	{
		$arDeal['PATH_TO_DEAL_SHOW'] = $arDeal['PATH_TO_DEAL_DETAILS'];
		$arDeal['PATH_TO_DEAL_EDIT'] = CCrmUrlUtil::AddUrlParams(
			$arDeal['PATH_TO_DEAL_DETAILS'],
			array('init_mode' => 'edit')
		);
	}
	else
	{
		$arDeal['PATH_TO_DEAL_SHOW'] = CComponentEngine::makePathFromTemplate(
			($arParams['IS_RECURRING']  !== 'Y') ? $arParams['PATH_TO_DEAL_SHOW'] : $arParams['PATH_TO_DEAL_RECUR_SHOW'],
			array('deal_id' => $entityID)
		);

		$arDeal['PATH_TO_DEAL_EDIT'] = CComponentEngine::makePathFromTemplate(
			($arParams['IS_RECURRING']  !== 'Y') ? $arParams['PATH_TO_DEAL_EDIT'] : $arParams['PATH_TO_DEAL_RECUR_EDIT'],
			array('deal_id' => $entityID)
		);
	}

	$arDeal['PATH_TO_DEAL_COPY'] =  CHTTP::urlAddParams(
		$arDeal['PATH_TO_DEAL_EDIT'],
		array('copy' => 1)
	);

	if($arResult['CATEGORY_ID'] >= 0)
	{
		$arDeal['PATH_TO_DEAL_DELETE'] = CHTTP::urlAddParams(
				CComponentEngine::makePathFromTemplate(
					$arParams['PATH_TO_DEAL_CATEGORY'],
					array('category_id' => $arResult['CATEGORY_ID'])
				),
				array('action_'.$arResult['GRID_ID'] => 'delete', 'ID' => $entityID, 'sessid' => $arResult['SESSION_ID'])
			);
	}
	else
	{
		$arDeal['PATH_TO_DEAL_DELETE'] =  CHTTP::urlAddParams(
			$bInternal ? $APPLICATION->GetCurPage() : $arParams['PATH_TO_CURRENT_LIST'],
			array('action_'.$arResult['GRID_ID'] => 'delete', 'ID' => $entityID, 'sessid' => $arResult['SESSION_ID'])
		);
	}

	$contactID = isset($arDeal['~CONTACT_ID']) ? intval($arDeal['~CONTACT_ID']) : 0;
	$arDeal['PATH_TO_CONTACT_SHOW'] = $contactID <= 0 ? ''
		: CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_CONTACT_SHOW'], array('contact_id' => $contactID));

	$arDeal['~CONTACT_FORMATTED_NAME'] = $contactID <= 0 ? ''
		: CCrmContact::PrepareFormattedName(
				array(
					'HONORIFIC' => isset($arDeal['~CONTACT_HONORIFIC']) ? $arDeal['~CONTACT_HONORIFIC'] : '',
					'NAME' => isset($arDeal['~CONTACT_NAME']) ? $arDeal['~CONTACT_NAME'] : '',
					'LAST_NAME' => isset($arDeal['~CONTACT_LAST_NAME']) ? $arDeal['~CONTACT_LAST_NAME'] : '',
					'SECOND_NAME' => isset($arDeal['~CONTACT_SECOND_NAME']) ? $arDeal['~CONTACT_SECOND_NAME'] : ''
				)
			);
	$arDeal['CONTACT_FORMATTED_NAME'] = htmlspecialcharsbx($arDeal['~CONTACT_FORMATTED_NAME']);

	$arDeal['~CONTACT_FULL_NAME'] = $contactID <= 0 ? ''
		: CCrmContact::GetFullName(
			array(
				'HONORIFIC' => isset($arDeal['~CONTACT_HONORIFIC']) ? $arDeal['~CONTACT_HONORIFIC'] : '',
				'NAME' => isset($arDeal['~CONTACT_NAME']) ? $arDeal['~CONTACT_NAME'] : '',
				'LAST_NAME' => isset($arDeal['~CONTACT_LAST_NAME']) ? $arDeal['~CONTACT_LAST_NAME'] : '',
				'SECOND_NAME' => isset($arDeal['~CONTACT_SECOND_NAME']) ? $arDeal['~CONTACT_SECOND_NAME'] : ''
			)
		);
	$arDeal['CONTACT_FULL_NAME'] = htmlspecialcharsbx($arDeal['~CONTACT_FULL_NAME']);

	$companyID = isset($arDeal['~COMPANY_ID']) ? intval($arDeal['~COMPANY_ID']) : 0;
	$arDeal['PATH_TO_COMPANY_SHOW'] = $companyID <= 0 ? ''
		: CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_COMPANY_SHOW'], array('company_id' => $companyID));

	$arDeal['PATH_TO_USER_PROFILE'] = CComponentEngine::MakePathFromTemplate(
		$arParams['PATH_TO_USER_PROFILE'],
		array('user_id' => $arDeal['ASSIGNED_BY'])
	);
	$arDeal['PATH_TO_USER_BP'] = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER_BP'],
		array('user_id' => $userID)
	);

	if (!empty($arDeal['CREATED_BY_ID']))
	{
		$arDeal['CREATED_BY'] = $arDeal['~CREATED_BY'] = $arDeal['CREATED_BY_ID'];
	}
	$arDeal['PATH_TO_USER_CREATOR'] = CComponentEngine::MakePathFromTemplate(
		$arParams['PATH_TO_USER_PROFILE'],
		array('user_id' => $arDeal['CREATED_BY'])
	);

	if (!empty($arDeal['MODIFY_BY_ID']))
	{
		$arDeal['MODIFY_BY'] = $arDeal['~MODIFY_BY'] = $arDeal['MODIFY_BY_ID'];
	}
	$arDeal['PATH_TO_USER_MODIFIER'] = CComponentEngine::MakePathFromTemplate(
		$arParams['PATH_TO_USER_PROFILE'],
		array('user_id' => $arDeal['MODIFY_BY'])
	);

	$arDeal['CREATED_BY_FORMATTED_NAME'] = CUser::FormatName(
		$arParams['NAME_TEMPLATE'],
		array(
			'LOGIN' => $arDeal['CREATED_BY_LOGIN'],
			'NAME' => $arDeal['CREATED_BY_NAME'],
			'LAST_NAME' => $arDeal['CREATED_BY_LAST_NAME'],
			'SECOND_NAME' => $arDeal['CREATED_BY_SECOND_NAME']
		),
		true, false
	);

	$arDeal['MODIFY_BY_FORMATTED_NAME'] = CUser::FormatName(
		$arParams['NAME_TEMPLATE'],
		array(
			'LOGIN' => $arDeal['MODIFY_BY_LOGIN'],
			'NAME' => $arDeal['MODIFY_BY_NAME'],
			'LAST_NAME' => $arDeal['MODIFY_BY_LAST_NAME'],
			'SECOND_NAME' => $arDeal['MODIFY_BY_SECOND_NAME']
		),
		true, false
	);

	$typeID = isset($arDeal['TYPE_ID']) ? $arDeal['TYPE_ID'] : '';
	$arDeal['DEAL_TYPE_NAME'] = isset($arResult['TYPE_LIST'][$typeID]) ? $arResult['TYPE_LIST'][$typeID] : $typeID;

	$stageID = $arDeal['STAGE_ID'] = isset($arDeal['STAGE_ID']) ? $arDeal['STAGE_ID'] : '';
	$categoryID = $arDeal['CATEGORY_ID'] = isset($arDeal['CATEGORY_ID']) ? (int)$arDeal['CATEGORY_ID'] : 0;
	$arDeal['DEAL_STAGE_NAME'] = CCrmDeal::GetStageName($stageID, $categoryID);
	$arDeal['~DEAL_CATEGORY_NAME'] = DealCategory::getName($categoryID);
	$arDeal['DEAL_CATEGORY_NAME'] = htmlspecialcharsbx($arDeal['~DEAL_CATEGORY_NAME']);

	//region Client info
	if($contactID > 0)
	{
		$arDeal['CONTACT_INFO'] = array(
			'ENTITY_TYPE_ID' => CCrmOwnerType::Contact,
			'ENTITY_ID' => $contactID
		);

		if(!CCrmContact::CheckReadPermission($contactID, $userPermissions))
		{
			$arDeal['CONTACT_INFO']['IS_HIDDEN'] = true;
		}
		else
		{
			$arDeal['CONTACT_INFO'] =
				array_merge(
					$arDeal['CONTACT_INFO'],
					array(
						'TITLE' => isset($arDeal['~CONTACT_FORMATTED_NAME']) ? $arDeal['~CONTACT_FORMATTED_NAME'] : ('['.$contactID.']'),
						'PREFIX' => "DEAL_{$arDeal['~ID']}",
						'DESCRIPTION' => isset($arDeal['~COMPANY_TITLE']) ? $arDeal['~COMPANY_TITLE'] : ''
					)
				);
		}
	}
	if($companyID > 0)
	{
		$arDeal['COMPANY_INFO'] = array(
			'ENTITY_TYPE_ID' => CCrmOwnerType::Company,
			'ENTITY_ID' => $companyID
		);

		if(!CCrmCompany::CheckReadPermission($companyID, $userPermissions))
		{
			$arDeal['COMPANY_INFO']['IS_HIDDEN'] = true;
		}
		else
		{
			$arDeal['COMPANY_INFO'] =
				array_merge(
					$arDeal['COMPANY_INFO'],
					array(
						'TITLE' => isset($arDeal['~COMPANY_TITLE']) ? $arDeal['~COMPANY_TITLE'] : ('['.$companyID.']'),
						'PREFIX' => "DEAL_{$arDeal['~ID']}"
					)
				);
		}
	}

	if(isset($arDeal['CONTACT_INFO']))
	{
		$arDeal['CLIENT_INFO'] = $arDeal['CONTACT_INFO'];
	}
	elseif(isset($arDeal['COMPANY_INFO']))
	{
		$arDeal['CLIENT_INFO'] = $arDeal['COMPANY_INFO'];
	}
	//endregion

	if(isset($arDeal['~ACTIVITY_TIME']))
	{
		$time = MakeTimeStamp($arDeal['~ACTIVITY_TIME']);
		$arDeal['~ACTIVITY_EXPIRED'] = $time <= $now;
		$arDeal['~ACTIVITY_IS_CURRENT_DAY'] = $arDeal['~ACTIVITY_EXPIRED'] || CCrmActivity::IsCurrentDay($time);
	}

	$originatorID = isset($arDeal['~ORIGINATOR_ID']) ? $arDeal['~ORIGINATOR_ID'] : '';
	if($originatorID !== '')
	{
		$arDeal['~ORIGINATOR_NAME'] = isset($arResult['EXTERNAL_SALES'][$originatorID])
			? $arResult['EXTERNAL_SALES'][$originatorID] : '';

		$arDeal['ORIGINATOR_NAME'] = htmlspecialcharsbx($arDeal['~ORIGINATOR_NAME']);
	}

	if ($arResult['ENABLE_TASK'])
	{
		$arDeal['PATH_TO_TASK_EDIT'] = CHTTP::urlAddParams(
			CComponentEngine::MakePathFromTemplate(COption::GetOptionString('tasks', 'paths_task_user_edit', ''),
				array('task_id' => 0, 'user_id' => $userID)
			),
			array(
				'UF_CRM_TASK' => "D_{$entityID}",
				'TITLE' => urlencode(GetMessage('CRM_TASK_TITLE_PREFIX').' '),
				'TAGS' => urlencode(GetMessage('CRM_TASK_TAG')),
				'back_url' => urlencode($arParams['PATH_TO_DEAL_LIST'])
			)
		);
	}

	if (IsModuleInstalled('sale'))
	{
		$arDeal['PATH_TO_QUOTE_ADD'] =
			CHTTP::urlAddParams(
				CComponentEngine::makePathFromTemplate(
					$arParams['PATH_TO_QUOTE_EDIT'],
					array('quote_id' => 0)
				),
				array('deal_id' => $entityID)
			);
		$arDeal['PATH_TO_INVOICE_ADD'] =
			CHTTP::urlAddParams(
				CComponentEngine::makePathFromTemplate(
					$arParams['PATH_TO_INVOICE_EDIT'],
					array('invoice_id' => 0)
				),
				array('deal' => $entityID)
			);
	}

	if ($arResult['ENABLE_BIZPROC'])
	{
		$arDeal['BIZPROC_STATUS'] = '';
		$arDeal['BIZPROC_STATUS_HINT'] = '';

		$arDocumentStates = CBPDocument::GetDocumentStates(
			array('crm', 'CCrmDocumentDeal', 'DEAL'),
			array('crm', 'CCrmDocumentDeal', "DEAL_{$entityID}")
		);

		$arDeal['PATH_TO_BIZPROC_LIST'] =  CHTTP::urlAddParams(CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_DEAL_SHOW'],
			array(
				'deal_id' => $entityID
			)),
			array('CRM_DEAL_SHOW_V12_active_tab' => 'tab_bizproc')
		);

		$totalTaskQty = 0;
		$docStatesQty = count($arDocumentStates);
		if($docStatesQty === 1)
		{
			$arDocState = $arDocumentStates[array_shift(array_keys($arDocumentStates))];

			$docTemplateID = $arDocState['TEMPLATE_ID'];
			$paramName = "BIZPROC_{$docTemplateID}";
			$docTtl = isset($arDocState['STATE_TITLE']) ? $arDocState['STATE_TITLE'] : '';
			$docName = isset($arDocState['STATE_NAME']) ? $arDocState['STATE_NAME'] : '';
			$docTemplateName = isset($arDocState['TEMPLATE_NAME']) ? $arDocState['TEMPLATE_NAME'] : '';

			if($isInExportMode)
			{
				$arDeal[$paramName] = $docTtl;
			}
			else
			{
				$arDeal[$paramName] = '<a href="'.htmlspecialcharsbx($arDeal['PATH_TO_BIZPROC_LIST']).'">'.htmlspecialcharsbx($docTtl).'</a>';

				$docID = $arDocState['ID'];
				$taskQty = CCrmBizProcHelper::GetUserWorkflowTaskCount(array($docID), $userID);
				if($taskQty > 0)
				{
					$totalTaskQty += $taskQty;
				}

				$arDeal['BIZPROC_STATUS'] = $taskQty > 0 ? 'attention' : 'inprogress';
				$arDeal['BIZPROC_STATUS_HINT'] =
					'<div class=\'bizproc-item-title\'>'.
						htmlspecialcharsbx($docTemplateName !== '' ? $docTemplateName : GetMessage('CRM_BPLIST')).
						': <span class=\'bizproc-item-title bizproc-state-title\'><a href=\''.$arDeal['PATH_TO_BIZPROC_LIST'].'\'>'.
						htmlspecialcharsbx($docTtl !== '' ? $docTtl : $docName).'</a></span></div>';
			}
		}
		elseif($docStatesQty > 1)
		{
			foreach ($arDocumentStates as &$arDocState)
			{
				$docTemplateID = $arDocState['TEMPLATE_ID'];
				$paramName = "BIZPROC_{$docTemplateID}";
				$docTtl = isset($arDocState['STATE_TITLE']) ? $arDocState['STATE_TITLE'] : '';

				if($isInExportMode)
				{
					$arDeal[$paramName] = $docTtl;
				}
				else
				{
					$arDeal[$paramName] = '<a href="'.htmlspecialcharsbx($arDeal['PATH_TO_BIZPROC_LIST']).'">'.htmlspecialcharsbx($docTtl).'</a>';

					$docID = $arDocState['ID'];
					//TODO: wait for bizproc bugs will be fixed and replace serial call of CCrmBizProcHelper::GetUserWorkflowTaskCount on single call
					$taskQty = CCrmBizProcHelper::GetUserWorkflowTaskCount(array($docID), $userID);
					if($taskQty === 0)
					{
						continue;
					}

					if ($arDeal['BIZPROC_STATUS'] !== 'attention')
					{
						$arDeal['BIZPROC_STATUS'] = 'attention';
					}

					$totalTaskQty += $taskQty;
					if($totalTaskQty > 5)
					{
						break;
					}
				}
			}
			unset($arDocState);

			if(!$isInExportMode)
			{
				$arDeal['BIZPROC_STATUS_HINT'] =
					'<span class=\'bizproc-item-title\'>'.GetMessage('CRM_BP_R_P').': <a href=\''.$arDeal['PATH_TO_BIZPROC_LIST'].'\' title=\''.GetMessage('CRM_BP_R_P_TITLE').'\'>'.$docStatesQty.'</a></span>'.
					($totalTaskQty === 0
						? ''
						: '<br /><span class=\'bizproc-item-title\'>'.GetMessage('CRM_TASKS').': <a href=\''.$arDeal['PATH_TO_USER_BP'].'\' title=\''.GetMessage('CRM_TASKS_TITLE').'\'>'.$totalTaskQty.($totalTaskQty > 5 ? '+' : '').'</a></span>');
			}
		}
	}

	if (!isset($arDeal['ASSIGNED_BY_ID']))
		$arDeal['ASSIGNED_BY_ID'] = $arDeal['~ASSIGNED_BY_ID'] = isset($arDeal['~ASSIGNED_BY']) ? (int)$arDeal['~ASSIGNED_BY'] : 0;

	$arDeal['~ASSIGNED_BY'] = CUser::FormatName(
		$arParams['NAME_TEMPLATE'],
		array(
			'LOGIN' => isset($arDeal['~ASSIGNED_BY_LOGIN']) ? $arDeal['~ASSIGNED_BY_LOGIN'] : '',
			'NAME' => isset($arDeal['~ASSIGNED_BY_NAME']) ? $arDeal['~ASSIGNED_BY_NAME'] : '',
			'LAST_NAME' => isset($arDeal['~ASSIGNED_BY_LAST_NAME']) ? $arDeal['~ASSIGNED_BY_LAST_NAME'] : '',
			'SECOND_NAME' => isset($arDeal['~ASSIGNED_BY_SECOND_NAME']) ? $arDeal['~ASSIGNED_BY_SECOND_NAME'] : ''
		),
		true, false
	);
	$arDeal['ASSIGNED_BY'] = htmlspecialcharsbx($arDeal['~ASSIGNED_BY']);
	if(isset($arDeal['~TITLE']))
	{
		$arDeal['DEAL_SUMMARY'] = $arDeal['~TITLE'];
	}

	$userActivityID = isset($arDeal['~ACTIVITY_ID']) ? intval($arDeal['~ACTIVITY_ID']) : 0;
	$commonActivityID = isset($arDeal['~C_ACTIVITY_ID']) ? intval($arDeal['~C_ACTIVITY_ID']) : 0;
	if($userActivityID <= 0 && $commonActivityID <= 0)
	{
		$aclivitylessItems[] = $entityID;
	}

	$arResult['DEAL'][$entityID] = $arDeal;
}
unset($arDeal);

if(!empty($aclivitylessItems))
{
	$waitingInfos = \Thurly\Crm\Pseudoactivity\WaitEntry::getRecentInfos(CCrmOwnerType::Deal, $aclivitylessItems);
	foreach($waitingInfos as $waitingInfo)
	{
		$entityID = (int)$waitingInfo['OWNER_ID'];
		if(isset($arResult['DEAL'][$entityID]))
		{
			$arResult['DEAL'][$entityID]['~WAITING_TITLE'] = $waitingInfo['TITLE'];
		}
	}
}

$CCrmUserType->ListAddEnumFieldsValue(
	$arResult,
	$arResult['DEAL'],
	$arResult['DEAL_UF'],
	($isInExportMode ? ', ' : '<br />'),
	$isInExportMode,
	array(
		'FILE_URL_TEMPLATE' =>
			'/thurly/components/thurly/crm.deal.show/show_file.php?ownerId=#owner_id#&fieldName=#field_name#&fileId=#file_id#'
	)
);

$arResult['ENABLE_TOOLBAR'] = isset($arParams['ENABLE_TOOLBAR']) ? $arParams['ENABLE_TOOLBAR'] : false;
if($arResult['ENABLE_TOOLBAR'])
{
	$arResult['PATH_TO_DEAL_ADD'] = CComponentEngine::MakePathFromTemplate(
		$arParams['PATH_TO_DEAL_EDIT'],
		array('deal_id' => 0)
	);

	$addParams = array();

	if($bInternal && isset($arParams['INTERNAL_CONTEXT']) && is_array($arParams['INTERNAL_CONTEXT']))
	{
		$internalContext = $arParams['INTERNAL_CONTEXT'];
		if(isset($internalContext['CONTACT_ID']))
		{
			$addParams['contact_id'] = $internalContext['CONTACT_ID'];
		}
		if(isset($internalContext['COMPANY_ID']))
		{
			$addParams['company_id'] = $internalContext['COMPANY_ID'];
		}
	}

	if(!empty($addParams))
	{
		$arResult['PATH_TO_DEAL_ADD'] = CHTTP::urlAddParams(
			$arResult['PATH_TO_DEAL_ADD'],
			$addParams
		);
	}
}

if (isset($arResult['DEAL_ID']) && !empty($arResult['DEAL_ID']))
{
	// try to load product rows
	$arProductRows = CCrmDeal::LoadProductRows(array_keys($arResult['DEAL_ID']));
	foreach($arProductRows as $arProductRow)
	{
		$ownerID = $arProductRow['OWNER_ID'];
		if(!isset($arResult['DEAL'][$ownerID]))
		{
			continue;
		}

		$arEntity = &$arResult['DEAL'][$ownerID];
		if(!isset($arEntity['PRODUCT_ROWS']))
		{
			$arEntity['PRODUCT_ROWS'] = array();
		}
		$arEntity['PRODUCT_ROWS'][] = $arProductRow;
	}
}

foreach($arResult['CATEGORIES'] as $categoryID => $IDs)
{
	// checkig access for operation
	$entityAttrs = CCrmDeal::GetPermissionAttributes($IDs, $categoryID);
	foreach($IDs as $ID)
	{
		$arResult['DEAL'][$ID]['EDIT'] = CCrmDeal::CheckUpdatePermission(
			$ID,
			$userPermissions,
			$categoryID,
			array('ENTITY_ATTRS' => $entityAttrs)
		);
		$arResult['DEAL'][$ID]['DELETE'] = CCrmDeal::CheckDeletePermission(
			$ID,
			$userPermissions,
			$categoryID,
			array('ENTITY_ATTRS' => $entityAttrs)
		);

		$arResult['DEAL'][$ID]['BIZPROC_LIST'] = array();
		if ($isBizProcInstalled)
		{
			foreach ($arBPData as $arBP)
			{
				if (!CBPDocument::CanUserOperateDocument(
					CBPCanUserOperateOperation::StartWorkflow,
					$userID,
					array('crm', 'CCrmDocumentDeal', 'DEAL_'.$arResult['DEAL'][$ID]['ID']),
					array(
						'UserGroups' => $CCrmBizProc->arCurrentUserGroups,
						'DocumentStates' => $arDocumentStates,
						'WorkflowTemplateId' => $arBP['ID'],
						'CreatedBy' => $arResult['DEAL'][$ID]['~ASSIGNED_BY_ID'],
						'UserIsAdmin' => $isAdmin,
						'DealCategoryId' => $categoryID,
						'CRMEntityAttr' => $entityAttrs
					)
				))
				{
					continue;
				}

				$arBP['PATH_TO_BIZPROC_START'] = CHTTP::urlAddParams(CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_DEAL_SHOW'],
					array(
						'deal_id' => $arResult['DEAL'][$ID]['ID']
					)),
					array(
						'workflow_template_id' => $arBP['ID'], 'bizproc_start' => 1,  'sessid' => $arResult['SESSION_ID'],
						'CRM_DEAL_SHOW_V12_active_tab' => 'tab_bizproc', 'back_url' => $arParams['PATH_TO_DEAL_LIST'])
				);

				if (isset($arBP['HAS_PARAMETERS']))
				{
					$params = \Thurly\Main\Web\Json::encode(array(
						'moduleId' => 'crm',
						'entity' => 'CCrmDocumentDeal',
						'documentType' => 'DEAL',
						'documentId' => 'DEAL_'.$arResult['DEAL'][$ID]['ID'],
						'templateId' => $arBP['ID'],
						'templateName' => $arBP['NAME'],
						'hasParameters' => $arBP['HAS_PARAMETERS']
					));
					$arBP['ONCLICK'] = 'BX.Bizproc.Starter.singleStart('.$params
						.', function(){BX.Main.gridManager.reload(\''.CUtil::JSEscape($arResult['GRID_ID']).'\');});';
				}

				$arResult['DEAL'][$ID]['BIZPROC_LIST'][] = $arBP;
			}
		}
	}
}

if (!$isInExportMode)
{
	$arResult['NEED_FOR_REBUILD_DEAL_ATTRS'] =
		$arResult['NEED_FOR_REBUILD_DEAL_SEMANTICS'] =
		$arResult['NEED_FOR_REBUILD_SEARCH_CONTENT'] =
		$arResult['NEED_FOR_BUILD_TIMELINE'] = false;

	if(!$bInternal)
	{
		if(COption::GetOptionString('crm', '~CRM_REBUILD_DEAL_SEARCH_CONTENT', 'N') === 'Y')
		{
			$arResult['NEED_FOR_REBUILD_SEARCH_CONTENT'] = true;
		}

		$arResult['NEED_FOR_BUILD_TIMELINE'] = $arParams['IS_RECURRING'] === 'Y'
			? \Thurly\Crm\Agent\Timeline\RecurringDealTimelineBuildAgent::getInstance()->isEnabled()
			: \Thurly\Crm\Agent\Timeline\DealTimelineBuildAgent::getInstance()->isEnabled();

		$arResult['NEED_FOR_REFRESH_ACCOUNTING'] = \Thurly\Crm\Agent\Accounting\DealAccountSyncAgent::getInstance()->isEnabled();

		if(CCrmPerms::IsAdmin())
		{
			if(COption::GetOptionString('crm', '~CRM_REBUILD_DEAL_ATTR', 'N') === 'Y')
			{
				$arResult['PATH_TO_PRM_LIST'] = CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_perm_list'));
				$arResult['NEED_FOR_REBUILD_DEAL_ATTRS'] = true;
			}
			if(COption::GetOptionString('crm', '~CRM_REBUILD_DEAL_SEMANTICS', 'N') === 'Y')
			{
				$arResult['NEED_FOR_REBUILD_DEAL_SEMANTICS'] = true;
			}
		}
	}

	$this->IncludeComponentTemplate();
	include_once($_SERVER['DOCUMENT_ROOT'].'/thurly/components/thurly/crm.deal/include/nav.php');
	return $arResult['ROWS_COUNT'];
}
else
{
	$APPLICATION->RestartBuffer();
	// hack. any '.default' customized template should contain 'excel' page
	$this->__templateName = '.default';

	if($sExportType === 'carddav')
	{
		Header('Content-Type: text/vcard');
	}
	elseif($sExportType === 'csv')
	{
		Header('Content-Type: text/csv');
		Header('Content-Disposition: attachment;filename=deals.csv');
	}
	elseif($sExportType === 'excel')
	{
		Header('Content-Type: application/vnd.ms-excel');
		Header('Content-Disposition: attachment;filename=deals.xls');
	}
	Header('Content-Type: application/octet-stream');
	Header('Content-Transfer-Encoding: binary');

	// add UTF-8 BOM marker
	if (defined('BX_UTF') && BX_UTF)
		echo chr(239).chr(187).chr(191);

	$this->IncludeComponentTemplate($sExportType);

	die();
}
?>
