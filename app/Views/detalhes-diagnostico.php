<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DiagnoseFácil - Detalhes do diagnóstico</title>
    <meta name="theme-color" content="#007ac2">
    <link rel="stylesheet" href="css/estilo.css?v=df-detalhes-1">
</head>
<body class="is-inner">
<?php require __DIR__ . '/partials/cabecalho.php'; ?>

<main id="main-content" class="shell main-content inner-view" tabindex="-1">
    <a class="back-link" href="?pagina=diagnosticos#lista-diagnosticos">
        <svg class="icon" aria-hidden="true" focusable="false"><use href="#i-arrow-left"/></svg>
        Voltar aos diagnósticos
    </a>

    <?php if ($detalhes === null): ?>
        <div class="page-heading">
            <h2>Diagnóstico não encontrado</h2>
            <p class="section-description">Volte à lista para consultar os registros cadastrados.</p>
        </div>
    <?php else: ?>
        <?php $diagnostico = $detalhes['diagnostico']; ?>
        <div class="page-heading">
            <h2>Detalhes do diagnóstico #<?= $diagnostico->getIdDiagnostico() ?></h2>
        </div>

        <section class="surface-card diagnostico-detalhes" aria-label="Dados do diagnóstico">
            <dl>
                <div>
                    <dt>Usuário</dt>
                    <dd><?= htmlspecialchars($detalhes['nome_usuario'], ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
                <div>
                    <dt>Data do registro</dt>
                    <dd><?= htmlspecialchars($diagnostico->getDataDiagnostico() ?? '—', ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
                <div>
                    <dt>Descrição do problema</dt>
                    <dd class="diagnostico-descricao"><?= htmlspecialchars($diagnostico->getDescricao(), ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
            </dl>

            <div class="form-actions">
                <a class="action-edit" href="?pagina=diagnosticos&editar=<?= $diagnostico->getIdDiagnostico() ?>#editar-diagnostico">Editar diagnóstico</a>
            </div>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
