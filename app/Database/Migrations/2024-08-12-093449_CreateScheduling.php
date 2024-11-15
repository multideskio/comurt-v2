<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScheduling extends Migration
{
    public function up()
    {
        //
        $db = db_connect();
        $db->disableForeignKeyChecks();

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_user' => [
                'type'       => 'BIGINT',
                'unsigned'   => true
            ],
            'id_customer' => [
                'type'       => 'BIGINT',
                'unsigned'   => true
            ],
            'date' => [
                'type' => 'datetime'
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'completed', 'cancelled'],
                'default'    => 'pending',
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['consultation', 'anamnesis', 'return'],
                'default'    => 'consultation',
            ],

            'id_deleted' => [
                'type'       => 'BIGINT',
                'unsigned'   => true
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
            ],
        ]);


        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('id_user',    'users',    'id', 'NO ACTION', 'NO ACTION');
        $this->forge->addForeignKey('id_customer', 'customers', 'id', 'NO ACTION', 'NO ACTION');

        $this->forge->createTable('appointments', true);
        $db->enableForeignKeyChecks();
    }

    public function down()
    {
        //
        $db = db_connect();
        $db->disableForeignKeyChecks();
        $this->forge->dropTable('appointments', true);
        $db->enableForeignKeyChecks();
    }
}
