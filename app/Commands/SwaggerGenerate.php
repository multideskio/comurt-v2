<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SwaggerGenerate extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Documentation';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'swagger:generate';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Gera os arquivos de documentação Swagger utilizando o Swagger-PHP.';

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        // Caminhos absolutos usando ROOTPATH e APPPATH
        $source = APPPATH . 'Controllers';
        $output = ROOTPATH . 'public/swagger.yaml';

        CLI::write('Gerando documentação Swagger...', 'yellow');

        // Comando para gerar os arquivos
        $command = ROOTPATH . "vendor/bin/openapi --output $output $source";
        exec($command, $outputLines, $resultCode);

        // Verificar o resultado
        if ($resultCode === 0) {
            CLI::write('Documentação Swagger gerada com sucesso!', 'green');
        } else {
            CLI::error('Erro ao gerar a documentação Swagger.');
            foreach ($outputLines as $line) {
                CLI::write($line, 'red');
            }
        }
    }
}
