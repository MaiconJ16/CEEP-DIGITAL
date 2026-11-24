<?php
session_start();

// Recupera o utilizador gravado no login ($_SESSION['usuario']) e garante o domínio @ceep.plg
$usuario_sessao = $_SESSION['usuario'] ?? ($_SESSION['utilizador'] ?? '');
if (!empty($usuario_sessao)) {
    $utilizador_completo = (strpos($usuario_sessao, '@') === false)
        ? $usuario_sessao . '@ceep.plg'
        : $usuario_sessao;
} else {
    $utilizador_completo = 'ximbica@ceep.plg';
}

$senha_sessao = $_SESSION['senha'] ?? '';
$mensagem_aviso = "";
$mensagem_sucesso = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $destinatario = trim($_POST['destinatario'] ?? '');
    $assunto = trim($_POST['assunto'] ?? '');
    $corpo = trim($_POST['mensagem'] ?? '');

    // Se o utilizador digitar apenas o login sem @, completa o domínio
    if (!empty($destinatario) && strpos($destinatario, '@') === false) {
        $destinatario .= '@ceep.plg';
    }

    $cabecalhos = "From: " . $utilizador_completo . "\r\n" .
                  "Content-Type: text/plain; charset=UTF-8\r\n";

    if (!empty($destinatario) && !empty($assunto) && !empty($corpo)) {
        if (mail($destinatario, $assunto, $corpo, $cabecalhos)) {
            $mensagem_sucesso = "E-mail enviado com sucesso para $destinatario!";
        } else {
            $mensagem_aviso = "Erro ao enviar o e-mail pelo Postfix.";
        }
    } else {
        $mensagem_aviso = "Por favor, preencha todos os campos.";
    }
}

$caixa_entrada = [];

if (!function_exists('imap_open')) {
    $mensagem_aviso = "Aviso: A extensão php-imap não está instalada ou ativada no Apache.";
} elseif (empty($senha_sessao)) {
    $mensagem_aviso = "Aviso: Sessão sem palavra-passe registada. Faça login novamente pelo painel principal.";
} else {
    $imap_server = "{127.0.0.1:993/imap/ssl/novalidate-cert}INBOX";
    $conexion = @imap_open($imap_server, $utilizador_completo, $senha_sessao);

    if ($conexion) {
        $total_mensagens = imap_num_msg($conexion);
        for ($i = $total_mensagens; $i >= 1; $i--) {
            $cabecalho = imap_headerinfo($conexion, $i);
            $corpo_msg = imap_fetchbody($conexion, $i, 1);

            $caixa_entrada[] = [
                'de'      => isset($cabecalho->fromaddress) ? imap_utf8($cabecalho->fromaddress) : 'Desconhecido',
                'assunto' => isset($cabecalho->subject) ? imap_utf8($cabecalho->subject) : 'Sem Assunto',
                'data'    => $cabecalho->date ?? '',
                'corpo'   => trim($corpo_msg)
            ];
        }
        imap_close($conexion);
    } else {
        $erro_imap = imap_last_error() ?: 'Falha desconhecida na resposta do servidor';
        $mensagem_aviso = "Aviso: Não foi possível carregar a caixa de entrada IMAP ($erro_imap). O envio de e-mails continua a funcionar.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Correio Eletrónico - CEEP DIGITAL</title>
    <style>
        body { background-color: #0d1117; color: #c9d1d9; font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: #161b22; padding: 25px; border-radius: 8px; border: 1px solid #30363d; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #30363d; padding-bottom: 15px; margin-bottom: 20px; }
        .btn-voltar { background: #21262d; color: #c9d1d9; padding: 8px 15px; text-decoration: none; border-radius: 6px; border: 1px solid #30363d; }
        .btn-voltar:hover { background: #30363d; }
        .alert-error { background: #f8514926; border: 1px solid #f85149; color: #ff7b72; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background: #2ea04326; border: 1px solid #2ea043; color: #3fb950; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #8b949e; }
        input, textarea { width: 100%; padding: 10px; background: #0d1117; border: 1px solid #30363d; color: #c9d1d9; border-radius: 6px; box-sizing: border-box; }
        button { background: #238636; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        button:hover { background: #2ea043; }
        .inbox-section { margin-top: 30px; border-top: 1px solid #30363d; padding-top: 20px; }
        .email-item { background: #21262d; padding: 12px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #30363d; }
        .email-body { margin-top: 8px; padding-top: 8px; border-top: 1px solid #30363d; color: #c9d1d9; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h2>Correio Eletrónico - CEEP DIGITAL</h2>
                <small style="color: #8b949e;">Conectado como: <strong><?php echo htmlspecialchars($utilizador_completo); ?></strong></small>
            </div>
            <a href="principal.php" class="btn-voltar">← Voltar ao Painel</a>
        </div>

        <?php if (!empty($mensagem_aviso)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($mensagem_aviso); ?></div>
        <?php endif; ?>

        <?php if (!empty($mensagem_sucesso)): ?>
            <div class="alert-success"><?php echo htmlspecialchars($mensagem_sucesso); ?></div>
        <?php endif; ?>

        <h3>Nova Mensagem</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label>Destinatário (ex: utilizador ou utilizador@ceep.plg)</label>
                <input type="text" name="destinatario" required placeholder="utilizador@ceep.plg">
            </div>
            <div class="form-group">
                <label>Assunto</label>
                <input type="text" name="assunto" required placeholder="Assunto da mensagem">
            </div>
            <div class="form-group">
                <label>Mensagem</label>
                <textarea name="mensagem" rows="4" required placeholder="Escreva a sua mensagem..."></textarea>
            </div>
            <button type="submit">Enviar E-mail</button>
        </form>

        <div class="inbox-section">
            <h3>Caixa de Entrada (<?php echo count($caixa_entrada); ?>)</h3>
            <?php if (empty($caixa_entrada)): ?>
                <p style="color: #8b949e; text-align: center;">Nenhuma mensagem na caixa de entrada.</p>
            <?php else: ?>
                <?php foreach ($caixa_entrada as $msg): ?>
                    <div class="email-item">
                        <strong>De:</strong> <?php echo htmlspecialchars($msg['de']); ?><br>
                        <strong>Assunto:</strong> <?php echo htmlspecialchars($msg['assunto']); ?><br>
                        <small style="color: #8b949e;"><?php echo htmlspecialchars($msg['data']); ?></small>
                        <?php if (!empty($msg['corpo'])): ?>
                            <div class="email-body"><?php echo htmlspecialchars($msg['corpo']); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
