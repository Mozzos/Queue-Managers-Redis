<?php

namespace Mozzos\QueueManagers\Providers;
use Illuminate\Support\ServiceProvider;
use Mozzos\QueueManagers\QueueJob;
use Queue;
use Illuminate\Support\Facades\Redis;
class QueueManagersProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Queue::after(function ($event) {
            if (isset($event->data['qid'])){
                $queueJob = QueueJob::get($event->data['qid']);
                $queueJob = $queueJob->finish();
                Redis::LSET(config('queue-managers.name'),$queueJob->qid,$queueJob->toJson());
            }
        });
        Queue::before(function ($event){
            if (isset($event->data['qid'])){
                $queueJob = QueueJob::get($event->data['qid']);
                $queueJob = $queueJob->initialization();
                Redis::LSET(config('queue-managers.name'),$queueJob->qid,$queueJob->toJson());
            }else{
                $index = Redis::LLen(config('queue-managers.name'));
                $event->data[] = ['qid'=> $index++];
                $queueJob = QueueJob::make($event->data['qid']);
                Redis::LSET(config('queue-managers.name'),$queueJob->qid,$queueJob->toJson());
            }
        });
        Queue::failing(function ($event) {
            // $event->connectionName
            // $event->data
            // $event->data
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
