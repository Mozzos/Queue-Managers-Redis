<?php

namespace Mozzos\QueueManagers;


use Illuminate\Support\Facades\Config;
use Mozzos\NLPTool\Client;
use Redis;

interface QueueManagers
{

    public function setMapping();

    static function getByUuid($uuid);
}