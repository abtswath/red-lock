<?php
require_once './vendor/autoload.php';

use Great\RedLock\Lock;
use Ramsey\Uuid\Uuid;

$lock = new Lock('resource', Uuid::uuid4()->toString(), 1000);

var_dump($lock);