<a class="skip-link" href="#main-content">Ir para o conteúdo</a>
<svg class="sprite" aria-hidden="true" focusable="false" width="0" height="0">
    <symbol id="i-home" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6m3 10v-3a6 6 0 0 0-3-5"/></symbol>
    <symbol id="i-file" viewBox="0 0 24 24"><path d="M14 2H5v20h14V7l-5-5Z M14 2v6h5M8 12h8M8 16h6"/></symbol>
    <symbol id="i-zap" viewBox="0 0 24 24"><path d="m13 2-8 11h6l-1 9 9-12h-6V2Z"/></symbol>
    <symbol id="i-drop" viewBox="0 0 24 24"><path d="M12 2s-7 8-7 13a7 7 0 0 0 14 0c0-5-7-13-7-13Z M9 16c.2 1.2.9 1.8 2 2"/></symbol>
    <symbol id="i-plug" viewBox="0 0 24 24"><path d="M8 3v6m8-6v6M6 9h12v4a6 6 0 0 1-12 0V9Zm6 10v3"/></symbol>
    <symbol id="i-wind" viewBox="0 0 24 24"><path d="M3 8h12a3 3 0 1 0-3-3m-9 7h16a2 2 0 1 1-2 2M3 16h10a3 3 0 1 1-3 3"/></symbol>
    <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
    <symbol id="i-arrow-right" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></symbol>
    <symbol id="i-arrow-left" viewBox="0 0 24 24"><path d="m15 5-7 7 7 7"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></symbol>
</svg>
<header class="site-header">
    <div class="shell">
        <div class="topbar">
            <a class="brand" href="?pagina=inicio" aria-label="DiagnoseFácil — ir para o início">
                <span class="brand-mark"><svg class="icon " aria-hidden="true" focusable="false"><use href="#i-home"/></svg></span>
                <span class="brand-name">diagnose<span>fácil.</span></span>
            </a>
            <nav class="header-nav" aria-label="Menu principal">
                <a href="?pagina=inicio"<?= ($pagina ?? 'inicio') === 'inicio' ? ' aria-current="page"' : '' ?>>Início</a>
                <a href="?pagina=usuarios"<?= ($pagina ?? 'inicio') === 'usuarios' ? ' aria-current="page"' : '' ?>>Usuários</a>
                <a href="?pagina=diagnosticos"<?= ($pagina ?? 'inicio') === 'diagnosticos' ? ' aria-current="page"' : '' ?>>Diagnósticos</a>
            </nav>
        </div>
        <?php if (!in_array($pagina ?? 'inicio', ['usuarios', 'diagnosticos'], true)): ?>
        <div class="header-copy">
            <h1>Um lugar para cuidar da sua casa.</h1>
            <p>Organize usuários e registre os problemas encontrados em sua residência.</p>
        </div>
        <?php endif; ?>
    </div>
</header>
