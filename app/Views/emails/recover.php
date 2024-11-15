<div style="border: 1px solid #e7e9eb; border-radius: 5px; padding: 20px; font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto;">
    <h1 style="font-size: 22px; color: #520172; text-align: center; margin-bottom: 20px;">
        Solicitação de Alteração de Senha
    </h1>

    <p style="margin: 0 0 10px; font-size: 16px; line-height: 1.5;">
        Olá <?= htmlspecialchars($name) ?>,
    </p>

    <p style="margin: 0 0 15px; font-size: 16px; line-height: 1.5;">
        Recebemos uma solicitação para alterar a sua senha. Clique no botão abaixo para criar uma nova senha.
        Se você não fez essa solicitação, por favor, ignore este e-mail.
    </p>

    <div style="text-align: center; margin: 20px 0;">
        <a href="<?= "{$baseUrl}/novasenha/?token=" . $token ?>"
            style="display: inline-block; padding: 12px 24px; font-size: 16px; color: #fff; background-color: #520172; 
           text-decoration: none; border-radius: 8px; text-align: center; border: 2px solid #520172; 
           box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); transition: background-color 0.3s ease;"
            aria-label="Botão para criar nova senha">
            Criar nova senha
        </a>
    </div>

    <p style="margin: 15px 0 10px; font-size: 14px; color: #888;">
        Se o botão acima não funcionar, copie e cole o link abaixo em seu navegador:
    </p>

    <p style="margin: 0 0 20px; font-size: 14px;">
        <a href="<?= "{$baseUrl}/novasenha/?token=" . $token ?>" style="color: #520172; text-decoration: none;">
            <?= "{$baseUrl}/novasenha/?token=" . $token ?>
        </a>
    </p>

    <p style="margin: 20px 0 10px; font-size: 14px;">
        Caso precise de ajuda, entre em contato com nossa <a href="[URL da Central de Ajuda]" style="color: #520172; text-decoration: none; font-weight: bold;">Central de Ajuda</a>.
    </p>

    <p style="margin: 0 0 10px; font-size: 14px;">
        Obrigado por usar nossos serviços!
    </p>

    <p style="margin: 20px 0 0; font-size: 14px;">
        Atenciosamente,<br>
        Equipe <?= $company ?>
    </p>
</div>

<!-- Styles for responsiveness -->
<style>
    @media only screen and (max-width: 600px) {
        div {
            padding: 15px;
        }

        h1 {
            font-size: 20px;
        }

        p,
        a {
            font-size: 14px;
        }

        a {
            padding: 10px 20px;
        }
    }
</style>