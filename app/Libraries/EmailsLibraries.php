<?php
declare(strict_types=1);
namespace App\Libraries;

use App\Models\PlatformModel;
use CodeIgniter\Config\Services;
use Exception;

class EmailsLibraries
{
    protected $envio;
    protected $remetente;
    protected $nomeRemetente;
    protected $modelConfig;
    protected $email;
    protected $config;

    public function __construct()
    {
        $this->email = Services::email();

        $data = $this->data();

        if ($data['ativar_smtp']) {
            $this->initializeSMTP($data);
        }

        $this->remetente     = $data['e-remetente'];
        $this->nomeRemetente = $data['n-remetente'];

        log_message('info', '[LINE ' . __LINE__ . '] [EmailsLibraries::__construct] EmailsLibraries initialized successfully.');
    }

    protected function data(): array
    {
        $modelAdmin = new PlatformModel();
        $data = $modelAdmin->find(1);

        log_message('info', '[LINE ' . __LINE__ . '] [EmailsLibraries::data] Data retrieved from PlatformModel.');

        return [
            'SMTPHost'    => $data['smtpHost'],
            'SMTPUser'    => $data['smtpUser'],
            'SMTPPass'    => $data['smtpPass'],
            'SMTPPort'    => intval($data['smtpPort']),
            'SMTPCrypto'  => $data['smtpCrypto'],
            'e-remetente' => $data['senderEmail'],
            'n-remetente' => $data['senderName'],
            'ativar_smtp' => $data['activeSmtp']
        ];
    }

    protected function initializeSMTP(array $data)
    {
        $config['protocol']   = 'smtp';
        $config['SMTPHost']   = $data['SMTPHost'];
        $config['SMTPUser']   = $data['SMTPUser'];
        $config['SMTPPass']   = $data['SMTPPass'];
        $config['SMTPPort']   = $data['SMTPPort'];
        $config['SMTPCrypto'] = $data['SMTPCrypto'];
        $config['mailType']   = 'html';
        $this->email->initialize($config);

        log_message('info', '[LINE ' . __LINE__ . '] [EmailsLibraries::initializeSMTP] SMTP configuration initialized.');
    }

    public function send(string $email, string $assunto, string $message): bool
    {
        try {
            log_message('info', '[LINE ' . __LINE__ . '] [EmailsLibraries::send] Preparing to send email.');

            $this->email->setFrom($this->remetente, $this->nomeRemetente);
            $this->email->setTo($email);
            $this->email->setSubject($assunto);
            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', "[LINE " . __LINE__ . "] [EmailsLibraries::send] Email sent successfully to {$email}.");
                return true;
            } else {
                $error = $this->email->printDebugger(['headers']);
                log_message('error', '[LINE ' . __LINE__ . '] [EmailsLibraries::send] Email sending failed: ' . $error);
                // Continua o processamento sem interromper
                return false;
            }
        } catch (Exception $e) {
            log_message('error', '[LINE ' . __LINE__ . '] [EmailsLibraries::send] ' . $e->getMessage());
            // Continua o processamento sem lançar exceção
            return false;
        }
    }

    public function testarEnvioEmail(string $email, string $assunto, string $message): bool
    {
        try {
            log_message('info', '[LINE ' . __LINE__ . '] [EmailsLibraries::testarEnvioEmail] Preparing to send test email.');

            $this->email->setFrom($this->remetente, $this->nomeRemetente);
            $this->email->setTo($email);
            $this->email->setSubject($assunto);
            $this->email->setMessage($message);

            if ($this->email->send()) {
                log_message('info', "[LINE " . __LINE__ . "] [EmailsLibraries::testarEnvioEmail] Test email sent successfully to {$email}.");
                return true;
            } else {
                $error = $this->email->printDebugger(['headers']);
                log_message('error', '[LINE ' . __LINE__ . '] [EmailsLibraries::testarEnvioEmail] Test email sending failed: ' . $error);
                return false;
            }
        } catch (Exception $e) {
            log_message('error', '[LINE ' . __LINE__ . '] [EmailsLibraries::testarEnvioEmail] ' . $e->getMessage());
            return false;
        }
    }
}
