<?php

use Config\Paths;
use CodeIgniter\Database\Config;

require_once 'vendor/codeigniter4/framework/system/bootstrap.php';

$paths = new Paths();
$bootstrap = new \CodeIgniter\Bootstrap\ExtendMinimal($paths);
$ctx = $bootstrap->bootContext();

$db = Config::connect();
$result = $db->query('SELECT id, event_type, LEFT(payload, 300) as p, status FROM queue_jobs ORDER BY id DESC LIMIT 3')->getResult();

foreach ($result as $row) {
    echo "ID: " . $row->id . " | " . $row->event_type . " | " . $row->status . PHP_EOL;
    echo "Payload: " . $row->p . PHP_EOL . PHP_EOL;
}