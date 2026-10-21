<?php
/**
 * Funções auxiliares usadas na aplicação.
 */

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function formatarMoeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function redirecionar(string $url): void
{
    header("Location: {$url}");
    exit;
}

function mensagemFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function definirFlash(string $tipo, string $texto): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfValido(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Lê os dados do formulário de produto, normaliza e valida.
 * Retorna um array com dois itens: os dados limpos e a lista de erros.
 */
function validarProduto(array $entrada): array
{
    $dados = [
        'nome' => trim($entrada['nome'] ?? ''),
        'categoria' => trim($entrada['categoria'] ?? ''),
        'preco' => str_replace(',', '.', trim($entrada['preco'] ?? '')),
        'quantidade' => trim($entrada['quantidade'] ?? ''),
        'estoque_minimo' => trim($entrada['estoque_minimo'] ?? '5'),
    ];

    $erros = [];

    if ($dados['nome'] === '') {
        $erros[] = 'O nome do produto é obrigatório.';
    }
    if ($dados['categoria'] === '') {
        $erros[] = 'A categoria é obrigatória.';
    }
    if (!is_numeric($dados['preco']) || (float) $dados['preco'] < 0) {
        $erros[] = 'Informe um preço válido.';
    }
    if (!ctype_digit($dados['quantidade'])) {
        $erros[] = 'Informe uma quantidade válida (número inteiro maior ou igual a zero).';
    }
    if (!ctype_digit($dados['estoque_minimo'])) {
        $erros[] = 'Informe um estoque mínimo válido.';
    }

    return [$dados, $erros];
}
