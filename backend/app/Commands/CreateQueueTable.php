<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateQueueTable extends BaseCommand
{
    protected $group = 'Queue';
    protected $name = 'queue:create-table';
    protected $description = 'Create the queue_jobs table';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        $sql = 'CREATE TABLE IF NOT EXISTS queue_jobs (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(100) NOT NULL,
            payload JSON NOT NULL,
            status ENUM("pending", "processing", "completed", "failed") DEFAULT "pending",
            attempts INT(11) DEFAULT 0,
            max_attempts INT(11) DEFAULT 3,
            last_error TEXT NULL,
            processed_at DATETIME NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        $db->query($sql);

        CLI::write('✅ Tabla queue_jobs creada correctamente', 'green');
    }
}