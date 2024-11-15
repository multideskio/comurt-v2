<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Platform extends Migration
{
    public function up()
    {
        //
        $db = db_connect();
        $db->disableForeignKeyChecks();

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'company' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => true
            ],
            'logo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],

            'urlBase' => [
                'type' => 'VARCHAR',
                'constraint' => 80
            ],

            /** SMTP */
            'senderEmail' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => true
            ],
            'senderName' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => true
            ],
            'smtpHost' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => true
            ],
            'smtpUser' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => true
            ],
            'smtpPass' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => true
            ],
            'smtpPort' => [
                'type' => 'SMALLINT',
                'constraint' => 5,
                'null' => true
            ],
            'smtpCrypto' => [
                'type' => 'VARCHAR',
                'constraint' => 5,
                'default' => 'tls'
            ],
            'activeSmtp' => [
                'type' => 'BOOL',
                'default' => TRUE
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
        $this->forge->createTable('platform', true);
        $db->enableForeignKeyChecks();
    }

    public function down()
    {
        //
        $this->forge->dropTable('platform', true);
    }
}
