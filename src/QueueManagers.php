<?php

namespace Mozzos\QueueManagers;


use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

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

    static function all($order = [])
    {
        $length = Redis::HLEN(config('queue-managers.name'));
        if ($length > 0) {
            $arrray = Redis::HVALS(config('queue-managers.name'));
            if (count($arrray > 0)) {
                $jobs = [];
                if (!empty($order)) {
                    $arrray = self::sort($arrray, $order);
                    foreach ($arrray as $value) {
                        if (!empty($value)) {
                            $jobs[] = QueueJob::toBase($value);
                        } else {
                            return [];
                        }
                    }
                } else {
                    foreach ($arrray as $value) {
                        if (!empty($value)) {
                            $jobArray = json_decode($value);
                            $jobs[] = QueueJob::toBase($jobArray);
                        } else {
                            return [];
                        }
                    }
                }
                return $jobs;
            }
            return [];
        }
        return [];
    }

    static function put($queueId, $data)
    {
        $flag = Redis::HMSET(config('queue-managers.name'), ["$queueId" => $data]);
        Redis::EXPIRE(config('queue-managers.name'),86400);
        if ($flag) {
            return true;
        }
        return false;
    }

    function order($array)
    {
        $this->orders = $array;
    }

    static function sort(array $array, $order)
    {
        if (count($order) > 1) {
            $res = [];
            foreach ($array as $key => $row) {
                $row = QueueJob::toBase(json_decode($row))->toArray();
                ${$order[0]}[$key] = $row[$order[0]];
                $res[] = $row;
            }
            if ($order[1] == 'desc') {
                array_multisort(${$order[0]}, SORT_DESC, SORT_NUMERIC, $res);
            } else {
                array_multisort(${$order[0]}, SORT_ASC, SORT_NUMERIC, $res);
            }
            return $res;
        }
    }

    static function pagination($order, $num)
    {
        $length = Redis::HLEN(config('queue-managers.name'));
        if ($length > 0) {
            $arrray = Redis::HVALS(config('queue-managers.name'));
            if (count($arrray > 0)) {
                $jobs = [];
                if (!empty($order)) {
                    $arrray = self::sort($arrray, $order);
                    foreach ($arrray as $value) {
                        if (!empty($value)) {
                            $jobs[] = QueueJob::toBase($value);
                        } else {
                            return [];
                        }
                    }
                } else {
                    foreach ($arrray as $value) {
                        if (!empty($value)) {
                            $jobArray = json_decode($value);
                            $jobs[] = QueueJob::toBase($jobArray);
                        } else {
                            return [];
                        }
                    }
                }
                $colltion = collect($jobs);
                $colltions =  $colltion->forPage(request('page') ? request('page') : 1,10);
                return new LengthAwarePaginator($colltions, $length, $num, request('page') ? request('page') : 1, [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]);
            }
            return [];
        }
        return [];
    }


}