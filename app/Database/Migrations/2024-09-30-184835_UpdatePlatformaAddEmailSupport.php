<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdatePlatformaAddEmailSupport extends Migration
{
    public function up()
    {
        //
        $this->forge->addColumn('platform', [
            'emailSupport' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
                'default' => 'multidesk.io@gmail.com',
                'after' => 'logo'
            ],
        ]);

    }

    public function down()
    {
        //
        $this->forge->dropColumn('platform', 'emailSupport');
    }
}
