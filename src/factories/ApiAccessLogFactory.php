<?php namespace LinkThrow\HmacPacketAuth\Factories;

use LinkThrow\HmacPacketAuth\Database\Models\ApiAccessLog;
use Validator;

class ApiAccessLogFactory {

    /**
     *
     * @var Asset
     */
    protected $apiAccessLogData;

    public function __construct($apiAccessLogData)
    {
        $this->apiAccessLogData = $apiAccessLogData;
    }

    public function create()
    {
        $rules = ApiAccessLog::staticRules();

        $validator = Validator::make($this->apiAccessLogData, $rules);
        if($validator->fails()) {
            abort(422, json_encode($validator->errors()->all()));
        }

        $this->apiAccessLog = new ApiAccessLog;
        $this->apiAccessLog->fill($this->apiAccessLogData);
        $this->apiAccessLog->save();
        return $this->apiAccessLog;
    }
}
