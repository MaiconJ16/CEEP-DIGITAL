<?php
session_start();

// Verifica se o utilizador está autenticado; caso contrário, volta para o login
if (!isset($_SESSION['usuario']) || !isset($_SESSION['senha'])) {
    header("Location: index.php");
    exit;
}

$usuario       = $_SESSION['usuario'];
$nome_completo = $_SESSION['nome_completo'] ?? strtoupper($usuario);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel - CEEP DIGITAL</title>
    <style>
        body {
            background-color: #121417;
            color: #e2e8f0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
        }
        .card {
            background-color: #1e2229;
            padding: 24px 28px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #2d333f;
        }
        .header-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #3b82f6;
        }
        .header-card h1 {
            margin: 0;
            font-size: 1.5rem;
            color: #f8fafc;
        }
        .btn-sair {
            background-color: #dc2626;
            color: #ffffff;
            padding: 8px 18px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-sair:hover {
            background-color: #b91c1c;
        }
        .info-card h2 {
            margin-top: 0;
            font-size: 1.2rem;
            color: #f8fafc;
        }
        .info-card p {
            color: #cbd5e1;
            margin: 8px 0;
            font-size: 0.95rem;
        }
        .btn-correio {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 14px;
        }
        .btn-correio:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card header-card">
            <h1>Bem-vindo(a), <?= htmlspecialchars($nome_completo) ?>!</h1>
            <a href="logout.php" class="btn-sair">Sair</a>
        </div>

        <div class="card info-card">
            <h2>Painel Institucional - CEEP DIGITAL</h2>
            <p>Usuário conectado: <strong><?= htmlspecialchars($usuario) ?></strong></p>
            <p>Plataforma configurada com sucesso no IP 10.0.11.236.</p>

            <div>
                <a href="correio.php" class="btn-correio">
                    ✉ Aceder ao Correio Institucional
                </a>
            </div>
        </div>
    </div>
</body>
</html>
