(() => {
  'use strict';
  const chaveInterfaceLocal = 'diagnosefacil_canva_etapa1_relatos_v1';
  const labels = {eletrica:'Elétrica', hidraulica:'Hidráulica', eletrodomesticos:'Eletrodomésticos', climatizacao:'Ar e clima', outros:'Outros'};
  const sinceLabels = {hoje:'Hoje',dias:'Há alguns dias',semanas:'Há semanas ou mais',nao_sei:'Não sei'};
  const frequencyLabels = {constante:'O tempo todo',as_vezes:'Às vezes',uma_vez:'Aconteceu uma vez',nao_sei:'Não sei'};
  const riskLabels = {sim:'Sim — há sinal de perigo',nao:'Não percebi sinais de perigo',nao_sei:'Não tenho certeza'};
  const descriptionExamples = {
    eletrica:'Ex.: A tomada da sala está fazendo estalos.',
    hidraulica:'Ex.: A torneira da cozinha pinga mesmo fechada.',
    eletrodomesticos:'Ex.: A geladeira liga, mas não está resfriando.',
    climatizacao:'Ex.: O ar-condicionado liga, mas não esfria.',
    outros:'Descreva o que aconteceu e o que você percebeu.'
  };
  const views = ['home','analysis','review','result','history','detail','safety','help'];
  const $ = selector => document.querySelector(selector);
  const escapeHtml = value => String(value).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  let activeRecordId = null;
  let pendingRecord = null;
  let selectedPhotos = []; // [{file,url}], sempre temporárias
  let toastTimer;
  let currentView = 'home';
  let isSaving = false;
  let registros = [];
  let usuarios = [];
  let registroEmEdicao = null;
  let versaoConsulta = 0;
  let erroConsulta = '';
  let carregando = true;

  async function requisitar(dados) {
    const opcoes = { cache:'no-store' };
    if (dados) {
      opcoes.method = 'POST';
      opcoes.headers = {
        'Content-Type': 'application/json',
        'X-CSRF-Token': $('meta[name="csrf-token"]').content
      };
      opcoes.body = JSON.stringify(dados);
    }
    let resposta;
    let resultado;
    try {
      resposta = await fetch('?pagina=relatos-api', opcoes);
      resultado = await resposta.json();
    } catch {
      throw new Error('Não foi possível conectar ao DiagnoseFácil.');
    }
    if (!resposta.ok) throw new Error(resultado.erro || 'Não foi possível concluir a operação.');
    return resultado;
  }

  async function atualizarDados() {
    const consulta = ++versaoConsulta;
    carregando = true;
    erroConsulta = '';
    try {
      const dados = await requisitar();
      if (consulta !== versaoConsulta) return;
      registros = dados.registros;
      usuarios = dados.usuarios;
      for (const seletor of [$('#problem-user'), $('#import-user')]) {
        const selecionado = seletor.value;
        seletor.replaceChildren(new Option(
          usuarios.length ? 'Selecione um usuário' : 'Cadastre um usuário para continuar', ''
        ));
        for (const usuario of usuarios) seletor.add(new Option(usuario.nome, usuario.id));
        seletor.value = usuarios.some(usuario=>usuario.id===selecionado) ? selecionado : '';
      }
    } catch (erro) {
      if (consulta !== versaoConsulta) return;
      erroConsulta = erro.message;
      toast(erroConsulta);
    } finally {
      if (consulta === versaoConsulta) {
        carregando = false;
        updateHome();
        if (currentView === 'history') renderHistory();
      }
    }
  }
  function toast(message) {
    const el = $('#toast');
    clearTimeout(toastTimer);el.textContent=message;el.hidden=false;
    toastTimer=setTimeout(()=>el.hidden=true,3600);
  }
  function dateLabel(value) {
    if (!value) return 'Data desconhecida';
    const date=new Date(value.replace(' ', 'T'));
    return Number.isNaN(date.getTime()) ? 'Data desconhecida' : date.toLocaleString('pt-BR',{dateStyle:'short',timeStyle:'short'});
  }
  function updateHome() {
    const count=registros.length;
    $('#home-history-summary').textContent=count?`${count} ${count===1?'relato salvo':'relatos salvos'}`:'Consulte seus relatos anteriores';
  }
  function go(view) {
    if (!views.includes(view)) return;
    const previous = currentView;
    for (const id of views) $('#view-'+id).hidden=id!==view;
    currentView = view;
    document.body.classList.toggle('is-inner', view!=='home');
    if(view==='history') {
      renderHistory();
      if(previous!=='history') void atualizarDados();
    }
    if(view==='home') updateHome();
    const titles={analysis:'Nova análise',review:'Revisar relato',result:'Orientação inicial',history:'Histórico',detail:'Detalhes',safety:'Segurança',help:'Ajuda'};
    document.title=view==='home'?'DiagnoseFácil — Orientações para sua casa':(titles[view]+' | DiagnoseFácil');
    window.scrollTo({top:0,behavior:'instant'});
    if(view !== previous){
      const title = $('#view-'+view+' h2');
      if(title){title.setAttribute('tabindex','-1');title.focus({preventScroll:true});}
    }
  }
  function updateDescriptionExample() {
    const category=$('#problem-category').value;
    $('#problem-description').placeholder=descriptionExamples[category] || 'Ex.: A torneira da cozinha está pingando mesmo fechada.';
  }
  function updateDescriptionCount() {
    const campo=$('#problem-description');
    $('#description-counter').textContent=campo.value.length+'/'+campo.maxLength;
  }
  // Termos servem apenas para exibir cautela na interface; não detectam perigo de forma confiável.
  function mentionsAlertSignal(value) {
    const text=String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
    return /(?:cheiro\s+de\s+gas|vazamento\s+de\s+gas|fumaca|faisca|choque\s+eletrico|pegando\s+fogo|incendio|curto[- ]circuito|fio\s+desencapado|agua.{0,35}(?:tomada|fiacao)|tomada.{0,35}agua)/.test(text);
  }
  function updateRiskWarning() {
    const choice=$('input[name="danger"]:checked')?.value;
    $('#danger-warning').hidden=choice!=='sim'&&choice!=='nao_sei'&&!mentionsAlertSignal($('#problem-description').value);
  }
  function clearPhotos() {
    selectedPhotos.forEach(p=>URL.revokeObjectURL(p.url));
    selectedPhotos=[];
    $('#problem-photo').value='';
    renderPhotos();
  }
  function renderPhotos() {
    const list=$('#photo-list');
    list.replaceChildren();
    selectedPhotos.forEach((p,index)=>{
      const card=document.createElement('div');card.className='photo-item';
      const img=document.createElement('img');img.src=p.url;img.alt='Prévia da foto '+(index+1);
      const footer=document.createElement('div');footer.className='photo-item-footer';
      const name=document.createElement('span');name.className='photo-item-name';name.textContent=p.file.name;name.title=p.file.name;
      const remove=document.createElement('button');remove.type='button';remove.className='remove-photo';remove.dataset.removePhoto=String(index);remove.title='Remover '+p.file.name;remove.setAttribute('aria-label','Remover '+p.file.name);
      remove.innerHTML='<svg class="icon" aria-hidden="true"><use href="#i-x"/></svg>';
      footer.append(name,remove);card.append(img,footer);list.append(card);
    });
  }
  function addPhotos(files) {
    const accepted=['image/jpeg','image/png','image/webp'];
    for (const file of files) {
      if(!accepted.includes(file.type) || file.size===0 || file.size>8*1024*1024) {
        toast('Use imagens JPG, PNG ou WEBP de até 8 MB cada.');continue;
      }
      if(selectedPhotos.length>=3){toast('Você pode adicionar no máximo 3 fotos.');break;}
      if(selectedPhotos.some(p=>p.file.name===file.name && p.file.size===file.size && p.file.lastModified===file.lastModified)) {
        toast('Essa foto já foi adicionada.');continue;
      }
      selectedPhotos.push({file,url:URL.createObjectURL(file)});
    }
    $('#problem-photo').value='';
    renderPhotos();
  }
  function startAnalysis(category='') {
    $('#problem-form').reset();
    pendingRecord=null;
    registroEmEdicao=null;
    $('#problem-description').maxLength=1200;
    $('#analysis-heading').textContent='O que está acontecendo?';
    $('#confirm-save').setAttribute('aria-label','Salvar relato e ver orientação');
    clearFieldErrors();
    $('#problem-category').value=Object.hasOwn(labels,category)?category:'';
    updateDescriptionExample();updateDescriptionCount();updateRiskWarning();clearPhotos();$('#photo-fold').open=false;
    go('analysis');
    if(category)$('#problem-description').focus({preventScroll:true});
    else $('#problem-category').focus({preventScroll:true});
  }
  function clearFieldErrors() {
    document.querySelectorAll('#problem-form [aria-invalid]').forEach(el => el.removeAttribute('aria-invalid'));
    $('.risk-fieldset').removeAttribute('data-invalid');
    $('#form-error').hidden=true;
  }
  function showFormError(message,selector) {
    clearFieldErrors();
    const el=$('#form-error');el.textContent=message;el.hidden=false;
    const input=$(selector);
    if(input){
      input.setAttribute('aria-invalid','true');
      if(selector.includes('danger')) $('.risk-fieldset').dataset.invalid='true';
      input.focus({preventScroll:true});input.scrollIntoView({block:'center',behavior:'smooth'});
    }
  }
  function collectReport() {
    const form=$('#problem-form');
    const category=$('#problem-category').value;
    const description=$('#problem-description').value.trim();
    const danger=form.elements.danger.value;
    const userId=$('#problem-user').value;
    const usuario=usuarios.find(item=>item.id===userId);
    if(!usuario){showFormError(carregando?'Aguarde o carregamento dos usuários.':'Selecione um usuário cadastrado.','#problem-user');return null;}
    if(!Object.hasOwn(labels,category)){showFormError('Selecione a área do problema.','#problem-category');return null;}
    const minimo=registroEmEdicao===null?15:1;
    if(description.length<minimo){showFormError('Descreva o problema com pelo menos '+minimo+(minimo===1?' caractere.':' caracteres.'),'#problem-description');return null;}
    if(!['sim','nao','nao_sei'].includes(danger)){showFormError('Informe se você percebe algum sinal de perigo.','input[name="danger"]');return null;}
    clearFieldErrors();
    return {id:registroEmEdicao,userId,userName:usuario.nome,category,description,danger};
  }
  function renderReview() {
    const r=pendingRecord;
    if(!r)return;
    const data=[
      ['Usuário',r.userName],
      ['Área do problema',labels[r.category]],
      ['Descrição',r.description],
      ['Sinal de perigo',riskLabels[r.danger]],
      ['Fotos',selectedPhotos.length?`${selectedPhotos.length} ${selectedPhotos.length===1?'foto selecionada':'fotos selecionadas'}`:'Nenhuma foto adicionada']
    ];
    const dl=$('#review-content');dl.replaceChildren();
    for(const [label,value] of data){
      const box=document.createElement('div');box.className='review-cell';
      const dt=document.createElement('dt');dt.textContent=label;
      const dd=document.createElement('dd');dd.textContent=value;
      box.append(dt,dd);dl.append(box);
    }
    const strip=$('#review-photo-strip');
    strip.replaceChildren();
    strip.hidden=selectedPhotos.length===0;
    selectedPhotos.forEach((p,index)=>{
      const img=document.createElement('img');
      img.src=p.url;
      img.alt=`Foto ${index+1} para revisão: ${p.file.name}`;
      strip.append(img);
    });
    $('#review-warning').hidden=r.danger==='nao'&&!mentionsAlertSignal(r.description);
  }
  // Orientações ilustrativas; nenhuma causa ou urgência abaixo é um diagnóstico validado.
  function buildDemo(record) {
    const hasRedFlag=mentionsAlertSignal(record.description);
    const high=record.danger==='sim'||hasRedFlag;
    const uncertain=!high&&record.danger==='nao_sei';
    const pro={eletrica:'eletricista',hidraulica:'encanador',eletrodomesticos:'técnico de eletrodomésticos',climatizacao:'técnico de climatização',outros:'profissional especializado'}[record.category]||'profissional especializado';
    const scenario={
      eletrica:'Instalações, tomadas e aparelhos podem falhar por motivos diferentes. Sem avaliação, não é possível distinguir a origem.',
      hidraulica:'Vazamentos e gotejamentos podem ocorrer em pontos diferentes da instalação. A origem precisa ser verificada no local.',
      eletrodomesticos:'Um aparelho pode apresentar sintomas semelhantes por causas diferentes. A avaliação depende do equipamento e das condições de uso.',
      climatizacao:'Sistemas de climatização têm diferentes componentes. O relato, sozinho, não identifica a origem da falha.',
      outros:'As informações do relato não permitem confirmar uma origem específica.'
    }[record.category] || 'O relato sozinho não permite afirmar qual é a origem do problema.';
    if(high) return {
      level:'alto', chip:record.danger==='sim'?'Perigo informado':'Sinal de atenção no texto',headline:'Priorize sua segurança',
      explanation:record.danger==='sim'?'Você marcou que percebe um sinal de perigo. Evite qualquer tentativa de reparo.':'Seu texto menciona um sinal de atenção, mesmo sem perigo marcado. Isso não é uma avaliação técnica de risco.',
      cause:'Origem não confirmada',causeDescription:'Não é possível identificar a causa por este protótipo. Evite exposição ao possível risco.',
      guidance:[
        'Afaste-se da área se houver risco imediato.',
        'Não toque em fios, tomadas, equipamentos ou instalações possivelmente perigosos.',
        'Se houver suspeita de gás, evite chamas e interruptores e saia do local.',
        'Em emergência, acione o serviço adequado da sua região.'
      ],
      proTitle:'Procure ajuda especializada com prioridade',
      proDescription:`Conforme a situação, contate um ${pro}. Em perigo imediato, priorize serviços de emergência.`
    };
    if(uncertain) return {
      level:'medio',chip:'Risco não confirmado',headline:'Atenção antes de agir',
      explanation:'Você informou que não sabe se existe perigo. É melhor evitar tentativas de reparo até esclarecer a situação.',
      cause:'Origem não confirmada',causeDescription:scenario,
      guidance:[
        'Não tente desmontar nem consertar o equipamento por conta própria.',
        'Observe apenas sinais visíveis, mantendo distância de qualquer área suspeita.',
        'Caso perceba fumaça, faíscas, cheiro de gás ou água próxima de eletricidade, afaste-se.',
        'Procure avaliação profissional para esclarecer o risco.'
      ],
      proTitle:'Peça uma avaliação profissional',proDescription:`Um ${pro} pode verificar as condições e orientar o próximo passo com segurança.`
    };
    return {
      level:'informativo',chip:'Nenhum sinal marcado',headline:'Próximos cuidados',
      explanation:'Você marcou que não percebeu sinais de perigo. Isso não significa que a situação foi avaliada ou seja segura.',
      cause:'Origem não confirmada',causeDescription:scenario,
      guidance:[
        'Anote quando o problema acontece e quais sinais você percebe.',
        'Evite desmontar equipamentos ou mexer em instalações elétricas, hidráulicas ou de gás.',
        'Se aparecer algum sinal de perigo, interrompa a tentativa de solução e afaste-se.',
        'Procure um profissional se o problema continuar ou piorar.'
      ],
      proTitle:'Se necessário, consulte um profissional',proDescription:`Um ${pro} pode avaliar a situação e confirmar a causa antes de qualquer reparo.`
    };
  }
  function renderResult(record,origin='new') {
    activeRecordId=record.id;
    const r=buildDemo(record);
    $('#result-urgency').textContent=r.chip;
    $('#result-urgency').className='risk-chip risk-'+r.level;
    $('.result-hero').dataset.risk=r.level;
    $('#result-headline').textContent=r.headline;
    $('#result-explanation').textContent=r.explanation;
    $('#result-cause-title').textContent=r.cause;
    $('#result-cause-description').textContent=r.causeDescription;
    const ul=$('#result-guidance');ul.replaceChildren();
    for(const guidance of r.guidance){const li=document.createElement('li');li.textContent=guidance;ul.append(li);}
    $('#result-pro-title').textContent=r.proTitle;
    $('#result-pro-description').textContent=r.proDescription;
    const dl=$('#result-report-summary');dl.replaceChildren();
    for(const [label,value] of [['Usuário',record.userName],['Área',labels[record.category]||'Sem categoria'],['Descrição',record.description],['Sinal de perigo',riskLabels[record.danger]||'Não informado'],['Data',dateLabel(record.date)]]){
      const box=document.createElement('div');const dt=document.createElement('dt');dt.textContent=label;const dd=document.createElement('dd');dd.textContent=value;box.append(dt,dd);dl.append(box);
    }
    $('#result-back').dataset.go=origin==='detail'?'detail':'home';
    $('#result-back span').textContent=origin==='detail'?'Voltar aos detalhes':'Voltar ao início';
    go('result');
  }
  const normalizeSearch=value=>String(value||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLocaleLowerCase('pt-BR');
  function sortedRecords() {
    const instante = data => data ? Date.parse(data.replace(' ', 'T')) || 0 : 0;
    return [...registros].sort((a,b)=>{
      return instante(b.date)-instante(a.date) || Number(b.id)-Number(a.id);
    });
  }
  function renderHistory() {
    const records=sortedRecords();
    $('#history-tools').hidden=records.length===0;
    $('#history-meta').hidden=records.length===0;
    $('#export-history').disabled=records.length===0;
    const query=normalizeSearch($('#history-search').value.trim());
    const category=$('#history-category').value;
    const shown=records.filter(r=>(category==='all'||(r.category||'sem_categoria')===category)&&(!query||normalizeSearch(r.description+' '+r.userName+' '+(labels[r.category]||'')).includes(query)));
    const list=$('#history-list');
    $('#history-count').textContent=records.length?`${shown.length} de ${records.length} ${records.length===1?'registro':'registros'}`:'Nenhum registro salvo';
    if(!records.length){
      list.innerHTML='<div class="empty-state"><svg class="icon" aria-hidden="true"><use href="#i-clock"/></svg><h3></h3></div>';
      list.querySelector('h3').textContent=erroConsulta || (carregando?'Carregando relatos…':'Nenhum relato salvo');
      return;
    }
    if(!shown.length){
      list.innerHTML='<div class="empty-state"><svg class="icon" aria-hidden="true"><use href="#i-search"/></svg><h3>Nenhum registro encontrado</h3><p>Tente outra palavra ou escolha uma área diferente.</p><button type="button" class="outline-button" id="clear-history-filters">Limpar filtros</button></div>';
      return;
    }
    list.innerHTML=shown.map(r=>{
      const danger=r.danger==='sim'?'Perigo informado':r.danger==='nao_sei'?'Perigo incerto':r.danger==='nao'?'Perigo não percebido':'Perigo não informado';
      const dangerClass=r.danger==='sim'?'history-tag-danger':r.danger==='nao_sei'?'history-tag-uncertain':'history-tag-neutral';
      return `<button class="history-item" type="button" data-record-id="${escapeHtml(r.id)}" aria-label="Ver detalhes: ${escapeHtml(r.description.slice(0,80))}">
        <span class="history-item-icon"><svg class="icon" aria-hidden="true"><use href="#i-clock"/></svg></span>
        <span class="history-item-text">
          <span class="history-item-top"><span class="history-category">${escapeHtml(labels[r.category]||'Sem categoria')}</span><span class="history-date">${escapeHtml(dateLabel(r.date))}</span></span>
          <strong>${escapeHtml(r.description.slice(0,145))}${r.description.length>145?'…':''}</strong>
          <span class="history-tags"><span class="history-tag ${dangerClass}">${danger}</span><span class="history-tag history-tag-neutral">${escapeHtml(r.userName)}</span></span>
        </span>
        <svg class="icon history-chevron" aria-hidden="true"><use href="#i-arrow-right"/></svg>
      </button>`;
    }).join('');
  }
  function appendDetailField(dl,label,value) {
    const row=document.createElement('div');
    const dt=document.createElement('dt');dt.textContent=label;
    const dd=document.createElement('dd');dd.textContent=value;
    row.append(dt,dd);dl.append(row);
  }
  function openDetail(id) {
    const record=registros.find(r=>r.id===id);
    if(!record){toast('Registro não encontrado.');go('history');return;}
    activeRecordId=record.id;
    $('#detail-category').textContent=labels[record.category]||'Sem categoria';
    $('#detail-date').textContent=dateLabel(record.date);
    $('#detail-description').textContent=record.description;
    $('#detail-risk').textContent=record.danger==='sim'?'Sim':record.danger==='nao'?'Não':record.danger==='nao_sei'?'Não sei':'Não informado';
    $('#detail-risk').className='detail-risk-answer '+(record.danger==='sim'?'history-tag-danger':record.danger==='nao_sei'?'history-tag-uncertain':'history-tag-neutral');
    const extra=$('#detail-extra-fields');extra.replaceChildren();
    appendDetailField(extra,'Usuário',record.userName);
    if(record.location)appendDetailField(extra,'Local ou equipamento',record.location);
    if(record.since)appendDetailField(extra,'Quando começou',sinceLabels[record.since]||'Não informado');
    if(record.frequency)appendDetailField(extra,'Frequência',frequencyLabels[record.frequency]||'Não informado');
    const demo=record.demo===true;
    $('#detail-demo').hidden=!demo;
    $('#detail-legacy-note').hidden=demo;
    if(demo){
      const result=buildDemo(record);
      $('#detail-demo-cause').textContent=result.cause;
      $('#detail-demo-description').textContent='Veja o exemplo de orientação e cuidados relacionados a este relato.';
    }
    go('detail');
  }

  function exportHistory() {
    const data=sortedRecords();
    if(!data.length){toast('Não há registros para exportar.');return;}
    const json=JSON.stringify({app:'DiagnoseFácil',tipo:'historico_de_relatos',versao:1,exportadoEm:new Date().toISOString(),registros:data},null,2);
    const url=URL.createObjectURL(new Blob([json],{type:'application/json;charset=utf-8'}));
    const link=document.createElement('a');link.href=url;link.download='diagnosefacil-historico.json';document.body.append(link);link.click();link.remove();
    setTimeout(()=>URL.revokeObjectURL(url),1000);
    toast('Histórico exportado para um arquivo JSON.');
  }

  function selecionarDestinoImportacao() {
    const campo=$('#import-user');
    if(campo.value)return campo.value;
    toast(usuarios.length?'Selecione o usuário que receberá os relatos.':'Cadastre um usuário para importar relatos.');
    campo.focus();
    return null;
  }

  async function importarHistorico(historico, userId, fonte) {
    if(!Array.isArray(historico)||historico.length===0){
      toast('Nenhum relato foi encontrado para importar.');
      return;
    }
    if(historico.some(registro=>!registro||typeof registro!=='object'||Array.isArray(registro))){
      throw new Error('O histórico contém um relato inválido.');
    }
    const registros=historico.map(({id,description,category,danger,date,location,since,frequency})=>(
      {id,description,category,danger,date,location,since,frequency}
    ));
    isSaving=true;
    try {
      const {importados,repetidos}=await requisitar({acao:'importar',userId,fonte,registros});
      await atualizarDados();
      toast(`${importados} importado(s). ${repetidos} já estava(m) no histórico desse usuário.`);
    } finally {
      isSaving=false;
    }
  }
  function discardWarning(nextView) {
    if(nextView==='analysis' && currentView==='review') return false;
    if(!['analysis','review'].includes(currentView)) return false;
    if(['analysis','review'].includes(nextView)) return false;
    const hasContent=$('#problem-user').value || $('#problem-description').value.trim() || $('#problem-category').value || $('input[name="danger"]:checked') || selectedPhotos.length;
    if(!hasContent)return false;
    if(!window.confirm('Sair sem salvar este relato? As informações preenchidas serão descartadas.'))return true;
    clearPhotos();pendingRecord=null;$('#problem-form').reset();clearFieldErrors();updateDescriptionCount();updateRiskWarning();
    return false;
  }
  document.addEventListener('click',async event=>{
    if(isSaving)return;
    if(event.target.closest('.users-link,.user-management-link')){
      if(discardWarning('home'))event.preventDefault();
      return;
    }
    const category=event.target.closest('[data-category]');
    if(category){startAnalysis(category.dataset.category);return;}
    const remove=event.target.closest('[data-remove-photo]');
    if(remove){const index=Number(remove.dataset.removePhoto);if(!Number.isInteger(index)||index<0||index>=selectedPhotos.length)return;const [removed]=selectedPhotos.splice(index,1);URL.revokeObjectURL(removed.url);renderPhotos();return;}
    const record=event.target.closest('[data-record-id]');
    if(record){openDetail(record.dataset.recordId);return;}
    if(event.target.closest('#detail-result')){
      const record=registros.find(x=>x.id===activeRecordId);
      if(record?.demo===true)renderResult(record,'detail');
      else toast('Orientação não encontrada para este registro.');
      return;
    }
    if(event.target.closest('#export-history')){exportHistory();return;}
    if(event.target.closest('#edit-record')){
      const registro=registros.find(item=>item.id===activeRecordId);
      if(!registro)return;
      startAnalysis(registro.category || '');
      registroEmEdicao=registro.id;
      $('#analysis-heading').textContent='Editar relato';
      $('#problem-user').value=registro.userId;
      $('#problem-description').maxLength=5000;
      $('#problem-description').value=registro.description;
      if (registro.danger) $('#problem-form').elements.danger.value=registro.danger;
      updateDescriptionCount();updateRiskWarning();
      return;
    }
    if(event.target.closest('#clear-history-filters')){
      $('#history-search').value='';$('#history-category').value='all';renderHistory();return;
    }
    if(event.target.closest('#delete-record')){
      if(isSaving||!activeRecordId||!window.confirm('Excluir este relato salvo?'))return;
      const id=activeRecordId;
      isSaving=true;
      $('#delete-record').disabled=true;
      try {
        await requisitar({acao:'excluir',id});
        registros=registros.filter(item=>item.id!==id);
        activeRecordId=null;
        go('history');
        toast('Relato excluído.');
      } catch (erro) {
        toast(erro.message);
      } finally {
        isSaving=false;
        $('#delete-record').disabled=false;
      }
      return;
    }
    const button=event.target.closest('[data-go]');
    if(button){const dest=button.dataset.go;if(isSaving||discardWarning(dest))return;if(dest==='analysis')startAnalysis();else if(dest==='detail')openDetail(activeRecordId);else go(dest);}
  });
  $('#history-search').addEventListener('input',renderHistory);
  $('#history-category').addEventListener('change',renderHistory);
  $('#import-browser').addEventListener('click',async ()=>{
    if(isSaving)return;
    const userId=selecionarDestinoImportacao();
    if(userId===null)return;
    try {
      const historico=JSON.parse(localStorage.getItem(chaveInterfaceLocal)||'[]');
      await importarHistorico(historico,userId,'interface');
    } catch (erro) {
      toast(erro instanceof SyntaxError?'Não foi possível ler o histórico antigo.':erro.message);
    }
  });
  $('#import-file').addEventListener('click',()=>{
    if(!isSaving&&selecionarDestinoImportacao()!==null)$('#history-file').click();
  });
  $('#history-file').addEventListener('change',async event=>{
    const campo=event.currentTarget;
    const arquivo=campo.files[0];
    campo.value='';
    if(!arquivo||isSaving)return;
    const userId=selecionarDestinoImportacao();
    if(userId===null)return;
    if(arquivo.size>3000000){toast('Escolha um JSON de até 3 MB.');return;}
    try {
      const dados=JSON.parse(await arquivo.text());
      if(!Array.isArray(dados?.registros)||
        !['historico_local_demonstrativo','historico_de_relatos'].includes(dados.tipo)){
        toast('Escolha um JSON exportado pelo histórico do projeto.');
        return;
      }
      const fonte=dados.tipo==='historico_local_demonstrativo'?'interface':'integrado';
      await importarHistorico(dados.registros,userId,fonte);
    } catch (erro) {
      toast(erro instanceof SyntaxError?'O arquivo não contém um JSON válido.':erro.message);
    }
  });
  $('#problem-description').addEventListener('input',()=>{updateDescriptionCount();updateRiskWarning();clearFieldErrors();});
  $('#problem-category').addEventListener('change',()=>{updateDescriptionExample();clearFieldErrors();});
  $('#problem-user').addEventListener('change',clearFieldErrors);
  document.querySelectorAll('input[name="danger"]').forEach(input=>input.addEventListener('change',()=>{
    updateRiskWarning();clearFieldErrors();
  }));
  $('#choose-photos').addEventListener('click',()=>$('#problem-photo').click());
  $('#problem-photo').addEventListener('change',event=>addPhotos(Array.from(event.target.files||[])));
  $('#problem-form').addEventListener('submit',event=>{
    event.preventDefault();
    pendingRecord=collectReport();
    if(!pendingRecord)return;
    renderReview();go('review');
  });
  function backToEdit(){if(!isSaving)go('analysis');}
  $('#back-to-edit').addEventListener('click',backToEdit);
  $('#edit-report').addEventListener('click',backToEdit);
  $('#confirm-save').addEventListener('click',async ()=>{
    if(!pendingRecord || isSaving)return;
    isSaving=true;
    $('#confirm-save').disabled=true;
    const editando=registroEmEdicao!==null;
    try {
      const {id,userId,category,description,danger}=pendingRecord;
      const {registro}=await requisitar({acao:'salvar',id,userId,category,description,danger});
      ++versaoConsulta;
      carregando=false;
      registros=[registro,...registros.filter(item=>item.id!==registro.id)];
      registroEmEdicao=null;pendingRecord=null;clearPhotos();$('#problem-form').reset();
      updateHome();
      renderResult(registro,editando?'detail':'new');
      toast(editando?'Relato atualizado.':'Relato registrado.');
    } catch (erro) {
      toast(erro.message);
    } finally {
      isSaving=false;
      $('#confirm-save').disabled=false;
    }
  });
  const parametros=new URLSearchParams(window.location.search);
  if (parametros.get('view')==='history') go('history');
  else { go('home'); void atualizarDados(); }
})();
