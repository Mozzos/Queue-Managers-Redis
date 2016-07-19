<?php

namespace Mozzos\QueueManagers;


use Carbon\Carbon;
use Illuminate\Foundation\Console\QueuedJob;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Config;
use Mozzos\NLPTool\Client;
use Redis;

class QueueJob implements QueueManagers
{
    use InteractsWithQueue;

    private $qid;

    # 0 未执行 1 执行中 2 已执行 3 删除
    private $status = 0;

    # 0 正常 1 移除
    private $delete = 0;

    # 错误次数
    private $error_count = 0;

    # 执行次数
    private $execute_count = 0;

    # 创建时间
    private $create_time;

    # 最后执行时间
    private $last_time;

    function __construct($qid = null)
    {
        if ($qid){
            $this->qid = $qid;
        }
        $time = Carbon::now()->timestamp;
        $this->create_time =$time;
        $this->last_time = $time;
    }

    function initialization(){
        if ($this->delete = 1 ){
            $this->delete();
        }
        $this->last_time = Carbon::now()->timestamp;
        $this->execute_count++;
        if ($this->status == 0){
            $this->status = 1;
        }else if ($this->status == 1){
            $this->error_count++;
            $this->status == 0;
        }
        return $this;
    }

    static function make($qid){
        $queueJob = new static($qid);
        return $queueJob;
    }

    function finish(){
        $this->status = 3;
        return $this;
    }

    function toArray(){
        return [
            'qid'=>$this->qid,
            'status'=>$this->status,
            'type' => $this->type,
            'error_count' => $this->error_count,
            'create_time' => $this->create_time,
            'last_time'=> $this->last_time,
            'execute_count'=>$this->execute_count
        ];
    }

    function toJson(){
        return json_encode($this->toArray());
    }

    static function toBase(Array $array){
        $instace = new static();
        $instace->qid = $array['qid'];
        $instace->status = $array['status'];
        $instace->delete = $array['delete'];
        $instace->error_count = $array['error_count'];
        $instace->create_time = $array['create_time'];
        $instace->last_time = $array['last_time'];
        $instace->execute_count = $array['execute_count'];
        return $instace;
    }

    static function get($qid)
    {
        $result = Redis::LINDEX(config('queue-managers.name'),$qid);
        if ($result){
            $result = json_decode($result);
            return self::toBase($result);
        }else{
            return null;
        }
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