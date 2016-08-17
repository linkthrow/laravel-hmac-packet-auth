<?php namespace LinkThrow\HmacPacketAuth\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiKey extends Model {

    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
	protected $table = 'api_keys';
    public $timestamps = true;
    protected $fillable = ['description', 'api_public_key', 'api_secret_key', 'role'];

    private static $rules = [
        'description' => 'required|string',
        'api_public_key' => 'required|string',
        'api_secret_key' => 'sometimes|integer|exists:api_keys,id',
        'role' => 'required|in:normal,extended'
    ];

    public function rules()
    {
        return ApiKey::$rules;
    }

    public static function staticRules()
    {
        return ApiKey::$rules;
    }

    public function accessLogs()
    {
        return $this->hasMany('LinkThrow\HmacPacketAuth\Database\Models\ApiAccessLog', 'api_key_id');
    }
}
