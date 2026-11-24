<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

define('LDAP_SERVER', 'ldap://10.0.10.20');
define('LDAP_DOMAIN', '@ceep.plg');
define('LDAP_BASE_DN', 'DC=ceep,DC=plg');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioInput = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $senhaInput   = isset($_POST['senha']) ? trim($_POST['senha']) : '';

    if (empty($usuarioInput) || empty($senhaInput)) {
        echo json_encode(["sucesso" => false, "mensagem" => "Por favor, preencha todos os campos obrigatórios."]);
        exit;
    }

    // Normaliza o login caso o utilizador digite "utilizador@ceep.plg" no formulário
    $login_limpo = (strpos($usuarioInput, '@') !== false)
        ? strstr($usuarioInput, '@', true)
        : $usuarioInput;

    $ldap_conn = @ldap_connect(LDAP_SERVER);
    if (!$ldap_conn) {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro crítico: Servidor AD inacessível."]);
        exit;
    }

    // Configurações de protocolo e TIMEOUT RÁPIDO (3 segundos para não travar a página)
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
    ldap_set_option($ldap_conn, LDAP_OPT_TIMELIMIT, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_NETWORK_TIMEOUT, 3);

    $user_dn = $login_limpo . LDAP_DOMAIN;
    $bind = @ldap_bind($ldap_conn, $user_dn, $senhaInput);

    if ($bind) {
        $login_seguro = function_exists('ldap_escape')
            ? ldap_escape($login_limpo, "", LDAP_ESCAPE_FILTER)
            : $login_limpo;

        $filter     = "(sAMAccountName=$login_seguro)";
        $attributes = ["displayName"];
        $search     = @ldap_search($ldap_conn, LDAP_BASE_DN, $filter, $attributes);
        $info       = $search ? @ldap_get_entries($ldap_conn, $search) : false;

        $nome_completo = $login_limpo;
        if ($info && $info['count'] > 0 && isset($info[0]['displayname'][0])) {
            $nome_completo = $info[0]['displayname'][0];
        }

        session_regenerate_id(true);

        // Sessões para o Painel e para o Correio (IMAP)
        $_SESSION['logado']       = true;
        $_SESSION['usuario_id']   = $login_limpo;
        $_SESSION['usuario_nome'] = $nome_completo;
        $_SESSION['usuario']      = $login_limpo;
        $_SESSION['utilizador']   = $login_limpo . LDAP_DOMAIN;
        $_SESSION['senha']        = $senhaInput;

        @ldap_unbind($ldap_conn);

        echo json_encode([
            "sucesso"  => true,
            "mensagem" => "Bem-vindo(a), " . $nome_completo . "! Autenticação realizada com sucesso."
        ]);
        exit;
    } else {
        @ldap_unbind($ldap_conn);
        echo json_encode([
            "sucesso"  => false,
            "mensagem" => "Usuário ou senha incorretos. Tente novamente."
        ]);
        exit;
    }
} else {
    echo json_encode(["sucesso" => false, "mensagem" => "Método não permitido."]);
}
?>