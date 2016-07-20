<?php

namespace Mozzos\QueueManagers;


use Carbon\Carbon;
use Illuminate\Foundation\Console\QueuedJob;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Config;
use Mozzos\NLPTool\Client;
use Illuminate\Support\Facades\Redis;

class QueueJob
{
    use InteractsWithQueue;

    private $queueId;

    # 0 未执行 1 执行中 2 已执行 3 删除
    private $status = 0;

    # 0 正常 1 移除
    private $delete = 0;

    # 错误次数
    private $error_count = 0;

    # 执行次数
    private $execute_count = 1;

    # 创建时间
    private $create_time;

    # 最后执行时间
    private $last_time;

    private $data = '';

    private $type = 'redis';

    function __construct($queueId = null, $data = null)
    {
        if ($queueId) {
            $this->queueId = $queueId;
        }
        if (isset($data['data']['commandName'])) {
            $this->data = $data['data']['commandName'];
        }
        if (config('queue.default')){
            $type = config('queue.default');
        }
        $time = Carbon::now()->timestamp;
        $this->create_time = $time;
        $this->last_time = $time;
    }

    function initialization($job, $data)
    {
        if ($this->delete == 1) {
            $job->delete();
            $this->status = 3;
        }
        if (isset($data['data']['commandName'])) {
            $this->data = $data['data']['commandName'];
        }
        $this->last_time = Carbon::now()->timestamp;
        if (config('queue.default')){
            $type = config('queue.default');
        }
        $this->execute_count++;
        if ($this->status == 0) {
            $this->status = 1;
        } else if ($this->status == 1) {
            $this->error_count++;
            $this->status == 0;
        }
        return $this;
    }

    static function make($queueId, $data)
    {
        $queueJob = new static($queueId, $data);
        return $queueJob;
    }

    function finish()
    {
        $this->status = 2;
        return $this;
    }

    function toArray()
    {
        return [
            'queueId' => $this->queueId,
            'status' => $this->status,
            'delete' => $this->delete,
            'error_count' => $this->error_count,
            'create_time' => $this->create_time,
            'last_time' => $this->last_time,
            'execute_count' => $this->execute_count,
            'data' => isset($this->data) ? $this->data : '',
            'type' => config('queue.default')
        ];
    }

    /**
     * @return bool
     */
    function remove(){
        if ($this->status !=2){
            $this->delete = 1;
            QueueManagers::put($this->queueId,$this->toJson());
            return true;
        }
        return false;

    }

    function toJson()
    {
        return json_encode($this->toArray());
    }

    static function toBase($array)
    {
        if (is_array($array)) {
            $instace = new static();
            $instace->queueId = $array['queueId'];
            $instace->status = $array['status'];
            $instace->delete = $array['delete'];
            $instace->error_count = $array['error_count'];
            $instace->create_time = $array['create_time'];
            $instace->last_time = $array['last_time'];
            $instace->execute_count = $array['execute_count'];
            $instace->data = isset($array['data']) ? $array['data'] : '';
            $instace->type = config('queue.default');
        } else if (is_object($array)) {
            $instace = new static();
            $instace->queueId = $array->queueId;
            $instace->status = $array->status;
            $instace->delete = $array->delete;
            $instace->error_count = $array->error_count;
            $instace->create_time = $array->create_time;
            $instace->last_time = $array->last_time;
            $instace->execute_count = $array->execute_count;
            $instace->data = isset($array->data) ? $array->data : '';
            $instace->type = $array->type;
        }
        return $instace;
    }


    function __get($name)
    {
        return $this->$name;
    }

    function __set($name, $value)
    {
        $this->$name = $value;
    }

}