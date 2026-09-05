(()=>{'use strict';
const d=window.HN_REPORT_DATA||{};
const $=(s,c=document)=>c.querySelector(s), $$=(s,c=document)=>[...c.querySelectorAll(s)];
const reduce=window.matchMedia&&matchMedia('(prefers-reduced-motion: reduce)').matches;
const labels=d.labels||[], values=d.values||[], total=Math.max(1,values.reduce((a,b)=>a+(+b||0),0));
const isAr=document.documentElement.lang==='ar';
const t=(en,ar)=>isAr?ar:en;

// Readiness reactor: animate the number without delaying page interaction.
$$('[data-report-score]').forEach(el=>{
  const target=+el.dataset.reportScore||0;
  if(reduce){el.textContent=target;return;}
  el.textContent='0'; const start=performance.now();
  const tick=now=>{const p=Math.min(1,(now-start)/850),e=1-Math.pow(1-p,3);el.textContent=Math.round(target*e);if(p<1)requestAnimationFrame(tick)};
  requestAnimationFrame(tick);
});

// Threat signature ring — generated from real DB counts.
const donut=$('[data-donut]');
if(donut&&values.length){
  const palette=['#62f0e0','#71aaff','#b998ff','#ff79c9','#66e0ad','#ffc96b','#ff7f96'];
  let cursor=0; const stops=[];
  values.forEach((v,i)=>{const a=cursor/total*360;cursor+=+v;const b=cursor/total*360;stops.push(`${palette[i%palette.length]} ${a}deg ${b}deg`);});
  donut.style.background=`conic-gradient(${stops.join(',')})`;
  $$('[data-legend-index]').forEach(btn=>btn.addEventListener('click',()=>{
    const i=+btn.dataset.legendIndex;$$('[data-legend-index]').forEach(x=>x.classList.remove('active'));btn.classList.add('active');
    const v=+btn.dataset.value||0;const pct=Math.round(v/total*100);
    donut.style.setProperty('--selected-share',pct+'%');donut.classList.add('active');
    const core=donut.querySelector('strong');if(core){core.textContent=v;core.dataset.originalTotal=total;}
    setTimeout(()=>{if(core&&!btn.classList.contains('active'))core.textContent=total},reduce?0:1200);
  }));
}

// 24h pulse — click any hour and update the command readout.
$$('.pulse-node').forEach(btn=>btn.addEventListener('click',()=>{
  $$('.pulse-node').forEach(x=>x.classList.remove('active'));btn.classList.add('active');
  const out=$('[data-pulse-readout]');if(!out)return;
  const th=+btn.dataset.threat||0,op=+btn.dataset.ops||0;
  out.innerHTML=`<b>${btn.dataset.hour}</b><span>${t('Security events: '+th+' · Normal operations: '+op,'الأحداث الأمنية: '+th+' · العمليات الطبيعية: '+op)}</span>`;
}));

// Evidence constellation — focus a real target from the DB.
$$('.const-node').forEach(btn=>btn.addEventListener('click',()=>{
  $$('.const-node').forEach(x=>x.classList.remove('active'));btn.classList.add('active');
  const out=$('[data-radar-readout]');if(!out)return;
  const label=btn.dataset.label||t('Defense vector','محور دفاع');const value=+btn.dataset.value||0; const ops=+btn.dataset.ops||0; const blocked=+btn.dataset.blocked||0;
  out.innerHTML=`<b>${label}</b><span>${t(value+' security events · '+blocked+' blocked · '+ops+' normal operations',''+value+' حدثًا أمنيًا · '+blocked+' محجوب · '+ops+' عمليات طبيعية')}</span>`;
}));

// Release readiness — interactive explanation.
$$('[data-coverage]').forEach(btn=>btn.addEventListener('click',()=>{
  const out=$('[data-coverage-readout]');if(!out)return;
  const n=btn.dataset.coverage;const ready=btn.classList.contains('ready');
  out.innerHTML=`<b>${t('Vector '+n, 'المحور '+n)} · ${ready?t('READY','جاهز'):t('REVIEW','مراجعة')}</b><span>${ready?t('Evidence path detected and represented in the release brief.','تم اكتشاف مسار الأدلة وتمثيله ضمن التقرير.'):t('Review the source and evidence path before presenting this vector.','راجع المصدر ومسار الأدلة قبل عرض هذا المحور.')}</span>`;
}));

// Lightweight anchor navigation — no page-transition overlay, so navigation stays immediate.
$$('[data-report-jump]').forEach(btn=>btn.addEventListener('click',()=>{
  const target=document.getElementById(btn.dataset.reportJump);if(!target)return;
  target.scrollIntoView({behavior:reduce?'auto':'smooth',block:'start'});
}));

const meter=$('[data-posture]');if(meter){meter.style.setProperty('--posture',Math.max(0,Math.min(100,+meter.dataset.posture||0))+'%');}
})();
