<?php

if (!function_exists('getCacheExpirationTimeInSeconds')) {
    function getCacheExpirationTimeInSeconds(int $days): string
    {
        return $days * 24 * 60 * 60;
    }
}

if (!function_exists('gera_token')) {
    function gera_token($text = false): string
    {
        if ($text) {
            $base64Token = substr(hash('sha256', $text), 0, 60);
        } else {
            $base64Token = substr(hash('sha256', time()), 0, 60);
        }
        return $base64Token;
    }
}

if (!function_exists('saudacao')) {
    function saudacao($name)
    {
        date_default_timezone_set('America/Sao_Paulo'); // Define o fuso horário para o Brasil

        $hora = date('H');
        $nameReturn = explode(' ', $name);

        if ($hora >= 5 && $hora < 12) {
            return "Bom dia {$nameReturn[0]}!";
        } elseif ($hora >= 12 && $hora < 18) {
            return "Boa tarde {$nameReturn[0]}!";
        } else {
            return "Boa noite {$nameReturn[0]}!";
        }
    }
}


if (!function_exists('generateSlug')) {
    function generateSlug($length = 20)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $length);
    }
}

if (!function_exists('generateMagicLink')) {
    /**
     * Gera uma hash única para um link mágico.
     * 
     * @param string $userEmail O email do cliente para associar a hash.
     * @param int $expiryInMinutes Tempo de expiração do link, em minutos. Padrão é 60 minutos.
     * @return string Hash única gerada.
     */
    function generateMagicLink($userEmail, $expiryInMinutes = 60)
    {
        // Cria uma string única baseada no email do cliente e no tempo de expiração
        $uniqueData = $userEmail . time() . bin2hex(random_bytes(16));

        // Gera uma hash criptograficamente segura
        $hash = hash('sha256', $uniqueData);

        // Opcional: você pode armazenar essa hash com um tempo de expiração no banco de dados
        // Para que o link só seja válido por um determinado tempo

        return $hash;
    }
}


if (!function_exists('create_protocol')) {
    function create_protocol(int $id_customer): string
    {
        // Obtém a data e hora atual no formato desejado
        $dateTime = date('YmdHis'); // AnoMêsDiaHoraMinutoSegundo

        // Gera o protocolo concatenando com o ID do cliente
        $protocol = $dateTime . $id_customer;

        return $protocol;
    }
}
