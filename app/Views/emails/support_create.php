<div style="border: 1px solid #e7e9eb; border-radius: 5px; padding: 20px; font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto;">
    <h1 style="font-size: 22px; color: #520172; text-align: center; margin-bottom: 20px;">
        Confirmação de Solicitação de Suporte
    </h1>

    <p style="margin: 0 0 10px; font-size: 16px; line-height: 1.5;">
        Olá <?= htmlspecialchars($name) ?>,
    </p>

    <p style="margin: 0 0 15px; font-size: 16px; line-height: 1.5;">
        Recebemos o seu pedido de suporte. Nosso time de suporte entrará em contato com você em breve.
    </p>

    <div style="border: 1px solid #ccc; border-radius: 5px; padding: 10px; background-color: #f9f9f9; margin: 20px 0;">
        <p style="margin: 0 0 5px; font-size: 16px; font-weight: bold;">
            Protocolo de Atendimento:
        </p>
        <p style="margin: 0; font-size: 18px; color: #520172;">
            <?= $protocol ?>
        </p>
    </div>

    <p style="margin: 15px 0 10px; font-size: 14px; color: #888;">
        Caso precise de mais informações ou queira acompanhar o andamento do seu chamado, por favor, utilize o número de protocolo acima.
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
