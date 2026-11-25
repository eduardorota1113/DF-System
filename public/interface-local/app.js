/* DiagnoseFácil — refinamento v1.1 das interfaces (frontend demonstrativo).
 * Regras simples, sem IA, API, análise de fotos ou banco de dados remoto. Fotos temporárias.
 */
(() => {
  'use strict';
  // Mantém a mesma chave da etapa 1 para preservar relatos já registrados no navegador.
  const KEY = 'diagnosefacil_canva_etapa1_relatos_v1';
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

  function getRecords() {
    try {
      const value = JSON.parse(localStorage.getItem(KEY) || '[]');
      return Array.isArray(value) ? value.filter(r =>
        r && typeof r.id === 'string' && r.id.length > 0 && r.id.length < 200 &&
        typeof r.description === 'string' && r.description.length > 0 &&
        r.description.length <= 5000 && typeof r.category === 'string' &&
        Object.hasOwn(labels,r.category) &&
        ['sim','nao','nao_sei',undefined].includes(r.danger)
      ).slice(0,100) : [];
    } catch { return []; }
  }
  function saveRecords(records) {
    try {localStorage.setItem(KEY,JSON.stringify(records.slice(0,100)));return true;}
    catch {toast('Não foi possível salvar neste navegador.');return false;}
  }
  function toast(message) {
    const el = $('#toast');
    clearTimeout(toastTimer);el.textContent=message;el.hidden=false;
    toastTimer=setTimeout(()=>el.hidden=true,3600);
  }
  function dateLabel(value) {
    const date=new Date(value);
    return Number.isNaN(date.getTime()) ? 'Data desconhecida' : date.toLocaleString('pt-BR',{dateStyle:'short',timeStyle:'short'});
  }
  function updateHome() {
    const count=getRecords().length;
    $('#home-history-summary').textContent=count?`${count} ${count===1?'relato salvo':'relatos salvos'} neste navegador`:'Suas análises anteriores aparecerão aqui';
  }
  function go(view) {
    if (!views.includes(view)) return;
    const previous = currentView;
    for (const id of views) $('#view-'+id).hidden=id!==view;
    currentView = view;
    document.body.classList.toggle('is-inner', view!=='home');
    if(view==='history') renderHistory();
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
    $('#description-counter').textContent=$('#problem-description').value.length+'/1200';
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
    if(!Object.hasOwn(labels,category)){showFormError('Selecione a área do problema.','#problem-category');return null;}
    if(description.length<15){showFormError('Descreva o problema com pelo menos 15 caracteres.','#problem-description');return null;}
    if(!['sim','nao','nao_sei'].includes(danger)){showFormError('Informe se você percebe algum sinal de perigo.','input[name="danger"]');return null;}
    clearFieldErrors();
    return {category,description,danger};
  }
  function renderReview() {
    const r=pendingRecord;
    if(!r)return;
    const data=[
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
    for(const [label,value] of [['Área',labels[record.category]||'Outra'],['Descrição',record.description],['Sinal de perigo',riskLabels[record.danger]||'Não informado'],['Data',dateLabel(record.date)]]){
      const box=document.createElement('div');const dt=document.createElement('dt');dt.textContent=label;const dd=document.createElement('dd');dd.textContent=value;box.append(dt,dd);dl.append(box);
    }
    $('#result-back').dataset.go=origin==='detail'?'detail':'home';
    $('#result-back span').textContent=origin==='detail'?'Voltar aos detalhes':'Voltar ao início';
    go('result');
  }
  const normalizeSearch=value=>String(value||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLocaleLowerCase('pt-BR');
  function sortedRecords() {
    return getRecords().map((record,index)=>({record,index})).sort((a,b)=>{
      const da=Date.parse(a.record.date),db=Date.parse(b.record.date);
      const ta=Number.isFinite(da)?da:0,tb=Number.isFinite(db)?db:0;
      return tb-ta||a.index-b.index;
    }).map(x=>x.record);
  }
  function renderHistory() {
    const records=sortedRecords();
    $('#history-tools').hidden=records.length===0;
    $('#history-meta').hidden=records.length===0;
    $('#data-controls').hidden=records.length===0;
    $('#export-history').disabled=records.length===0;
    $('#clear-history').disabled=records.length===0;
    const query=normalizeSearch($('#history-search').value.trim());
    const category=$('#history-category').value;
    const shown=records.filter(r=>(category==='all'||r.category===category)&&(!query||normalizeSearch(r.description+' '+(labels[r.category]||'')).includes(query)));
    const list=$('#history-list');
    $('#history-count').textContent=records.length?`${shown.length} de ${records.length} ${records.length===1?'registro':'registros'}`:'Nenhum registro salvo';
    if(!records.length){
      list.innerHTML='<div class="empty-state"><svg class="icon" aria-hidden="true"><use href="#i-clock"/></svg><h3>Nenhum relato salvo</h3><p>Se o navegador permitir salvar dados, seus relatos aparecerão aqui.</p></div>';
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
          <span class="history-item-top"><span class="history-category">${escapeHtml(labels[r.category]||'Outro problema')}</span><span class="history-date">${escapeHtml(dateLabel(r.date))}</span></span>
          <strong>${escapeHtml(r.description.slice(0,145))}${r.description.length>145?'…':''}</strong>
          <span class="history-tags"><span class="history-tag ${dangerClass}">${danger}</span>${r.demo===true?'':'<span class="history-tag history-tag-demo">Relato antigo</span>'}</span>
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
    const record=getRecords().find(r=>r.id===id);
    if(!record){toast('Registro não encontrado.');go('history');return;}
    activeRecordId=record.id;
    $('#detail-category').textContent=labels[record.category]||'Outro problema';
    $('#detail-date').textContent=dateLabel(record.date);
    $('#detail-description').textContent=record.description;
    $('#detail-risk').textContent=record.danger==='sim'?'Sim':record.danger==='nao'?'Não':record.danger==='nao_sei'?'Não sei':'Não informado';
    $('#detail-risk').className='detail-risk-answer '+(record.danger==='sim'?'history-tag-danger':record.danger==='nao_sei'?'history-tag-uncertain':'history-tag-neutral');
    const extra=$('#detail-extra-fields');extra.replaceChildren();
    if(record.location) appendDetailField(extra,'Local ou equipamento',record.location);
    if(record.since) appendDetailField(extra,'Quando começou',sinceLabels[record.since]||'Não informado');
    if(record.frequency) appendDetailField(extra,'Frequência',frequencyLabels[record.frequency]||'Não informado');
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
    const data=getRecords();
    if(!data.length){toast('Não há registros para exportar.');return;}
    const json=JSON.stringify({app:'DiagnoseFácil',tipo:'historico_local_demonstrativo',versao:1,exportadoEm:new Date().toISOString(),registros:data},null,2);
    const url=URL.createObjectURL(new Blob([json],{type:'application/json;charset=utf-8'}));
    const link=document.createElement('a');link.href=url;link.download='diagnosefacil-historico.json';document.body.append(link);link.click();link.remove();
    setTimeout(()=>URL.revokeObjectURL(url),1000);
    toast('Histórico exportado para um arquivo JSON.');
  }
  function discardWarning(nextView) {
    if(nextView==='analysis' && currentView==='review') return false;
    if(!['analysis','review'].includes(currentView)) return false;
    if(['analysis','review'].includes(nextView)) return false;
    const hasContent=$('#problem-description').value.trim() || $('#problem-category').value || $('input[name="danger"]:checked') || selectedPhotos.length;
    if(!hasContent)return false;
    if(!window.confirm('Sair sem salvar este relato? As informações preenchidas serão descartadas.'))return true;
    clearPhotos();pendingRecord=null;$('#problem-form').reset();clearFieldErrors();updateDescriptionCount();updateRiskWarning();
    return false;
  }
  document.addEventListener('click',event=>{
    const category=event.target.closest('[data-category]');
    if(category){startAnalysis(category.dataset.category);return;}
    const remove=event.target.closest('[data-remove-photo]');
    if(remove){const index=Number(remove.dataset.removePhoto);if(!Number.isInteger(index)||index<0||index>=selectedPhotos.length)return;const [removed]=selectedPhotos.splice(index,1);URL.revokeObjectURL(removed.url);renderPhotos();return;}
    const record=event.target.closest('[data-record-id]');
    if(record){openDetail(record.dataset.recordId);return;}
    if(event.target.closest('#detail-result')){
      const record=getRecords().find(x=>x.id===activeRecordId);
      if(record?.demo===true)renderResult(record,'detail');
      else toast('Orientação não encontrada para este registro.');
      return;
    }
    if(event.target.closest('#export-history')){exportHistory();return;}
    if(event.target.closest('#clear-history')){
      if(!getRecords().length)return;
      if(!window.confirm('Apagar TODOS os relatos deste navegador? Esta ação não pode ser desfeita.'))return;
      if(saveRecords([])){activeRecordId=null;renderHistory();updateHome();toast('Histórico apagado deste navegador.');}
      return;
    }
    if(event.target.closest('#clear-history-filters')){
      $('#history-search').value='';$('#history-category').value='all';renderHistory();return;
    }
    if(event.target.closest('#delete-record')){
      if(!activeRecordId||!window.confirm('Excluir este relato salvo?'))return;
      const next=getRecords().filter(x=>x.id!==activeRecordId);
      if(saveRecords(next)){activeRecordId=null;go('history');toast('Relato excluído.');}return;
    }
    const button=event.target.closest('[data-go]');
    if(button){const dest=button.dataset.go;if(discardWarning(dest))return;if(dest==='analysis')startAnalysis();else go(dest);}
  });
  $('#history-search').addEventListener('input',renderHistory);
  $('#history-category').addEventListener('change',renderHistory);
  $('#problem-description').addEventListener('input',()=>{updateDescriptionCount();updateRiskWarning();clearFieldErrors();});
  $('#problem-category').addEventListener('change',()=>{updateDescriptionExample();clearFieldErrors();});
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
  function backToEdit(){go('analysis');}
  $('#back-to-edit').addEventListener('click',backToEdit);
  $('#edit-report').addEventListener('click',backToEdit);
  $('#confirm-save').addEventListener('click',()=>{
    if(!pendingRecord || isSaving)return;
    isSaving=true;
    const id=(typeof crypto!=='undefined' && typeof crypto.randomUUID==='function') ? crypto.randomUUID() : String(Date.now())+'-'+Math.random().toString(36).slice(2);
    const record={...pendingRecord,id,date:new Date().toISOString(),demo:true};
    const saved=saveRecords([record,...getRecords()]);
    pendingRecord=null;clearPhotos();$('#problem-form').reset();renderResult(record,'new');isSaving=false;
    if(saved)toast('Relato registrado neste navegador.');
    // Quando o armazenamento está indisponível, a demonstração pode ser vista sem criar um registro.
  });
  updateHome();go('home');
})();
