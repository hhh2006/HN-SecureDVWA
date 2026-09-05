(function(){
  'use strict';

  var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var POLL_MS = 30000;
  var POLL_JITTER = 350;

  function countUp(){
    var nodes=document.querySelectorAll('.sd-count');
    nodes.forEach(function(el){
      var target=Math.max(0,parseInt(el.getAttribute('data-value')||'0',10)||0);
      if(REDUCED){el.textContent=String(target);return;}
      var start=performance.now(),duration=850;
      function tick(now){
        var p=Math.min(1,(now-start)/duration), eased=1-Math.pow(1-p,3);
        el.textContent=String(Math.round(target*eased));
        if(p<1)requestAnimationFrame(tick);
      }
      requestAnimationFrame(tick);
    });
  }

  function clock(){
    var el=document.getElementById('sd-clock');
    if(!el)return;
    function tick(){
      var d=new Date();
      el.textContent=[d.getHours(),d.getMinutes(),d.getSeconds()].map(function(v){return String(v).padStart(2,'0');}).join(':');
    }
    tick();
    setInterval(tick,1000);
  }

  var autoRefreshEnabled=false;
  var autoRefreshListeners=[];

  function controls(){
    var refresh=document.getElementById('sd-refresh'), auto=document.getElementById('sd-autorefresh');
    if(refresh)refresh.addEventListener('click',function(){
      refresh.classList.add('busy');
      setTimeout(function(){location.reload();},180);
    });

    if(auto){
      var key='sd-auto-refresh', enabled=localStorage.getItem(key)==='1';
      function paint(){
        auto.classList.toggle('active',enabled);
        auto.setAttribute('aria-pressed',enabled?'true':'false');
        auto.setAttribute('title',enabled?'Automatic telemetry polling is ON — every 30 seconds':'Automatic telemetry polling is OFF');
        autoRefreshEnabled=enabled;
        autoRefreshListeners.forEach(function(fn){fn(enabled);});
      }
      auto.addEventListener('click',function(){
        enabled=!enabled;
        localStorage.setItem(key,enabled?'1':'0');
        paint();
      });
      paint();
    }
  }

  function liveSoc(){
    var banner=document.getElementById('sd-live-banner'), status=document.getElementById('sd-live-status');
    if(!banner)return;

    var rows=document.querySelectorAll('.sd-event-row'), latest=0;
    rows.forEach(function(r){ latest=Math.max(latest,parseInt(r.getAttribute('data-event-id')||'0',10)||0); });

    var busy=false, timer=null, stopped=false;
    function setStatus(text){if(status)status.textContent=text;}
    function flash(){
      banner.classList.remove('updated');
      void banner.offsetWidth;
      banner.classList.add('updated');
    }
    function schedule(delay){
      if(stopped)return;
      if(timer)clearTimeout(timer);
      timer=setTimeout(poll, delay);
    }
    function poll(){
      if(stopped)return;
      if(document.hidden){ schedule(POLL_MS); return; }
      if(busy){ schedule(POLL_MS); return; }
      busy=true;
      fetch('api_events.php?since='+encodeURIComponent(latest),{
        credentials:'same-origin', cache:'no-store', headers:{'Accept':'application/json'}
      }).then(function(r){
        if(!r.ok)throw new Error('HTTP '+r.status);
        return r.json();
      }).then(function(data){
        if(!data || !data.ok)return;
        var incoming=Array.isArray(data.events)?data.events:[];
        if(incoming.length){
          latest=Math.max(latest,data.latest_id||0);
          flash();
          setStatus('NEW SECURITY SIGNAL • '+incoming.length+' event'+(incoming.length===1?'':'s')+' • UI preserved');
          var newest=incoming[incoming.length-1];
          if(!REDUCED && 'Notification' in window && Notification.permission==='granted'){
            new Notification('SecureDVWA Security Event',{body:(newest.attack_type||'Security event')+' · '+(newest.result||'Observed')});
          }
        }else{
          setStatus('Monitoring • last sync '+new Date().toLocaleTimeString()+' • next sync 30s');
        }
      }).catch(function(){
        setStatus('Telemetry link unavailable • retrying in 30s');
      }).finally(function(){
        busy=false;
        schedule(POLL_MS + Math.floor(Math.random()*POLL_JITTER));
      });
    }

    autoRefreshListeners.push(function(enabled){
      if(enabled) setStatus('AUTO 30S • live telemetry polling enabled');
      else setStatus('LIVE MONITOR • background sync every 30s');
    });
    setStatus(autoRefreshEnabled?'AUTO 30S • live telemetry polling enabled':'LIVE MONITOR • background sync every 30s');
    schedule(900);
    banner.addEventListener('dblclick',function(){
      if('Notification' in window&&Notification.permission==='default')Notification.requestPermission();
    });
    document.addEventListener('visibilitychange',function(){
      if(!document.hidden){ setStatus('Monitoring • resumed • next sync 30s'); schedule(500); }
    });
    window.addEventListener('beforeunload',function(){
      stopped=true;
      if(timer)clearTimeout(timer);
    });
  }

  function hero3D(){ return; }

  function eventLinks(){
    document.querySelectorAll('.sd-event-row[data-event-id]').forEach(function(row){
      row.addEventListener('click',function(e){
        if(e.target.closest('a,button,input,select'))return;
        var id=row.getAttribute('data-event-id');
        if(id)location.href='investigation.php?id='+encodeURIComponent(id);
      });
    });
  }

  function boot(){
    countUp();
    clock();
    controls();
    liveSoc();
    hero3D();
    eventLinks();
  }

  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot);else boot();
})();
