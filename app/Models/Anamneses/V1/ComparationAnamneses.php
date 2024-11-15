<?php

declare(strict_types=1);

namespace App\Models\Anamneses\V1;

use App\Models\AnamnesesModel;

class ComparationAnamneses extends AnamnesesModel
{
    /**
     * Compara uma lista de anamneses com a primeira sendo a base.
     *
     * @param array $anamneses Lista de anamneses a serem comparadas.
     * @return array Resultados da comparação.
     */
    public function compare(string $input): array
    {
        $currentUser = $this->getAuthenticatedUser();

        // Verificar se o usuário está autenticado
        if (empty($currentUser) || !isset($currentUser->id)) {
            throw new \RuntimeException(lang('Errors.userNotAuthenticated') ?: 'User not authenticated or invalid user data.');
        }

        // Dividir a string de IDs em um array
        $idsArray = array_map('trim', explode(',', $input));

        // Garantir que ao menos dois IDs foram fornecidos
        if (count($idsArray) < 2) {
            throw new \RuntimeException(lang('Errors.twoIdsRequired') ?: 'You need at least two IDs for comparison.');
        }

        // Consultar o banco de dados para os IDs fornecidos
        $this->where('id_user', $currentUser->id);
        $anamneses = $this->whereIn('id', $idsArray)->findAll();

        // Verificar se foram encontrados registros no banco
        if (empty($anamneses) || count($anamneses) < 2) {
            throw new \DomainException(lang('Errors.anamnesesNotFound') ?: 'No anamneses found for the provided IDs.', 404);
        }

        // Primeira anamnese é a base para comparação
        $base = $anamneses[0];
        unset($anamneses[0]); // Remover a base das outras anamneses para comparação

        $comparisonResults = [];

        // Lista completa de campos a serem comparados
        $fieldsToCompare = [
            'mental_imbalance',
            'mental_percentage',
            'emotional_imbalance',
            'emotional_percentage',
            'spiritual_imbalance',
            'spiritual_percentage',
            'physical_imbalance',
            'physical_percentage',
            'coronary_chakra_imbalance',
            'coronary_chakra_percentage',
            'coronary_chakra_activity',
            'coronary_chakra_affects_organ',
            'frontal_chakra_imbalance',
            'frontal_chakra_percentage',
            'frontal_chakra_activity',
            'frontal_chakra_affects_organ',
            'laryngeal_chakra_imbalance',
            'laryngeal_chakra_percentage',
            'laryngeal_chakra_activity',
            'laryngeal_chakra_affects_organ',
            'cardiac_chakra_imbalance',
            'cardiac_chakra_percentage',
            'cardiac_chakra_activity',
            'cardiac_chakra_affects_organ',
            'solar_plexus_chakra_imbalance',
            'solar_plexus_chakra_percentage',
            'solar_plexus_chakra_activity',
            'solar_plexus_chakra_affects_organ',
            'sacral_chakra_imbalance',
            'sacral_chakra_percentage',
            'sacral_chakra_activity',
            'sacral_chakra_affects_organ',
            'base_chakra_imbalance',
            'base_chakra_percentage',
            'base_chakra_activity',
            'base_chakra_affects_organ',
            'aura_size',
            'aura_protection',
            'aura_size_comments',
            'opening_size',
            'opening_size_comments',
            'color_lack',
            'color_excess',
            'color_base',
            'health_energy',
            'energy_comments',
            'family_area',
            'affective_area',
            'professional_area',
            'financial_area',
            'mission_area',
            'created_at'
        ];

        foreach ($anamneses as $anamnese) {
            $comparison = [
                'id' => $anamnese['id'],
                'id_customer' => $anamnese['id_customer'],
                'create' => $anamnese['created_at'],
                'differences' => [],
                'base_id' => $base['id']  // Adiciona o ID da anamnese base para referência
            ];

            // Comparar cada campo
            foreach ($fieldsToCompare as $field) {
                if (isset($base[$field], $anamnese[$field])) {
                    // Converter para float ou string, dependendo do tipo de dado
                    $baseValue = is_numeric($base[$field]) ? (float) $base[$field] : (string) $base[$field];
                    $currentValue = is_numeric($anamnese[$field]) ? (float) $anamnese[$field] : (string) $anamnese[$field];

                    // Armazena a comparação entre a base e a anamnese atual
                    $comparison['differences'][$field] = [
                        'base_value' => $baseValue,
                        'current_value' => $currentValue,
                        'difference' => is_numeric($baseValue) ? ($currentValue - $baseValue) : null  // Se numérico, calcular diferença
                    ];
                }
            }

            // Adicionar ao array de resultados de comparação
            $comparisonResults[] = $comparison;
        }

        return $comparisonResults;
    }
}
