<?php

define('DVWA_WEB_PAGE_TO_ROOT','../');
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';
dvwaPageStartup(array('authenticated'));

$page=dvwaPageNewGrab();
$page['title']=dvwaText('Source','المصدر').' — '. $page['title'];

if(array_key_exists('id',$_GET)){
    $id=(string)$_GET['id'];
    $levels=['low','medium','high','impossible'];
    $vuln=$id;
    $sources=[];
    foreach($levels as $level){
        $path=DVWA_WEB_PAGE_TO_ROOT."vulnerabilities/{$id}/source/{$level}.php";
        $src=@file_get_contents($path);
        if($src!==false){
            $src=str_replace(['$html .='],['echo'],$src);
            $sources[$level]=highlight_string($src,true);
        }
    }
    $labels=['low'=>dvwaText('Low','منخفض'),'medium'=>dvwaText('Medium','متوسط'),'high'=>dvwaText('High','مرتفع'),'impossible'=>dvwaText('Impossible','محكم')];
    $page['body']='<div class="body_padded"><section class="source-shell"><div class="source-kicker">HN / '.dvwaText('SOURCE COMPARISON CONSOLE','وحدة مقارنة المصادر').'</div><h1 class="source-title">'.dvwaSecurityEscape($vuln).' — '.dvwaText('All Levels','جميع المستويات').'</h1><div class="console-toolbar"><span class="console-path">'.dvwaSecurityEscape($id).'</span><span class="console-badge">'.dvwaText('READ ONLY','قراءة فقط').'</span></div>';
    foreach($levels as $level){if(!isset($sources[$level]))continue;$page['body'].='<section class="source-block"><div class="source-block-head"><span>'.dvwaSecurityEscape($labels[$level]).'</span></div><div class="source-code">'.$sources[$level].'</div></section>';}
    $page['body'].='</section></div>';
}else{$page['body']='<p>'.dvwaText('Not found','غير موجود').'</p>';}

dvwaSourceHtmlEcho($page);
?>
