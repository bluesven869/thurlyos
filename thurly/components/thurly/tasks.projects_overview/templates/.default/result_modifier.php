<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

// create template controller with js-dependency injections
$arResult['HELPER'] = $helper = require(dirname(__FILE__).'/helper.php');
$arParams =& $helper->getComponent(
)->arParams; // make $arParams the same variable as $this->__component->arParams, as it really should be

if ($helper->checkHasFatals())
{
	return;
}

//region TITLE
$sTitle = $sTitleShort = GetMessage("TASKS_TITLE_PROJECTS_OVERVIEW");
$APPLICATION->SetPageProperty("title", $sTitle);
$APPLICATION->SetTitle($sTitleShort);
//endregion TITLE

$arResult['TEMPLATE_DATA'] = array(// contains data generated in result_modifier.php
);
//$arResult['JS_DATA'] = array(// everything you put here, will be accessible inside js controller through this.option('keyName')
//);

if (!function_exists('prepareTaskRow'))
{
	function prepareProjectRow($row, $arParams)
	{
		$resultRow = array(
			'ID' => $row['GROUP_ID'],
			'PROJECT' => prepareProjectRowTitle($row, $arParams),
			'PROJECT_DATE_START' => $row['PROJECT_DATE_START'] ? date('d.m.Y', strtotime($row['PROJECT_DATE_START'])) : '',
			'PROJECT_DATE_FINISH' => $row['PROJECT_DATE_START'] ? date('d.m.Y', strtotime($row['PROJECT_DATE_FINISH'])) : '',

			'IN_WORK' => prepareProjectRowInWork($row, $arParams),
			'COMPLETE' => '<a href="'.$row['PATHES']['COMPLETE'].'">'.$row['COUNTERS']['COMPLETE'].'</a>',
			'ALL' => '<a href="'.$row['PATHES']['ALL'].'">'.$row['COUNTERS']['ALL'].'</a>',
			'EFFECTIVE' =>$row['COUNTERS']['EFFECTIVE'].'%',
		);

		return $resultRow;
	}
}

if (!function_exists('prepareProjectRowTitle'))
{
	function prepareProjectRowTitle($row, $arParams)
	{
		$image = \Thurly\Tasks\UI::getAvatarFile($row['IMAGE_ID'], array('WIDTH' => 50, 'HEIGHT' => 50));

		$out = '<div class="tasks-project-owerview-container">';

		$out .= '<div class="tasks-project-overview-group-avatar">';

		$out .= '<a class="tasks-proj-avatar" href="'.$row['PATHES']['TO_GROUP'].'">';
		if ($image)
		{
			$out .= '<img src="'.$image['RESIZED']['SRC'].'"  />';

		}
		$out .= '</a>';
		$out .= '</div>';

		$out .= '<div class="tasks-project-overview-title">';
		$out .= '<a class="tasks-project-overview-title-link" href="'.$row['PATHES']['TO_GROUP'].'">'.htmlspecialcharsbx($row['NAME']).'</a>';

		$out .= '<span class="tasks-project-overview-members">';

		$out .= GetMessage('TASKS_PROJECTS_OVERVIEW_HEADS_'.(int)count($row['MEMBERS']['HEADS'])>1);

		if($row['MEMBERS']['HEADS'])
		{
			foreach ($row['MEMBERS']['HEADS'] as $member)
			{
				$photoSrc = getUserPictureSrc($member['PHOTO_ID'], $member['USER_GENDER'], 25, 25);
				$out .= '<a  href="'.
						$member['HREF'].'" class="tasks-project-overview-member-avatar">';
				if ($photoSrc)
				{
					$out .= '<img src="'.$photoSrc.'" />';
				}
				$out .= '</a>';
			}
		}

		$countMembers = count($row['MEMBERS']['MEMBERS']);
		if($countMembers > 0)
		{

			$out .= '<span class="tasks-project-overview-members-additional-popup">'.CTasksTools::getMessagePlural(
				$countMembers,
				'TASKS_PROJECT_OVERVIEW_MEMBERS_COUNT',
				array(
					'#ID#'=>'tasks-project-overviews-'.$row['GROUP_ID'],
					'#COUNT#'=>$countMembers,
					'#GROUP_ID#'=>$row['GROUP_ID']
				)
			).'</span>';
		}
		$out .= '</span></div></div>';

		return $out;
	}
}

if (!function_exists('getUserPictureSrc'))
{
	function getUserPictureSrc($photoId, $gender = '?', $width = 100, $height = 100)
	{
		static $cache = array();

		$key = $photoId.'.'.$width.'.'.$height;

		if (!array_key_exists($key, $cache))
		{
			$src = false;

			if ($photoId > 0)
			{
				$imageFile = CFile::GetFileArray($photoId);
				if ($imageFile !== false)
				{
					$tmpImage = CFile::ResizeImageGet(
						$imageFile,
						array("width" => $width, "height" => $height),
						BX_RESIZE_IMAGE_EXACT,
						false
					);
					$src = $tmpImage["src"];
				}

				$cache[$key] = $src;
			}
		}

		return $cache[$key];
	}
}

if (!function_exists('prepareProjectRowInWork'))
{
	function prepareProjectRowInWork($row, $arParams)
	{
		return '<a href="'.
			   $row['PATHES']['IN_WORK'].
			   '">'.
			   $row['COUNTERS']['IN_WORK'].
			   '</a>'.
			   ($row['COUNTERS']['EXPIRED'] > 0 ? '<span class="tasks-project-overview-expired">'.
												  $row['COUNTERS']['EXPIRED'].
												  '</span>' : '');
	}
}

$arResult['ROWS'] = array();
if (!empty($arResult['GROUPS']))
{
	foreach ($arResult['GROUPS'] as $row)
	{
		$rowItem = array(
			"id" => $row["GROUP_ID"],
			'columns' => prepareProjectRow($row, $arParams)
		);

		$arResult['ROWS'][] = $rowItem;
	}
}