<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Subscriptions extends Migration
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
            'idPlan' => [
                'type' => 'int',
                'unsigned' => true
            ],
            'idUser' => [
                'type' => 'BIGINT',
                'unsigned' => true
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
        $this->forge->addForeignKey('idPlan', 'plans', 'id', 'NO ACTION', 'NO ACTION');
        $this->forge->addForeignKey('idUser', 'users', 'id', 'NO ACTION', 'NO ACTION');
        $this->forge->createTable('subscriptions', true);
        $db->enableForeignKeyChecks();

        $seeder = \Config\Database::seeder();
        $seeder->call('UserSeeder');
    }

    public function down()
    {
        //
        $this->forge->dropTable('subscriptions', true);
    }
}
