<?

namespace Thurly\Disk\Ui\Preview;

use Thurly\Disk\Folder;

class ExternalLink
{
	/**
	 * Returns HTML code for file preview.
	 * @param array $params Expected keys: action, hash.
	 * @return string
	 */
	public static function buildPreview(array $params)
	{
		global $APPLICATION;

		if($params['action'] == 'default' && isset($params['hash']))
		{
			if(!\Thurly\Disk\ExternalLink::isValidValueForField('HASH', $params['hash']))
			{
				//bad hash
				return null;
			}
			$externalLink = \Thurly\Disk\ExternalLink::load(array('=HASH' => $params['hash']), array('OBJECT'));
			if (
				!$externalLink ||
				$externalLink->isExpired() ||
				$externalLink->hasPassword() ||
				!($externalLink->getObject() instanceof File))
			{
				//could not make preview
				return null;
			}
			$file = $externalLink->getFile();
			$params['fileId'] = $file->getId();
			$params['externalLink'] = true;
			ob_start();
			$APPLICATION->includeComponent(
					'thurly:disk.file.preview',
					'',
					$params
			);
			return ob_get_clean();
		}
		return null;
	}

	/**
	 * Returns true if current user has read access to the file.
	 * @param array $params Allowed keys: hash.
	 * @param int $userId Current user's id.
	 * @return bool
	 */
	public static function checkUserReadAccess(array $params, $userId)
	{
		if(!\Thurly\Disk\ExternalLink::isValidValueForField('HASH', $params['hash']))
		{
			//bad hash
			return false;
		}
		$externalLink = \Thurly\Disk\ExternalLink::load(array('=HASH' => $params['hash']), array('OBJECT'));
		if(!$externalLink || $externalLink->isExpired() || $externalLink->hasPassword())
		{
			//could not make preview
			return false;
		}
		return true;
	}
}