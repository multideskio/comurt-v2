<?php

namespace App\Database\Seeds;

use App\Models\CustomersModel;
use CodeIgniter\Database\Seeder;
use Faker\Factory;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $data = [];
        // Instancia o model e o faker
        $customerModel = new CustomersModel();
        $faker = Factory::create('pt_BR'); // Para dados brasileiros

        // Loop para criar 100 cadastros
        for ($i = 0; $i < 100; $i++) {
            // Gera dados fictícios para o customer
            $data[] = [
                'idUser'   => 1, // ID do usuário, pode ser ajustado conforme necessário
                'name'     => $faker->name,
                'photo'    => $faker->imageUrl(200, 200, 'people', true, 'Faker'), // URL de uma foto fictícia
                'email'    => $faker->unique()->safeEmail,
                'phone'    => $faker->phoneNumber,
                'doc'      => $faker->cpf(false), // Gera CPF sem pontos e traços
                'generous' => $faker->randomElement(['M', 'F', 'O']), // Gêneros: Masculino, Feminino, Outro
            ];
        }

        // Insere os dados no banco de dados
        $customerModel->insertBatch($data);
        
        echo "100 cadastros de clientes foram inseridos com sucesso. \n";
        
    }
}
