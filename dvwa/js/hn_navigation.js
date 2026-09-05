(()=>{'use strict';
const reduce=matchMedia('(prefers-reduced-motion: reduce)').matches;
const $=(s,c=document)=>c.querySelector(s), $$=(s,c=document)=>Array.from(c.querySelectorAll(s));
const menu=$('#main_menu'),container=$('#container');if(!menu||!container)return;
const scrollKey='hn.securedvwa.menu.scrollTop';
const restoreMenuScroll=()=>{
  let saved=null;
  try{saved=sessionStorage.getItem(scrollKey)}catch(_){ }
  if(saved===null)return;
  const top=Math.max(0,Math.min(Number(saved)||0,menu.scrollHeight-menu.clientHeight));
  const apply=()=>{menu.scrollTop=top;document.documentElement.classList.add('hn-nav-scroll-restored')};
  requestAnimationFrame(()=>requestAnimationFrame(apply));
  setTimeout(apply,80);
};
const saveMenuScroll=()=>{try{sessionStorage.setItem(scrollKey,String(menu.scrollTop))}catch(_){ }};
menu.addEventListener('scroll',()=>{clearTimeout(menu._hnScrollSave);menu._hnScrollSave=setTimeout(saveMenuScroll,70)},{passive:true});
window.addEventListener('pagehide',saveMenuScroll,{passive:true});
window.addEventListener('beforeunload',saveMenuScroll,{passive:true});
restoreMenuScroll();
const progress=document.createElement('div');progress.className='hn-nav-progress';document.body.appendChild(progress);
$$('a[href]').forEach(a=>a.addEventListener('click',()=>{if(reduce)return;const h=a.getAttribute('href');if(!h||h[0]==='#'||a.target==='_blank'||a.hasAttribute('download')||/^(mailto:|tel:|javascript:)/i.test(h))return;try{const d=new URL(a.href,location.href);if(d.origin!==location.origin||d.href===location.href)return;progress.classList.remove('run');void progress.offsetWidth;progress.classList.add('run')}catch(_){}} ,{passive:true}));
const key='hn.securedvwa.sidebar.collapsed';
function save(v){try{localStorage.setItem(key,v?'1':'0')}catch(_){} }
function setCollapsed(v){container.classList.toggle('hn-sidebar-collapsed',!!v);save(!!v);const b=$('.hn-sidebar-collapse');if(b){b.textContent=v?'›':'‹';b.setAttribute('aria-label',v?'Expand sidebar':'Collapse sidebar');b.setAttribute('aria-expanded',v?'false':'true')}const r=$('.hn-sidebar-reopen');if(r){r.hidden=!v;r.setAttribute('aria-hidden',v?'false':'true')}}

const markCurrent=()=>{$$('.menuBlocks li').forEach(li=>{const a=$('a[href]',li);if(!a)return;const current=a.classList.contains('selected')||li.classList.contains('selected');if(current){li.setAttribute('aria-current','true')}else{li.removeAttribute('aria-current')}})};
markCurrent();
// Keep the main navigation visible after every page navigation. Collapse is user-controlled only.
setCollapsed(false);
$('.hn-sidebar-collapse')?.addEventListener('click',e=>{e.preventDefault();e.stopPropagation();setCollapsed(!container.classList.contains('hn-sidebar-collapsed'));});
$('.hn-sidebar-reopen')?.addEventListener('click',e=>{e.preventDefault();e.stopPropagation();setCollapsed(false);});
$('.hn-nav-toggle')?.addEventListener('click',()=>{const open=container.classList.toggle('hn-mobile-nav-open');$('.hn-nav-toggle').setAttribute('aria-expanded',open?'true':'false')});
document.addEventListener('click',e=>{if(container.classList.contains('hn-mobile-nav-open')&&!menu.contains(e.target)&&!$('.hn-nav-toggle')?.contains(e.target))container.classList.remove('hn-mobile-nav-open')});
const search=$('#hn-nav-search-input');const filter=()=>{const q=(search?.value||'').trim().toLowerCase();$$('.menuBlocks li').forEach(li=>{li.hidden=!!q&&!li.textContent.toLowerCase().includes(q)});$$('.hn-nav-section').forEach(sec=>{sec.hidden=!!q&&!$$('.menuBlocks li:not([hidden])',sec).length})};search?.addEventListener('input',filter);
const overlay=document.createElement('div');overlay.className='hn-command-palette';overlay.innerHTML='<div class="hn-cp-shell"><div class="hn-cp-head"><span class="hn-cp-brand">HN COMMAND</span><button type="button" class="hn-cp-close">Esc</button></div><div class="hn-cp-search"><span>⌕</span><input type="search" id="hn-cp-input" placeholder="Search security module…" autocomplete="off"></div><div class="hn-cp-list" role="listbox"></div><div class="hn-cp-foot"><span>↑ ↓ Navigate</span><span>Enter Open</span><span>Esc Close</span></div></div>';document.body.appendChild(overlay);
const cpInput=$('#hn-cp-input',overlay),cpList=$('.hn-cp-list',overlay);let cpIndex=0;
const close=()=>overlay.classList.remove('open');
const render=q=>{const links=$$('#main_menu a[href]').filter(a=>a.textContent.toLowerCase().includes(q.toLowerCase()));cpIndex=0;cpList.innerHTML=links.map((a,i)=>'<a href="'+a.href+'" data-cp-index="'+i+'"><span class="cp-icon">'+(a.dataset.navId||'•')+'</span><span><b>'+a.textContent.trim()+'</b><small>'+a.getAttribute('href')+'</small></span><kbd>↵</kbd></a>').join('')||'<div class="hn-cp-empty">No matching modules</div>';paintCP();$$('.hn-cp-list a',overlay).forEach(a=>a.addEventListener('click',close));};
const paintCP=()=>{$$('.hn-cp-list a',overlay).forEach((a,i)=>a.classList.toggle('active',i===cpIndex));};
const open=()=>{overlay.classList.add('open');cpInput.value='';render('');setTimeout(()=>cpInput.focus(),30)};
$('.hn-command-trigger')?.addEventListener('click',open);$('.hn-cp-close',overlay).addEventListener('click',close);overlay.addEventListener('click',e=>{if(e.target===overlay)close()});cpInput.addEventListener('input',()=>render(cpInput.value));
document.addEventListener('keydown',e=>{if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='k'){e.preventDefault();open();return}if(e.key==='/'&&!['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)){e.preventDefault();search?.focus();return}if(e.key==='Escape'&&overlay.classList.contains('open')){close();return}if(overlay.classList.contains('open')&&(e.key==='ArrowDown'||e.key==='ArrowUp')){e.preventDefault();const n=$$('.hn-cp-list a',overlay).length;if(n){cpIndex=(cpIndex+(e.key==='ArrowDown'?1:-1)+n)%n;paintCP();}}if(overlay.classList.contains('open')&&e.key==='Enter'){const a=$('.hn-cp-list a.active',overlay);if(a){e.preventDefault();location.href=a.href}}});
})();
