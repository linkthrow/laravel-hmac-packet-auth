<?php namespace LinkThrow\HmacPacketAuth\Database\Models;

use Illuminate\Database\Eloquent\Model;
use LinkThrow\HmacPacketAuth\Database\Models\ApiKey;

class ApiAccessLog extends Model {

    protected $table = 'api_access_logs';
    public $timestamps = true;

    protected $fillable = ['ip_address', 'url_requested', 'nonce', 'api_key_id', 'access_token_id', 'data'];
    protected $appends = ['api_key'];
    protected $hidden = array('created_at', 'updated_at', 'deleted_at', 'api_key_id');

    private static $rules = [
        'url_requested' => 'required|string',
        'nonce' => 'required|string',
        'api_key_id' => 'sometimes|integer|exists:api_keys,id',
        'access_token_id' => 'sometimes|integer|exists:access_tokens,id',
        'ip_address' => 'sometimes|string',
        'data' => 'sometimes|string',
    ];

    public function rules()
    {
        return ApiAccessLog::$rules;
    }

    public static function staticRules()
    {
        return ApiAccessLog::$rules;
    }

    public function apiKey()
    {
    	if($this->api_key_id) {
        	return $this->belongsTo('LinkThrow\HmacPacketAuth\Database\Models\ApiKey', 'api_key_id');
        } else {
        	return false;
        }
    }

    public function accessToken()
    {
    	if($this->access_token_id) {
        	return $this->belongsTo('LinkThrow\HmacPacketAuth\Database\Models\AccessToken', 'access_token_id');
        } else {
        	return false;
        }
    }

    public function getApiKeyAttribute()
    {
        $apiKey = ApiKey::find($this->api_key_id);
        return $apiKey;
    }
}
