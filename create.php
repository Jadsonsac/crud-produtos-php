<?php
session_start();
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';

$erros = [];
$dados = ['nome' => '', 'categoria' => '', 'preco' => '', 'quantidade' => '', 'estoque_minimo' => '5'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados['nome'] = trim($_POST['nome'] ?? '');
    $dados['categoria'] = trim($_POST['categoria'] ?? '');
    $dados['preco'] = str_replace(',', '.', trim($_POST['preco'] ?? ''));
    $dados['quantidade'] = trim($_POST['quantidade'] ?? '');
    $dados['estoque_minimo'] = trim($_POST['estoque_minimo'] ?? '5');

    if ($dados['nome'] === '') {
        $erros[] = 'O nome do produto é obrigatório.';
    }
    if ($dados['categoria'] === '') {
        $erros[] = 'A categoria é obrigatória.';
    }
    if (!is_numeric($dados['preco']) || (float) $dados['preco'] < 0) {
        $erros[] = 'Informe um preço válido.';
    }
    if (!ctype_digit((string) $dados['quantidade']) || (int) $dados['quantidade'] < 0) {
        $erros[] = 'Informe uma quantidade válida (número inteiro maior ou igual a zero).';
    }
    if (!ctype_digit((string) $dados['estoque_minimo']) || (int) $dados['estoque_minimo'] < 0) {
        $erros[] = 'Informe um estoque mínimo válido.';
    }

    if (empty($erros)) {
        $stmt = $pdo->prepare(
            'INSERT INTO produtos (nome, categoria, preco, quantidade, estoque_minimo)
             VALUES (:nome, :categoria, :preco, :quantidade, :estoque_minimo)'
        );
        $stmt->execute([
            ':nome' => $dados['nome'],
            ':categoria' => $dados['categoria'],
            ':preco' => (float) $dados['preco'],
            ':quantidade' => (int) $dados['quantidade'],
            ':estoque_minimo' => (int) $dados['estoque_minimo'],
        ]);

        definirFlash('sucesso', 'Produto cadastrado com sucesso.');
        redirecionar('index.php');
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Produto</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Novo Produto</h1>
        <a href="index.php" class="btn">Voltar</a>
    </header>

    <?php if (!empty($erros)): ?>
        <div class="alert alert-erro">
            <ul>
                <?php foreach ($erros as $erro): ?>
                    <li><?= h($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="form">
        <label for="nome">Nome *</label>
        <input type="text" id="nome" name="nome" value="<?= h($dados['nome']) ?>" maxlength="150" required>

        <label for="categoria">Categoria *</label>
        <input type="text" id="categoria" name="categoria" value="<?= h($dados['categoria']) ?>" maxlength="80" required>

        <label for="preco">Preço (R$) *</label>
        <input type="text" id="preco" name="preco" value="<?= h($dados['preco']) ?>" placeholder="Ex: 49.90" required>

        <label for="quantidade">Quantidade em estoque *</label>
        <input type="number" id="quantidade" name="quantidade" value="<?= h($dados['quantidade']) ?>" min="0" required>

        <label for="estoque_minimo">Estoque mínimo</label>
        <input type="number" id="estoque_minimo" name="estoque_minimo" value="<?= h($dados['estoque_minimo']) ?>" min="0">

        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
</div>
</body>
</html>
