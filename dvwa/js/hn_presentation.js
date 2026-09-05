(()=>{'use strict';
const $=s=>document.querySelector(s); const reduce=matchMedia('(prefers-reduced-motion: reduce)').matches;
const demo=document.createElement('div'); demo.className='hn-demo-overlay';
demo.innerHTML='<div class="hn-demo-shell"><header><span class="demo-kicker">SECUREDVWA / PRESENTATION MODE</span><button type="button" class="demo-close">ESC</button></header><div class="demo-progress"><i></i></div><div class="demo-content"><span class="demo-index">01 / 05</span><h2>Command Center Walkthrough</h2><p></p><div class="demo-actions"><button type="button" class="demo-next">NEXT STEP →</button><button type="button" class="demo-exit">EXIT DEMO</button></div></div></div>';
document.body.appendChild(demo);
const steps=[
 ['01 / 05','Command Center Walkthrough','ابدأ من لوحة المراقبة الأمنية وشاهد الحالة العامة والـ telemetry المسجل فعليًا.','security/dashboard.php'],
 ['02 / 05','Threat Intelligence','استعرض الخط الزمني وتركيب محاولات الهجوم ومصادرها من السجل الأمني.','security/timeline.php'],
 ['03 / 05','Risk Assessment','انتقل إلى مصفوفة المخاطر لرؤية الأولويات المحسوبة من البيانات المتاحة.','security/risk.php'],
 ['04 / 05','Assurance','اعرض الضوابط الأمنية ودورة BEFORE → TEST → HARDENED → RETEST.','security/assurance.php'],
 ['05 / 05','Executive Report','اختم العرض بتقرير أمني قابل للطباعة أو الحفظ كـ PDF.','security/report.php']
]; let idx=0;
function paint(){const x=steps[idx];$('.demo-index',demo).textContent=x[0];$('.demo-content h2',demo).textContent=x[1];$('.demo-content p',demo).textContent=x[2];$('.demo-progress i',demo).style.width=((idx+1)/steps.length*100)+'%';$('.demo-next',demo).textContent=idx===steps.length-1?'OPEN REPORT →':'NEXT STEP →';}
function close(){demo.classList.remove('open');document.body.classList.remove('hn-presentation-active')}
function open(){idx=0;paint();demo.classList.add('open');document.body.classList.add('hn-presentation-active')}
$('.hn-demo-trigger')?.addEventListener('click',open);$('.demo-close',demo).addEventListener('click',close);$('.demo-exit',demo).addEventListener('click',close);
$('.demo-next',demo).addEventListener('click',()=>{if(idx<steps.length-1){idx++;paint()}else location.href=steps[idx][3]}); demo.addEventListener('click',e=>{if(e.target===demo)close()});
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&demo.classList.contains('open'))close(); if(demo.classList.contains('open')&&e.key==='ArrowRight'){e.preventDefault();$('.demo-next',demo).click()} if(e.key.toLowerCase()==='d'&&!['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)&&!e.ctrlKey&&!e.metaKey)open()});
$('.hn-alert-trigger')?.addEventListener('click',()=>location.href='security/notifications.php');
const c=$('#container'),reopen=$('.hn-sidebar-reopen'); reopen?.addEventListener('click',()=>{c?.classList.remove('hn-sidebar-collapsed');try{localStorage.setItem('hn.securedvwa.sidebar.collapsed','0')}catch(_){};reopen.blur()});
})();
