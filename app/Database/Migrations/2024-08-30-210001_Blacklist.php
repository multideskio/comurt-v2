<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Blacklist extends Migration
{
    public function up()
    {
        //
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'token' => [
                'type' => 'TEXT',
            ],
            'expires_at' => [
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
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('jwt_blacklist', true);
    }

    public function down()
    {
        //
        $this->forge->dropTable('jwt_blacklist', true);
    }
}
