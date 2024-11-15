<?php

/**
 * CONFIG PT BR
 */
return [
    // Mensagens de Erro Genéricas
    'badRequest' => 'Requisição inválida.',
    'unauthorized' => 'Não autorizado. Token inválido ou ausente.',
    'forbidden' => 'Você não tem permissão para acessar este recurso.',
    'notFound' => 'Recurso não encontrado.',
    'methodNotAllowed' => 'Método não permitido para esta rota.',
    'conflict' => 'Conflito de recurso. Já existe um recurso com essas informações.',
    'unsupportedMediaType' => 'Tipo de mídia não suportado.',
    'unprocessableEntity' => 'Erro de validação. Alguns campos não são válidos.',
    'tooManyRequests' => 'Muitas requisições. Por favor, tente novamente mais tarde.',
    'serverError' => 'Erro interno do servidor. Por favor, tente novamente mais tarde.',

    // Mensagens de Sucesso Genéricas
    'resourceCreated' => 'Cadastro realizado com sucesso.',
    'resourceUpdated' => 'Alteração feita com sucesso.',
    'resourceDeleted' => 'Deletado com sucesso.',

    // Mensagens Relacionadas à Autenticação
    'invalidCredentials' => 'Credenciais inválidas.',
    'accountDisabled' => 'Conta desativada. Entre em contato com o administrador.',
    'tokenExpired' => 'Token expirado. Faça login novamente.',
    'tokenInvalid' => 'Token inválido ou ausente.',

    // Mensagens de Validação
    'validationError' => 'Erro de validação. Por favor, verifique os dados enviados.',
    'missingRequiredFields' => 'Campos obrigatórios estão faltando.',
    'invalidData' => 'Dados inválidos fornecidos.',
    'invalidFormat' => 'Formato inválido fornecido.',

    'authorizationHeaderNotFound' => 'Cabeçalho de autorização não encontrado',
    'logoutSuccessful' => 'Logout realizado com sucesso, token invalidado',
    'invalidToken' => 'Token inválido.',

    'userNotAuthenticated' => 'Usuário não autenticado ou dados inválidos.',
    'twoIdsRequired' => 'Você precisa fornecer pelo menos dois IDs para a comparação.',
    'anamnesesNotFound' => 'Nenhuma anamnese encontrada para os IDs fornecidos.',

    'roleNotSpecified' => 'Função não especificada.',

    'anamneseNotFound' => 'Anamnese não encontrada ou você não tem permissão para excluí-la.',
    'anamneseDeleteError' => 'Erro ao excluir a anamnese: {errors}.',

    'idIsRequired' => 'O ID é obrigatório.',

    // Mensagem de erro dinâmico com campo específico
    '{field}' => 'Houve um problema com o campo {field}: {0}',
];
