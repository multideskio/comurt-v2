<?php

declare(strict_types=1);

namespace App\Models\Anamneses\V1;

use App\Models\AnamnesesModel;

class DeleteAnamneses extends AnamnesesModel
{

    public function deleteId(int $id): void
    {
        // Obter o usuário atual
        $currentUser = $this->getAuthenticatedUser();
        if (!isset($currentUser->id)) {
            throw new \RuntimeException(lang('Errors.userNotAuthenticated')); // Usa a tradução
        }
        $currentUserId = $currentUser->id;

        // Verificar se a anamnese pertence ao usuário atual
        $anamneses = $this->where('id', $id)
            ->where('id_user', $currentUserId)
            ->first();

        // Verifica se a anamnese foi encontrada
        if (!$anamneses) {
            throw new \RuntimeException(lang('Errors.anamneseNotFound')); // Usa a tradução
        }

        // Exclui o registro da anamnese
        if (!$this->delete($id)) {
            // Captura erros da instância correta do Model, se houver
            $errors = implode(', ', $this->errors());
            throw new \Exception(lang('Errors.anamneseDeleteError', ['errors' => $errors])); // Usa a tradução
        }
    }
}
