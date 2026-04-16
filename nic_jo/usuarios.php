<?php
require_once("conexao.php");

$acao = $_GET['acao'] ?? 'listar';
$erro = "";
$sucesso = "";

if ($acao == 'salvar') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $mensagem = trim($_POST['mensagem']);
    $senhaInput = $_POST['senha'];

    if (empty($nome)||empty($email)||empty($senhaInput)||empty($mensagem)) {
        $erro = "Preencha todos os campos!";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido!";
    } 
    elseif (strlen($mensagem) > 250) {
        $erro = "Max. 250 caracteres";
    } 
    else {
      
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $erro = "Este e-mail já está cadastrado!";
        } else {
            $senha = password_hash($senhaInput, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, mensagem) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senha, $mensagem]);

            $sucesso = "Usuário cadastrado com sucesso!";
        }
    }
}

if ($acao == 'excluir') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: usuarios.php");
    exit;
}

if ($acao == 'editar') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($acao == 'update') {
    $id = $_POST['id'];
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $mensagem = trim($_POST['mensagem']);
    if (empty($nome) || empty($email) || empty($mensagem)) {
        $erro = "Preencha todos os campos!";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido!";
    } 
    elseif (strlen($mensagem) > 250) {
        $erro = "A mensagem deve ter no máximo 250 caracteres!";
    } 
    else {
        $stmt = $conn->prepare("UPDATE usuarios SET nome=?, email=?, mensagem=? WHERE id=?");
        $stmt->execute([$nome, $email, $mensagem, $id]);
        header("Location: usuarios.php");
        exit;
    }
}

$stmt = $conn->query("SELECT * FROM usuarios ORDER BY id DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <?php
    $view = $_GET['view'] ?? 'cadastro';
    ?>
    <?php if ($view == 'cadastro'): ?>

        <h1><?= isset($usuario) ? "Editar" : "Cadastro" ?></h1>
        <?php if (!empty($erro)): ?>
            <div class="alert erro"><?= $erro ?></div>
        <?php endif; ?>
        <?php if (!empty($sucesso)): ?>
            <div class="alert sucesso"><?= $sucesso ?></div>
        <?php endif; ?>

        <form method="POST" action="usuarios.php?acao=<?= isset($usuario) ? 'update' : 'salvar' ?>" class="user-form">
            <input type="hidden" name="id" value="<?= $usuario['id'] ?? '' ?>">
            <input type="text" name="nome" placeholder="Nome"
                value="<?= $usuario['nome'] ?? '' ?>" required>
            <input type="email" name="email" placeholder="Email"
                value="<?= $usuario['email'] ?? '' ?>" required>
            <textarea 
                name="mensagem" 
                maxlength="250" 
                placeholder="Mensagem (máx. 250 caracteres)" 
                required><?= $usuario['mensagem'] ?? '' ?></textarea>
            <?php if (!isset($usuario)): ?>
                <input type="password" name="senha" placeholder="Senha" required>
            <?php endif; ?>
            <button type="submit">Salvar</button>
        </form>

            <h2>Sistema de Usuários</h2>

    <div class="menu">
        <a href="index.php" class="btn">Cadastro</a><br><br>
        <a href="usuarios.php?view=lista" class="btn">Consultar</a><br><br>
    </div>

    <?php endif; ?>
    <?php if ($view == 'lista'): ?>

        <h2>Consulta de Usuários</h2>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Mensagem</th>
                    <th>Ações</th>
                </tr>

                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['nome']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td class="mensagem-coluna"><?= htmlspecialchars($u['mensagem']) ?></td>
                    <td class="acoes">
                        <a class="link-edit" href="?acao=editar&id=<?= $u['id'] ?>">Editar</a>
                        <a class="link-delete" href="?acao=excluir&id=<?= $u['id'] ?>" onclick="return confirm('Tem certeza?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>

            </table>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
