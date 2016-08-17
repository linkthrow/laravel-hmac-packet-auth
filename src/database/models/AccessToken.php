<?php namespace LinkThrow\HmacPacketAuth\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class AccessToken extends Model {

    const NUMBER_WEEKS_TOKEN_VALID = 4;

    use SoftDeletes;

    protected $table = 'access_tokens';
    public $timestamps = true;

    protected $appends = [];
    protected $hidden = array('created_at', 'updated_at', 'deleted_at', 'user_id');
    protected $dates = ['deleted_at', 'expired_at'];
    protected $fillable = ['user_id', 'access_token', 'device', 'device_id', 'device_token', 'expires', 'ip_address', 'expired_at'];

    protected $casts = [
        'expires' => 'boolean',
    ];

    private static $rules = [
        'user_id' => 'required|integer|exists:users,id',
        'access_token' => 'required|string|unique:access_tokens,access_token',
        'device' => 'required|string',
        'device_id' => 'sometimes|string',
        'device_token' => 'sometimes|string',
        'expires' => 'required|boolean',
        'ip_address' => 'required|string',
    ];

    public function rules()
    {
        return AccessToken::$rules;
    }

    public static function staticRules()
    {
        return AccessToken::$rules;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function accessLogs()
    {
        return $this->hasMany('LinkThrow\HmacPacketAuth\Database\Models\ApiAccessLog', 'access_token_id');
    }

    public function isValid() {
        if($this->expires) {
            $twoWeeksAgo = Carbon::now()->subWeeks(self::NUMBER_WEEKS_TOKEN_VALID);
            return ($this->updated_at > $twoWeeksAgo) ? true : false;
        } else {
            return true;
        }
    }

    public static function generateRandomHash() {
        $hashFound = false;
        $randomHash = '';
        do {
            $randomHash = AccessToken::generateRandomString();
            $hashFound = (AccessToken::where('access_token', '=', $randomHash)->count() === 0) ? true : false;
        } while(!$hashFound);
        return $randomHash;
    }

    private static function generateRandomString() {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }
}
