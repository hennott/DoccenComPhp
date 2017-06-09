<?php
//@E:Still in development! Only for experimental use!;
//@A:Markus Hottenrott @ share2brain.com;
//@m:
$GLOBALS['AppVersion']['Index'] = 'v0.0.2 (2015-02-03)';
//@D:Die Index-Datei lädt die Modulübersicht und eine entsprechende Navigation.;
//@c:2015-08-30:Umbenennung zu Doccen.com;
require_once('lib.php');
ab5_InitDoccen();
$GLOBALS['Templ'] = str_replace('{{RightContent}}', file_get_contents($SetCfg->DOC_StartPage) , $GLOBALS['Templ']);
$ModulMenu = '<span class="Headline">Module</span><br /><a class="MenuLink" href="composer.php">Composer</a><br /><br /><a class="MenuLink" href="grabber.php">Grabber</a><br /><br /><a class="MenuLink" href="autodoc.php"><b>AutoDoc</b></a><br /><br /><a class="MenuLink" href="workflow.php">Workflow</a>';
if(is_file('_pack.php')) {
	$ModulMenu .= '<br /><br /><br /><span style="font-size: 10pt;"><a class="" href="_pack.php">Package (Nur intern)</a></span>';
}
$GLOBALS['Templ'] = str_replace('{{LeftContent}}', $ModulMenu, $GLOBALS['Templ']);
$GLOBALS['Templ'] = str_replace('{{NavBar}}', '', $GLOBALS['Templ']);
$GLOBALS['Templ'] = str_replace('{{AddStyle}}', '.MenuLink { font-variant: small-caps; font-size: 15pt; font-weight: light; } #LeftContent { padding: 5px; }', $GLOBALS['Templ']);
echo $GLOBALS['Templ'];
?>