<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDateTasks extends Migration
{
    public function up()
    {
        //
        $this->forge->addColumn('tasks', [
            'datetime' => [
                'type' => 'DATETIME'
            ]
        ]);
    }

    public function down()
    {
        //
        $this->forge->dropColumn('tasks', 'datetime');
    }
}
