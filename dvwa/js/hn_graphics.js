(()=>{'use strict';
const reduce=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
function enhance(){
  document.querySelectorAll('.sd .bar .fill').forEach((el,i)=>{el.classList.add('hn-graph-fill');el.style.setProperty('--gdelay',Math.min(i*70,420)+'ms')});
  document.querySelectorAll('.sd .ring,.sd .donut').forEach(el=>el.classList.add('hn-graph-orb'));
  document.querySelectorAll('.sd .line-chart').forEach(svg=>{
    svg.classList.add('hn-graph-svg');
    const ns='http://www.w3.org/2000/svg';
    if(!svg.querySelector('.hn-chart-spark')){
      const g=document.createElementNS(ns,'g');g.setAttribute('class','hn-chart-spark');
      for(let i=0;i<14;i++){const c=document.createElementNS(ns,'circle');c.setAttribute('cx',20+i*43);c.setAttribute('cy',18+(i%5)*24);c.setAttribute('r',i%4===0?'1.6':'1');c.setAttribute('class','hn-spark-dot');c.style.setProperty('--spark-delay',(-i*.45)+'s');g.appendChild(c)}
      svg.appendChild(g);
    }
  });
  document.querySelectorAll('.sd .panel').forEach((p,i)=>{p.classList.add('hn-visual-panel');p.style.setProperty('--panel-index',i)});
  document.querySelectorAll('.sd .criteria,.sd .stream-item,.sd .bar-row').forEach(el=>el.classList.add('hn-visual-row'));
  if(!document.querySelector('.hn-graphic-status')){
    const host=document.querySelector('.sd .sd-signal-strip');
    if(host){const s=document.createElement('div');s.className='hn-graphic-status';s.innerHTML='<span class="hn-gs-line"></span><span class="hn-gs-label">VISUAL ENGINE</span><b>ACTIVE</b><i></i><i></i><i></i><i></i><i></i>';host.parentNode.insertBefore(s,host.nextSibling)}
  }
  if(!reduce) document.documentElement.classList.add('hn-graphics-ready');
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',enhance,{once:true});else enhance();
})();
