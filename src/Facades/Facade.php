<?php
/**
 * Created by PhpStorm.
 * User: Brett
 * Date: 2016/6/13
 * Time: 23:17
 */

namespace Mozzos\QueueManagers\Facades;


use Illuminate\Support\Facades\Facade;

class QueueJob extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'QueueJob';
    }
}