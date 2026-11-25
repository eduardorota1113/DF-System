<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DiagnoseFácil - Usuários</title>
    <meta name="theme-color" content="#007ac2">
    <link rel="stylesheet" href="css/estilo.css?v=df-interface-1">
</head>
<body class="is-inner">

<?php require __DIR__ . '/partials/cabecalho.php'; ?>

<main id="main-content" class="shell main-content inner-view" tabindex="-1">
    <a class="back-link" href="?pagina=inicio"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-arrow-left"/></svg> Voltar ao início</a>
    <div class="page-heading">
        <p class="eyebrow">GESTÃO DE USUÁRIOS</p>
        <h2>Pessoas e seus cadastros</h2>
        <p class="section-description">Cadastre usuários e mantenha suas informações atualizadas.</p>
    </div>

    <?php if ($mensagemSucesso): ?>
        <p role="status" class="notice notice-success">
            <?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if ($erroFormulario): ?>
        <p role="alert" class="notice notice-error">
            <?= htmlspecialchars($erroFormulario, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <section id="cadastro-usuario" class="cadastro-usuario surface-card" aria-labelledby="titulo-cadastro">
        <div class="section-heading">
            <h3 id="titulo-cadastro">Cadastrar novo usuário</h3>
            <p>Preencha nome e e-mail. O telefone é opcional.</p>
        </div>

        <form method="post" action="?pagina=usuarios">

            <input type="hidden" name="acao" value="cadastrar">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>"
            >

            <div class="campo">
                <label for="nome">Nome <span class="required" aria-hidden="true">*</span></label>
                <input
                    type="text"
                    id="nome"
                    name="nome"
                    placeholder="Digite o nome"
                    maxlength="100"
                    required
                >
            </div>

            <div class="campo">
                <label for="email">E-mail <span class="required" aria-hidden="true">*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Digite o e-mail"
                    maxlength="150"
                    required
                >
            </div>

            <div class="campo">
                <label for="telefone">Telefone <span class="optional">(opcional)</span></label>
                <input
                    type="tel"
                    id="telefone"
                    name="telefone"
                    placeholder="Digite o telefone (opcional)"
                    maxlength="20"
                >
            </div>

            <div class="form-actions"><button type="submit"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-plus"/></svg> Cadastrar usuário</button></div>
        </form>
    </section>

    <?php if ($usuarioEdicao !== null): ?>

        <section id="editar-usuario" class="cadastro-usuario surface-card edit-section" aria-labelledby="titulo-edicao">
            <h3 id="titulo-edicao">Editar usuário</h3>

            <form method="post" action="?pagina=usuarios">

                <input type="hidden" name="acao" value="atualizar">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>"
                >

                <input
                    type="hidden"
                    name="id_usuario"
                    value="<?= $usuarioEdicao->getIdUsuario() ?>"
                >

                <div class="campo">
                    <label for="editar_nome">Nome <span class="required" aria-hidden="true">*</span></label>
                    <input
                        type="text"
                        id="editar_nome"
                        name="nome"
                        value="<?= htmlspecialchars($usuarioEdicao->getNome(), ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="editar_email">E-mail <span class="required" aria-hidden="true">*</span></label>
                    <input
                        type="email"
                        id="editar_email"
                        name="email"
                        value="<?= htmlspecialchars($usuarioEdicao->getEmail(), ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="editar_telefone">Telefone <span class="optional">(opcional)</span></label>
                    <input
                        type="tel"
                        id="editar_telefone"
                        name="telefone"
                        value="<?= htmlspecialchars($usuarioEdicao->getTelefone() ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="20"
                    >
                </div>

                <div class="form-actions"><button type="submit">Salvar alterações</button>

                <a class="cancel-link" href="?pagina=usuarios">Cancelar edição</a></div>
            </form>
        </section>

    <?php endif; ?>

    <section id="lista-usuarios" class="record-section" aria-labelledby="titulo-lista">
        <div class="section-heading">
            <h3 id="titulo-lista">Usuários cadastrados</h3>
            <p>Consulte os dados e use as ações para editar ou excluir um cadastro.</p>
        </div>

    <?php if (empty($usuarios)): ?>

        <p class="empty-state">Nenhum usuário encontrado. Use o formulário acima para começar.</p>

    <?php else: ?>

        <div class="table-scroll" role="region" aria-labelledby="titulo-lista" tabindex="0">
        <table>
            <caption class="visually-hidden">Usuários cadastrados e ações disponíveis</caption>
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nome</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Telefone</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td data-label="ID"><?= $usuario->getIdUsuario() ?></td>

                        <td data-label="Nome"><?= htmlspecialchars($usuario->getNome(), ENT_QUOTES, 'UTF-8') ?></td>

                        <td data-label="E-mail"><?= htmlspecialchars($usuario->getEmail(), ENT_QUOTES, 'UTF-8') ?></td>

                        <td data-label="Telefone"><?= htmlspecialchars($usuario->getTelefone() ?? '-', ENT_QUOTES, 'UTF-8') ?></td>

                        <td data-label="Ações"><div class="table-actions"><a class="action-edit" href="?pagina=usuarios&editar=<?= $usuario->getIdUsuario() ?>#editar-usuario">
                                Editar
                            </a>

                            <form
                                method="post"
                                action="?pagina=usuarios"
                                style="display: inline;"
                                onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');"
                            >
                                <input
                                    type="hidden"
                                    name="acao"
                                    value="excluir"
                                >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id_usuario"
                                    value="<?= $usuario->getIdUsuario() ?>"
                                >

                                <button class="action-delete" type="submit">Excluir</button>
                            </form></div></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>

    <?php endif; ?>
    </section>
</main>

</body>
</html>
