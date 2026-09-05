/* HN SecureDVWA Ultra Cinematic Interaction Layer v3.8 */
(function(){'use strict';
 const body=document.body;if(!body)return;
 const reduce=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
 body.classList.add('hn-ultra-v38');
 const vignette=document.createElement('div');vignette.className='hn-v38-vignette';body.appendChild(vignette);
 let toastTimer=0;
 function toast(msg){let t=document.querySelector('.hn-v38-toast');if(!t){t=document.createElement('div');t.className='hn-v38-toast';body.appendChild(t)}t.textContent=msg;t.classList.add('show');clearTimeout(toastTimer);toastTimer=setTimeout(()=>t.classList.remove('show'),2600)}
 function controlbar(){const main=document.querySelector('#main_body');if(!main||document.querySelector('.hn-v38-controlbar'))return;const bar=document.createElement('div');bar.className='hn-v38-controlbar';bar.innerHTML='<span class="ux-chip"><b>◉</b> CINEMATIC UI</span><span class="ux-chip"><b>3D</b> INTERACTIVE</span><button type="button" data-ux="focus">FOCUS</button><button type="button" data-ux="motion">MOTION</button><button type="button" data-ux="contrast">CONTRAST</button>';const first=main.querySelector('.hn-project-bar')||main.firstElementChild;main.insertBefore(bar,first||null);
 const focus=bar.querySelector('[data-ux="focus"]'),motion=bar.querySelector('[data-ux="motion"]'),contrast=bar.querySelector('[data-ux="contrast"]');
 const set=(btn,cls)=>{const on=body.classList.toggle(cls);btn.classList.toggle('active',on);btn.setAttribute('aria-pressed',String(on));try{localStorage.setItem('hn.v38.'+cls,on?'1':'0')}catch(_){};toast(on?'Enabled '+cls.replace('hn-','').replaceAll('-',' ').toUpperCase():'Disabled '+cls.replace('hn-','').replaceAll('-',' ').toUpperCase())};
 focus.onclick=()=>set(focus,'hn-focus-mode');motion.onclick=()=>set(motion,'hn-user-motion');contrast.onclick=()=>{body.classList.toggle('hn-high-contrast');contrast.classList.toggle('active');toast('Visual contrast adjusted')};
 }
 function tilt(){return;}
 function keyboard(){document.addEventListener('keydown',e=>{if(e.key==='?'&&!['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)){toast('Shortcuts: Ctrl/⌘K Command Palette • / Search • Esc Close')}})}
 function boot(){controlbar();tilt();keyboard();}
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot,{once:true});else boot();
})();
