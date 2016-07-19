<?php namespace Mozzos\QueueManagers;

use QueueManagers as Client;
use Ramsey\Uuid\Uuid;
use Redis;

trait QueueTraits
{

    #qid
    private $qid;

    public static function getQueueList()
    {

    }
    public function start()
    {
        if ($this->qid){
            $queueJob = QueueJob::get($this->qid);
            $queueJob = $queueJob->initialization();
            Redis::LSET(config('queue-managers.name'),$queueJob->qid,$queueJob->toJson());
        }
        if ($this->qid && $queueJob){
            $index = Redis::LLen(config('queue-managers.name'));
            $this->qid = $index++;
            $queueJob = QueueJob::make($this->qid);
            Redis::LSET(config('queue-managers.name'),$this->qid,$queueJob->toJson());
        }
    }

    public function end()
    {
        if ($this->qid){
            $queueJob = QueueJob::get($this->qid);
            $queueJob = $queueJob->finish();
            Redis::LSET(config('queue-managers.name'),$queueJob->qid,$queueJob->toJson());
        }
    }
//    private function check($qid){
//        Redis::sMembers($qid);
//    }
}