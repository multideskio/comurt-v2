<?php
declare(strict_types=1);

namespace App\Models\Customers\V1;

use App\Models\CustomersModel;
use App\Models\TimeLinesModel;
use App\Models\UsersModel;

class CreateCustomer extends CustomersModel
{


    public function createCustomer(array $params): array
    {
        $currentUser = $this->getAuthenticatedUser();
        $genero      = $this->validateType($params['genero']);

        // Verifica se endereço de e-mail está no banco de dados relacionado ao usuário atual
        $row = $this->where([
            'email'  => $params['email'],
            'idUser' => $currentUser['id'],
        ])->countAllResults();

        if ($row > 0) {
            throw new \DomainException('This email is already registered. Check your customer table.');
        }

        // Dados para cadastro
        $data = [
            'idUser'    => $currentUser['id'],
            'name'      => $params['name'],
            'email'     => $params['email'],
            'phone'     => $params['phone'],
            'photo'     => $params['photo'] ?? null,
            'birthDate' => $params['date']  ?? null, // Adicionando fallback para campos opcionais
            'doc'       => $params['doc']   ?? null,
            'generous'  => $genero
        ];

        // Inserção no banco de dados
        if (!$this->insert($data)) {
            // Captura erros da instância correta do Model
            $errors = $this->errors();
            throw new \RuntimeException('Error registering the customer: ' . implode(', ', $errors));
        }
        $id = $this->getInsertID(); // Usar getInsertID para obter o ID inserido
        $this->insertLog($currentUser, $id);
        return ['id' => $id, 'message' => 'Customer created'];
    }

    private function validateType($type){
        $allowedSortFields = ['male', 'female', 'unspecified', 'non-binary', 'gender fluid', 'agender', 'other'];
        return in_array($type, $allowedSortFields) ? $type : 'unspecified';
    }


    /**
     * Obtém o usuário autenticado.
     *
     * @return array Dados do usuário autenticado.
     * @throws \RuntimeException Se o usuário não estiver autenticado.
     */
    private function getAuthenticatedUser(): array
    {
        $userModel = new UsersModel();
        $currentUser = $userModel->me();

        if (!isset($currentUser['id'])) {
            log_message('info', __LINE__ . ' Unauthenticated user.');
            throw new \RuntimeException('Unauthenticated user.');
        }

        return $currentUser;
    }

    private function insertLog($currentUser, $id){
        $modelTime = new TimeLinesModel();
        $modelTime->insert(
            [
                'idUser' => $currentUser['id'],
                'idCustomer' => $id,
                'type' => 'create_customer'
            ]
        );
    }
}
