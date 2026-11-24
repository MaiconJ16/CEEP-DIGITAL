<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Diagnostico de Conexao LDAP</h2>";

$host = '10.0.10.28';
$port = 389; // ou 636 para ldaps

$ldap = @ldap_connect($host, $port);

if ($ldap) {
    echo "<p style='color:green;'>[OK] ldap_connect executado com sucesso para o host $host na porta $port.</p>";
    
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

    // Testar bind anonimo ou com conta de teste
    $bind = @ldap_bind($ldap, "202430016623@ceep.plg", "tua_senha_aqui");
    if ($bind) {
        echo "<p style='color:green;'>[SUCESSO] Autenticacao LDAP efetuada com sucesso!</p>";
    } else {
        echo "<p style='color:orange;'>[AVISO] Falha no bind LDAP. Codigo do erro: " . ldap_errno($ldap) . " - " . ldap_error($ldap) . "</p>";
    }
    @ldap_unbind($ldap);
} else {
    echo "<p style='color:red;'>[ERRO] Nao foi possivel conectar ao servidor LDAP.</p>";
}
?>

