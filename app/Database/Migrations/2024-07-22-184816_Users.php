<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Users extends Migration
{
    public function up()
    {
        $db = db_connect();
        $db->disableForeignKeyChecks();

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Primary key of the table',
            ],
            'platformId' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Foreign key to the platform table',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => 'Name of the user',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => false,
                //'unique'     => true,
                'comment'    => 'Unique email address of the user',
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'comment'    => 'Phone number of the user',
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => 'Hashed password for authentication',
            ],
            'photo' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'URL of the user\'s profile photo',
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Brief description or bio of the user',
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'Authentication token for the user',
            ],
            'magic_link' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'Magic link for the user',
            ],
            'checked' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Indicates if the user has been verified',
            ],
            'admin' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Indicates if the user has admin privileges',
            ],
            'education' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'User\'s educational background',
            ],
            'languages' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'Languages spoken by the user, separated by commas',
            ],
            'department' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'Department or field of work of the user',
            ],
            'social_networks' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'Social networks the user is active on, separated by commas',
            ],
            'company' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'Company the user works for',
            ],
            'birthdate' => [
                'type'       => 'DATE',
                'null'       => true,
                'comment'    => 'User\'s birth date',
            ],
            'show_personal_chart_dashboard' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Show personal chart on the dashboard',
            ],
            'show_family_chart_dashboard' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Show family chart on the dashboard',
            ],
            'show_friends_chart_dashboard' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Show friends chart on the dashboard',
            ],
            'show_appointments_chart_dashboard' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Show appointments chart on the dashboard',
            ],
            'show_basic_info_dashboard' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Show basic information on the dashboard',
            ],
            'receive_updates_email' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Receive updates via email',
            ],
            'receive_updates_sms' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Receive updates via SMS',
            ],
            'receive_updates_whatsapp' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Receive updates via WhatsApp',
            ],
            'receive_scheduling_reminders' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Receive scheduling reminders',
            ],
            'receive_cancellation_reminders' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'comment'    => 'Receive cancellation reminders',
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'Timestamp when the user was created',
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'Timestamp when the user was last updated',
            ],
            'deleted_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'Timestamp when the user was deleted',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('platformId', 'platform', 'id', 'NO ACTION', 'NO ACTION');
        $this->forge->createTable('users', true);
        $db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->forge->dropTable('users', true);
    }
}
