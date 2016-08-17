<?php namespace LinkThrow\HmacPacketAuth\Middleware;

use App;
use Closure;
use Config;
use LinkThrow\HmacPacketAuth\Classes\APICheckAuthValid;
use LinkThrow\HmacPacketAuth\Database\Models\AccessToken;
use LinkThrow\HmacPacketAuth\Database\Models\ApiAccessLog;
use LinkThrow\HmacPacketAuth\Database\Models\ApiKey;
use LinkThrow\HmacPacketAuth\Factories\ApiAccessLogFactory;

class HttpPacketTokenCheckMiddleware {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (Config::get('hmacPackageAuth.turnedOffForLocal') && App::environment('local')) {
			return $next($request);
		}

		//Quick hack to get image upload working
		if($request->header('url') && (strpos($request->header('url'), 'upload/image') !== false)) {
			return $next($request);
		}

		if($request->header('key') || $request->header('access-token')) {
			$checkRequestAuthValid = new APICheckAuthValid($request);
			if($checkRequestAuthValid->isRateValid()) {

				if($request->header('timestamp')) {

					if($checkRequestAuthValid->isTimelimitValid()) {

						if($request->header('client-nonce')) {

							//Check nonce
							if($request->header('key')) {
								$apiKey = ApiKey::where('api_public_key', '=', $request->header('key'))->first();
								if(ApiAccessLog::where('api_key_id', '=', $apiKey->id)->where('nonce', '=', $request->header('client-nonce'))->exists()) {
									abort(421, 'Unauthorized action - Nonce already used!');
								}
							}

							if($request->header('access_token')) {
								$accessToken = AccessToken::where('access_token', '=', $request->header('access-token'))->first();
								if(ApiAccessLog::where('access_token_id', '=', $accessToken->id)->where('nonce', '=', $request->header('client-nonce'))->exists()) {
									abort(421, 'Unauthorized action - Nonce already used!');
								}
							}

							if($request->header('url')) {
								if($request->header('hash')) {

									$theData = $request->all();
									if(isset($theData['user_id'])) {
										unset($theData['user_id']);
									}

									$theData['token'] = ($request->header('key')) ? $request->header('key') : $request->header('access-token');
									$theData['timestamp'] = $request->header('timestamp');
									$theData['client-nonce'] = $request->header('client-nonce');
									$theData['url'] = $request->header('url');
									
									$hash = hash_hmac('sha512', json_encode($this->convertAllDataToString($theData), JSON_UNESCAPED_SLASHES), $theData['token']);
									if($hash === $request->header('hash')) {

										//Log the api call
										$apiLogData = array();
										if($request->header('key')) {
											$apiKey = ApiKey::where('api_public_key', '=', $request->header('key'))->first();
											$apiLogData['api_key_id'] = $apiKey->id;
										}

										if($request->header('access-token')) {
											$accessToken = AccessToken::where('access_token', '=', $request->header('access-token'))->first();
											$apiLogData['access_token_id'] = $accessToken->id;
										}

										$apiLogData['url_requested'] = $request->header('url');
										$apiLogData['nonce'] = $request->header('client-nonce');
										$apiLogData['ip_address'] = $request->ip();

										if($request->all()) {
											$apiLogData['data'] = json_encode($request->all(), JSON_UNESCAPED_SLASHES);
										}

										$apiLogFactory = new ApiAccessLogFactory($apiLogData);
										$log = $apiLogFactory->create();

										return $next($request);
									} else {
										abort(421, 'Unauthorized action - Hash is not valid!');
									}
								} else {
									abort(421, 'Unauthorized action - No hash supplied!');
								}
							} else {
								abort(421, 'Unauthorized action - No url supplied!');
							}
						} else {
							abort(421, 'Unauthorized action - No nonce supplied!');
						}

					} else {
						abort(421, 'Unauthorized action - Timelimit for request has exceeded!');
					}

				} else {
					abort(421, 'Unauthorized action - No timestamp supplied!');
				}

			} else {
				abort(422, 'Unauthorized action - Authentication invalid!');
			}
		} else {
			abort(421, 'Unauthorized action - No authorization crudentials supplied!');
		}
	}

	function convertAllDataToString($theObject) {
		$newObject = array();
		foreach ($theObject as $key => $value) {
			$newObject[$key] = (string)$value;
		}
		return $newObject;
	}

}
