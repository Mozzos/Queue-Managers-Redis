<?php

namespace Mozzos\QueueManagers\Providers;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\ServiceProvider;
use Mozzos\QueueManagers\QueueJob;
use Mozzos\QueueManagers\QueueManagers;
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
        if (config('queue.default')!='sync'){
            Queue::after(function (JobProcessed $event) {
                $job = '';
                    if (isset($event->data['data']['command'])){
                        $job = unserialize($event->data['data']['command']);
                    }else if (isset($event->data['data']['data'])){
                        $job = unserialize($event->data['data']['data']);
                    }
                if (!empty($job)) {
                    $id = sha1(serialize($job));
                } else if (isset($event->data['data'])) {
                    $id = md5(json_encode($event->data['data']));
                }
                $queueJob = $this->client()->get($id);
                if ($queueJob) {
                    $queueJob = $queueJob->finish();
                    $this->client()->put($queueJob->queueId, $queueJob->toJson());
                }

            });
            Queue::before(function (JobProcessing $event) {
                $job = '';
                    if (isset($event->data['data']['command'])){
                        $job = unserialize($event->data['data']['command']);
                    }else if (isset($event->data['data']['data'])){
                        $job = unserialize($event->data['data']['data']);
                    }
                if (!empty($job)) {
                    $id = sha1(serialize($job));
                } else if (isset($event->data['data'])) {
                    $id = md5(json_encode($event->data['data']));
                }
                $queueJob = $this->client()->get($id);
                if ($queueJob) {
                    $queueJob = $queueJob->initialization($event->job, $event->data);
                    $this->client()->put($queueJob->queueId, $queueJob->toJson());
                } else {
                    $queueJob = QueueJob::make($id, $event->data);
                    $this->client()->put($queueJob->queueId, $queueJob->toJson());
                }
            });
            Queue::failing(function ($event) {
                // $event->connectionName
                // $event->data
                // $event->data
            });
        }
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

    public function client()
    {
        return new QueueManagers();
    }
}
