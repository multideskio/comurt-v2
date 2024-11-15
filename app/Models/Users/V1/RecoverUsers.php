<?php

declare(strict_types=1);

namespace App\Models\Users\V1;

use App\Libraries\EmailsLibraries;
use App\Models\PlatformModel;
use App\Models\UsersModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;

class RecoverUsers extends UsersModel
{
    /**
     * Recupera o usuário com base no email.
     *
     * @param string $email Email do usuário a ser recuperado.
     * @return bool
     * @throws PageNotFoundException Caso o usuário não seja encontrado.
     */
    public function recover(string $email): array
    {
        // Busca o usuário pelo email
        $data = $this->where('email', $email)->first();

        // Se o usuário não for encontrado, lança uma exceção do CodeIgniter
        if (!$data) {
            log_message('info', "User with email '{$email}' not found.");
            throw new PageNotFoundException("User with email '{$email}' not found.");
        }

        // Exemplo de chamada para enviar um email com as informações necessárias
        $result = $this->sendEmail($data['name'], $data['email'], $data['token'], $data['magic_link']);

        return $result;
    }

    private function sendEmail($name, $email, $token, $magicLink)
    {
        $platForm          = $this->platForm();
        $data['name']      = $name;
        $data['email']     = $email;
        $data['token']     = $token;
        $data['magicLink'] = $magicLink;
        $data['baseUrl']   = $platForm['urlBase'];
        $data['company']   = $platForm['company'];

        $liEmail = new EmailsLibraries;

        $html = view('emails/recover', $data);

        $email = $liEmail->send($email, 'Recuperação de senha', $html);

        return $data;
    }

    private function platForm(): array
    {
        $modelPlatform = new PlatformModel();
        $data = $modelPlatform->first();

        if (!$data) {
            throw new Exception("Error fetching platform data");
        }
        return $data;
    }

    public function updatePass(string $token, string $password)
    {
        // Busca o registro no banco de dados com base no token
        $data = $this->where('token', $token)->first();

        // Se o token não for encontrado, lança uma exceção com a mensagem traduzida
        if (empty($data)) {
            throw new \RuntimeException(lang('Errors.invalidToken')); // 'invalidToken' deve existir no arquivo de tradução
        }

        // Prepara os dados para atualizar a senha
        $update = [
            'id' => $data['id'],
            'password' => $password // Hash da nova senha
        ];

        // Tenta salvar os dados atualizados
        if (!$this->save($update)) {
            // Captura os erros do model
            $errors = $this->errors();

            // Traduz os erros capturados
            $translatedErrors = [];
            foreach ($errors as $field => $error) {
                // Tenta buscar a tradução para cada erro específico
                $translatedErrors[$field] = lang('Errors.' . $field, [$error]);
            }

            // Lança uma exceção com os erros traduzidos
            throw new \RuntimeException(implode(', ', $translatedErrors)); // Concatena os erros em uma única string
        }

        // Retorna uma mensagem de sucesso
        return lang('Errors.resourceUpdated'); // 'resourceUpdated' deve existir no arquivo de tradução
    }
}
