<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DiagnoseFácil - Diagnósticos</title>
    <meta name="theme-color" content="#007ac2">
    <link rel="stylesheet" href="css/estilo.css?v=df-detalhes-1">
</head>
<body class="is-inner">

<?php require __DIR__ . '/partials/cabecalho.php'; ?>

<main id="main-content" class="shell main-content inner-view" tabindex="-1">
    <a class="back-link" href="?pagina=inicio"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-arrow-left"/></svg> Voltar ao início</a>
    <div class="page-heading">
        <p class="eyebrow">REGISTRO DE PROBLEMAS</p>
        <h2>Os problemas, organizados.</h2>
        <p class="section-description">Registre o que aconteceu e consulte os diagnósticos de cada usuário.</p>
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

    <section id="cadastro-diagnostico" class="cadastro-usuario surface-card" aria-labelledby="titulo-cadastro">
        <div class="section-heading">
            <h3 id="titulo-cadastro">Cadastrar diagnóstico</h3>
            <p>Selecione o usuário e descreva o problema encontrado.</p>
        </div>

        <form method="post" action="?pagina=diagnosticos">

            <input type="hidden" name="acao" value="cadastrar">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>"
            >

            <div class="campo">
                <label for="id_usuario">Usuário <span class="required" aria-hidden="true">*</span></label>

                <select id="id_usuario" name="id_usuario" required>
                    <option value="">Selecione um usuário</option>

                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= $usuario->getIdUsuario() ?>">
                            <?= htmlspecialchars($usuario->getNome(), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="descricao">Descrição do problema <span class="required" aria-hidden="true">*</span></label>

                <textarea
                    id="descricao"
                    name="descricao"
                    rows="4"
                    required
                    placeholder="Descreva o problema encontrado"
                ></textarea>
            </div>

            <div class="form-actions"><button type="submit"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-plus"/></svg> Cadastrar diagnóstico</button></div>
        </form>
    </section>

    <?php if ($diagnosticoEdicao !== null): ?>

        <section id="editar-diagnostico" class="cadastro-usuario surface-card edit-section" aria-labelledby="titulo-edicao">
            <h3 id="titulo-edicao">Editar diagnóstico</h3>

            <form method="post" action="?pagina=diagnosticos">

                <input type="hidden" name="acao" value="atualizar">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>"
                >

                <input
                    type="hidden"
                    name="id_diagnostico"
                    value="<?= $diagnosticoEdicao->getIdDiagnostico() ?>"
                >

                <div class="campo">
                    <label for="editar_usuario">Usuário <span class="required" aria-hidden="true">*</span></label>

                    <select id="editar_usuario" name="id_usuario" required>
                        <?php foreach ($usuarios as $usuario): ?>

                            <option
                                value="<?= $usuario->getIdUsuario() ?>"
                                <?= $usuario->getIdUsuario() === $diagnosticoEdicao->getIdUsuario() ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($usuario->getNome(), ENT_QUOTES, 'UTF-8') ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label for="editar_descricao">Descrição <span class="required" aria-hidden="true">*</span></label>

                    <textarea
                        id="editar_descricao"
                        name="descricao"
                        rows="4"
                        required
                    ><?= htmlspecialchars(
                        $diagnosticoEdicao->getDescricao(),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?></textarea>
                </div>

                <div class="form-actions"><button type="submit">Salvar alterações</button>

                <a class="cancel-link" href="?pagina=diagnosticos">Cancelar</a></div>
            </form>
        </section>

    <?php endif; ?>

    <section id="lista-diagnosticos" class="record-section" aria-labelledby="titulo-lista">
        <div class="section-heading">
            <h3 id="titulo-lista">Diagnósticos cadastrados</h3>
            <p>Descrição completa, data e usuário vinculado a cada registro.</p>
        </div>

    <?php if (empty($diagnosticos)): ?>

        <p class="empty-state">Nenhum diagnóstico encontrado. Use o formulário acima para começar.</p>

    <?php else: ?>

        <div class="table-scroll" role="region" aria-labelledby="titulo-lista" tabindex="0">
        <table>
            <caption class="visually-hidden">Diagnósticos cadastrados e ações disponíveis</caption>
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">ID do usuário</th>
                    <th scope="col">Descrição</th>
                    <th scope="col">Data</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($diagnosticos as $diagnostico): ?>
                    <tr>
                        <td data-label="ID"><?= $diagnostico->getIdDiagnostico() ?></td>

                        <td data-label="ID do usuário"><?= $diagnostico->getIdUsuario() ?></td>

                        <td data-label="Descrição"><?= htmlspecialchars(
                                $diagnostico->getDescricao(),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></td>

                        <td data-label="Data"><?= htmlspecialchars(
                                $diagnostico->getDataDiagnostico() ?? '-',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></td>

                        <td data-label="Ações"><div class="table-actions">
                            <a class="action-edit action-details" href="?pagina=diagnosticos&detalhes=<?= $diagnostico->getIdDiagnostico() ?>"
                                aria-label="Ver detalhes do diagnóstico <?= $diagnostico->getIdDiagnostico() ?>">
                                Ver detalhes
                            </a>
                            <a class="action-edit" href="?pagina=diagnosticos&editar=<?= $diagnostico->getIdDiagnostico() ?>#editar-diagnostico">
                                Editar
                            </a>

                            <form
                                method="post"
                                action="?pagina=diagnosticos"
                                style="display: inline;"
                                onsubmit="return confirm('Tem certeza que deseja excluir este diagnóstico?');"
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
                                    name="id_diagnostico"
                                    value="<?= $diagnostico->getIdDiagnostico() ?>"
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
