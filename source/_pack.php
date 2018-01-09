<?php

//@E:Still in development! Only for experimental use!;
//@E:Nur für den internen Gebrauch bestimmt!;
//@A:Markus Hottenrott @ share2brain.com;

//@c:2015-08-30:Umbenennung zu Doccen.com;

require_once('lib.php');

if(!isset($GLOBALS['SetCfg'])) {
	ab5_InitDoccen();
	packRun();
	pack_Send();
	$GLOBALS['Templ'] = str_replace('{{NavBar}}', '<span class="Headline"><a href="index.php" class="MenuLink">'.$GLOBALS['LangCurrent']->Global->Start.'</a>', $GLOBALS['Templ']);
	$GLOBALS['Templ'] = str_replace('{{LeftContent}}', '<span class="Headline">Hinweis</span><p>Dieser "Packer" ist für interne Anforderungen gemacht!</p>', $GLOBALS['Templ']);
	$GLOBALS['Templ'] = str_replace('{{RightContent}}', '<span class="Headline">Protokoll</span>'.$GLOBALS['Protocol'], $GLOBALS['Templ']);
	$GLOBALS['Templ'] = str_replace('{{AddStyle}}', '#LeftContent { padding: 5px; } #RightContent { padding: 5px; }', $GLOBALS['Templ']);
	echo $GLOBALS['Templ'];
}

function pack_Send() {
	if(is_file('_pack'.DIRECTORY_SEPARATOR.'_pack.zip') ) {
		unlink('_pack'.DIRECTORY_SEPARATOR.'_pack.zip');
	}
	$FileIndex = ab5_CrawleDir(array('Path'=>'_pack'));
	$Log = '<b>Build package</b><br />';
	foreach ($FileIndex['Result'] as $File) {
		$FileName = explode(DIRECTORY_SEPARATOR,$File);
		$FileName = end($FileName);
		$Log .= '+ '.$FileName.'<br />';
		$FileS[] = array('FileName'=>$FileName,'FileData'=>file_get_contents($File));
	}

	//@c:2015-10-27:Prettyfy der JSON-Ausgabe;
	$FileS = json_encode($FileS, JSON_PRETTY_PRINT);

	$Log .= '<b>Send package</b><br />';

	$Url = 'https://scs.hennott.de/index.php';
	$Data = array('Access'=>'doccen.update.repo','Account'=>'master','ApiKey'=>'test','Set'=>'true','Data'=>$FileS);
	$Log .= '+ Send to -Url: '.$Url.' -Access: '.$Data['Access'].'<br />';

	$Connect = curl_init();

	curl_setopt($Connect, CURLOPT_URL, $Url);
	curl_setopt($Connect, CURLOPT_POST, true);
	curl_setopt($Connect, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($Connect, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($Connect, CURLOPT_POSTFIELDS, $Data);

	$Result = curl_exec($Connect);

	$Log .= '+ Send result -Result: '.$Result.'<br />';

	curl_close($Connect);

	$GLOBALS['Protocol'] .= $Log;
	$Return['Log'] = $Log;
	return $Return;
}

function packRun() {
	$GLOBALS['Protocol'] = '<b>Clean and prepare files</b><br />';
	pack_CleanFile('index.php');
	pack_CleanFile('composer.php');
	pack_CleanFile('grabber.php');
	pack_CleanFile('autodoc.php');
	pack_CleanFile('workflow.php');
	pack_CleanFile('lib.php');
	pack_CleanFile('de.lang');
	pack_CleanFile('en.lang');
	//pack_CleanFile('set.config');
	//pack_CleanFile('my.config');
	pack_CleanFile('template.config');
	pack_CleanFile('update.php');
	pack_CleanFile('update.config');
	pack_CleanFile('start.html');
	//pack_CleanFile('DoccenStart.png');
	$Return['Log'] = $GLOBALS['Protocol'];
	return $Return;
}

function pack_CleanFile($FileName) {
	$File = file_get_contents($FileName);

	$File = preg_replace('!/\*.*?\*/!s', '', $File);
	//$File = preg_replace('/\n\s*\n/', "\n", $File);
	//Remove Multiline Comments
	$File = preg_replace('!^[ \t]*/\*.*?\*/[ \t]*[\r\n]!s', '', $File);
	//Removes single line '//' comments, treats blank characters
	$File = preg_replace('![ \t]*// .*[ \t]*[\r\n]!', '', $File);
	//Strip blank lines
	$File = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $File);

	//$File = preg_replace( "/\r|\n/", " ", $File);
	//$File = preg_replace( '/;$\n/', ";", $File);
	$GLOBALS['Protocol'] .= '+ <b>Package:</b> '.$FileName.'<br />&nbsp;&nbsp;> Copy '.__DIR__.DIRECTORY_SEPARATOR.'_pack'.DIRECTORY_SEPARATOR.$FileName.'<br />&nbsp;&nbsp;> Copy '.__DIR__.DIRECTORY_SEPARATOR.'SourceS'.DIRECTORY_SEPARATOR.'Doccen SelfDoc'.DIRECTORY_SEPARATOR.''.$FileName.'<br />';

	file_put_contents(__DIR__.DIRECTORY_SEPARATOR.'_pack'.DIRECTORY_SEPARATOR.$FileName, $File);
	file_put_contents(__DIR__.DIRECTORY_SEPARATOR.'SourceS'.DIRECTORY_SEPARATOR.'Doccen SelfDoc'.DIRECTORY_SEPARATOR.''.$FileName, $File);
}

?>