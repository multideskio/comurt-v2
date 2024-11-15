<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTableAnamenseMissionArea extends Migration
{
    public function up()
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

    public function down()
    {
        //
        // Reverter as mudanças para o tipo original ENUM
        $fields = [
            'family_area' => [
                'type' => 'ENUM',
                'constraint' => ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente'],
                'default' => 'regular',
            ],
            'affective_area' => [
                'type' => 'ENUM',
                'constraint' => ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente'],
                'default' => 'regular',
            ],
            'professional_area' => [
                'type' => 'ENUM',
                'constraint' => ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente'],
                'default' => 'regular',
            ],
            'financial_area' => [
                'type' => 'ENUM',
                'constraint' => ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente'],
                'default' => 'regular',
            ],
            'mission_area' => [
                'type' => 'ENUM',
                'constraint' => ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente'],
                'default' => 'regular',
            ],
        ];

        // Revertendo para ENUM
        $this->forge->modifyColumn('anamneses', $fields);
    }
}
