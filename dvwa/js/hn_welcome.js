(()=>{'use strict';
const w=document.querySelector('.hn-welcome');if(!w)return;
const close=()=>{w.classList.add('hn-welcome-hide');window.setTimeout(()=>w.remove(),650)};
const b=w.querySelector('.hn-welcome-close'); if(b)b.addEventListener('click',close);
const reduce=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
window.setTimeout(close, reduce ? 3200 : 6200);
})();
