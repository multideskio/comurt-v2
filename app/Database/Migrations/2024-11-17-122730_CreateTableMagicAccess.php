<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableLinks extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'link_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'used' => [
                'type' => 'TINYINT',
                'default' => 0,
                'null' => false,
            ],
            'expiration' => [
                'type' => 'DATE',
                'null' => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('magiclinks', true);
    }

    public function down()
    {
        $this->forge->dropTable('magiclinks', true);
    }
}