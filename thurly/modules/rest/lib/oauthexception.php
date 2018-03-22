<?
namespace Thurly\Rest;

class OAuthException
	extends RestException
{
	protected $result;

	public function __construct($oauthResult, \Exception $previous = null)
	{
		$this->result = $oauthResult;

		parent::__construct(
			$this->result['error_description'],
			static::ERROR_OAUTH,
			isset($oauthResult["error_status"])
				? $oauthResult["error_status"]
				: \CRestServer::STATUS_UNAUTHORIZED,
			$previous
		);

		if($oauthResult['additional'] && is_array($oauthResult['additional']))
		{
			$this->setAdditional($oauthResult['additional']);
		}
	}

	public function getErrorCode()
	{
		return $this->result['error'];
	}
}
?>