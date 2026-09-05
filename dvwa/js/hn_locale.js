/* HN SecureDVWA bilingual UI helper: locale is server-controlled; this improves accessibility. */
(function(){
  document.addEventListener('DOMContentLoaded',function(){
    var root=document.documentElement;
    if(root.lang==='ar'){ root.dir='rtl'; document.body.classList.add('hn-rtl'); }
    document.querySelectorAll('.hn-language-switch a').forEach(function(a){
      a.addEventListener('click',function(){ document.documentElement.classList.add('hn-locale-changing'); });
    });

  var progress=document.createElement('div');
  progress.className='hn-nav-progress';
  document.body.appendChild(progress);
  document.querySelectorAll('a[href]').forEach(function(a){
    a.addEventListener('click',function(e){
      if(e.defaultPrevented || a.target==='_blank' || a.hasAttribute('download')) return;
      var href=a.getAttribute('href')||'';
      if(!href || href.charAt(0)==='#' || href.indexOf('javascript:')===0) return;
      if(window.location.href.split('#')[0]===a.href.split('#')[0]) return;
      progress.classList.remove('run'); void progress.offsetWidth; progress.classList.add('run');
    }, {passive:true});
  });
  });
})();
