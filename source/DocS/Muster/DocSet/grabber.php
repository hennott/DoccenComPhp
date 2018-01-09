<?php
//@E:Still in development! Only for experimental use!;
//@A:Markus Hottenrott @ share2brain.com;
//@m:
$GLOBALS['AppVersion']['Grabber'] = 'v0.1.4 (2015-11-11)';
//@c:2015-02-18:Umbau auf einheitliches Layout und lib als Initialisierung.;
//@c:2015-07-29:Neustrukturierung, um die Komponente im Workflow einbinden zu können;
//@c:2015-08-11:Json wird in PRETTY_PRINT ausgegeben.;
//@D:Das Grabber-Modul kopiert entsprechend der Konfiguration Dateien aus dem SourceS-Ordner in die entsprechenden Order der Dokumentation. Das ist dann nötig, wenn Sie mittels Skript alle Dateien dort hinkopieren und daraus verschiedene Dokumentationen ableiten. Dies entspricht dem zweiten Schritt im Workflow-Modul.;
//@M:Dieses Modul kann auch durch externe Tools ersetzt werden, indem man einen SyncJob entsprechend konfiguriert.;
//@c:2015-08-30:Umbenennung zu Doccen.com;
require_once('lib.php');
if(!isset($GLOBALS['SetCfg'])) {
	ab5_InitDoccen();
	$GLOBALS['Templ'] = str_replace('{{NavBar}}', '<span class="Headline"><a href="index.php" class="MenuLink">'.$GLOBALS['LangCurrent']->Global->Start.'</a></span><a class="MenuLink" href="?Mode=Add">'.$GLOBALS['LangCurrent']->Grabber->Add.'</a><br /><a class="MenuLink" href="?Mode=Remove">'.$GLOBALS['LangCurrent']->Grabber->Remove.'</a><br /><br /><a class="MenuLink" href="?Run=all">'.$GLOBALS['LangCurrent']->Grabber->Run.'</a>', $GLOBALS['Templ']);
	if(!isset($_GET['Mode'])) { $_GET['Mode'] = 'Add'; }
	if(isset($_GET['Run'])) { $_GET['Mode'] = 'Run'; }
	switch($_GET['Mode']) {
		case 'Add':
			//@H:Modus "Add";
			//@D:In diesem Modus kann man Fragmente (PartS) einer Zieldatei zuordnen, die im SourceS Verzeichnis liegt.;
			$SourceFileS = ab5_CrawleDir(array('Path'=>$GLOBALS['SetCfg']->PATH_Sources));
			$SourceFileList = '';
			//@c:2015-11-11:Anpassung der Prüfung für leere Ergebnisse.;
			if(isset($SourceFileS['Result']) && count($SourceFileS['Result']) > 0) {
				$SourceFileRelPathAct = '';
				foreach($SourceFileS['Result'] as $SourceFile) {
					$SourceFileRel = substr($SourceFile, strlen($GLOBALS['SetCfg']->PATH_Sources)+1);
					$SourceFileRelExtS = explode('.', $SourceFileRel);
					$SourceFileRelExt = strtoupper(end($SourceFileRelExtS));
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
			$GLOBALS['Templ'] = str_replace('{{LeftContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Grabber->Source.' <a href="#" style="font-size: 0.5em;" onclick="ShowByClass(\'SourceFilePath\'); return false;">[+]</a></span>'.$SourceFileList, $GLOBALS['Templ']);
			$TargetFileList = '';
			if(isset($GLOBALS['DocS']) && count($GLOBALS['DocS']) > 0) {
				foreach ($GLOBALS['DocS']  as $TargetDir) {
					$TargetFileList .= '<h2>'.substr($TargetDir, strlen($GLOBALS['SetCfg']->PATH_DocS)+1).'</h2><button type="submit" value="'.$TargetDir.DIRECTORY_SEPARATOR.$GLOBALS['SetCfg']->PATH_DocSet.'" name="Target">'.$GLOBALS['SetCfg']->PATH_DocSet.'</button>'.'<button type="submit" value="'.$TargetDir.DIRECTORY_SEPARATOR.$GLOBALS['SetCfg']->PATH_DocAdd.'" name="Target"/>'.$GLOBALS['SetCfg']->PATH_DocAdd.'</button><br /><br />';	
				}
			}
			$GLOBALS['Templ'] = str_replace('{{RightContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Grabber->Target.'</span>'.$TargetFileList, $GLOBALS['Templ']);
			break;
		case 'Remove':
			//@H:Modus "Remove";
			//@D:Im Remove-Modus werden die Dateien NICHT gelöscht, sondern deren Zuordnung zu einer Zieldatei wird aufgehoben.;
			$TargetFileList = '';
			if(isset($GLOBALS['DocS']) && count($GLOBALS['DocS']) > 0) {
				foreach ($GLOBALS['DocS']  as $TargetDir) {
					$TargetFileList .= $TargetDir.'<br /><button type="submit" value="'.$TargetDir.DIRECTORY_SEPARATOR.$GLOBALS['SetCfg']->PATH_DocSet.'" name="Index">'.$GLOBALS['SetCfg']->PATH_DocSet.'</button>'.'<button type="submit" value="'.$TargetDir.DIRECTORY_SEPARATOR.$GLOBALS['SetCfg']->PATH_DocAdd.'" name="Index"/>'.$GLOBALS['SetCfg']->PATH_DocAdd.'</button><br /><br />';	
				}
			}
			$GLOBALS['Templ'] = str_replace('{{LeftContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Grabber->Target.'</span>'.$TargetFileList, $GLOBALS['Templ']);
			if(isset($_POST['RemoveFile'])) {
				$Cfg = json_decode(file_get_contents('my.config'));
				$RemoveFile = explode('|', $_POST['RemoveFile']);
				$SourceFile = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR, $RemoveFile[1]);
				$_POST['Index'] = $RemoveFile[0];
				$Target = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR, $RemoveFile[0]);
				unset($Cfg->CopyS->$Target->$SourceFile);
				file_put_contents('my.config', json_encode($Cfg, JSON_PRETTY_PRINT));
			}		
			if(isset($_POST['Index'])) {
				//$SourceFileS = ab5_CrawleDir(array('Path'=>$_POST['Index']));
				$SourceFileList = '';
				$Cfg = json_decode(file_get_contents('my.config'));
				$_POST['Index2'] = str_replace('\\\\','\\', $_POST['Index']);
				//@c:2015-07-29:Fehler bei Leeren Dokumentation wird jetzt abgefangen;
				if(isset($Cfg->CopyS->$_POST['Index2']) && count($Cfg->CopyS->$_POST['Index2']) > 0) {
					foreach($Cfg->CopyS->$_POST['Index2'] as $SourceFile=>$State) {
						$SourceFileRel = substr($SourceFile, 8);
						//$SourceFileExt = 'Sources\\'.$SourceFileRel;
						if($State == 1) {
							$SourceFileList .= '<button name="RemoveFile" value="'.$_POST['Index2'].'|'.$SourceFile.'" />X</button> '.$SourceFileRel.'<br />';
						}
					}
				}
				$GLOBALS['Templ'] = str_replace('{{RightContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Grabber->Source.'</span>'.$SourceFileList, $GLOBALS['Templ']);
			} else {
				$GLOBALS['Templ'] = str_replace('{{RightContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Grabber->Source.'</span>', $GLOBALS['Templ']);
			}
			break;
		case 'Run':
			//@H:Modus "Run";
			//@D:Der Modus Run führt alle Vorgänge für das Zusammenführen aus.;
			break;
		default:
	} 
	if(isset($_POST['Target'])) {
		$Cfg = json_decode(file_get_contents('my.config'));
		$Cfg->update = date('Y-m-d H:i',time());
		$Target = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $_POST['Target']);
		if(isset($_POST['SourceS']) && count($_POST['SourceS']) > 0) {
			foreach($_POST['SourceS'] as $SourceFile=>$State) {
				$SourceFile = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $SourceFile);
				$SourceFileRel = substr($SourceFile, strlen($GLOBALS['SetCfg']->PATH_Sources)+1);
				if(!isset($Cfg->CopyS)) {
					$Cfg->CopyS = new stdClass();	
				}
				if(!isset($Cfg->CopyS->$Target)) {
					$Cfg->CopyS->$Target = new stdClass();	
				}
				if(!isset($Cfg->CopyS->$Target->$SourceFile)) {
					$Cfg->CopyS->$Target->$SourceFile = new stdClass();	
				}
				$Cfg->CopyS->$Target->$SourceFile = true;
			}
		}
		file_put_contents('my.config', json_encode($Cfg, JSON_PRETTY_PRINT));
	}
	if(isset($_GET['Run'])) {
		$Result = GrabberRun($_GET['Run']);
		$GLOBALS['Templ'] = str_replace('{{RightContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Grabber->RemoveS.'</span>'.$Result['RemoveList'], $GLOBALS['Templ']);
		$GLOBALS['Templ'] = str_replace('{{LeftContent}}', '<span class="Headline">'.$GLOBALS['LangCurrent']->Grabber->CopyS.'</span>'.$Result['CopyList'], $GLOBALS['Templ']);
	}
	$GLOBALS['Templ'] = str_replace('{{AddStyle}}', '#Col2 { width: calc(50% - 50px); } #Col3 { width: calc(50% - 50px); } #LeftContent { padding: 5px; } #RightContent { padding: 5px; }', $GLOBALS['Templ']);
	echo $GLOBALS['Templ'];
}
function GrabberRun($Run) {
	//@F:GrabberRun;
	//@D:Diese Funktion führt die eigentlichen Dateioperationen aus.;
	//@c:2015-07-29:Auslagerung der "Run" Funktion, für die Verwendung im Workflow;	
	$Cfg = json_decode(file_get_contents('my.config'));
	$RemoveList = $CopyList = '';
	if($Run == '') { $Run = 'all'; }
	if($Run == 'all') {
		if(isset($Cfg->CopyS) && count($Cfg->CopyS) > 0) {
			foreach($Cfg->CopyS as $Target=>$FileS){
				if(isset($Cfg->CopyS->$Target) && count($Cfg->CopyS->$Target) > 0) {
					foreach($Cfg->CopyS->$Target as $File=>$State) {
						if($State) {
							$FileSource = $File;
							$FileTargetS = explode(DIRECTORY_SEPARATOR, substr($File,strlen($GLOBALS['SetCfg']->PATH_Sources)));
							$FileTarget = $Target.DIRECTORY_SEPARATOR.end($FileTargetS);
							//@c:2015-02-03:Legt automatisch die Unterverzeichnisse an;
							$TargetPath = substr($FileTarget,0,strrpos($FileTarget, DIRECTORY_SEPARATOR) );
							if(!is_dir($TargetPath)) {
								mkdir($TargetPath);
							}
							file_put_contents($FileTarget, file_get_contents($FileSource));
							$CopyList .= '+ <b>Copy:</b> <small>'.$FileSource.'</small><br />&nbsp;&nbsp;> '.$FileTarget.'<br />';
						} else {
							unlink($FileTarget);
							$RemoveList .= '- <b>Remove:</b> '.$FileTarget.'<br />';
						}
					}
				}
			}
		}
	} else {
	}
	$Result['RemoveList'] = $RemoveList;
	$Result['CopyList'] = $CopyList;
	return $Result;
}
?>