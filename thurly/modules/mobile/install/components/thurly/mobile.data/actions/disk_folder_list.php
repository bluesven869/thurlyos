<?
use Thurly\Disk\Driver;
use Thurly\Disk\File;
use Thurly\Disk\Folder;
use Thurly\Main\Application;
use Thurly\Main\Localization\Loc;
use Thurly\Main\Text\Encoding;
use Thurly\Main\Type\Collection;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if(!CModule::IncludeModule("disk"))
{
	return array();
}
if(empty($_REQUEST['entityId']) || empty($_REQUEST['type'])  || empty($_REQUEST['path']) )
{
	$data = array('status' => 'not_found');

	return $data;
}

function mobileDiskPrepareForJson($string)
{
	if(!Application::getInstance()->isUtfMode())
	{
		return Encoding::convertEncodingArray($string, SITE_CHARSET, 'UTF-8');
	}
	return $string;
}

CUtil::JSPostUnescape();
$data = array();
$type = $_REQUEST['type'];
$path = urldecode($_REQUEST['path']);

if($type == 'user')
{
	$entityId = (int)$_REQUEST['entityId'];
	$storage = Driver::getInstance()->getStorageByUserId($entityId);
	if(!$storage)
	{
		$data = array('status' => 'not_found');

		return $data;
	}
}
elseif($type == 'common')
{
	$entityId = $_REQUEST['entityId'];
	$storage = Driver::getInstance()->getStorageByCommonId($entityId);
	if(!$storage)
	{
		$data = array('status' => 'not_found');

		return $data;
	}
}
elseif($type == 'group')
{
	$entityId = (int)$_REQUEST['entityId'];
	$storage = Driver::getInstance()->getStorageByGroupId($entityId);
	if(!$storage)
	{
		$data = array('status' => 'not_found');

		return $data;
	}
}
else
{
	$data = array('status' => 'not_found');

	return $data;
}
$urlManager = Driver::getInstance()->getUrlManager();
$currentFolderId = $urlManager->resolveFolderIdFromPath($storage, $path);
/** @var Folder $folder */
$folder = Folder::loadById($currentFolderId);
if(!$folder)
{
	$data = array('status' => 'not_found');

	return $data;
}

$securityContext = $storage->getCurrentUserSecurityContext();
$items = array();
$countFolders = $countFiles = 0;
foreach($folder->getChildren($securityContext) as $item)
{
	/** @var File|Folder $item */
	$isFolder = $item instanceof Folder;
	if($isFolder)
	{
		$icon = CMobileHelper::mobileDiskGetIconByFilename($item->getName());
		$items[] = array(
			'NAME' => mobileDiskPrepareForJson($item->getName()),
			'UPDATE_TIME' => $item->getUpdateTime()->getTimestamp(),
			'TABLE_URL' => SITE_DIR . 'mobile/index.php?' .
					'&mobile_action=' . 'disk_folder_list'.
					'&path=' . (mobileDiskPrepareForJson($path . '/' . $item->getName())).
					'&entityId=' . $entityId.
					'&type=' . $type
			,
			'IMAGE' => CComponentEngine::makePathFromTemplate('/thurly/components/thurly/mobile.disk.file.detail/images/folder.png'),
			'TABLE_SETTINGS' => array(
				'type' => 'files',
				'useTagsInSearch' => 'NO',
			),
		);
		$countFolders++;
	}
	else
	{
		$icon = CMobileHelper::mobileDiskGetIconByFilename($item->getName());
		$itemData = array(
			'ID' => $item->getId(),
			'VALUE' => \Thurly\Disk\Uf\FileUserType::NEW_FILE_PREFIX.$item->getId(),
			'UPDATE_TIME' => $item->getUpdateTime()->getTimestamp(),
			'NAME' => mobileDiskPrepareForJson($item->getName()),
			'URL' => array(
				'URL' => SITE_DIR . "mobile/ajax.php?mobile_action=disk_download_file&action=downloadFile&fileId={$item->getId()}&filename=" . mobileDiskPrepareForJson($item->getName()),
				'EXTERNAL' => 'YES',
			),
			'IMAGE' => CComponentEngine::makePathFromTemplate('/thurly/components/thurly/mobile.disk.file.detail/images/' . $icon),
			'TAGS' => mobileDiskPrepareForJson(\CFile::FormatSize($item->getSize()) . ' ' . $item->getUpdateTime()),
		);

		if (\Thurly\Disk\TypeFile::isImage($item))
		{
			$signature = \Thurly\Disk\Security\ParameterSigner::getImageSignature($item->getId(), 250, 250);
			$previewUrl = SITE_DIR . "mobile/ajax.php?" . http_build_query(array(
				'mobile_action' => 'disk_download_file',
				'action' => 'showFile',
				'fileId' => $item->getId(),
				'width' => 250,
				'height' => 250,
				'signature' => $signature,
				'filename' => $item->getName(),
			));
			$itemData['URL']['PREVIEW'] = $previewUrl;
			$itemData['IMAGE'] = $previewUrl;
		}

		$items[] = $itemData;

		$countFiles++;
	}
}
unset($item);

Collection::sortByColumn($items, array('UPDATE_TIME' => SORT_DESC));

$data = array(
	"data" => $items,
	"TABLE_SETTINGS" => array(
		'folderId' => $folder->getId(),
		'storageId' => $folder->getStorageId(),
		'allowUpload'=>"YES",
		'footer' => mobileDiskPrepareForJson(Loc::getMessage('MD_DISK_TABLE_FOLDERS_FILES', array(
			'#FOLDERS#' => $countFolders,
			'#FILES#' => $countFiles,
		))),
	),
);

return $data;
