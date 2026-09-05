(()=>{'use strict';
const reduce=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const root=document.documentElement, body=document.body, container=document.querySelector('#container');
if(!container)return;

const iconMap={
 home:'⌂',instructions:'◌',setup:'⚙',brute:'⌁',exec:'⌁',csrf:'↯',fi:'◫',upload:'⇧',captcha:'◉',sqli:'⌘',sqli_blind:'◍',weak_id:'◈',xss_d:'✦',xss_r:'✧',xss_s:'✹',csp:'◫',javascript:'JS',authbypass:'⛨',open_redirect:'↗',encryption:'◇',api:'API',security:'🛡',security_dashboard:'⌬',security_health:'♥',security_alerts:'⚠',security_timeline:'◷',security_risk:'△',security_controls:'◈',security_assurance:'✓',security_report:'▤',phpinfo:'PHP',about:'i',logout:'⇥'
};
const colorMap={
 home:'#67f3e3',instructions:'#6aa8ff',setup:'#ffc968',brute:'#ff75cc',exec:'#ff6f86',csrf:'#b58cff',fi:'#65d8ff',upload:'#5ce6a6',captcha:'#ffd36e',sqli:'#ff8d8d',sqli_blind:'#ffb36b',weak_id:'#b98cff',xss_d:'#6ee7ff',xss_r:'#8fb6ff',xss_s:'#ff75cc',csp:'#65d8ff',javascript:'#ffc968',authbypass:'#ff6f86',open_redirect:'#78e08f',encryption:'#c4a0ff',api:'#6ee7ff',security:'#7ce7a2',security_dashboard:'#67f3e3',security_health:'#5ce6a6',security_alerts:'#ff6f86',security_timeline:'#6aa8ff',security_risk:'#ffc968',security_controls:'#b58cff',security_assurance:'#5ce6a6',security_report:'#67f3e3',phpinfo:'#8fb6ff',about:'#9ba7bf',logout:'#ff6f86'
};

function initNav(){
 document.querySelectorAll('#main_menu a[data-nav-id]').forEach(a=>{
   const id=a.dataset.navId||'';
   if(!a.querySelector('.hn-nav-icon')){
     const s=document.createElement('span'); s.className='hn-nav-icon'; s.setAttribute('aria-hidden','true'); s.textContent=iconMap[id]||'•'; a.prepend(s);
   }
   const li=a.closest('li'); if(li){ li.style.setProperty('--module',colorMap[id]||'#67f3e3'); li.dataset.module=id; }
 });
 document.querySelectorAll('.hn-nav-section').forEach(sec=>{
   const title=sec.querySelector('.hn-nav-section-title');
   if(!title||title.dataset.enhanced)return;
   title.dataset.enhanced='1'; title.setAttribute('role','button'); title.setAttribute('tabindex','0');
   const key=sec.dataset.navSection||'section';
   const stateKey='hn.securedvwa.section.'+key;
   const saved=localStorage.getItem(stateKey);
   if(saved==='collapsed')sec.classList.add('is-collapsed');
   const toggle=()=>{
     sec.classList.toggle('is-collapsed');
     try{localStorage.setItem(stateKey,sec.classList.contains('is-collapsed')?'collapsed':'open')}catch(_){ }
   };
   title.addEventListener('click',toggle); title.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();toggle()}});
   title.setAttribute('aria-expanded',String(!sec.classList.contains('is-collapsed')));
   sec.addEventListener('transitionend',()=>title.setAttribute('aria-expanded',String(!sec.classList.contains('is-collapsed'))));
 });
}
function initTextEffects(){
 const els=[...document.querySelectorAll('#main_body h1,#main_body h2,.panel-head h2,.sd .brand h1,.hn-sidebar-brand b')];
 els.forEach(el=>el.classList.add('hn-color-flow'));
 document.querySelectorAll('#main_body .card,.sd .panel,.sd .hero,.vulnerable_code_area').forEach(el=>el.classList.add('hn-depth-card'));
}
function initReveal(){
 if(reduce||!('IntersectionObserver' in window))return;
 const io=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('hn-visible');io.unobserve(e.target)}}),{threshold:.08});
 document.querySelectorAll('#main_body .panel,#main_body .card,#main_body .vulnerable_code_area,#main_body table,#main_body form').forEach((el,i)=>{el.classList.add('hn-reveal');el.style.setProperty('--reveal-delay',Math.min(i*35,280)+'ms');io.observe(el)});
}
function initParticles(){
 if(reduce||document.querySelector('.hn-ambient-field'))return;
 const field=document.createElement('div'); field.className='hn-ambient-field';
 for(let i=0;i<18;i++){const p=document.createElement('i');p.style.setProperty('--x',Math.round(Math.random()*100)+'%');p.style.setProperty('--y',Math.round(Math.random()*100)+'%');p.style.setProperty('--d',(6+Math.random()*10).toFixed(1)+'s');p.style.setProperty('--delay',(-Math.random()*12).toFixed(1)+'s');p.style.setProperty('--s',(1+Math.random()*2.6).toFixed(1)+'px');field.appendChild(p)}
 body.appendChild(field);
}
function initFocusDock(){
 if(document.querySelector('.hn-ui-dock'))return;
 const dock=document.createElement('aside'); dock.className='hn-ui-dock';
 dock.innerHTML='<button type="button" data-ui="focus" aria-pressed="false" title="Focus mode">◒<span>FOCUS</span></button><button type="button" data-ui="motion" aria-pressed="false" title="Reduce interface motion">✦<span>MOTION</span></button><button type="button" data-ui="top" title="Back to top">↑<span>TOP</span></button>';
 body.appendChild(dock);
 const focus=dock.querySelector('[data-ui="focus"]'), motion=dock.querySelector('[data-ui="motion"]');
 const set=(btn,cls)=>{const active=body.classList.toggle(cls);btn.classList.toggle('active',active);btn.setAttribute('aria-pressed',String(active));try{localStorage.setItem('hn.ui.'+cls,active?'1':'0')}catch(_){}};
 try{if(localStorage.getItem('hn.ui.hn-focus-mode')==='1')set(focus,'hn-focus-mode');if(localStorage.getItem('hn.ui.hn-user-motion')==='1')set(motion,'hn-user-motion')}catch(_){ }
 focus.addEventListener('click',()=>set(focus,'hn-focus-mode'));
 motion.addEventListener('click',()=>set(motion,'hn-user-motion'));
 dock.querySelector('[data-ui="top"]').addEventListener('click',()=>window.scrollTo({top:0,behavior:reduce?'auto':'smooth'}));
}
function initRipples(){
 if(reduce)return;
 document.addEventListener('click',e=>{
   const b=e.target.closest('button,input[type=submit],input[type=button],.sd-action,.hn-command-trigger,.hn-demo-trigger');
   if(!b||b.disabled)return;
   const s=document.createElement('i');s.className='hn-ripple';s.style.left='50%';s.style.top='50%';b.appendChild(s);setTimeout(()=>s.remove(),700);
 },{passive:true});
}
function boot(){initNav();initTextEffects();initReveal();initParticles();initFocusDock();initRipples();body.classList.add('hn-ultra-ready')}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot,{once:true});else boot();
})();
