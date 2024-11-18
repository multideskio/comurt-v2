<?php
declare(strict_types=1);
namespace App\Libraries;

use CodeIgniter\Config\Services;
use Exception;

class EmailsLibraries
{
    protected $remetente;
    protected $nomeRemetente;
    protected $email;
    protected $config;

    public function __construct()
    {
        $this->email = Services::email();

        if (filter_var(getenv('SMTP_ACTIVE'), FILTER_VALIDATE_BOOLEAN)) {
            $this->remetente = getenv('SENDER_EMAIL');
            $this->nomeRemetente = getenv('SENDER_NAME');
            $this->initializeSMTP();
        }

        log_message('info', '[LINE ' . __LINE__ . '] [EmailsLibraries::__construct] EmailsLibraries initialized successfully.');
    }

    protected function initializeSMTP()
    {
        $config['protocol']   = 'smtp';
        $config['SMTPHost']   = getenv('SMTP_HOST');
        $config['SMTPUser']   = getenv('SMTP_USER');
        $config['SMTPPass']   = getenv('SMTP_PASS');
        $config['SMTPPort']   = (int) getenv('SMTP_PORT');
        $config['SMTPCrypto'] = getenv('SMTP_CRYPTO');
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
                return false;
            }
        } catch (Exception $e) {
            log_message('error', '[LINE ' . __LINE__ . '] [EmailsLibraries::send] ' . $e->getMessage());
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
