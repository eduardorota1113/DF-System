<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#007ac2">
    <title>DiagnoseFácil - Início</title>
    <link rel="stylesheet" href="css/estilo.css?v=df-interface-1">
</head>
<body>
    <?php require __DIR__ . '/partials/cabecalho.php'; ?>
    <main id="main-content" class="shell home-content" tabindex="-1">
        <section class="intro-section" aria-labelledby="home-heading">
            <h2 id="home-heading">Como podemos ajudar?</h2>
            <p class="section-description">Registre um problema ou consulte os diagnósticos cadastrados.</p>
        </section>
        <div class="category-grid" role="group" aria-label="Exemplos de problemas que você pode registrar">
            <div class="category"><span class="category-icon"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-zap"/></svg></span><span>Elétrica</span></div>
            <div class="category"><span class="category-icon"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-drop"/></svg></span><span>Hidráulica</span></div>
            <div class="category"><span class="category-icon"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-plug"/></svg></span><span>Eletrodomésticos</span></div>
            <div class="category"><span class="category-icon"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-wind"/></svg></span><span>Ar e clima</span></div>
        </div>
        <nav class="quick-actions" aria-label="Ações do sistema">
            <a class="action-card action-card-primary" href="?pagina=diagnosticos#cadastro-diagnostico">
                <span class="action-symbol"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-plus"/></svg></span>
                <span class="action-copy"><strong>Registrar um problema</strong><small>Cadastre um diagnóstico e vincule ao usuário.</small></span>
                <svg class="icon action-chevron" aria-hidden="true" focusable="false"><use href="#i-arrow-right"/></svg>
            </a>
            <a class="action-card action-card-secondary" href="?pagina=diagnosticos#lista-diagnosticos">
                <span class="action-symbol"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-clock"/></svg></span>
                <span class="action-copy"><strong>Consultar diagnósticos</strong><small>Veja os registros e edite as informações.</small></span>
                <svg class="icon action-chevron" aria-hidden="true" focusable="false"><use href="#i-arrow-right"/></svg>
            </a>
            <a class="action-card action-card-secondary" href="?pagina=usuarios">
                <span class="action-symbol"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-users"/></svg></span>
                <span class="action-copy"><strong>Gerenciar usuários</strong><small>Cadastre, consulte e atualize os dados dos usuários.</small></span>
                <svg class="icon action-chevron" aria-hidden="true" focusable="false"><use href="#i-arrow-right"/></svg>
            </a>
        </nav>
        <section class="resumo-painel" aria-label="Resumo dos cadastros">
            <div class="resumo-item"><span class="stat-icon"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-users"/></svg></span><div><strong><?= (int) $resumo['usuarios'] ?></strong><span>Usuários cadastrados</span></div></div>
            <div class="resumo-item"><span class="stat-icon"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-file"/></svg></span><div><strong><?= (int) $resumo['diagnosticos'] ?></strong><span>Diagnósticos cadastrados</span></div></div>
        </section>
    </main>
</body>
</html>
