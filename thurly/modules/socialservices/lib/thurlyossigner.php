<?
namespace Thurly\Socialservices;

use Thurly\Main\Web\Json;
use Thurly\Main\Security\Sign\Signer;
use Thurly\Main\Security\Sign\HmacAlgorithm;

class ThurlyOSSigner
	extends Signer
{
	public function __construct()
	{
		parent::__construct(new HmacAlgorithm('sha256'));
	}

	public function sign($value, $salt = null)
	{
		$valueEnc = base64_encode(Json::encode($value));
		return parent::sign($valueEnc, $salt);
	}

	public function unsign($signedValue, $salt = null)
	{
		$encodedValue = parent::unsign($signedValue, $salt);
		return Json::decode(base64_decode($encodedValue));
	}

	/**
	 * Return encoded signature
	 *
	 * @param string $value
	 * @return mixed
	 */
	protected function encodeSignature($value)
	{
		return base64_encode($value);
	}

	/**
	 * Return decoded signature
	 *
	 * @param string $value
	 * @return string
	 */
	protected function decodeSignature($value)
	{
		return base64_decode($value);
	}
}