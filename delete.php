<?php
session_start();
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    definirFlash('erro', 'Não foi possível validar a solicitação. Tente novamente.');
    redirecionar('index.php');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if (!$id) {
    definirFlash('erro', 'Produto inválido.');
    redirecionar('index.php');
}

$stmt = $pdo->prepare('DELETE FROM produtos WHERE id = :id');
$stmt->execute([':id' => $id]);

definirFlash(
    $stmt->rowCount() > 0 ? 'sucesso' : 'erro',
    $stmt->rowCount() > 0 ? 'Produto excluído com sucesso.' : 'Produto não encontrado.'
);

redirecionar('index.php');
