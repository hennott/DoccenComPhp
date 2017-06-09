<?php
//@E:Still in development! Only for experimental use!;
//@A:Markus Hottenrott @ share2brain.com;
//@m:
$GLOBALS['AppVersion']['Composer'] = 'v0.0.7 (2015-11-11)';
//@c:2015-02-18:Umbau auf einheitliches Layout und lib als Initialisierung.;
//@c:2015-07-29:Neustrukturierung, um die Komponente im Workflow einbinden zu können;
//@D:Die Aufgabe des Composer´s ist das Zusammenfügen von Softwareteilen zu einem Paket. Dabei werden einfach Fragmente zusammengesetzt, ohne jede "intelligente" Behandlung. Im Rahmen des Workflow-Moduls steht der Composer an erster Stelle.;
//@M:Das Modul ist optional und bietet sich nur an, wenn sie den Code frakturiert speichern.;
//@c:2015-08-30:Umbenennung zu Doccen.com;
require_once('lib.php');
if(!isset($GLOBALS['SetCfg'])) {
	ab5_InitDoccen();
	//@E:Sortierung der Dateilisten ist nicht korrekt.Es wird nur innerhalb eines Ordners sortiert, aber nicht über die Ordner hinweg.;
	$GLOBALS['Templ'] = str_replace('{{NavBar}}', '<span class="Headline"><a href="index.php" class="MenuLink">'.$GLOBALS['LangCurrent']->Global->Start.'</a></span><a class="MenuLink" href="?Mode=Add">'.$GLOBALS['LangCurrent']->Composer->Add.'</a><br /><a class="MenuLink" href="?Mode=Remove">'.$GLOBALS['LangCurrent']->Composer->Remove.'</a><br /><br /><a class="MenuLink" href="?Run=all">'.$GLOBALS['LangCurrent']->Composer->Run.'</a>', $GLOBALS['Templ']);
	if(!isset($_GET['Mode'])) { $_GET['Mode'] = 'Add'; }
	if(isset($_GET['Run'])) { $_GET['Mode'] = 'Run'; }
	$FileExt = array('JS','CSS','HTML','PHP');
	switch($_GET['Mode']) {
		case 'Add':
			//@H:Modus "Add";
			//@D:In diesem Modus kann man Quelldateien einer Zieldatei zuordnen.;
			$SourceFileS = ab5_CrawleDir(array('Path'=>$SetCfg->PATH_Parts));
			//@c:2015-11-11:Anpassung des Verhaltens bei leere Ergebnissen.;
			$SourceFileS['Result'] = ab5_SortArrayByColumn($SourceFileS['Result']);
			$SourceFileList = '';
			if(isset($SourceFileS['Result']) && count($SourceFileS['Result']) > 0) {
				$SourceFileRelPathAct = '';
				foreach($SourceFileS['Result'] as $SourceFile) {
					$SourceFileRel = substr($SourceFile, strlen($SetCfg->PATH_Parts)+1);
					$SourceFileRelExtS = explode('.', $SourceFileRel);
					$SourceFileRelExt = strtoupper(end($SourceFileRelExtS));
					if(in_array($SourceFileRelExt,$FileExt)) {
						$SourceFileRelS = explode(DIRECTORY_SEPARATOR, $SourceFileRel);
						$SourceFileRelName = end($SourceFileRelS);
						$SourceFileRelPath = substr($SourceFileRel,0,strrpos($SourceFileRel, DIRECTORY_SEPARATOR));
						if($SourceFileRelPath !== $SourceFileRelPathAct) {
							$SourceFileList .= '<span class="SourceFilePath" style="display: none;"><h3>'.$SourceFileRelPath.DIRECTORY_SEPARATOR.'</h3></span>';
							$SourceFileRelPathAct = $SourceFileRelPath;
						}
						$SourceFileList .= '<input type="checkbox" name="SourceS['.$SourceFile.']" />'.$SourceFileRelName.'<br />';
					}
				}
			}
			$GLOBALS['Templ'] = str_replace('{{LeftContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Composer->Parts.' <a href="#" style="font-size: 0.5em;" onclick="ShowByClass(\'SourceFilePath\'); return false;">[+]</a></span>'.$SourceFileList.'', $GLOBALS['Templ']);
			$TargetFileS = ab5_CrawleDir(array('Path'=>$SetCfg->PATH_Sources));
			$TargetFileList = '';
			if(isset($TargetFileS['Result']) && count($TargetFileS['Result'])>0) {
				$TargetFileRelPathAct = '';
				foreach ($TargetFileS['Result']  as $TargetFile) {
					$TargetFileRel = substr($TargetFile, strlen($SetCfg->PATH_Sources)+1);
					$TargetFileRelExtS = explode('.', $TargetFileRel);
					$TargetFileRelExt = strtoupper(end($TargetFileRelExtS));
					if(in_array($TargetFileRelExt,$FileExt)) {
						$TargetFileRelS = explode(DIRECTORY_SEPARATOR, $TargetFileRel);
						$TargetFileRelName = end($TargetFileRelS);
						$TargetFileRelPath = substr($TargetFileRel,0,strrpos($TargetFileRel, DIRECTORY_SEPARATOR));
						if($TargetFileRelPath !== $TargetFileRelPathAct) {
							$TargetFileList .= '<span class="TargetFilePath" style="display: none;"><h3>'.$TargetFileRelPath.DIRECTORY_SEPARATOR.'</h3></span>';
							$TargetFileRelPathAct = $TargetFileRelPath;
						}				
						$TargetFileList .= '<button type="submit" value="'.$TargetFile.'" name="Target"/>+</button>'.$TargetFileRelName.'<br />';	
					}
				}
			}
			$GLOBALS['Templ'] = str_replace('{{RightContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Composer->Target.' <a href="#" style="font-size: 0.5em;" onclick="ShowByClass(\'TargetFilePath\'); return false;">[+]</a></span>'.$TargetFileList, $GLOBALS['Templ']);
			break;
		case 'Remove':
			//@H:Modus "Remove";
			//@D:In diesem Modus kann man die Verbindung von einer Quelldatei zu einer Zieldatei aufheben.;
			$TargetFileS = ab5_CrawleDir(array('Path'=>$SetCfg->PATH_Sources));
			$TargetFileS['Result'] = ab5_SortArrayByColumn($TargetFileS['Result']);
			$TargetFileList = '';
			if(isset($TargetFileS['Result']) && count($TargetFileS['Result'])>0) {
				$TargetFileRelPathAct = '';
				foreach ($TargetFileS['Result']  as $TargetFile) {
					$TargetFileRel = substr($TargetFile, strlen($SetCfg->PATH_Sources)+1);
					$TargetFileRelS = explode(DIRECTORY_SEPARATOR, $TargetFileRel);
					$TargetFileRelName = end($TargetFileRelS);
					$TargetFileRelPath = substr($TargetFileRel,0,strrpos($TargetFileRel, DIRECTORY_SEPARATOR));
					if($TargetFileRelPath !== $TargetFileRelPathAct) {
						$TargetFileList .= '<span class="TargetFilePath" style="display: none;"><h3>'.$TargetFileRelPath.DIRECTORY_SEPARATOR.'</h3></span>';
						$TargetFileRelPathAct = $TargetFileRelPath;
					}				
					$TargetFileList .= '<button type="submit" value="'.$TargetFile.'" name="Index"/>+</button>'.$TargetFileRelName.'<br />';		
				}
			}
			$GLOBALS['Templ'] = str_replace('{{LeftContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Composer->Target.' <a href="#" style="font-size: 0.5em;" onclick="ShowByClass(\'TargetFilePath\'); return false;">[+]</a></span>'.$TargetFileList, $GLOBALS['Templ']);
			if(isset($_POST['RemoveFile'])) {
				$Cfg = json_decode(file_get_contents('my.config'));
				$RemoveFile = explode('|', $_POST['RemoveFile']);
				$SourceFile = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR, $RemoveFile[1]);
				$_POST['Index'] = $RemoveFile[0];
				$Target = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR, $RemoveFile[0]);
				unset($Cfg->ComposeS->$Target->$SourceFile);
				//@c:2015-10-27:Ausgabe in my.config jetzt mit prettyfy.;
				file_put_contents('my.config', json_encode($Cfg, JSON_PRETTY_PRINT));
			}		
			if(isset($_POST['Index'])) {
				$SourceFileList = '';
				$Cfg = json_decode(file_get_contents('my.config'));
				$_POST['Index2'] = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR, $_POST['Index']);
				//@c:2015-07-29:Fehler bei nicht zusammengesetzten Dateien wird abgefangen.;
				if(isset($Cfg->ComposeS->$_POST['Index2']) && count($Cfg->ComposeS->$_POST['Index2'])>0) {
					foreach($Cfg->ComposeS->$_POST['Index2'] as $SourceFile=>$State) {
						$SourceFileRel = substr($SourceFile, 8);
						if($State == 1) {
							$SourceFileList .= '<button name="RemoveFile" value="'.$_POST['Index2'].'|'.$SourceFile.'" />X</button> '.$SourceFileRel.'<br />';
						}
					}
				}
				$GLOBALS['Templ'] = str_replace('{{RightContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Composer->Parts.'</span>'.$SourceFileList, $GLOBALS['Templ']);
			} else {
				$GLOBALS['Templ'] = str_replace('{{RightContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Composer->Parts.'</span>', $GLOBALS['Templ']);
			}
			break;
		case 'Run':
			//@H:Modus "Run";
			//@D:Dieser Modus führt die Befehle aus und fügt die Dateien zusammen.;
			break;
		default:
	} 
	if(isset($_POST['Target'])) {
		$Cfg = json_decode(file_get_contents('my.config'));
		$Cfg->update = date('Y-m-d H:i',time());
		$Target = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $_POST['Target']);
		if(isset($_POST['SourceS']) && count($_POST['SourceS'])>0) {
			foreach($_POST['SourceS'] as $SourceFile=>$State) {
				$SourceFile = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $SourceFile);
				$SourceFileRel = substr($SourceFile, strlen($SetCfg->PATH_Sources)+1);
				$Cfg->ComposeS->$Target->$SourceFile = true;
			}
		}
		file_put_contents('my.config', json_encode($Cfg, JSON_PRETTY_PRINT));
	}
	if(isset($_GET['Run'])) {
		$Result = ComposerRun($_GET['Run']);
		$GLOBALS['Templ'] = str_replace('{{RightContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Composer->Log.'</span>'.$Result['Log'], $GLOBALS['Templ']);
		$GLOBALS['Templ'] = str_replace('{{LeftContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Composer->Log.'</span>', $GLOBALS['Templ']);
	}
	$GLOBALS['Templ'] = str_replace('{{AddStyle}}', '#Col2 { width: calc(50% - 50px); } #Col3 { width: calc(50% - 50px); } #LeftContent { padding: 5px; } #RightContent { padding: 5px; }', $GLOBALS['Templ']);
	echo $GLOBALS['Templ'];
}
function ComposerRun($Run) {
	//@F:ComposerRun;
	//@D:Diese Funktion übernimmt das eigentliche zusammensetzen der Dateien.;
	//@c:2015-07-29:Auslagerung der "Run" Funktion, für die Verwendung im Workflow;
	if($Run == '') { $Run = 'all'; }
	//@c:2015-07-29:Reporting der durchgeführten Aktionen ergänzt;
	$Log = '';
	$Cfg = json_decode(file_get_contents('my.config'));
	if($Run == 'all') {
		if(isset($Cfg->ComposeS) && count($Cfg->ComposeS)>0) {
			foreach($Cfg->ComposeS as $Target=>$FileS){
				$NewFileContent = '';
				$Log .= '= <b>Compose:</b> '.$Target.'<br />';
				if(isset($Cfg->ComposeS->$Target) && count($Cfg->ComposeS->$Target)>0) {
					foreach($Cfg->ComposeS->$Target as $File=>$State) {
						if($State) {
							$FileSource = $File;
							$Log .= '&nbsp;&nbsp;+ Source: <small>'.$FileSource.'</small><br />';
							$NewFileContent .= file_get_contents($FileSource);
						}			
					}
				}
				file_put_contents('SourceS/composertest.js', $NewFileContent);
			}
		}
	} else {
	}
	$Result['Log'] = $Log;
	return $Result;
}
?>