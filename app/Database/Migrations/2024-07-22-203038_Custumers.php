<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Custumers extends Migration
{
    public function up()
    {
        $db = db_connect();
        $db->disableForeignKeyChecks();

        //
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'idUser' => [
                'type' => 'BIGINT',
                'unsigned' => true
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true
            ],
            
            'photo' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true
            ],

            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => '20'
            ],
            'doc' => [
                'type' => 'varchar',
                'constraint' => 15,
                'null' => true
            ],
            'generous' => [
                'type'       => 'ENUM',
                'constraint' => ["male", "female", "unspecified", "non-binary", "gender fluid", "agender", "other"],
                'default'    => 'unspecified',
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ["myself", "family", "friend", "professional"],
                'default'    => 'myself',
            ],
            'birthDate' => [
                'type' => 'date',
                'null' => true
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
        $this->forge->addForeignKey('idUser', 'users', 'id', 'NO ACTION', 'NO ACTION');
        $this->forge->createTable('customers', true);
        $db->enableForeignKeyChecks();
    }

    public function down()
    {
        //
        $this->forge->dropTable('customers', true);
    }
}
