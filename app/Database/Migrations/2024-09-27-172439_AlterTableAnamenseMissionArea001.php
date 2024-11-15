<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTableAnamenseMissionArea001 extends Migration
{
    public function up()
    {
        //
        $fields = [
            'family_area' => [
                'type' => 'INT',
                'constraint' => 3, // Tamanho do campo INT, já que é mapeado para valores de 1 a 7
                'null' => false,
                'default' => 50, // 'regular' no mapeamento de INT
            ],
            'affective_area' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => false,
                'default' => 50, // 'regular'
            ],
            'professional_area' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => false,
                'default' => 50, // 'regular'
            ],
            'financial_area' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => false,
                'default' => 50, // 'regular'
            ],
            'mission_area' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => false,
                'default' => 50, // 'regular'
            ],
        ];

        // Aplicando as modificações
        $this->forge->modifyColumn('anamneses', $fields);
    }

    public function down()
    {
        //
        $fields = [
            'family_area' => [
                'type' => 'INT',
                'constraint' => 1, // Tamanho do campo INT, já que é mapeado para valores de 1 a 7
                'null' => false,
                'default' => 4, // 'regular' no mapeamento de INT
            ],
            'affective_area' => [
                'type' => 'INT',
                'constraint' => 1,
                'null' => false,
                'default' => 4, // 'regular'
            ],
            'professional_area' => [
                'type' => 'INT',
                'constraint' => 1,
                'null' => false,
                'default' => 4, // 'regular'
            ],
            'financial_area' => [
                'type' => 'INT',
                'constraint' => 1,
                'null' => false,
                'default' => 4, // 'regular'
            ],
            'mission_area' => [
                'type' => 'INT',
                'constraint' => 1,
                'null' => false,
                'default' => 4, // 'regular'
            ],
        ];

        // Aplicando as modificações
        $this->forge->modifyColumn('anamneses', $fields);
    }
}
