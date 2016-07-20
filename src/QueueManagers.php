<?php

namespace Mozzos\QueueManagers;


use Illuminate\Support\Facades\Config;
use Mozzos\NLPTool\Client;
use Redis;

class QueueManagers
{
    /**
     * @param $queueId
     * @return null|QueueJob
     */
    static function get($queueId)
    {
        $result = Redis::HEXISTS(config('queue-managers.name'), $queueId);
        if ($result > 0) {
            $result = json_decode(Redis::HGET(config('queue-managers.name'), $queueId));
            return QueueJob::toBase($result);
        } else {
            return null;
        }
    }

    static function all()
    {
        $length = Redis::HLEN(config('queue-managers.name'));
        if ($length > 0) {
            $arrray = Redis::HVALS(config('queue-managers.name'));
            if (count($arrray > 0)) {
                $jobs = [];
                foreach ($arrray as $value) {
                    if (!empty($value)) {
                        $jobArray = json_decode($value);
                        $jobs[] = QueueJob::toBase($jobArray);
                    }else{
                        return [];
                    }
                }
                return $jobs;
            }
            return [];
        }
        return [];
    }

    static function put($queueId,$data)
    {
        $flag = Redis::HMSET(config('queue-managers.name'),["$queueId"=>$data]);
        if ($flag){
            return true;
        }
        return false;
    }
}