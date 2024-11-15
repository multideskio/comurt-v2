<?php

namespace App\Models\Appointments\V1;

use App\Models\AppointmentsModel;
use App\Models\CustomersModel;

/**
 * Classe CreateAppointments
 *
 * Responsável por criar novos compromissos com base nos parâmetros fornecidos.
 */
class CreateAppointments extends AppointmentsModel
{
    /**
     * Cria um novo compromisso.
     *
     * @param array $params Parâmetros necessários para criar o compromisso.
     * @return array Resultado da criação com ID e mensagem.
     * @throws \RuntimeException Se ocorrer algum erro na criação do compromisso.
     */
    public function create(array $params)
    {
        $currentUser = $this->getAuthenticatedUser();
        $customer    = $this->getCustomer(intval($currentUser['id']), intval($params['id_customer']));
        $date        = $this->validateDate($params['date']);
        $type        = $this->validateType($params['type']);

        // Valida a data do compromisso
        if (!$date) {
            // throw new \RuntimeException('Invalid scheduling date or earlier than the current date.'); // 422
            $date = date('Y-m-d H:i:s');
        }

        // Verifica conflito de horário com outros compromissos
        /*if ($this->hasScheduleConflict($date, intval($currentUser['id']))) {
            throw new \DomainException('There is already a schedule at the same time or within a 30-minute interval.'); // 409
        }*/

        // Dados para inserção no banco de dados
        $data = [
            'id_user' => $currentUser['id'],
            'id_customer' => $params['id_customer'],
            'date' => $date,
            'type' => $type
        ];

        // Tenta inserir os dados no banco
        if (!$this->insert($data)) {
            $errors = $this->errors();
            throw new \RuntimeException('Error registering the appointment: ' . implode(', ', $errors)); // 422
        }

        // Obtém o ID do compromisso recém-criado
        $id = $this->getInsertID();
        return ['id' => $id, 'message' => 'Schedule created successfully.'];
    }

    private function validateType($type)
    {
        $allowedSortFields = ['consultation', 'anamnesis', 'return'];
        return in_array($type, $allowedSortFields) ? $type : 'consultation';
    }

    /**
     * Obtém o cliente associado ao usuário autenticado.
     *
     * @param int $currentUser ID do usuário atual.
     * @param int $customer ID do cliente.
     * @return array Dados do cliente.
     * @throws \RuntimeException Se o cliente não pertencer ao usuário atual.
     */
    private function getCustomer(int $currentUser, int $customer)
    {
        $customerModel = new CustomersModel();

        $customer = $customerModel->where(
            [
                'id'     => $customer,
                'idUser' => $currentUser
            ]
        )->findAll();

        if (!$customer) {
            log_message('info', __LINE__ . "The client is not the current user's.");
            throw new \RuntimeException('User without permission to register the appointment for the current customer.');
        }

        return $customer;
    }

    /**
     * Valida a data fornecida para o compromisso.
     *
     * @param string|null $date Data do compromisso no formato 'Y-m-d H:i'.
     * @return string|null Data validada no formato 'Y-m-d H:i' ou null se inválida.
     */
    private function validateDate(?string $date)
    {
        if ($date) {
            $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $date);

            if ($dateTime && $dateTime->format('Y-m-d H:i') === $date) {
                $now = new \DateTime();
                if ($dateTime >= $now) {
                    return $dateTime->format('Y-m-d H:i');
                }
            }
        }

        return null;
    }

    /**
     * Verifica se há conflito de horário com compromissos existentes.
     *
     * @param string $date Data do novo compromisso.
     * @return bool Retorna verdadeiro se houver conflito de horário.
     */
    private function hasScheduleConflict(string $date, ?int $currentUser): bool
    {
        // Define o intervalo de 1 hora antes e depois do horário do compromisso
        $start = (new \DateTime($date))->modify('-30 minutes')->format('Y-m-d H:i');
        $end = (new \DateTime($date))->modify('+30 minutes')->format('Y-m-d H:i');

        // Busca no banco de dados compromissos que estejam dentro do intervalo de 1 hora
        $existingAppointments = $this->where('date >=', $start)
            ->where('date <=', $end)
            ->where('id_user', $currentUser)
            ->findAll();

        // Retorna verdadeiro se houver algum compromisso no intervalo
        return !empty($existingAppointments);
    }
}
