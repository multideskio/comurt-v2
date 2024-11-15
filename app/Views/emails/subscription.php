<div style="border: 1px solid #e7e9eb; border-radius: 5px; padding: 20px; font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto;">
    <h1 style="font-size: 22px; color: #ffa974; text-align: center; margin-bottom: 20px;">
        Conta Criada com Sucesso!
    </h1>

    <p style="margin: 0 0 10px; font-size: 16px; line-height: 1.5;">
        Olá <?= htmlspecialchars($name) ?>,
    </p>

    <p style="margin: 0 0 15px; font-size: 16px; line-height: 1.5;">
        Parabéns! Sua conta foi criada com sucesso.
    </p>

    <p style="margin: 0 0 15px; font-size: 16px; line-height: 1.5;">
        Estamos empolgados em tê-lo a bordo. Abaixo estão os detalhes da sua nova conta:
    </p>

    <p style="margin: 0 0 10px; font-size: 16px;">
        <strong>Nome de Usuário:</strong> <?= htmlspecialchars($email) ?>
    </p>

    <p style="margin: 0 0 20px; font-size: 16px;">
        <strong>Link da plataforma:</strong>
        <a href="<?= $baseUrl . '/login' ?>" style="color: #ffa974; text-decoration: none; font-weight: bold;">
            <?= $baseUrl . '/login' ?>
        </a>
    </p>

    <div style="text-align: center; margin: 20px 0;">
        <a href="<?= $baseUrl . '/primeiro-acesso?token=' . $token ?>"
            style="display: inline-block; padding: 12px 24px; font-size: 16px; color: #fff; background-color: #ffa974; 
           text-decoration: none; border-radius: 8px; text-align: center; border: 2px solid #ffa974; 
           box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); transition: background-color 0.3s ease;"
            aria-label="Botão para acessar sua conta">
            ACESSAR SUA CONTA
        </a>
    </div>

    <!-- <div style="text-align: center; margin: 20px 0;">
        <a href="<?= $baseUrl . '/login?magiclink=' . $magicLink ?>" 
           style="display: inline-block; padding: 12px 24px; font-size: 16px; color: #fff; background-color: #000; 
           text-decoration: none; border-radius: 8px; text-align: center; border: 2px solid #520172; 
           box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); transition: background-color 0.3s ease;" 
           aria-label="Botão para acessar sua conta">
            ACESSO DIRETO
        </a>
    </div> -->

    <p style="margin: 15px 0 10px; font-size: 14px; color: #888;">
        Caso tenha qualquer dúvida ou precise de ajuda, nossa equipe de suporte está sempre disponível para assisti-lo.
        Basta responder a este e-mail ou visitar nossa <a href="<?= $baseUrl . '/ajuda' ?>"
            style="color: #ffa974; text-decoration: none; font-weight: bold;">Central de Ajuda</a>.
    </p>

    <p style="margin: 0 0 10px; font-size: 14px;">
        Agradecemos por se juntar a nós!
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