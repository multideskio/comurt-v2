<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Emails extends Migration
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
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'null' => false
            ],
            'template' => [
                'type' => 'TEXT',
                'null' => false
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inative'],
                'default'    => 'active',
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
        $this->forge->addForeignKey('platformId', 'platform', 'id', 'NO ACTION', 'NO ACTION');

        $this->forge->createTable('templatesEmails', true);
        $db->enableForeignKeyChecks();

        //$seeder = \Config\Database::seeder();
        //$seeder->call('EmailsSeeder');
    }

    public function down()
    {
        //
        $db = db_connect();
        $db->disableForeignKeyChecks();
        $this->forge->dropTable('templatesEmails', true);
        $db->enableForeignKeyChecks();
    }
}
