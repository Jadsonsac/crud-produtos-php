<?php
session_start();
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../src/functions.php';

$busca = trim($_GET['busca'] ?? '');
$apenasBaixoEstoque = isset($_GET['baixo_estoque']);

$porPagina = 10;
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));

$where = ' WHERE 1=1';
$params = [];

if ($busca !== '') {
    $where .= ' AND (nome LIKE :busca_nome OR categoria LIKE :busca_categoria)';

    $params[':busca_nome'] = "%{$busca}%";
    $params[':busca_categoria'] = "%{$busca}%";
}

if ($apenasBaixoEstoque) {
    $where .= ' AND quantidade <= estoque_minimo';
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM produtos' . $where);
$stmt->execute($params);
$totalProdutos = (int) $stmt->fetchColumn();

$totalPaginas = max(1, (int) ceil($totalProdutos / $porPagina));
$pagina = min($pagina, $totalPaginas);
$deslocamento = ($pagina - 1) * $porPagina;

$stmt = $pdo->prepare('SELECT * FROM produtos' . $where . ' ORDER BY nome ASC LIMIT :limite OFFSET :deslocamento');
foreach ($params as $nome => $valor) {
    $stmt->bindValue($nome, $valor);
}
$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':deslocamento', $deslocamento, PDO::PARAM_INT);
$stmt->execute();
$produtos = $stmt->fetchAll();

$flash = mensagemFlash();
?>

<?php
$titulo = 'CRUD de Produtos';
require __DIR__ . '/../templates/header.php';
?>
    <header>
        <h1>Controle de Produtos</h1>
        <a href="create.php" class="btn btn-primary">+ Novo produto</a>
    </header>

    <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['tipo']) ?>"><?= h($flash['texto']) ?></div>
    <?php endif; ?>

    <form method="get" class="filtros-form">
        <input type="text" name="busca" placeholder="Buscar por nome ou categoria..." value="<?= h($busca) ?>">
        <label class="checkbox">
            <input type="checkbox" name="baixo_estoque" value="1" <?= $apenasBaixoEstoque ? 'checked' : '' ?>>
            Apenas estoque baixo
        </label>
        <button type="submit" class="btn">Filtrar</button>
        <?php if ($busca !== '' || $apenasBaixoEstoque): ?>
            <a href="index.php" class="btn">Limpar</a>
        <?php endif; ?>
    </form>

    <?php if (empty($produtos)): ?>
        <p class="vazio">Nenhum produto encontrado.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Qtd.</th>
                    <th>Situação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($produtos as $produto): ?>
                <?php $baixoEstoque = $produto['quantidade'] <= $produto['estoque_minimo']; ?>
                <tr>
                    <td><strong><?= h($produto['nome']) ?></strong></td>
                    <td><?= h($produto['categoria']) ?></td>
                    <td><?= h(formatarMoeda((float) $produto['preco'])) ?></td>
                    <td><?= (int) $produto['quantidade'] ?></td>
                    <td>
                        <?php if ($baixoEstoque): ?>
                            <span class="badge badge-alerta">Estoque baixo</span>
                        <?php else: ?>
                            <span class="badge badge-ok">Normal</span>
                        <?php endif; ?>
                    </td>
                    <td class="acoes">
                        <a href="edit.php?id=<?= (int) $produto['id'] ?>" class="btn btn-sm">Editar</a>
                        <form method="post" action="delete.php" class="form-inline" onsubmit="return confirm('Tem certeza que deseja excluir este produto?');">
                            <input type="hidden" name="id" value="<?= (int) $produto['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPaginas > 1): ?>
            <nav class="paginacao" aria-label="Paginação">
                <?php if ($pagina > 1): ?>
                    <a href="<?= h(urlPagina($pagina - 1)) ?>" class="btn btn-sm">&larr; Anterior</a>
                <?php endif; ?>
                <span>Página <?= $pagina ?> de <?= $totalPaginas ?> &middot; <?= $totalProdutos ?> produtos</span>
                <?php if ($pagina < $totalPaginas): ?>
                    <a href="<?= h(urlPagina($pagina + 1)) ?>" class="btn btn-sm">Próxima &rarr;</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
<?php require __DIR__ . '/../templates/footer.php'; ?>
