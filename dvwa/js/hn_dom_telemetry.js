(function(){
  'use strict';
  if (!/\/vulnerabilities\/xss_d(?:\/|$)/i.test(location.pathname)) return;
  var raw = location.hash ? decodeURIComponent(location.hash.slice(1)) : '';
  if (!raw) return;
  var suspicious = /(?:<\s*script\b|javascript\s*:|on[a-z]+\s*=|<\s*(?:img|svg|iframe|object|embed|style|link)\b)/i.test(raw);
  if (!suspicious) return;
  try {
    var body = new URLSearchParams();
    body.set('event','dom_hash_payload');
    fetch('../../security/api_log_event.php', {
      method:'POST', credentials:'same-origin', cache:'no-store',
      headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8','Accept':'application/json'},
      body:body.toString(), keepalive:true
    }).catch(function(){});
  } catch(e){}
})();
