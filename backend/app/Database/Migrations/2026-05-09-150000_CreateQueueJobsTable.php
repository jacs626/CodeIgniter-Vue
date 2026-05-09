<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQueueJobsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'event_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'payload' => [
                'type' => 'JSON',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'processing', 'completed', 'failed'],
                'default' => 'pending',
            ],
            'attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'max_attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 3,
            ],
            'last_error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('queue_jobs');
    }

    public function down()
    {
        $this->forge->dropTable('queue_jobs');
    }
}