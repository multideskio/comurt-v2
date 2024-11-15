<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WebhookLogs extends Migration
{
    public function up()
    {
        //
        $db = db_connect();
        $db->disableForeignKeyChecks();

        $this->forge->addField([
            
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'platformId' => [
                'type' => 'int',
                'unsigned' => true
            ],

            'status' => [
                'type' => 'VARCHAR',
                'constraint' => '15',
                'null' => true
            ],

            'messageError' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true
            ],

            'codeStatus' => [
                'type' => 'INT',
                'contraint' => 3,
                'null' => true
            ],
            
            'received' => [
                'type' => 'TEXT'
            ],
            
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ]
        ]);


        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('platformId', 'platform', 'id', 'NO ACTION', 'NO ACTION');
        $this->forge->createTable('logs_webhook', true);
        $db->enableForeignKeyChecks();
    }

    public function down()
    {
        //
        $this->forge->dropTable('logs_webhook', true);
    }
}
