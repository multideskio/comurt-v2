<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTableMagicAccess extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('magiclinks', [
            'expiration' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('magiclinks', [
            'expiration' => [
                'type' => 'DATE',
                'null' => false,
            ],
        ]);
    }
}