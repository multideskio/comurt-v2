<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TasksSeeder extends Seeder
{
    public function run(int $idUser = null, string $type = null)
    {
        //
        if (!$idUser || !$type) {
            log_message('error', 'Não foi possível executar a inserção das tarefas para o usuário. idUser ou type estão faltando.');
            return false;
        }
        
    }
}
