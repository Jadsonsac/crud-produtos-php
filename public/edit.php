<?php
session_start();
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../src/functions.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    definirFlash('erro', 'Produto inválido.');
    redirecionar('index.php');
}

$stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = :id');
$stmt->execute([':id' => $id]);
$produto = $stmt->fetch();

if (!$produto) {
    definirFlash('erro', 'Produto não encontrado.');
    redirecionar('index.php');
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$dados, $erros] = validarProduto($_POST);
    $produto = array_merge($produto, $dados);

    if (empty($erros)) {
        $stmt = $pdo->prepare(
            'UPDATE produtos
             SET nome = :nome, categoria = :categoria, preco = :preco, quantidade = :quantidade, estoque_minimo = :estoque_minimo
             WHERE id = :id'
        );
        $stmt->execute([
            ':nome' => $produto['nome'],
            ':categoria' => $produto['categoria'],
            ':preco' => (float) $produto['preco'],
            ':quantidade' => (int) $produto['quantidade'],
            ':estoque_minimo' => (int) $produto['estoque_minimo'],
            ':id' => $id,
        ]);

        definirFlash('sucesso', 'Produto atualizado com sucesso.');
        redirecionar('index.php');
    }
}
?>

<?php
$titulo = 'Editar Produto';
require __DIR__ . '/../templates/header.php';
?>
    <header>
        <h1>Editar Produto</h1>
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
        <input type="hidden" name="id" value="<?= (int) $produto['id'] ?>">

        <label for="nome">Nome *</label>
        <input type="text" id="nome" name="nome" value="<?= h($produto['nome']) ?>" maxlength="150" required>

        <label for="categoria">Categoria *</label>
        <input type="text" id="categoria" name="categoria" value="<?= h($produto['categoria']) ?>" maxlength="80" required>

        <label for="preco">Preço (R$) *</label>
        <input type="text" id="preco" name="preco" value="<?= h((string) $produto['preco']) ?>" required>

        <label for="quantidade">Quantidade em estoque *</label>
        <input type="number" id="quantidade" name="quantidade" value="<?= h((string) $produto['quantidade']) ?>" min="0" required>

        <label for="estoque_minimo">Estoque mínimo</label>
        <input type="number" id="estoque_minimo" name="estoque_minimo" value="<?= h((string) $produto['estoque_minimo']) ?>" min="0">

        <button type="submit" class="btn btn-primary">Atualizar</button>
    </form>
<?php require __DIR__ . '/../templates/footer.php'; ?>
