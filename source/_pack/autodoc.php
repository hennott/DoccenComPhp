<?php
//@E:Still in development! Only for experimental use!;
//@A:Markus Hottenrott @ share2brain.com;
//@m:
$GLOBALS['AppVersion']['AutoDoc'] = 'v0.1.10 (2017-03-16)';
//@c:2015-02-18:Umbau auf einheitliches Layout und lib als Initialisierung.;
//@c:2015-02-03:Voller Umbau auf neue Architektur bezüglich externer Quellen von Config und Übersetzung.;
//@c:2015-07-29:Fehlerhafte Funktionsverweise wurde korrigiert;
//@c:2015-07-29:Anpassung fehlerhafter Klassenzuweisungen für das Styling;
//@c:2016-02-15:Zahlreiche kleiner Korrekturen an dem DOM-Output.;
//@D:Das Modul AutoDoc ist das Herzstück des gleichnamigen Projekts. Alle anderen Module unterstützen dieses Modul und sollen den Prozess von der Entwicklung hin zu einer aktuellen Dokumentation verbessern. AutoDoc übernimmt dabei den entscheidenen Prozess und verwandelt die Code-Dateien in ein visuell aufbereitetes Dokument.;
//@c:2015-08-30:Umbenennung zu Doccen.com;
require_once('lib.php');
if(!isset($GLOBALS['SetCfg'])) {
	//@M:Wenn die Komponente standalone verwendet wird, dann muss erst die zentrale Initialisierung durchlaufen werden und anschließend wird die "Init"-Funktion aufgerufen.;
	ab5_InitDoccen();
	AutoDocPrepare();
}
function AutoDocPrepare() {
	//@F:AutoDocPrepare;
	//@D:Diese Funktion bereitet die Dokumentation vor. Es wird das entsprechende Layout vorbereitet und schlussendlich auch die Dokumentation ausgegeben. Im Modus "ExportWf", kann auch nichts ausgegeben werden.;
	if(isset($_GET['Doc']) && $GLOBALS['MultiDoc']) { 
		//@V:$GLOBALS['Doc'];
		$GLOBALS['Doc'] = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $_GET['Doc']);
		//@c:2015-02-03:Die doppelten Verzeichnistrenner werden nun herausgenommen;
	}
	if(isset($_GET['V'])){
		//@V:$GLOBALS['V'];
		$GLOBALS['V'] = $_GET['V'];
	}
	//@V:$GLOBALS['CountFiles']<br />$GLOBALS['CountChapters']<br />$GLOBALS['DocAddFileS'];
	if(isset($GLOBALS['V'])) {
		//@M:Wenn eine Viewmodus definiert wurde, werden alle nötigen Dateien durchsucht und ausgelesen.;
		//@c:2015-08-04:Bei DocAdd werden nur noch definierte Dateien ausgegeben. Der Filter DOC_DocAddFormat aus der set.config wird hier verwendet.;
		$DocSetFileS = ab5_CrawleDir(array('Path'=>$GLOBALS['Doc'].$GLOBALS['SetCfg']->PATH_DocSet));
		$DocAddFileS = ab5_CrawleDir(array('Path'=>$GLOBALS['Doc'].$GLOBALS['SetCfg']->PATH_DocAdd,'Filter'=>$GLOBALS['SetCfg']->DOC_DocAddFormat));
		if(isset($DocSetFileS['Result'])) {	$GLOBALS['CountSources'] = count($DocSetFileS['Result']); } else { $GLOBALS['CountSources'] = 0; }
		if(isset($DocAddFileS['Result'])) { $GLOBALS['CountChapters'] = count($DocAddFileS['Result']); } else { $GLOBALS['CountChapters'] = 0; }
		$GLOBALS['CountFiles'] = $GLOBALS['CountChapters'] + $GLOBALS['CountSources'];
		if(isset($DocAddFileS['Result']) && count($DocAddFileS['Result']) > 0) {
			$GLOBALS['DocAddFileS'] = $DocAddFileS['Result'];
			natcasesort($GLOBALS['DocAddFileS']);
		} else {
			unset($GLOBALS['DocAddFileS']);
		}
		if(isset($DocSetFileS['Result']) && count($DocSetFileS['Result']) > 0) {
			$GLOBALS['DocSetFileS'] = $DocSetFileS['Result'];	
			natcasesort($GLOBALS['DocSetFileS']);
		} else {
			unset($GLOBALS['DocSetFileS']);
		}
		$Result = '';
		//@M:Es werden PHP, JS, CSS, HTML und sonstige Textdateien berücksichtigt.;
		if(isset($DocSetFileS['Result']) && count($DocSetFileS['Result']) > 0) {
			foreach ($DocSetFileS['Result'] as $File) {
				$tmp = explode(",", $File);
				$FileExt = strtoupper(end($tmp));	
				$tmp = explode(DIRECTORY_SEPARATOR, $File);
				$FileName = end($tmp);
				switch($FileExt) {
					default:
					$Result[] = ParseTxt($File);
				}
			}
		}
	}
	//@c:2014-10-29:Erweitert um Sprachunterstützung;
	//@M:Es wird die grundlegende Vorlage geladen. Diese enthält JS-Funktionen, CSS-Definitionen und das DOM-Modell.;
	//@E:Anpassung aller Darstellungen an neue Vorlage fehlt noch.;
	if(!isset($GLOBALS['Doc']) && $GLOBALS['MultiDoc']) {
		//@M:Es wird am Anfang eine Liste aller möglichen Dokumentationen ausgegeben.;
		$GLOBALS['Templ'] = str_replace('{{NavBar}}', '<span class="Headline"><a href="index.php" class="MenuLink">'.$GLOBALS['LangCurrent']->Global->Start.'</a></span>', $GLOBALS['Templ']);
		$ViewMenu = '<span class="Headline">'.$GLOBALS['Locale'][$GLOBALS['LangS'][0]]->AutoDoc->DocSStartView.'</span>';
		foreach($GLOBALS['DocS'] as $Doc) {
			$Doc = substr($Doc, strpos($Doc,DIRECTORY_SEPARATOR)+1);
			$ViewMenu = $ViewMenu . '<br /><a href="?Doc='.'DocS'.DIRECTORY_SEPARATOR.$Doc.'" style="color: #FFFFFF; text-decoration: none; cursor: pointer;">'.$Doc.'</a><br />';
		}
		$GLOBALS['Templ'] = str_replace('{{LeftContent}}', $ViewMenu, $GLOBALS['Templ']);
		if(is_file('DocS/'.$GLOBALS['SetCfg']->DOC_StartPage)) {
			$StartContent = '<div class="MainContent" id="Chapter_'.str_replace(DIRECTORY_SEPARATOR, '_', $GLOBALS['SetCfg']->DOC_StartPage).'" style="display: block;"><div style="margin: 5px;">'.ParseAddContent('DocS/'.$GLOBALS['SetCfg']->DOC_StartPage).'</div></div>';
		} else {
			$StartContent = '<div class="MainContent" id="Chapter_'.str_replace(DIRECTORY_SEPARATOR, '_', $GLOBALS['SetCfg']->DOC_StartPage).'" style="display: block;">..</div>';
		}
		$GLOBALS['Templ'] = str_replace('{{RightContent}}', $StartContent, $GLOBALS['Templ']);
		$GLOBALS['Templ'] = str_replace('{{AddStyle}', '#LeftContent { padding: 5px; }', $GLOBALS['Templ']);
		echo $GLOBALS['Templ'];
	} elseif(!isset($GLOBALS['V'])) {
		//@M:Wenn zwar ein Doc ausgewählt wurde, jedoch die Ansicht nicht definiert wurde;
		//@c:2015-04-03:Fehlerhafter Link korrigiert;
		if($GLOBALS['MultiDoc']) { $GLOBALS['Templ'] = str_replace('{{NavBar}}', '<a href="autodoc.php" class="MenuLink"><span class="Headline MenuLink">'.$GLOBALS['LangCurrent']->AutoDoc->NaviToStart.'</span></a>', $GLOBALS['Templ']); }
		$ViewMenu = '<span class="Headline">'.$GLOBALS['LangCurrent']->AutoDoc->StartView.'</span>';
		foreach($GLOBALS['LangS'] as $Lang) {
			$ViewMenu = $ViewMenu .'<br /><span class="HeadlineS">'.$GLOBALS['LangCurrent']->Global->Language.'</span><a href="?V=User&L='.$Lang.'&Doc='.$GLOBALS['Doc'].DIRECTORY_SEPARATOR.'" style="color: #FFFFFF; text-decoration: none; cursor: pointer;">'.$GLOBALS['LangCurrent']->AutoDoc->User.'</a><br /><a href="?V=Expert&L='.$Lang.'&Doc='.$GLOBALS['Doc'].DIRECTORY_SEPARATOR.'" style="color: #FFFFFF; text-decoration: none; cursor: pointer;">'.$GLOBALS['LangCurrent']->AutoDoc->Expert.'</a><br />';
		}
		$GLOBALS['Templ'] = str_replace('{{LeftContent}}', $ViewMenu, $GLOBALS['Templ']);
		$GLOBALS['Templ'] = str_replace('{{NavBar}}', '', $GLOBALS['Templ']);
		if(is_file($GLOBALS['Doc'].DIRECTORY_SEPARATOR.$GLOBALS['SetCfg']->DOC_StartPage)) {
			$StartContentInner = '<div style="margin: 5px;">'.ParseAddContent($GLOBALS['Doc'].DIRECTORY_SEPARATOR.$GLOBALS['SetCfg']->DOC_StartPage).'</div>';
		} elseif(is_file($GLOBALS['Doc'].$GLOBALS['SetCfg']->DOC_StartPage)) {
			$StartContentInner = '<div style="margin: 5px;">'.ParseAddContent($GLOBALS['Doc'].$GLOBALS['SetCfg']->DOC_StartPage).'</div>';
		} else {
			$StartContentInner = '..';
		}
		$StartContent = '<div class="MainContent" id="Chapter_'.str_replace(DIRECTORY_SEPARATOR, '_', $GLOBALS['SetCfg']->DOC_StartPage).'" style="display: block;"></div>';
		$StartContent = '<div class="MainContent" id="Chapter_'.str_replace(DIRECTORY_SEPARATOR, '_', $GLOBALS['SetCfg']->DOC_StartPage).'" style="display: block;">'.$StartContentInner.'</div>';
		$GLOBALS['Templ'] = str_replace('{{RightContent}}', $StartContent, $GLOBALS['Templ']);
		$GLOBALS['Templ'] = str_replace('{{AddStyle}', '#LeftContent { padding: 5px; }', $GLOBALS['Templ']);
		echo $GLOBALS['Templ'];
	} elseif(isset($GLOBALS['ExportWf'])) {
		//@M:Wenn das Dokument per Workflow exportiert wird, erfolgt keine visuelle Ausgabe.;
		$Data['Result'] = $Result;
		$DocResult = AutoDocEcho($Data);
		return $DocResult;
	} else {
		//@M:Wenn ein konkretes Dokument ausgewählt wurde, dann wird dieses angezeigt.;
		$Data['Result'] = $Result;
		$DocResult = AutoDocEcho($Data);
		echo $DocResult['FullContent'];
	}
}
function AutoDocRun($RunDocS) {
	//@F:AutoDocRun;
	//@D:Die Funktion AutoDocRun wird nur vom Workflow aufgerufen und handelt dessen Einstellungen ab.
	//@c:2015-08-03:Implementierung des Workflows für die Erzeugung von Dokumenten;
	if(!is_array($RunDocS)) { $RunDocS = $GLOBALS['DocS']; }
	$Log = '';
	foreach($RunDocS as $Doc) {
		$GLOBALS['Doc'] = $Doc.DIRECTORY_SEPARATOR;
		//@V:$GLOBALS['ExportWf'];
		$GLOBALS['ExportWf'] = true;
		foreach($GLOBALS['LangS'] as $LangCode) {
			//@M:Ausgabe des Expert-Manual;
			$GLOBALS['LangCurrent'] = $GLOBALS['Locale'][$LangCode];
			$GLOBALS['V'] = 'Expert';
			AutoDocPrepare();
			$FileName = 'ExportS'.DIRECTORY_SEPARATOR.$GLOBALS['DocName'].'_'.$GLOBALS['V'].'_'.$GLOBALS['LangCurrentCode'];
			$Log .= '+ <b>Export:</b> '.$FileName.'<br />';
			//@M:Ausgabe des User-Manual;
			$GLOBALS['V'] = 'User';
			AutoDocPrepare();
			$FileName = 'ExportS'.DIRECTORY_SEPARATOR.$GLOBALS['DocName'].'_'.$GLOBALS['V'].'_'.$GLOBALS['LangCurrentCode'];
			$Log .= '+ <b>Export:</b> '.$FileName.'<br />';
		}
	}
	$Return['Log'] = $Log;
	return $Return;
}
function AutoDocEcho($Data) {
	//@F:AutoDocEcho;
	//@D:Die Ausgabe inklusive dem Export wird über die Funktion umgesetzt. Dabei werden alle Inhalte übergeben und lediglich die Ausgabe gesteuert.;
	//@I:Data (Array mit allen Informatione, welche dann in VisualDoc umgewandelt werden);
	$VisualComponentS = VisualDoc($Data['Result']);
	$GLOBALS['TemplAd'] = $GLOBALS['Templ'];
	$GLOBALS['TemplAd'] = str_replace('{{NavBar}}', $VisualComponentS['NavBar'], $GLOBALS['TemplAd']);
	$GLOBALS['TemplAd'] = str_replace('{{LeftContent}}', $VisualComponentS['SideBar'], $GLOBALS['TemplAd']);
	$GLOBALS['TemplAd'] = str_replace('{{RightContent}}', $VisualComponentS['MainContent'], $GLOBALS['TemplAd']);
	$GLOBALS['TemplAd'] = str_replace('{{AddStyle}', '#LeftContent { padding: 5px; }', $GLOBALS['TemplAd']);
	$GLOBALS['TemplAd'] = str_replace('{{AddStyle}', '#LeftContent { padding: 5px; }', $GLOBALS['TemplAd']);	
	ob_start();
	echo $GLOBALS['TemplAd'];
	//@c:2015-02-18:Die Exportfunktion wurde ergänzt und ins Menü eingebunden.;
	$FullContent = ob_get_contents();
	ob_end_clean();
	//@c:2015-07-30:Es werden zwei Fassungen exportiert. Eine mit Zeitstempel und eine ohne, deren Inhalt wird überschrieben.;
	$FileNameVersion = $GLOBALS['DocName'].'_'.$GLOBALS['V'].'_'.$GLOBALS['LangCurrentCode'].'_'.date('Y-m-d_H-i', time()).'.htm';
	$FileName = $GLOBALS['DocName'].'_'.$GLOBALS['V'].'_'.$GLOBALS['LangCurrentCode'].'.htm';
	//@c:2016-02-01:Umstellung der Art, wie Dokumente exportiert werden. Dazu gibt es fünf neue Parameter, die gesetzt werden können.;
	$FilePathBase = __DIR__.DIRECTORY_SEPARATOR.'ExportS'.DIRECTORY_SEPARATOR;
	$FilePathExtend = __DIR__.DIRECTORY_SEPARATOR.'ExportS'.DIRECTORY_SEPARATOR.$GLOBALS['DocName'].DIRECTORY_SEPARATOR.$GLOBALS['V'].DIRECTORY_SEPARATOR;
	$FilePathPub = __DIR__.DIRECTORY_SEPARATOR.'ExportS'.DIRECTORY_SEPARATOR.$GLOBALS['V'].DIRECTORY_SEPARATOR;
	if((isset($_GET['Export']) || isset($GLOBALS['ExportWf'])) && (($GLOBALS['V'] == 'Expert' && $GLOBALS['SetCfg']->DocExportExpert == true) || ($GLOBALS['V'] == 'User' && $GLOBALS['SetCfg']->DocExportUser == true)) ) {
		if(!is_dir($FilePathBase)) {
			ab5_MakeDirR($FilePathBase);
		}
		if(!is_dir($FilePathExtend)) {
			ab5_MakeDirR($FilePathExtend);
		}
		if(!is_dir($FilePathPub)) {
			ab5_MakeDirR($FilePathPub);
		}
		if($GLOBALS['SetCfg']->DocExportBase == 'true') {
			file_put_contents($FilePathBase.$FileName, $FullContent);
			file_put_contents($FilePathBase.$FileNameVersion, $FullContent);
		}
		if($GLOBALS['SetCfg']->DocExportExtend == 'true') {
			file_put_contents($FilePathExtend.$FileName, $FullContent);
			file_put_contents($FilePathExtend.$FileNameVersion, $FullContent);
		}
		if($GLOBALS['SetCfg']->DocExportPub == 'true') {
			file_put_contents($FilePathPub.$FileName, $FullContent);
		}
	}
	$Return['FullContent'] = $FullContent;
	return $Return;
}
function VisualDoc ($FullIndex) {
	//@F:VisualDoc;
	//@D:Mit der Funktion VisualDoc werden die Informationen in grafischen Text umgewandelt;
	//@I:FullIndex (enthält alle geparsten Informationen);
	//@O:VisualComponentS ['NavBar'] ['SideBar'] ['MainContent'];
	//@c:2015-02-03:Querverweise für Dateien und Funktionen wurden eingeführt;
	//@c:2015-02-05:Die StartPage wird über die neue Funktion "ParseAddContent" eingebunden;
	$GlobalChangeLogS = Array();
	$DocSetFileS = Array();
	$FunctionS = Array();
	$MainContent = '';
	if(count($FullIndex) > 0 && $FullIndex !== '') {
		foreach($FullIndex as $IndexDoc) {
			//@c:2015-02-03:Alle Dateiverweise werden per Key angesprochen;
			if(!isset($GLOBALS['FileKey'])) { $GLOBALS['FileKey'] = 0; }
			$GLOBALS['FileKey'] += 1;
			$GLOBALS['FileRef'][$IndexDoc['FilePath']] = $GLOBALS['FileKey'];
			$MainContentFunction = '';
			$MainContentFile = '';
			$ChangeLogS = Array();
			if(isset($IndexDoc['c']) && count($IndexDoc['c']) > 0){
				foreach($IndexDoc['c'] as $ThisLog) {
					$GlobalChangeLogS[] = $ThisLog;
					$ChangeLogS[] = $ThisLog;
				}
			}
			$CountFileInner = 0;
			$MainContentFile = $MainContentFile.'<div class="MainContent" id="File_'.$GLOBALS['FileKey'].'"><span class="HeadlineC">'.$IndexDoc['FileName'].'</span>';
			if(isset($IndexDoc['TextS'])) {
				foreach($IndexDoc['TextS'] as $Item) {
					if($GLOBALS['MultiLang']) {
						if(substr($Item,-8,4) == '::'.$GLOBALS['LangCurrentCode']) {
							$MainContentFile = str_replace('::'.$GLOBALS['LangCurrentCode'], '', $MainContentFile.$Item);
							$CountFileInner += 1;
						}
					} else {
						$MainContentFile = $MainContentFile.$Item;
						$CountFileInner += 1;					
					}
				}       
			}
			if(isset($IndexDoc['FunctionS'])) {
				natcasesort($IndexDoc['FunctionS']);
				foreach($IndexDoc['FunctionS'] as $FunctionName) {
					if(!isset($GLOBALS['CountFunctions'])) { $GLOBALS['CountFunctions'] = 0; }
					if(!isset($GLOBALS['FunctionKey'])) { $GLOBALS['FunctionKey'] = 0; }
					$GLOBALS['CountFunctions'] += 1;
					$GLOBALS['FunctionKey'] += 1;
					$GLOBALS['FunctionRef'][$FunctionName] = $GLOBALS['FunctionKey'];
					$MainContentFunction = $MainContentFunction.'<div class="MainContent" id="Function_'.$GLOBALS['FunctionKey'].'"><span class="HeadlineC">'.$FunctionName.'</span>';
					if($FunctionName !== '') {
						$FuncLink = '<small><a href="#" class="RefLink" onclick="ShowFunction(\'Function_'.$GLOBALS['FunctionRef'][$FunctionName].'\'); return false;">[+]</a></small>';
					} else {
						$FuncLink = '';
					}
					$MainContentFile = $MainContentFile.'<span class="HeadlineCF">'.$FunctionName.' '.$FuncLink.'</span>';
					if(isset($IndexDoc[$FunctionName]['TextS']) && count($IndexDoc[$FunctionName]['TextS']) > 0) {
						$CountFileInner += 1;
						$FunctionInFileS[] = array('SortKey'=>$IndexDoc['FileName'].$FunctionName,'FileName'=>$IndexDoc['FileName'],'Label'=>$FunctionName,'Link'=>$GLOBALS['FunctionKey']);
						$FunctionS[] = array('Label'=>$FunctionName,'Link'=>$GLOBALS['FunctionKey']);
						foreach($IndexDoc[$FunctionName]['TextS'] as $Item) {
							$CountFileInner += 1;
							$MainContentFile = $MainContentFile.$Item;
							$MainContentFunction = $MainContentFunction.$Item;
						}
					}
					if(isset($IndexDoc[$FunctionName]['c']) && count($IndexDoc[$FunctionName]['c']) > 0) {
						$FuncChangeLogS = '';
						foreach($IndexDoc[$FunctionName]['c'] as $ThisLog) {
							$GlobalChangeLogS[] = $ThisLog;
							$ChangeLogS[] = $ThisLog;
							$FuncChangeLogS[] = $ThisLog;
						}
						$MainContentFunction = $MainContentFunction.'<span class="HeadlineCH2">'.$GLOBALS['LangCurrent']->AutoDoc->ChangeLog.'</span>'.ChangeLog($FuncChangeLogS);
					}
					$MainContentFunction = $MainContentFunction.'<span class="HeadlineCH2">'.$GLOBALS['LangCurrent']->AutoDoc->Path.'</span><p>'.$IndexDoc['FilePathSub'].'<b>'.$IndexDoc['FileName'].'</b> <a href="#" class="RefLink" onclick="ShowFile(\'File_'.$GLOBALS['FileKey'].'\'); return false;">[+]</a></p>';
					$MainContentFunction = $MainContentFunction.'</div>';
				}
			}
			if(count($ChangeLogS) > 0) {
				if(count($ChangeLogS) > 5) {
					$MainContentFile = $MainContentFile.'<span class="HeadlineCF">'.$GLOBALS['LangCurrent']->AutoDoc->ChangeLog.'</span><p><a href="#" onclick="ShowNextByClass(this,\'HiddenChangeLog\'); return false;">'.$GLOBALS['LangCurrent']->AutoDoc->ChangeLogShow.'</a></p><span class="HiddenChangeLog" style="display: none;">'.ChangeLog($ChangeLogS).'</span>';
				} else {
					$MainContentFile = $MainContentFile.'<span class="HeadlineCF">'.$GLOBALS['LangCurrent']->AutoDoc->ChangeLog.'</span>'.ChangeLog($ChangeLogS);	
				}
			}
			if($CountFileInner > 0){
				$DocSetFile['LinkByName'] = '<a href="#" onclick="HideByClass(\'MainContent\');ShowById(\'File_'.$GLOBALS['FileKey'].'\');return false" class="MenuLink" data-pathsub="'.$IndexDoc['FilePathSub'].'">'.$IndexDoc['FileName'].'</a>';
				$DocSetFile['FilePathSub'] = $IndexDoc['FilePathSub'];
				$DocSetFile['FilePath'] = $IndexDoc['FilePath'];
				$DocSetFile['FileName'] = $IndexDoc['FileName'];
				$DocSetFileS[] = $DocSetFile;
			}
			$MainContentFile = $MainContentFile.'<span class="HeadlineCF">'.$GLOBALS['LangCurrent']->AutoDoc->Path.'</span><p>'.$IndexDoc['FilePathSub'].'<b>'.$IndexDoc['FileName'].'</b> </p>';
			$MainContentFile = $MainContentFile.'</div>';
			$CountFileInnerS[$IndexDoc['FilePath']] = $CountFileInner;
			$MainContent = $MainContent.$MainContentFunction.$MainContentFile;
			if(isset($IndexDoc['Eglobal']) && count($IndexDoc['Eglobal']) > 0 && $IndexDoc['Eglobal'] !== '') {
				$GlobalErrorLogS[] = array('Loc'=>$IndexDoc['FileName'],'FilePath'=>$IndexDoc['FilePath'],'FileKey'=>$IndexDoc['FilePathSub'].$IndexDoc['FileName'],'DataS'=>$IndexDoc['Eglobal']);
				if(!isset($GLOBALS['CountErrors'])) { $GLOBALS['CountErrors'] = 0; }
				$GLOBALS['CountErrors'] = $GLOBALS['CountErrors'] + count($IndexDoc['Eglobal']);
			}
			if(isset($IndexDoc['Rglobal']) && count($IndexDoc['Rglobal']) > 0 && $IndexDoc['Rglobal'] !== '') {
				$GlobalRequestLogS[] = array('Loc'=>$IndexDoc['FileName'],'FilePath'=>$IndexDoc['FilePath'],'FileKey'=>$IndexDoc['FilePathSub'].$IndexDoc['FileName'],'DataS'=>$IndexDoc['Rglobal']);
				if(!isset($GLOBALS['CountRequests'])) { $GLOBALS['CountRequests'] = 0; }
				$GLOBALS['CountRequests'] = $GLOBALS['CountRequests'] + count($IndexDoc['Rglobal']);
			}
		}
	}
	$GLOBALS['CountChanges'] = count($GlobalChangeLogS);
	//@c:2015-02-03:Darstellung des Namens der Dokumentation auf der Startseite wurde geändert;
	$GLOBALS['DocName'] = explode(DIRECTORY_SEPARATOR, substr($GLOBALS['Doc'], 0,-1));
	$GLOBALS['DocName'] = end($GLOBALS['DocName']);
    $SideBar = '<div id="GlobalInfo" style="display: block;" class="SideBarContent" ><span class="Headline">'.$GLOBALS['LangCurrent']->AutoDoc->StartHeadLeft.'</span><br /><span class="MenuText">'.$GLOBALS['LangCurrent']->AutoDoc->DocName.':<br /><b>'.$GLOBALS['DocName'].'</b></span><br /><br /><span class="MenuText">'.$GLOBALS['LangCurrent']->AutoDoc->StartView.':<br /><b>'.$GLOBALS['LangCurrent']->AutoDoc->$GLOBALS['V'].' ('.$GLOBALS['LangCurrent']->Global->Language.')</b></span></div>';
	$GlobalFileList = '<span class="Headline">'.$GLOBALS['LangCurrent']->AutoDoc->File.'</span>';
	$DocSetFileS = ab5_SortArrayByColumn($DocSetFileS,'FileName');
	foreach($DocSetFileS as $FileLink) {
		$GlobalFileList = $GlobalFileList.$FileLink['LinkByName'].'<br />';
	}
	$GlobalFileList = $GlobalFileList.'<br /><a href="#" onclick="HideByClass(\'MainContent\');HideByClass(\'SideBarContent\'); ShowById(\'GlobalFileSubList\');return false" class="MenuLink"><small>['.$GLOBALS['LangCurrent']->AutoDoc->SortFileByFolder.']</small></a>';
	$SideBar = $SideBar.'<div id="GlobalFileList" class="SideBarContent">'.$GlobalFileList.'</div>';
	$GlobalFileSubList = '<span class="Headline">'.$GLOBALS['LangCurrent']->AutoDoc->File.'</span>';
	$DocSetFileS = ab5_SortArrayByColumn($DocSetFileS,'FilePath');
	$CurrentFolderName = '';
	foreach($DocSetFileS as $FileLink) {
		if($CurrentFolderName !== $FileLink['FilePathSub']) {
			$GlobalFileSubList = $GlobalFileSubList.'<br /><span class="HeadlineS">'.$FileLink['FilePathSub'].'</span>';
		}
		$GlobalFileSubList = $GlobalFileSubList.$FileLink['LinkByName'].'<br />';
		$CurrentFolderName = $FileLink['FilePathSub'];
	}
	$GlobalFileSubList = $GlobalFileSubList.'<br /><a href="#" onclick="HideByClass(\'MainContent\');HideByClass(\'SideBarContent\'); ShowById(\'GlobalFileList\');return false" class="MenuLink"><small>['.$GLOBALS['LangCurrent']->AutoDoc->SortFileByName.']</small></a>';
	$SideBar = $SideBar.'<div id="GlobalFileSubList" class="SideBarContent">'.$GlobalFileSubList.'</div>';
	if(isset($GLOBALS['DocAddFileS']) && count($GLOBALS['DocAddFileS']) > 0) {
		$GlobalChapterList = '<span class="Headline">'.$GLOBALS['LangCurrent']->AutoDoc->Chapter.'</span><p>';
		//@c:2015-02-03:Kapitel korrekt eingebunden. Eine Pfadangabe entfällt, da der Dateiname der Überschrift entspricht.;
		//@c:2015-02-03:Kapitel werden jetzt über eine ChapterId angesprochen.;
		if($GLOBALS['MultiLang']) {
			//@c:2015-03-04:Prüfung auf leere Zusatzdokumente wurde eingebaut.;
			if(isset($GLOBALS['DocAddFileS']) && count($GLOBALS['DocAddFileS']) > 0) {
				foreach ($GLOBALS['DocAddFileS'] as $FileLink) {
					$FileName = substr($FileLink, strrpos($FileLink, DIRECTORY_SEPARATOR)+1,strrpos($FileLink, '.') - strrpos($FileLink, DIRECTORY_SEPARATOR)-1);
					$FileExt = explode('.', $FileLink);
					$FileExt = end($FileExt);
					//@c:2015-02-20:Einbindung von Bildern funktioniert jetzt richtig, da die Bilder nicht mehr als Dokument verarbeitet werden.;
					//@M:Als DocAdd werden derzeit nur HTML-Dokumente unterstützt.;
					if (strtoupper($FileExt) == "HTML") {
						if(!isset($GLOBALS['ChapterKey'])) { $GLOBALS['ChapterKey'] = 0; }
						$GLOBALS['ChapterKey'] += 1;
						$GLOBALS['ChapterRef'][$FileName] = $GLOBALS['ChapterKey'];
						if(substr($FileName,-3) == '.'.$GLOBALS['LangCurrentCode']) {
							$FileName = substr($FileName,0,-3);
							$MainContent = $MainContent.'<div class="MainContent" id="Chapter_'.$GLOBALS['ChapterKey'].'"><span class="HeadlineC">'.$FileName.'</span><div style="margin: 5px;">'.ParseAddContent($FileLink).'</div></div>';
							$FileLink = '<a href="#" onclick="HideByClass(\'MainContent\');ShowById(\'Chapter_'.$GLOBALS['ChapterKey'].'\');return false" class="MenuLink">'.$FileName.'</a>';
							$GlobalChapterList = $GlobalChapterList.$FileLink.'<br />';
						}
					}
				}
				$GlobalChapterList = $GlobalChapterList.'</p>';
				$SideBar = $SideBar.'<div id="GlobalChapterList" class="SideBarContent">'.$GlobalChapterList.'</div>';
			}
		} else {
			//@c:2015-03-04:Prüfung auf leere Zusatzdokumente wurde eingebaut.;
			if(isset($GLOBALS['DocAddFileS']) && count($GLOBALS['DocAddFileS']) > 0) {
				foreach ($GLOBALS['DocAddFileS'] as $FileLink) {
					$FileName = substr($FileLink, strrpos($FileLink, DIRECTORY_SEPARATOR)+1,strrpos($FileLink, '.') - strrpos($FileLink, DIRECTORY_SEPARATOR)-1);
					$FileExt = explode('.', $FileLink);
					$FileExt = end($FileExt);
					if (strtoupper($FileExt) == "HTML") {
						if(!isset($GLOBALS['ChapterKey'])) { $GLOBALS['ChapterKey'] = 0; }
						$GLOBALS['ChapterKey'] += 1;
						$GLOBALS['ChapterRef'][$FileName] = $GLOBALS['ChapterKey'];
						$MainContent = $MainContent.'<div class="MainContent" id="Chapter_'.$GLOBALS['ChapterKey'].'"><span class="HeadlineC">'.$FileName.'</span><div style="margin: 5px;">'.ParseAddContent($FileLink).'</div></div>';
						$FileLink = '<a href="#" onclick="HideByClass(\'MainContent\');ShowById(\'Chapter_'.$GLOBALS['ChapterKey'].'\');return false" class="MenuLink">'.$FileName.'</a>';
						$GlobalChapterList = $GlobalChapterList.$FileLink.'<br />';
					}
				}
				$GlobalChapterList = $GlobalChapterList.'</p>';
				$SideBar = $SideBar.'<div id="GlobalChapterList" class="SideBarContent">'.$GlobalChapterList.'</div>';
			}
		}
	}
	if(count($FunctionS) > 0) {
		$GlobalFunctionList = '<span class="Headline">'.$GLOBALS['LangCurrent']->AutoDoc->Function.'</span>';
		$FunctionS = ab5_SortArrayByColumn($FunctionS,'Label');
		foreach ($FunctionS as $FunctionName) {
			$GlobalFunctionList = $GlobalFunctionList.'<a href="#" onclick="HideByClass(\'MainContent\');ShowById(\'Function_'.$FunctionName['Link'].'\');return false" class="MenuLink">'.$FunctionName['Label'].'</a><br />';
		}
		$GlobalFunctionList = $GlobalFunctionList.'<br /><a href="#" onclick="HideByClass(\'MainContent\');HideByClass(\'SideBarContent\'); ShowById(\'GlobalFunctionListInFile\');return false" class="MenuLink"><small>['.$GLOBALS['LangCurrent']->AutoDoc->SortFuncByFile.']</small></a>';
		$SideBar = $SideBar.'<div id="GlobalFunctionList" class="SideBarContent">'.$GlobalFunctionList.'</div>';
		$GlobalFunctionListInFile = '<span class="Headline">'.$GLOBALS['LangCurrent']->AutoDoc->Function.'</span>';
		$FunctionInFileS = ab5_SortArrayByColumn($FunctionInFileS,'SortKey');
		$CurrentFileName = '';
		foreach ($FunctionInFileS as $FunctionName) {
			if($FunctionName['FileName'] !== $CurrentFileName) {
				$GlobalFunctionListInFile = $GlobalFunctionListInFile.'<br /><span class="HeadlineS">'.$FunctionName['FileName'].'</span>';
				$CurrentFileName = $FunctionName['FileName'];
			}
			$GlobalFunctionListInFile = $GlobalFunctionListInFile.'<a href="#" onclick="HideByClass(\'MainContent\');ShowById(\'Function_'.$FunctionName['Link'].'\');return false" class="MenuLink">'.$FunctionName['Label'].'</a><br />';
		}
		$GlobalFunctionListInFile = $GlobalFunctionListInFile.'<br /><a href="#" onclick="HideByClass(\'MainContent\');HideByClass(\'SideBarContent\'); ShowById(\'GlobalFunctionList\');return false" class="MenuLink"><small>['.$GLOBALS['LangCurrent']->AutoDoc->SortFuncByName.']</small></a><br />';
		$SideBar = $SideBar.'<div id="GlobalFunctionListInFile" class="SideBarContent">'.$GlobalFunctionListInFile.'</div>';		
	}
	if(!isset($GlobalChangeLogS)) { $GlobalChangeLogS = ''; }
	$MainContent = $MainContent.'<div class="MainContent" id="GlobalChangeLog"><span class="HeadlineC">'.$GLOBALS['LangCurrent']->AutoDoc->ChangeLog.'</span>'.GlobalChangeLog($GlobalChangeLogS,'Date','SORT_DESC').'</div>';
	if(!isset($GlobalErrorLogS)) { $GlobalErrorLogS = ''; }
	$MainContent = $MainContent.'<div class="MainContent" id="GlobalErrorLog"><span class="HeadlineC">'.$GLOBALS['LangCurrent']->AutoDoc->ErrorLog.'</span>'.RenderLog($GlobalErrorLogS,'FileKey','SORT_ASC').'</div>';
	if(!isset($GlobalRequestLogS)) { $GlobalRequestLogS = ''; }
	$MainContent = $MainContent.'<div class="MainContent" id="GlobalRequestLog"><span class="HeadlineC">'.$GLOBALS['LangCurrent']->AutoDoc->RequestLog.'</span>'.RenderLog($GlobalRequestLogS,'FileKey','SORT_ASC').'</div>';
	$MainContent = $MainContent.'<div class="MainContent" id="RawIndex"><span class="HeadlineC">'.$GLOBALS['LangCurrent']->AutoDoc->RawIndex.'</span><p>'.ab5_ReturnArrayAsTree($FullIndex).'</p></div>';
	if(!isset($GLOBALS['CountFunctions'])) { $GLOBALS['CountFunctions'] = 0; }
	if(!isset($GLOBALS['CountErrors'])) { $GLOBALS['CountErrors'] = 0; }
	if(!isset($GLOBALS['CountRequests'])) { $GLOBALS['CountRequests'] = 0; }
	if(!isset($GLOBALS['CountChanges'])) { $GLOBALS['CountChanges'] = 0; }
	$SideBar = $SideBar.'<div id="ToolSideInfo" class="SideBarContent" style="color: #FFFFFF;"><span class="Headline">'.$GLOBALS['LangCurrent']->AutoDoc->AutoDocInfo.'</span>'.$GLOBALS['LangCurrent']->AutoDoc->FileCount.': '.$GLOBALS['CountSources'].' '.$GLOBALS['LangCurrent']->AutoDoc->CountOf.' '.$GLOBALS['CountFiles'].'<br />'.$GLOBALS['LangCurrent']->AutoDoc->FunctionCount.': '.$GLOBALS['CountFunctions'].'<br />'.$GLOBALS['LangCurrent']->AutoDoc->ChapterCount.': '.$GLOBALS['CountChapters'].'<br />'.$GLOBALS['LangCurrent']->AutoDoc->LogCount.': '.count($GlobalChangeLogS).'<br />'.$GLOBALS['LangCurrent']->AutoDoc->ErrorCount.': '.$GLOBALS['CountErrors'].'<br />'.$GLOBALS['LangCurrent']->AutoDoc->RequestCount.': '.$GLOBALS['CountRequests'].'<br />'.$GLOBALS['LangCurrent']->AutoDoc->DocReleaseDate.': '.date('Y-m-d',time()).'<br /><br /><a href="http://doccen.com" style="color: #FFFFFF; text-decoration: none;">'.$GLOBALS['LangCurrent']->AutoDoc->LinkToSelfDocu.'</a><br /><br />'.$GLOBALS['LangCurrent']->AutoDoc->LinkToHome.'<br /><a href="http://doccen.com" style="color: #FFFFFF; text-decoration: none;"><b>doccen.com</b></a><br /><br /><small>'.$GLOBALS['LangCurrent']->AutoDoc->Version.' AutoDoc: '.$GLOBALS['AppVersion']['AutoDoc'].'<br />'.$GLOBALS['LangCurrent']->AutoDoc->Version.' Lib: '.$GLOBALS['AppVersion']['Lib'].'<br />'.$GLOBALS['LangCurrent']->AutoDoc->Version.' Schema: '.$GLOBALS['SetCfg']->schemaversion.'<br /></small></div>';
	//@stop;
	$MainContent = $MainContent.'<div class="MainContent" id="ToolInfo"><span class="HeadlineC">AutoDoc</span><p>'.$GLOBALS['LangCurrent']->AutoDoc->BasicHelp->Info.'</p><p>
		<table class="Info">
			<tr><th></th><th>'.$GLOBALS['LangCurrent']->AutoDoc->Expert.'</th><th>'.$GLOBALS['LangCurrent']->AutoDoc->User.'</th></tr>
			<tr><td colspan="99"><br /><b>'.$GLOBALS['LangCurrent']->AutoDoc->BasicHelp->HeadlineCommentDirect.'</b></td></tr>
			<tr><td><span class="HeadlineCF"><b>@F</b> '.$GLOBALS['LangCurrent']->AutoDoc->Function.';</span></td><td>x</td><td>x</td></tr>
			<tr><td><span class="HeadlineCH2"><b>@H</b> '.$GLOBALS['LangCurrent']->AutoDoc->Headline.';</span></td><td>x</td><td>x</td></tr>
			<tr><td><span class="HeadlineCH1"><b>@H1</b> '.$GLOBALS['LangCurrent']->AutoDoc->Headline.';</span></td><td>x</td><td>x</td></tr>
			<tr><td><span class="HeadlineCH2"><b>@H2</b> '.$GLOBALS['LangCurrent']->AutoDoc->Headline.';</span></td><td>x</td><td>x</td></tr>
			<tr><td><span class="HeadlineCH3"><b>@H3</b> '.$GLOBALS['LangCurrent']->AutoDoc->Headline.';</span></td><td>x</td><td>x</td></tr>
			<tr><td><span class="PartA"><b>@A</b> '.$GLOBALS['LangCurrent']->AutoDoc->Author.';</span></td><td>x</td><td>-</td></tr>
			<tr><td><span class="PartD"><b>@D</b> Text;</span></td><td>x</td><td>x</td></tr>
			<tr><td><span class="PartMemo"><b>@M</b> Memo;</span></td><td>x</td><td>x</td></tr>
			<tr><td><span class="PartMind"><b>@m</b> ExpertsMemo;</span></td><td>x</td><td>-</td></tr>
			<tr><td><span class="PartS"><b>@S</b> SourceCode Sample;</span></td><td>x</td><td>x</td></tr>
			<tr><td><span class="PartI"><b>@I</b> Input;</span></td><td>x</td><td>-</td></tr>
			<tr><td><span class="PartV"><b>@V</b> Variablen;</span></td><td>x</td><td>-</td></tr>
			<tr><td><span class="PartO"><b>@O</b> Output;</span></td><td>x</td><td>-</td></tr>
			<tr><td><span class="PartR"><b>@R</b> '.$GLOBALS['LangCurrent']->AutoDoc->Request.';</span></td><td>x</td><td>-</td></tr>
			<tr><td><span class="PartE"><b>@E</b> '.$GLOBALS['LangCurrent']->AutoDoc->BasicHelp->KnownIssue.';</span></td><td>x</td><td>-</td></tr>
			<tr><td colspan="99"><br /><b>'.$GLOBALS['LangCurrent']->AutoDoc->BasicHelp->HeadlineCommentIndirect.'</b></td></tr>
			<tr><td><span class="PartC"><b>@c:2014-10-01:</b> ChangeLog;</span></td><td>x</td><td>-</td></tr>
			<tr><td colspan="99"><br /><b>'.$GLOBALS['LangCurrent']->AutoDoc->BasicHelp->HeadlineCommentHidden.'</b></td></tr>
			<tr><td colspan="99"><b>@stop;</b> '.$GLOBALS['LangCurrent']->AutoDoc->BasicHelp->ParseStop.'</td></tr>
			<tr><td colspan="99"><b>@start;</b> '.$GLOBALS['LangCurrent']->AutoDoc->BasicHelp->ParseStart.'</td></tr>
			<tr><td colspan="99"><b>de:</b> '.$GLOBALS['LangCurrent']->AutoDoc->BasicHelp->TranslateComment.';</td></tr>
			<tr><td colspan="99"><b>.de</b> '.$GLOBALS['LangCurrent']->AutoDoc->BasicHelp->TranslateFile.'</td></tr>
		</table></div>';
	//@start;
	//@c:2015-02-03:Der Button "Menü" führt zum Start von AutoDoc zurück und nicht zur obersten Einstiegsebene;
	$NavBar = '<span class="Headline"><a href="?" class="MenuLink">'.$GLOBALS['LangCurrent']->AutoDoc->Navi.'</a></span>';
	$NavBar = $NavBar.'<a href="" class="MenuLink">'.$GLOBALS['LangCurrent']->AutoDoc->Start.'</a><br />';
	if(isset($GLOBALS['DocAddFileS']) && count($GLOBALS['DocAddFileS']) > 0) {
		$NavBar = $NavBar.'<a href="#" onclick="HideByClass(\'SideBarContent\'); HideByClass(\'MainContent\'); ShowById(\'GlobalChapterList\'); return false;" class="MenuLink">'.$GLOBALS['LangCurrent']->AutoDoc->Chapter.'</a><br />';
	}
	if(isset($DocSetFileS) && count($DocSetFileS) > 0) {
		$NavBar = $NavBar.'<a href="#" onclick="HideByClass(\'SideBarContent\'); HideByClass(\'MainContent\'); ShowById(\'GlobalFileSubList\'); return false;" class="MenuLink">'.$GLOBALS['LangCurrent']->AutoDoc->File.'</a><br />';
	}
	if(isset($FunctionS) && count($FunctionS) > 0) {
		$NavBar = $NavBar.'<a href="#" onclick="HideByClass(\'SideBarContent\'); HideByClass(\'MainContent\'); ShowById(\'GlobalFunctionList\'); return false;" class="MenuLink">'.$GLOBALS['LangCurrent']->AutoDoc->Function.'</a><br />';
	}
	if($GLOBALS['V']=='Expert') {
		$NavBar = $NavBar.'<br />';
		if(isset($GLOBALS['CountErrors']) && $GLOBALS['CountErrors'] > 0) {
			$NavBar = $NavBar.'<a href="#" onclick="HideByClass(\'SideBarContent\'); HideByClass(\'MainContent\'); ShowById(\'GlobalErrorLog\'); return false;" class="MenuLink">'.$GLOBALS['LangCurrent']->AutoDoc->ErrorLog.'</a><br />';
		}
		if(isset($GLOBALS['CountRequests']) && $GLOBALS['CountRequests'] > 0) {
			$NavBar = $NavBar.'<a href="#" onclick="HideByClass(\'SideBarContent\'); HideByClass(\'MainContent\'); ShowById(\'GlobalRequestLog\'); return false;" class="MenuLink">'.$GLOBALS['LangCurrent']->AutoDoc->RequestLog.'</a><br />';
		}
		if(isset($GLOBALS['CountChanges']) && $GLOBALS['CountChanges'] > 0) {
			$NavBar = $NavBar.'<a href="#" onclick="HideByClass(\'SideBarContent\'); HideByClass(\'MainContent\'); ShowById(\'GlobalChangeLog\'); return false;" class="MenuLink">'.$GLOBALS['LangCurrent']->AutoDoc->ChangeLog.'</a><br />';
		}
		$NavBar = $NavBar.'<a href="#" onclick="HideByClass(\'SideBarContent\'); HideByClass(\'MainContent\'); ShowById(\'RawIndex\'); return false;" class="MenuLink">'.$GLOBALS['LangCurrent']->AutoDoc->RawIndex.'</a><br />';
	    //@c:2014-10-16:Suchmodus hinzugefügt, der alles einblendet;
		$NavBar = $NavBar.'<a href="#" onclick="ShowByClass(\'SideBarContent\'); ShowByClass(\'MainContent\'); return false;" class="MenuLink">'.$GLOBALS['LangCurrent']->AutoDoc->SearchMode.'</a><br />';    
		$NavBar = $NavBar.'<br /><a href="#" onclick="HideByClass(\'SideBarContent\'); HideByClass(\'MainContent\'); ShowById(\'ToolSideInfo\'); ShowById(\'ToolInfo\'); return false;" class="MenuLink">'.$GLOBALS['LangCurrent']->AutoDoc->AutoDocInfo.'</a><br />';
		if(!isset($_GET['Export']) && !isset($GLOBALS['ExportWf'])) {
			$NavBar = $NavBar.'<a href="'.$_SERVER['REQUEST_URI'].'&Export">'.$GLOBALS['LangCurrent']->AutoDoc->Export.'</a>';
		}
	}
	if(is_file($GLOBALS['Doc'].$GLOBALS['SetCfg']->DOC_StartPage)) {
		$MainContent = $MainContent.'<div class="MainContent" id="Chapter_'.str_replace(DIRECTORY_SEPARATOR, '_', $GLOBALS['SetCfg']->DOC_StartPage).'" style="display: block;"><div style="margin: 5px;">'.ParseAddContent($GLOBALS['Doc'].$GLOBALS['SetCfg']->DOC_StartPage).'</div></div>';
	} else {
		$MainContent = $MainContent.'<div class="MainContent" id="Chapter_'.str_replace(DIRECTORY_SEPARATOR, '_', $GLOBALS['SetCfg']->DOC_StartPage).'" style="display: block;"></div>';
	}
	$VisualComponentS['NavBar'] = $NavBar;
	$VisualComponentS['SideBar'] = $SideBar;
	$VisualComponentS['MainContent'] = $MainContent;
	return $VisualComponentS;
}
function ParseAddContent ($FileLink) {
	//@F:ParseAddContent;
	//@D:Für eine verbesserte Einbindung von zusätzlichen Dokumenten (DocAdd), wandelt diese Funktion den importieren HTML-Code so um, dass am Ende ein funktionierendes Dokument eingebunden werden kann. Dies betrifft beispielsweise die Einbettung von Grafiken.;
	//@c:2015-02-05:Funktion wurde hinzugefügt.;
	$FileContent = file_get_contents($FileLink);
	$DocDom = new DOMDocument();
    $DocDom->loadHTML($FileContent);
	$TagS = $DocDom->getElementsByTagName('img');
	foreach ($TagS as $Tag) {
		$OldSrc = $Tag->getAttribute('src');
		$OldSrcExt = explode('.', $OldSrc);
		$OldSrcExt = strtolower(end($OldSrcExt));
		$FilePath = substr($FileLink,0,strrpos($FileLink, DIRECTORY_SEPARATOR));
		$NewSrc = 'data:image/'.$OldSrcExt.';base64,'.base64_encode(file_get_contents($FilePath.DIRECTORY_SEPARATOR.$OldSrc));
		$Tag->setAttribute('src', $NewSrc);
	}
	$Body = $DocDom->getElementsByTagName('body');
	$Body = $Body->item(0);
	//@c:2016-02-15:Exportiert nur noch den INHALT des Body von extrenen Dokumenten.;
	$HtmlOut = $DocDom->saveHTML($Body);
	return substr($HtmlOut, 6, $HtmlOut-13);
}
function ParseContent ($Doc,$File) {
	//@F:ParseContent;
	//@D:Diese Funktion baut den Index auf;
	//@c:2014-10-29:Changelog kann jetzt mit Datum 2014-10-01 oder mit Versionsnummer 0.0.1 definiert werden, bzw. mit allem, was zwischen den beiden Doppelpunkten steht;
	//@c:2014-10-29:Die Variablen I und O werden jetzt auch der Datei, statt nur einer Funktion zugeordnet;
	//@c:2014-10-21:Es können <br /> Tags zur Strukturierung von Code verwendet werden;
	//@stop;
	$PathS = explode(DIRECTORY_SEPARATOR,$File);
	$File = utf8_encode($File);
	$DocIndex['FilePath'] = $File;
	$FilePathSub = substr($File,strpos($File,'DocSet')+6);
	$FilePathSub = substr($FilePathSub,0,strrpos($FilePathSub,DIRECTORY_SEPARATOR)+1);
	$DocIndex['FilePathSub'] = $FilePathSub;
	$DocIndex['FileName'] = end($PathS);
	$Parse = true;
	$i = 0;
	while($Parse == true) {
		$Pos = FindNext($Doc,array('@F:','@c:','@M:','@S','@H:','@H1:','@H2:','@H3:','@m:','@D:','@A:','@I:','@O:','@V:','@R:','@E:','@stop;'));
		if($Pos !== false) {
			$Doc = substr($Doc, $Pos);
			$Case = substr($Doc, 0, strpos($Doc, ':')+1);
			if(strlen($Case) > 5) {
				$Case = substr($Doc, 0, strpos($Doc, ';')+1);
			}
			switch($Case) {
				case '@F:':
				$EndPos = strpos($Doc, ';')+1;
				$DocI = substr($Doc, 0, $EndPos);
				$F = substr($DocI, 3, strpos($DocI, ';', 3)-3);
				if($F !== '') {
					$DocIndex['FunctionS'][] = $F;
				}
				break;
				case '@H:':
				$EndPos = strpos($Doc, ';')+1;
				$DocI = substr($Doc, 0, $EndPos);
				$H = substr($DocI, 3, strpos($DocI, ';', 3)-3);
				if(isset($F) && $F <> '') {
					$DocIndex[$F]['H'][] = str_replace(array('*', '//'),"<br />",$H);
					$DocIndex[$F]['TextS'][] = '<p class="HeadlineCH2">'.str_replace(array('*', '//'),"<br />",$H).'</p>';
				} else {
					$DocIndex['H'][] = str_replace(array('*', '//'),"<br />",$H);
					$DocIndex['TextS'][] = '<p class="HeadlineCH1">'.str_replace(array('*', '//'),"<br />",$H).'</p>';
				}
				break;
				case '@H1:':
				$EndPos = strpos($Doc, ';')+1;
				$DocI = substr($Doc, 0, $EndPos);
				$H = substr($DocI, 4, strpos($DocI, ';', 4)-4);
				if(isset($F) && $F <> '') {
					$DocIndex[$F]['H'][] = str_replace(array('*', '//'),"<br />",$H);
					$DocIndex[$F]['TextS'][] = '<p class="HeadlineCH1">'.str_replace(array('*', '//'),"<br />",$H).'</p>';
				} else {
					$DocIndex['H'][] = str_replace(array('*', '//'),"<br />",$H);
					$DocIndex['TextS'][] = '<p class="HeadlineCH1">'.str_replace(array('*', '//'),"<br />",$H).'</p>';
				}
				break;	
				case '@H2:':
				$EndPos = strpos($Doc, ';')+1;
				$DocI = substr($Doc, 0, $EndPos);
				$H = substr($DocI, 4, strpos($DocI, ';', 4)-4);
				if(isset($F) && $F <> '') {
					$DocIndex[$F]['H'][] = str_replace(array('*', '//'),"<br />",$H);
					$DocIndex[$F]['TextS'][] = '<p class="HeadlineCH2">'.str_replace(array('*', '//'),"<br />",$H).'</p>';
				} else {
					$DocIndex['H'][] = str_replace(array('*', '//'),"<br />",$H);
					$DocIndex['TextS'][] = '<p class="HeadlineCH2">'.str_replace(array('*', '//'),"<br />",$H).'</p>';
				}
				break;	
				case '@H3:':
				$EndPos = strpos($Doc, ';')+1;
				$DocI = substr($Doc, 0, $EndPos);
				$H = substr($DocI, 4, strpos($DocI, ';', 4)-4);
				if(isset($F) && $F <> '') {
					$DocIndex[$F]['H'][] = str_replace(array('*', '//'),"<br />",$H);
					$DocIndex[$F]['TextS'][] = '<p class="HeadlineCH3">'.str_replace(array('*', '//'),"<br />",$H).'</p>';
				} else {
					$DocIndex['H'][] = str_replace(array('*', '//'),"<br />",$H);
					$DocIndex['TextS'][] = '<p class="HeadlineCH3">'.str_replace(array('*', '//'),"<br />",$H).'</p>';
				}
				break;					
				case '@M:':
				$EndPos = strpos($Doc, ';')+1;
				$DocI = substr($Doc, 0, $EndPos);
				$M = substr($DocI, 3, strpos($DocI, ';', 3)-3);
				if(isset($F) && $F <> '') {
					$DocIndex[$F]['M'][] = str_replace(array('*', '//'),"<br />",$M);
					$DocIndex[$F]['TextS'][] = '<p class="PartMemo">'.str_replace(array('*', '//'),"<br />",$M).'</p>';
				} else {
					$DocIndex['M'][] = str_replace(array('*', '//'),"<br />",$M);
					$DocIndex['TextS'][] = '<p class="PartMemo">'.str_replace(array('*', '//'),"<br />",$M).'</p>';
				}                    
				break;
				case '@S:':
				$EndPos = strpos($Doc, ';')+1;
				$DocI = substr($Doc, 0, $EndPos);
				$S = substr($DocI, 3, strpos($DocI, ';', 3)-3);
				$S = htmlentities($S);
				$S = str_replace("&lt;br /&gt;","<br />",$S);
				if(isset($F) && $F <> '') {
					$DocIndex[$F]['S'][] = str_replace(array('*', '//'),"<br />",$S);
					$DocIndex[$F]['TextS'][] = '<p class="PartS"><small>Sourcecode</small><br />'.str_replace(array('*', '//'),"<br />",$S).'</p>';
				} else {
					$DocIndex['S'][] = str_replace(array('*', '//'),"<br />",$S);
					$DocIndex['TextS'][] = '<p class="PartS"><small>Sourcecode</small><br />'.str_replace(array('*', '//'),"<br />",$S).'</p>';
				}                    
				break;					
				case '@m:':
				$EndPos = strpos($Doc, ';')+1;
				if($GLOBALS['V'] == 'Expert') {
					$DocI = substr($Doc, 0, $EndPos);
					$m = substr($DocI, 3, strpos($DocI, ';', 3)-3);
					if(isset($F) && $F <> '') {
						$DocIndex[$F]['m'][] = str_replace(array('*', '//'),"<br />",$m);
						$DocIndex[$F]['TextS'][] = '<p class="PartMind">'.str_replace(array('*', '//'),"<br />",$m).'</p>';
					} else {
						$DocIndex['m'][] = str_replace(array('*', '//'),"<br />",$m);
						$DocIndex['TextS'][] = '<p class="PartMind">'.str_replace(array('*', '//'),"<br />",$m).'</p>';
					}                    
				}
				break;
				case '@A:':
				$EndPos = strpos($Doc, ';')+1;
				if($GLOBALS['V'] == 'Expert') {
					$DocI = substr($Doc, 0, $EndPos);
					$A = substr($DocI, 3, strpos($DocI, ';', 3)-3);
					if(isset($F) && $F <> '') {
						$DocIndex[$F]['A'] = $A;
						$DocIndex[$F]['TextS'][] = '<p class="PartA"><small>Autor</small><br />'.str_replace(array('*', '//'),"<br />",$A).'</p>';
					} else {
						$DocIndex['A'] = $A;
						$DocIndex['TextS'][] = '<p class="PartA"><small>Autor</small><br />'.str_replace(array('*', '//'),"<br />",$A).'</p>';
					}                    
				}
				break;
				case '@c:':
				$EndPos = strpos($Doc, ';')+1;
				if($GLOBALS['V'] == 'Expert') {
					$DocI = substr($Doc, 0, $EndPos);
					$c = substr($DocI, 3, strpos($DocI, ';', 3)-3);
					$cTrimCount = strpos($c, ':');
					$cDate = substr($c, 0,$cTrimCount);
					$cMemo = substr($c, $cTrimCount+1);
					if(!isset($F)) { $F=''; }
					if(!isset($H)) { $H=''; }
					$cThis = array('Raw'=>$c,'Date'=>$cDate,'Memo'=>$cMemo,'FilePath'=>$DocIndex['FilePath'],'FileKey'=>$DocIndex['FilePathSub'].$DocIndex['FileName'],'FileName'=>$DocIndex['FileName'],'Func'=>$F,'Head'=>$H);
					if(isset($F) && $F <> '') {
						$DocIndex[$F]['c'][] = $cThis;
						$DocIndex['Cglobal'][] = str_replace(array('*', '//'),"<br />",$c.'<br /><span class="Sub">F: '.$F.'</span>');
					} elseif (isset($H) && $H <> '') {
						//@c:2015-07-29:Wenn keine Funktion gesetzt ist, wird versucht die letzte H zu verwenden.;
						$DocIndex['c'][] = $cThis;
						$DocIndex['Cglobal'][] = str_replace(array('*', '//'),"<br />",$c.'<br /><span class="Sub">H: '.$H.'</span>');
					} else {
						$DocIndex['c'][] = $cThis;
						$DocIndex['Cglobal'][] = str_replace(array('*', '//'),"<br />",$c);
					}
				}
				break;					
				case '@D:':
				$EndPos = strpos($Doc, ';')+1;
				$DocI = substr($Doc, 0, $EndPos);
				$D = substr($DocI, 3, strpos($DocI, ';', 3)-3);
				if(isset($F) && $F <> '') {
					$DocIndex[$F]['D'][] = str_replace(array('*', '//'),"<br />",$D);
					$DocIndex[$F]['TextS'][] = '<p class="PartD">'.str_replace(array('*', '//'),"<br />",$D).'</p>';
				} else {
					$DocIndex['D'][] = str_replace(array('*', '//'),"<br />",$D);
					$DocIndex['TextS'][] = '<p class="PartD">'.str_replace(array('*', '//'),"<br />",$D).'</p>';
				}                    
				break;
				case '@I:':
				$EndPos = strpos($Doc, ';')+1;
				if($GLOBALS['V'] == 'Expert') {
					$DocI = substr($Doc, 0, $EndPos);
					$I = substr($DocI, 3, strpos($DocI, ';', 3)-3);
					if(isset($F) && $F <> '') {
						$DocIndex[$F]['I'][] = str_replace(array('*', '//'),"<br />",$I);
						$DocIndex[$F]['TextS'][] = '<p class="PartI"><small>Eingabe</small><br />'.str_replace(array('*', '//'),"<br />",$I).'</p>';
					} else {
						$DocIndex['I'][] = str_replace(array('*', '//'),"<br />",$I);
						$DocIndex['TextS'][] = '<p class="PartI"><small>Eingabe</small><br />'.str_replace(array('*', '//'),"<br />",$I).'</p>';	
					}
				}
				break;
				case '@O:':
				$EndPos = strpos($Doc, ';')+1;
				if($GLOBALS['V'] == 'Expert') {
					$DocI = substr($Doc, 0, $EndPos);
					$O = substr($DocI, 3, strpos($DocI, ';', 3)-3);
					if(isset($F) && $F <> '') {
						$DocIndex[$F]['O'][] = str_replace(array('*', '//'),"<br />",$O);
						$DocIndex[$F]['TextS'][] = '<p class="PartO"><small>Ausgabe</small><br />'.str_replace(array('*', '//'),"<br />",$O).'</p>';
					} else {
						$DocIndex['O'][] = str_replace(array('*', '//'),"<br />",$O);
						$DocIndex['TextS'][] = '<p class="PartO"><small>Ausgabe</small><br />'.str_replace(array('*', '//'),"<br />",$O).'</p>';
					}
				}
				break;
				case '@V:':
				$EndPos = strpos($Doc, ';')+1;
				if($GLOBALS['V'] == 'Expert') {
					$DocI = substr($Doc, 0, $EndPos);
					$V = substr($DocI, 3, strpos($DocI, ';', 3)-3);
					if(isset($F) && $F <> '') {
						$DocIndex[$F]['V'][] = str_replace(array('*', '//'),"<br />",$V);
						$DocIndex[$F]['TextS'][] = '<p class="PartV"><small>Variable</small><br />'.str_replace(array('*', '//'),"<br />",$V).'</p>';
					} else {
						$DocIndex['V'][] = str_replace(array('*', '//'),"<br />",$V);
						$DocIndex['TextS'][] = '<p class="PartV"><small>Variable</small><br />'.str_replace(array('*', '//'),"<br />",$V).'</p>';
					} 
				}
				break;
				case '@R:':
				$EndPos = strpos($Doc, ';')+1;
				if($GLOBALS['V'] == 'Expert') {
					$DocI = substr($Doc, 0, $EndPos);
					$R = substr($DocI, 3, strpos($DocI, ';', 3)-3);
					if(isset($F) && $F <> '') {
						$DocIndex[$F]['R'][] = str_replace(array('*', '//'),"<br />",$R);
						$DocIndex[$F]['TextS'][] = '<p class="PartR"><small>Request</small><br />'.str_replace(array('*', '//'),"<br />",$R).'</p>';
						$DocIndex['Rglobal'][] = str_replace(array('*', '//'),"<br />",$R.'<br /><span class="Sub">F: '.$F.'</span>');
					} elseif(isset($H) && $H <> '') {
						//@c:2015-07-29:Ergänzung der Headline für das RequestLog;
						$DocIndex['R'][] = str_replace(array('*', '//'),"<br />",$R);
						$DocIndex['TextS'][] = '<p class="PartR"><small>Request</small><br />'.str_replace(array('*', '//'),"<br />",$R).'</p>';
						$DocIndex['Rglobal'][] = str_replace(array('*', '//'),"<br />",$R.'<br /><span class="Sub">H: '.$H.'</span>');
					} else {
						$DocIndex['R'][] = str_replace(array('*', '//'),"<br />",$R);
						$DocIndex['TextS'][] = '<p class="PartR"><small>Request</small><br />'.str_replace(array('*', '//'),"<br />",$R).'</p>';
						$DocIndex['Rglobal'][] = str_replace(array('*', '//'),"<br />",$R);
					} 
				}
				break;					
				case '@E:':
				$EndPos = strpos($Doc, ';')+1;
				if($GLOBALS['V'] == 'Expert') {
					$DocI = substr($Doc, 0, $EndPos);
					$E = substr($DocI, 3, strpos($DocI, ';', 3)-3);
					if(isset($F) && $F <> '') {
						$DocIndex[$F]['E'][] = str_replace(array('*', '//'),"<br />",$E);
						$DocIndex[$F]['TextS'][] = '<p class="PartE">'.str_replace(array('*', '//'),"<br />",$E).'</p>';
						$DocIndex['Eglobal'][] = str_replace(array('*', '//'),"<br />",$E.'<br /><span class="Sub">F: '.$F.'</span>');
					} elseif(isset($H) && $H <> '') {
						//@c:2015-07-29:Ergänzung der Headline für das ErrorLog;
						$DocIndex['E'][] = str_replace(array('*', '//'),"<br />",$E);
						$DocIndex['TextS'][] = '<p class="PartE">'.str_replace(array('*', '//'),"<br />",$E).'</p>';
						$DocIndex['Eglobal'][] = str_replace(array('*', '//'),"<br />",$E.'<br /><span class="Sub">H: '.$H.'</span>');
					} else {
						$DocIndex['E'][] = str_replace(array('*', '//'),"<br />",$E);
						$DocIndex['TextS'][] = '<p class="PartE">'.str_replace(array('*', '//'),"<br />",$E).'</p>';
						$DocIndex['Eglobal'][] = str_replace(array('*', '//'),"<br />",$E);
					} 
				}
				break;
				case '@stop;':
					$EndPos = FindNext($Doc,array('@start;'));
				break;
			}
			if(!isset($EndPos)) {
				$EndPos = strlen($Doc);
			}
			$Doc = substr($Doc, $EndPos);
			$i++;
		} else {
			$Parse = false;
		}
	}
	return $DocIndex;
	//@start;
}
function ParseTxt ($File) {
	//@F:ParseTxt;
	//@D:Für textbasierte Quelldateien kann diese Funktion verwendet werden. Je nach Dateityp könnte aber der Quelltext vorher noch für den Parser vorbereitet werden, falls er mittels PHP noch transformiert werden müsste.;
	if(is_file($File)) {
		return ParseContent(file_get_contents($File),$File);
	}
}
function FindNext($String,$Sep) {
	//@F:FindNext;
	//@D:Findet das nächste Vorkommen eines Seperators.;
	//@I:String<br />Array mit Suchwörtern;
	//@O:Position (Integer);
	$PosS = array();
	for($i=0;$i<count($Sep);$i++) {
		$PosI = strpos($String,$Sep[$i]);
		if(is_int($PosI)) {
			$PosS[] = $PosI;
		}
	}
	if(count($PosS) > 0 ) {
		return min($PosS);
	} else {
		return false;
	}
}
function ChangeLog($ChangeLogS) {
	//@F:ChangeLog;
	//@D:Erzeugt einen ChangeLog aus einem Array mit Daten;
	//@I:Array<br />[Func]<br />[Memo]<br />[Date];
	//@O:Direktausgaben als table;
	if(count($ChangeLogS) > 0 && $ChangeLogS !== ''){
		$ChangeLogS = ab5_SortArrayByColumn($ChangeLogS, 'Date', $Dir = 'SORT_DESC');
		$CurrentDate = '';
		//@c:2016-02-15:P-Tag entfernt;
		$Return ='<table class="ChLog">';
		foreach($ChangeLogS as $ThisLog) {
			$FuncLink = '';
			//@c:2015-07-29:Im ChangeLog wird auch auf die H Bezug genommen;
			if(isset($ThisLog['Func']) && $ThisLog['Func'] !== '') {
				$FuncLink .= 'F: '.$ThisLog['Func'].' <a href="#" class="RefLink" onclick="ShowFunction(\'Function_'.$GLOBALS['FunctionRef'][$ThisLog['Func']].'\'); return false;">[+]</a> ';
			} elseif (isset($ThisLog['Head']) && $ThisLog['Head'] !== '') {
				$FuncLink .= 'H: '.$ThisLog['Head'].'';
			}
			if($GLOBALS['MultiLang']) {
				if(substr($ThisLog['Memo'],-4) == '::'.$GLOBALS['LangCurrentCode']) {
					$ThisLog['Memo'] = substr($ThisLog['Memo'],0,-4);
					if($CurrentDate == $ThisLog['Date']) {
						$Return = $Return.'<tr><td></td><td>'.$ThisLog['Memo'].'<br /><span class="Sub"><small>'.$FuncLink.'</small></span></td></tr>';
					} else {
						$Return = $Return.'<tr><td>'.$ThisLog['Date'].'</td><td>'.$ThisLog['Memo'].'<br /><span class="Sub"><small>'.$FuncLink.'</small></span></td></tr>';
					}
					$CurrentDate = $ThisLog['Date'];
				}
			} else {	
				if($CurrentDate == $ThisLog['Date']) {
					$Return = $Return.'<tr><td></td><td>'.$ThisLog['Memo'].'<br /><span class="Sub"><small>'.$FuncLink.'</small></span></td></tr>';
				} else {
					$Return = $Return.'<tr><td>'.$ThisLog['Date'].'</td><td>'.$ThisLog['Memo'].'<br /><span class="Sub"><small>'.$FuncLink.'</small></span></td></tr>';
				}
				$CurrentDate = $ThisLog['Date'];
			}
		}
		$Return =$Return.'</table>';
		return $Return;
	}
}
function GlobalChangeLog($ChangeLogS) {
	//@F:GlobalChangeLog;
	//@D:Erzeugt einen ChangeLog aus einem Array mit Daten.;
	//@I:Array<br />[Func]<br />[Memo]<br />[Date];
	//@O:Direktausgaben als table;
	if(count($ChangeLogS) > 0 && $ChangeLogS !== ''){
		$ChangeLogS = ab5_SortArrayByColumn($ChangeLogS, 'Date', $Dir = 'SORT_DESC');
		$CurrentDate = '0000-00-00';
		//@c:2016-02-15:P-Tag entfernt;
		$Return ='<table class="ChLog">';
		foreach($ChangeLogS as $ThisLog) {
			//@c:2015-07-29:Im ChangeLog wird auch auf die H Bezug genommen (inkl. Verschiebung des Linkaufbaus VOR die MultiLang-Klausel);
			$FuncLink = '';
			if(isset($ThisLog['Func']) && $ThisLog['Func'] !== '') {
				$FuncLink .= '> F: '.$ThisLog['Func'].' <a href="#" class="RefLink" onclick="ShowFunction(\'Function_'.$GLOBALS['FunctionRef'][$ThisLog['Func']].'\'); return false;">[+]</a> ';
			} elseif (isset($ThisLog['Head']) && $ThisLog['Head'] !== '') {
				$FuncLink .= '> H: '.$ThisLog['Head'].'';
			}
			if($GLOBALS['MultiLang']) {
				if(substr($ThisLog['Memo'],-4) == '::'.$GLOBALS['LangCurrentCode']) {
					$ThisLog['Memo'] = substr($ThisLog['Memo'],0,-4);
					if($CurrentDate == $ThisLog['Date']) {
						$Return = $Return.'<tr><td></td><td>'.$ThisLog['Memo'].'<br /><span class="Sub"><small>'.$ThisLog['FileKey'].' <a href="#" class="RefLink" onclick="ShowFile(\'File_'.$GLOBALS['FileRef'][$ThisLog['FilePath']].'\'); return false;">[+]</a> '.$FuncLink.'</small></span></td></tr>';
					} else {
						$Return = $Return.'<tr><td>'.$ThisLog['Date'].'</td><td>'.$ThisLog['Memo'].'<br /><span class="Sub"><small>'.$ThisLog['FileKey'].' <a href="#" class="RefLink" onclick="ShowFile(\'File_'.$GLOBALS['FileRef'][$ThisLog['FilePath']].'\'); return false;">[+]</a> '.$FuncLink.'</small></span></td></tr>';
					}
					$CurrentDate = $ThisLog['Date'];
				}
			} else {
				if($CurrentDate == $ThisLog['Date']) {
					$Return = $Return.'<tr><td></td><td>'.$ThisLog['Memo'].'<br /><span class="Sub"><small>'.$ThisLog['FileKey'].' <a href="#" class="RefLink" onclick="ShowFile(\'File_'.$GLOBALS['FileRef'][$ThisLog['FilePath']].'\'); return false;">[+]</a> '.$FuncLink.'</small></span></td></tr>';
				} else {
					$Return = $Return.'<tr><td>'.$ThisLog['Date'].'</td><td>'.$ThisLog['Memo'].'<br /><span class="Sub"><small>'.$ThisLog['FileKey'].' <a href="#" class="RefLink" onclick="ShowFile(\'File_'.$GLOBALS['FileRef'][$ThisLog['FilePath']].'\'); return false;">[+]</a> '.$FuncLink.'</small></span></td></tr>';
				}
				$CurrentDate = $ThisLog['Date'];						
			}
		}
		$Return =$Return.'</table>';
		return $Return;
	}
}
function RenderLog($LogS,$Group = 'Date',$Dir = 'SORT_DESC') {
	//@F:RenderLog;
	//@D:Erzeugt einen ChangeLog aus einem Array mit Daten.;
	//@I:Array<br />[Func]<br />[Memo]<br />[Date];
	//@O:Direktausgaben als table;	
	if(count($LogS) > 0 && $LogS !== ''){
		$LogS = ab5_SortArrayByColumn($LogS, $Group, $Dir);		
		$Current[$Group] = '';
		$Return ='';
		//<a href="#" class="RefLink" onclick="ShowFile(\'File_'.$GLOBALS['FileRef'][$ThisLog[$Group]].'\'); return false;">[+]</a>
		foreach($LogS as $ThisLog) {
			if(isset($ThisLog['FilePath']) && $ThisLog['FilePath'] !== '') {
				$RefLink = '<a href="#" class="RefLink" onclick="ShowFile(\'File_'.$GLOBALS['FileRef'][$ThisLog['FilePath']].'\'); return false;">[+]</a>';
			} else {
				$RefLink = '';
			}
			if($Current[$Group] !== $ThisLog[$Group]) {
				$Return = $Return.'<span class="HeadlineCH2">'.$ThisLog[$Group].' '.$RefLink.'</span>';
			}
			$Return = $Return.'<table class="ChLog">';
			if(count($ThisLog['DataS']) > 0 && $ThisLog['DataS'] !== ''){
				foreach ($ThisLog['DataS'] as $Data) {
					if($GLOBALS['MultiLang']) {
						if(substr($Data,-4) == '::'.$GLOBALS['LangCurrentCode']) {
							$Data = substr($Data,0,-4);
							$Return = $Return.'<tr><td>'.$Data.'</td></tr>';
						}
					} else {
						$Return = $Return.'<tr><td>'.$Data.'</td></tr>';
					}
				}			
			}
			$Return = $Return.'</table>';
			$Current[$Group] = $ThisLog[$Group];
		}
		$Return = $Return.'';
		return $Return;
	}
}
?>