<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#007ac2">
  <meta name="description" content="DiagnoseFácil: orientação inicial para problemas domésticos.">
  <title>DiagnoseFácil — Orientações para sua casa</title>
  <meta name="csrf-token" content="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>">
  <link rel="icon" type="image/svg+xml" href="interface/assets/favicon.svg">
  <link rel="stylesheet" href="interface/styles.css">
  <script src="interface/app.js?v=diagnosefacil" defer></script>
</head>
<body>
  <a class="skip-link" href="#main-content">Ir para o conteúdo</a>
  <svg aria-hidden="true" focusable="false" width="0" height="0" class="sprite">
    <symbol id="i-home" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10Z"/></symbol>
    <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 2 20 5v6c0 5-3.2 8.2-8 11-4.8-2.8-8-6-8-11V5l8-3Z"/><path d="m9 12 2 2 4-4"/></symbol>
    <symbol id="i-help" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.6 2.6 0 1 1 4 2.2c-1 .7-1.5 1.1-1.5 2.5"/><path d="M12 17.4h.01"/></symbol>
    <symbol id="i-zap" viewBox="0 0 24 24"><path d="m13 2-8 11h6l-1 9 9-12h-6l0-8Z"/></symbol>
    <symbol id="i-drop" viewBox="0 0 24 24"><path d="M12 2s-7 8-7 13a7 7 0 0 0 14 0c0-5-7-13-7-13Z"/><path d="M9 16c.2 1.2.9 1.8 2 2"/></symbol>
    <symbol id="i-plug" viewBox="0 0 24 24"><path d="M8 3v6m8-6v6M6 9h12v4a6 6 0 0 1-12 0V9Zm6 10v3"/></symbol>
    <symbol id="i-wind" viewBox="0 0 24 24"><path d="M3 8h12a3 3 0 1 0-3-3m-9 7h16a2 2 0 1 1-2 2M3 16h10a3 3 0 1 1-3 3"/></symbol>
    <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
    <symbol id="i-arrow-right" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></symbol>
    <symbol id="i-arrow-left" viewBox="0 0 24 24"><path d="m15 5-7 7 7 7"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></symbol>
    <symbol id="i-camera" viewBox="0 0 24 24"><path d="M4 7h4l2-3h4l2 3h4v13H4V7Z"/><circle cx="12" cy="13" r="3"/></symbol>
    <symbol id="i-trash" viewBox="0 0 24 24"><path d="M4 7h16M10 3h4l1 4H9l1-4Zm-4 4 1 14h10l1-14M10 11v6m4-6v6"/></symbol>
    <symbol id="i-alert" viewBox="0 0 24 24"><path d="m12 3 10 18H2L12 3Z"/><path d="M12 9v5m0 3h.01"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol>
    <symbol id="i-x" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></symbol>
    <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 10h18"/></symbol>
    <symbol id="i-info" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10h.01"/></symbol>
    <symbol id="i-filter" viewBox="0 0 24 24"><path d="M4 7h16M7 12h10m-7 5h4"/></symbol>
    <symbol id="i-search" viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="7"/><path d="m16 16 5 5"/></symbol>
  </svg>

  <header class="site-header">
    <div class="shell">
      <div class="topbar">
        <button type="button" class="brand" data-go="home" aria-label="DiagnoseFácil — ir para o início">
          <span class="brand-mark"><svg class="icon" aria-hidden="true"><use href="#i-home"/></svg></span>
          <span class="brand-name">diagnose<span>fácil.</span></span>
        </button>
        <div class="header-actions">
          <a class="users-link" href="?pagina=usuarios">Usuários</a>
          <button class="icon-button" type="button" data-go="safety" title="Cuidados de segurança" aria-label="Cuidados de segurança"><svg class="icon"><use href="#i-shield"/></svg></button>
          <button class="icon-button" type="button" data-go="help" title="Ajuda" aria-label="Ajuda"><svg class="icon"><use href="#i-help"/></svg></button>
        </div>
      </div>
      <div class="header-copy">
        <h1>Seu assistente para cuidar da sua casa.</h1>
        <p>Descreva um problema e veja cuidados iniciais.</p>
      </div>
    </div>
  </header>

  <main id="main-content" class="main-content shell" tabindex="-1">
    <section id="view-home" class="view" aria-labelledby="home-heading">
      <div class="intro-section">
        <p class="eyebrow">COMECE POR AQUI</p>
        <h2 id="home-heading">Como podemos ajudar?</h2>
        <p class="section-description">Escolha uma área ou descreva o problema.</p>
      </div>

      <div class="category-grid" role="group" aria-label="Escolha a área do problema">
        <button class="category" type="button" data-category="eletrica"><span class="category-icon"><svg class="icon"><use href="#i-zap"/></svg></span><span>Elétrica</span></button>
        <button class="category" type="button" data-category="hidraulica"><span class="category-icon"><svg class="icon"><use href="#i-drop"/></svg></span><span>Hidráulica</span></button>
        <button class="category" type="button" data-category="eletrodomesticos"><span class="category-icon"><svg class="icon"><use href="#i-plug"/></svg></span><span>Eletrodomésticos</span></button>
        <button class="category" type="button" data-category="climatizacao"><span class="category-icon"><svg class="icon"><use href="#i-wind"/></svg></span><span>Ar e clima</span></button>
      </div>

      <div class="quick-actions">
        <button class="action-card action-card-primary" data-go="analysis" type="button">
          <span class="action-symbol"><svg class="icon"><use href="#i-plus"/></svg></span>
          <span class="action-copy"><strong>Identificar o problema</strong><small>Conte o que aconteceu</small></span>
          <svg class="icon action-chevron" aria-hidden="true"><use href="#i-arrow-right"/></svg>
        </button>
        <button class="action-card action-card-secondary" data-go="history" type="button">
          <span class="action-symbol"><svg class="icon"><use href="#i-clock"/></svg></span>
          <span class="action-copy"><strong>Histórico de análises</strong><small id="home-history-summary">Consulte seus relatos anteriores</small></span>
          <svg class="icon action-chevron" aria-hidden="true"><use href="#i-arrow-right"/></svg>
        </button>
      </div>
      <p class="disclaimer">Versão demonstrativa: sem diagnóstico por IA nem avaliação técnica.</p>
    </section>

    <section id="view-analysis" class="view inner-view analysis-view" aria-labelledby="analysis-heading" hidden>
      <button type="button" class="back-link" data-go="home"><svg class="icon"><use href="#i-arrow-left"/></svg> Voltar ao início</button>
      <p class="eyebrow">IDENTIFICAÇÃO DO PROBLEMA</p>
      <h2 id="analysis-heading">O que está acontecendo?</h2>
      <p class="section-description">Explique o que percebeu, sem precisar usar termos técnicos.</p>

      <div class="analysis-layout">
        <form id="problem-form" class="surface-card analysis-form" novalidate>
          <p class="required-note">Os campos com * são obrigatórios.</p>
          <div class="field">
            <label for="problem-user">Usuário <span class="required" aria-hidden="true">*</span></label>
            <select id="problem-user" name="userId" required>
              <option value="">Selecione um usuário</option>
            </select>
            <a class="user-management-link" href="?pagina=usuarios">Gerenciar usuários</a>
          </div>
          <div class="field">
            <label for="problem-category">Área do problema <span class="required" aria-hidden="true">*</span></label>
            <select id="problem-category" name="category" required>
              <option value="">Selecione uma área</option>
              <option value="eletrica">Elétrica</option>
              <option value="hidraulica">Hidráulica</option>
              <option value="eletrodomesticos">Eletrodomésticos</option>
              <option value="climatizacao">Ar e clima</option>
              <option value="outros">Outro problema doméstico</option>
            </select>
          </div>

          <div class="field">
            <div class="field-label-row">
              <label for="problem-description">Descreva o problema <span class="required" aria-hidden="true">*</span></label>
              <span id="description-counter" class="counter">0/1200</span>
            </div>
            <textarea id="problem-description" name="description" rows="5" minlength="15" maxlength="1200" placeholder="Ex.: A torneira da cozinha está pingando mesmo fechada." aria-describedby="description-help" required></textarea>
            <small id="description-help">Pelo menos 15 caracteres.</small>
          </div>

          <fieldset class="field fieldset risk-fieldset" aria-describedby="danger-hint">
            <legend>Há algum sinal de perigo? <span class="required" aria-hidden="true">*</span></legend>
            <p id="danger-hint">Ex.: cheiro de gás, fumaça, faíscas ou água perto de tomadas.</p>
            <div class="radio-options">
              <label><input type="radio" name="danger" value="nao" required> Não</label>
              <label><input type="radio" name="danger" value="sim"> Sim</label>
              <label><input type="radio" name="danger" value="nao_sei"> Não sei</label>
            </div>
          </fieldset>

          <div id="danger-warning" class="danger-warning" role="alert" hidden>
            <svg class="icon" aria-hidden="true"><use href="#i-alert"/></svg>
            <div><strong>Atenção ao sinal informado.</strong><p>Se houver risco, mantenha distância. Não toque em instalações suspeitas e procure ajuda especializada ou serviços de emergência, conforme a situação.</p></div>
          </div>

          <details class="photo-fold" id="photo-fold">
            <summary><svg class="icon" aria-hidden="true"><use href="#i-camera"/></svg><span>Adicionar fotos</span><small>Opcional</small><svg class="icon photo-fold-chevron" aria-hidden="true"><use href="#i-arrow-right"/></svg></summary>
            <div class="photo-fold-body">
              <button type="button" class="upload-field" id="choose-photos"><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg> Escolher imagens</button>
              <input id="problem-photo" hidden name="photo" type="file" accept="image/png,image/jpeg,image/webp" multiple aria-describedby="photo-hint">
              <p id="photo-hint" class="field-hint">Até 3 fotos, no máximo 8 MB cada. Serão apenas prévias e não serão salvas.</p>
              <div id="photo-list" class="photo-list" aria-live="polite"></div>
            </div>
          </details>

          <div id="form-error" class="form-error" role="alert" hidden></div>
          <button class="submit-button" type="submit">Revisar informações <svg class="icon"><use href="#i-arrow-right"/></svg></button>
          
        </form>
      </div>
    </section>

    <section id="view-review" class="view inner-view review-view" aria-labelledby="review-heading" hidden>
      <button type="button" class="back-link" id="back-to-edit"><svg class="icon"><use href="#i-arrow-left"/></svg> Voltar e editar</button>
      <p class="eyebrow">REVISÃO DAS INFORMAÇÕES</p>
      <h2 id="review-heading">Tudo certo com seu relato?</h2>
      <p class="section-description">Confira os dados antes de continuar.</p>

      <div class="surface-card review-card">
        <dl id="review-content" class="review-grid" aria-label="Resumo do problema informado"></dl>
        <div id="review-photo-strip" class="review-photo-strip" aria-label="Fotos selecionadas" hidden></div>

        <div id="review-warning" class="danger-warning" role="alert" hidden>
          <svg class="icon" aria-hidden="true"><use href="#i-alert"/></svg>
          <div><strong>Segurança em primeiro lugar.</strong><p>Se houver perigo, não tente consertar sozinho. Afaste-se da área e procure ajuda especializada ou os serviços de emergência, se necessário.</p></div>
        </div>

        <div class="review-actions">
          <button id="edit-report" type="button" class="outline-button">Editar informações</button>
          <button id="confirm-save" type="button" class="submit-button">Ver orientação inicial <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"/></svg></button>
        </div>
        <p class="form-footnote">Ao confirmar, o relato será salvo no banco. A orientação é demonstrativa.</p>
      </div>
    </section>

    <section id="view-result" class="view inner-view result-view" aria-labelledby="result-heading" hidden>
      <button type="button" class="back-link" id="result-back" data-go="home"><svg class="icon"><use href="#i-arrow-left"/></svg><span>Voltar ao início</span></button>
      <p class="eyebrow">ORIENTAÇÃO INICIAL</p>
      <h2 id="result-heading">Orientação para seu relato</h2>
      <p class="section-description">Cuidados iniciais com base nas informações que você preencheu.</p>

      <div class="result-demo-notice" role="note"><svg class="icon" aria-hidden="true"><use href="#i-info"/></svg><span><strong>Exemplo, não diagnóstico.</strong> Não há IA nem análise das fotos. Esta página não confirma a causa nem avalia o risco real.</span></div>

      <article class="result-hero surface-card" aria-labelledby="result-headline">
        <div class="result-hero-top"><span class="result-kicker">INFORMAÇÕES DO RELATO</span><span id="result-urgency" class="risk-chip">A avaliar</span></div>
        <h3 id="result-headline">Orientação inicial</h3>
        <p id="result-explanation">A orientação considera apenas as informações preenchidas.</p>
      </article>

      <div class="result-grid">
        <article class="surface-card result-information">
          <div class="result-section-heading"><span class="result-section-icon"><svg class="icon"><use href="#i-search"/></svg></span><h3>Sobre a causa</h3></div>
          <p id="result-cause-title" class="result-strong"></p>
          <p id="result-cause-description" class="result-supporting"></p>
        </article>
        <article class="surface-card result-information">
          <div class="result-section-heading"><span class="result-section-icon"><svg class="icon"><use href="#i-shield"/></svg></span><h3>O que fazer agora</h3></div>
          <ul id="result-guidance" class="guidance-list"></ul>
        </article>
      </div>

      <article class="result-professional" aria-label="Profissional recomendado">
        <span class="result-professional-icon"><svg class="icon"><use href="#i-help"/></svg></span>
        <div><h3 id="result-pro-title">Quando procurar ajuda?</h3><p id="result-pro-description"></p></div>
      </article>

      <details class="result-report-details">
        <summary>Ver informações do relato <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"/></svg></summary>
        <dl id="result-report-summary" class="result-report-summary"></dl>
      </details>

      <div class="result-bottom-actions">
        <button type="button" class="outline-button" data-go="analysis"><svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg> Nova análise</button>
        <button type="button" class="submit-button" data-go="history">Ver histórico <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"/></svg></button>
      </div>
      <p class="result-ending-note">Esta orientação não substitui uma inspeção profissional. Havendo perigo imediato, afaste-se e acione ajuda adequada.</p>
    </section>

    <section id="view-history" class="view inner-view history-view" aria-labelledby="history-heading" hidden>
      <button type="button" class="back-link" data-go="home"><svg class="icon"><use href="#i-arrow-left"/></svg> Voltar ao início</button>
      
      <h2 id="history-heading">Histórico de análises</h2>
      <p class="section-description">Consulte, filtre e edite os relatos cadastrados.</p>

      <div class="history-tools" id="history-tools" aria-label="Buscar e filtrar histórico">
        <div class="history-field history-search-field">
          <label for="history-search">Buscar relato</label>
          <div class="history-search-wrap"><svg class="icon" aria-hidden="true"><use href="#i-search"/></svg><input type="search" id="history-search" placeholder="Ex.: torneira pingando" autocomplete="off"></div>
        </div>
        <div class="history-field history-category-field">
          <label for="history-category">Área do problema</label>
          <select id="history-category">
            <option value="all">Todas as áreas</option>
            <option value="eletrica">Elétrica</option>
            <option value="hidraulica">Hidráulica</option>
            <option value="eletrodomesticos">Eletrodomésticos</option>
            <option value="climatizacao">Ar e clima</option>
            <option value="outros">Outros</option>
            <option value="sem_categoria">Sem categoria</option>
          </select>
        </div>
      </div>
      <div class="history-meta" id="history-meta"><span id="history-count" role="status" aria-live="polite"></span></div>
      <div id="history-list" class="history-list"></div>
      <div class="history-bottom-row">
        <button type="button" class="outline-button history-new" data-go="analysis">Nova análise <svg class="icon" aria-hidden="true"><use href="#i-plus"/></svg></button>
        <details class="data-controls" id="data-controls">
          <summary>Importar e exportar histórico</summary>
          <div class="data-controls-content">
            <div class="data-controls-buttons">
              <button type="button" id="export-history" class="outline-button">Baixar histórico (.json)</button>
            </div>
            <div class="history-import">
              <h3>Importar relatos</h3>
              <p id="import-help">Escolha quem receberá os relatos. Use o histórico local do navegador ou um JSON exportado pelo DF Interface ou pelo DiagnoseFácil integrado.</p>
              <div class="field">
                <label for="import-user">Vincular relatos a</label>
                <select id="import-user" aria-describedby="import-help">
                  <option value="">Selecione um usuário</option>
                </select>
              </div>
              <div class="data-controls-buttons">
                <button type="button" id="import-browser" class="outline-button">Importar deste navegador</button>
                <button type="button" id="import-file" class="outline-button">Importar arquivo JSON</button>
                <input type="file" id="history-file" accept=".json,application/json" hidden>
              </div>
              <a href="interface-local/index.html">Abrir o DF Interface local</a>
            </div>
          </div>
        </details>
      </div>
    </section>

    <section id="view-detail" class="view inner-view detail-view" aria-labelledby="detail-heading" hidden>
      <button type="button" class="back-link" data-go="history"><svg class="icon"><use href="#i-arrow-left"/></svg> Voltar ao histórico</button>
      <p class="eyebrow">REGISTRO ANTERIOR</p>
      <h2 id="detail-heading">Detalhes do relato</h2>
      <p class="section-description">Informações que você forneceu ao iniciar este relato.</p>

      <article class="surface-card detail-overview">
        <div class="detail-topline"><span id="detail-category" class="detail-category"></span><span id="detail-date" class="detail-date"></span></div>
        <h3 id="detail-description"></h3>
        <div class="detail-risk-line"><span class="detail-risk-title">Sinal de perigo informado</span><span id="detail-risk" class="detail-risk-answer"></span></div>
        <dl id="detail-extra-fields" class="detail-extra-fields"></dl>
      </article>

      <div id="detail-demo" class="detail-demo" hidden>
        <p class="detail-note"><svg class="icon" aria-hidden="true"><use href="#i-info"/></svg> <span>Este registro tem uma <strong>orientação ilustrativa</strong>, gerada por regras simples. Não é um diagnóstico por IA.</span></p>
        <div class="detail-result-teaser surface-card">
          <div><p class="detail-teaser-kicker">ORIENTAÇÃO DEMONSTRATIVA</p><h3 id="detail-demo-cause"></h3><p id="detail-demo-description"></p></div>
          <button type="button" id="detail-result" class="submit-button">Ver orientação <svg class="icon"><use href="#i-arrow-right"/></svg></button>
        </div>
      </div>
      <p id="detail-legacy-note" class="detail-note" hidden><svg class="icon" aria-hidden="true"><use href="#i-info"/></svg><span>Complete a área e o sinal de perigo para consultar a orientação demonstrativa.</span></p>
      <div class="detail-bottom-actions"><button type="button" id="edit-record" class="outline-button">Editar relato</button><button type="button" id="delete-record" class="delete-button"><svg class="icon"><use href="#i-trash"/></svg> Excluir registro</button></div>
    </section>

    <section id="view-safety" class="view inner-view" aria-labelledby="safety-heading" hidden>
      <button type="button" class="back-link" data-go="home"><svg class="icon"><use href="#i-arrow-left"/></svg> Voltar ao início</button>
      <p class="eyebrow">CUIDADOS IMPORTANTES</p>
      <h2 id="safety-heading">Segurança em primeiro lugar.</h2>
      <div class="surface-card info-content">
        <p>Alguns problemas precisam da avaliação de um profissional, especialmente quando há risco para pessoas ou para a casa.</p>
        <ul><li>Cheiro de gás ou suspeita de vazamento.</li><li>Fumaça, faíscas ou cheiro intenso de queimado.</li><li>Água próxima de tomadas ou instalações elétricas.</li><li>Risco de incêndio, choque ou alagamento.</li></ul>
        <p><strong>Se houver perigo imediato, afaste-se da área e procure os serviços de emergência adequados.</strong></p>
      </div>
    </section>

    <section id="view-help" class="view inner-view" aria-labelledby="help-heading" hidden>
      <button type="button" class="back-link" data-go="home"><svg class="icon"><use href="#i-arrow-left"/></svg> Voltar ao início</button>
      <p class="eyebrow">SOBRE O PROJETO</p>
      <h2 id="help-heading">Como funciona o DiagnoseFácil?</h2>
      <div class="surface-card info-content">
        <p>O DiagnoseFácil é um projeto para ajudar pessoas a entender possíveis problemas domésticos, como falhas elétricas, hidráulicas, em eletrodomésticos e climatização.</p>
        <p>Você pode vincular um relato a um usuário, revisar os dados e consultar orientações demonstrativas baseadas em regras simples. O histórico fica salvo no banco do projeto.</p>
        <p>As fotos servem para revisar o relato enquanto você preenche o formulário. Elas ficam no navegador durante essa etapa.</p>
        <p>Para consultar os relatos antigos, abra o <a href="interface-local/index.html">DF Interface local</a> ou importe os dados pelo histórico. O <a href="?pagina=painel">painel do DF POO</a> reúne os atalhos de cadastro.</p>
      </div>
    </section>
  </main>
  <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true" hidden></div>
  <noscript><p class="javascript-note">Ative o JavaScript para navegar pelas telas de relato e histórico.</p></noscript>
</body>
</html>
