<?php namespace LinkThrow\HmacPacketAuth\Classes;

use Illuminate\Http\Request;
use LinkThrow\HmacPacketAuth\Database\Models\ApiKey;
use LinkThrow\HmacPacketAuth\Database\Models\ApiAccessLog;
use LinkThrow\HmacPacketAuth\Database\Models\AccessToken;
use Config;

class APICheckAuthValid {

	private $request;

	//seconds
	private $rateLimitCheckOn = Config::get('hmacPackageAuth.rateLimit.turnedOn');
	private $rateLimitTimePeriod = Config::get('hmacPackageAuth.rateLimit.timePeriod');
	private $rateLimitNumber = Config::get('hmacPackageAuth.rateLimit.limitNumber');

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function isRateValid()
	{
		if($this->request->header('key')) {

			return $this->checkApiKeyValid();

		} else if($this->request->header('access-token')) {

			return $this->checkAccessTokenValid();

		} else {
			return false;
		}
	}

	public function isTimelimitValid()
	{
		if($this->request->header('timestamp') && (($this->request->header('timestamp') + $this->rateLimitTimePeriod) >= time())) {
			return true;
		} else {
			return false;
		}
	}

	private function checkApiKeyValid()
	{
		if(ApiKey::where('api_public_key', '=', $this->request->header('key'))) {

			$apiKey = ApiKey::where('api_public_key', '=', $this->request->header('key'))->first();

			if($apiKey) {
				return $this->checkApiKeyRateValid($apiKey->id, $this->request->header('ip-address'));
			} else {
				return false;
			}

		} else {
			return false;
		}
	}

	private function checkApiKeyRateValid($apiKeyId, $ipAddress) {
		if($this->rateLimitCheckOn) {
			$numberRequests = ApiAccessLog::where('api_key_id', '=', $apiKeyId)->where('ip_address', '=', $ipAddress)->where('created_at', '>=', date('Y-m-d H:i:s', time() - $this->rateLimitTimePeriod))->count();
			if($numberRequests > $this->rateLimitNumber) {
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}

	private function checkAccessTokenValid()
	{
		if(AccessToken::where('access_token', '=', $this->request->header('access-token'))) {
			$accessToken = AccessToken::where('access_token', '=', $this->request->header('access-token'))->first();
			
			if($accessToken) {
				return $this->checkAccessTokenRateValid($accessToken->id, $this->request->header('ip-address'));
			}
		} else {
			return false;
		}
	}

	private function checkAccessTokenRateValid($accessTokenId, $ipAddress) {
		if($this->rateLimitCheckOn) {
			$numberRequests = ApiAccessLog::where('access_token_id', '=', $accessTokenId)->where('ip_address', '=', $ipAddress)->where('created_at', '>=', date('Y-m-d H:i:s', time() - $this->rateLimitTimePeriod))->count();
			if($numberRequests > $this->rateLimitNumber) {
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}

}
