<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Anamneses extends Migration
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

            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
            ],

            'mental_imbalance' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => 'não',
            ],
            'mental_percentage' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],

            'emotional_imbalance' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => 'não',
            ],
            'emotional_percentage' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],

            'spiritual_imbalance' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => 'não',
            ],
            'spiritual_percentage' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],

            'physical_imbalance' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => 'não',
            ],
            'physical_percentage' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],

            'coronary_chakra_imbalance' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL
            ],
            'coronary_chakra_percentage' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            'coronary_chakra_activity' => [
                'type' => 'ENUM',
                'constraint' => ['HIPO', 'HIPER'],
                'default' => NULL,
            ],
            'coronary_chakra_affects_organ' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],

            'frontal_chakra_imbalance' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],
            'frontal_chakra_percentage' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            'frontal_chakra_activity' => [
                'type' => 'ENUM',
                'constraint' => ['HIPO', 'HIPER'],
                'default' => NULL,
            ],
            'frontal_chakra_affects_organ' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],

            'laryngeal_chakra_imbalance' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],
            'laryngeal_chakra_percentage' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            'laryngeal_chakra_activity' => [
                'type' => 'ENUM',
                'constraint' => ['HIPO', 'HIPER'],
                'default' => NULL,
            ],
            'laryngeal_chakra_affects_organ' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],

            'cardiac_chakra_imbalance' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],
            'cardiac_chakra_percentage' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            'cardiac_chakra_activity' => [
                'type' => 'ENUM',
                'constraint' => ['HIPO', 'HIPER'],
                'default' => NULL,
            ],
            'cardiac_chakra_affects_organ' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],

            'solar_plexus_chakra_imbalance' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],
            'solar_plexus_chakra_percentage' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            'solar_plexus_chakra_activity' => [
                'type' => 'ENUM',
                'constraint' => ['HIPO', 'HIPER'],
                'default' => NULL,
            ],
            'solar_plexus_chakra_affects_organ' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],

            'sacral_chakra_imbalance' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],
            'sacral_chakra_percentage' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            'sacral_chakra_activity' => [
                'type' => 'ENUM',
                'constraint' => ['HIPO', 'HIPER'],
                'default' => NULL,
            ],
            'sacral_chakra_affects_organ' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],

            'base_chakra_imbalance' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],
            'base_chakra_percentage' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            'base_chakra_activity' => [
                'type' => 'ENUM',
                'constraint' => ['HIPO', 'HIPER'],
                'default' => NULL,
            ],
            'base_chakra_affects_organ' => [
                'type' => 'ENUM',
                'constraint' => ['sim', 'não'],
                'default' => NULL,
            ],

            'aura_size' => [
                'type' => 'INT',
                'constraint' => 3
            ],

            'aura_size_comments' => [
                'type' => 'TEXT',
                'null' => true
            ],

            'opening_size' => [
                'type' => 'INT',
                'constraint' => 3
            ],

            'opening_size_comments' => [
                'type' => 'TEXT',
                'null' => true
            ],

            'color_lack' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],

            'color_excess' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],

            'health_energy' => [
                'type' => 'INT',
                'constraint' => '5',
                'null' => true,
            ],

            'energy_comments' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            "family_area" => [
                'type' => 'ENUM',
                'constraint' => ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente'],
                'default' => 'regular',
            ],

            "affective_area" => [
                'type' => 'ENUM',
                'constraint' => ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente'],
                'default' => 'regular',
            ],

            "professional_area" => [
                'type' => 'ENUM',
                'constraint' => ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente'],
                'default' => 'regular',
            ],

            "financial_area" => [
                'type' => 'ENUM',
                'constraint' => ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente'],
                'default' => 'regular',
            ],

            "mission_area" => [
                'type' => 'ENUM',
                'constraint' => ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente'],
                'default' => 'regular',
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

        $this->forge->createTable('anamneses', true);
        $db->enableForeignKeyChecks();
    }

    public function down()
    {
        //
        $this->forge->dropTable('anamneses', true);
    }
}
