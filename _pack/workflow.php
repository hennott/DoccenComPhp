<?php
//@E:Still in development! Only for experimental use!;
//@A:Markus Hottenrott @ share2brain.com;
//@m:
$GLOBALS['AppVersion']['Workflow'] = 'v0.0.5 (2015-10-27)';
//@D:Das Modul Workflow soll den Ablauf aller Module in ein Zusammenspiel bringen und so den Ablauf beschleunigen. Es ist also das Ziel, dass man einen Ablauf/Workflow definiert, der dann Composer, Grabber, AutoDoc samt Export automatisiert. Auf diese Weise kann per Klick oder TimerJob immer wieder eine aktuelle Version ausgegeben werden.;
//@c:2015-08-30:Umbenennung zu Doccen.com;
require_once('lib.php');
ab5_InitDoccen();
$GLOBALS['TemplWf'] = $GLOBALS['Templ'];
$GLOBALS['LangCurrentWf'] = $GLOBALS['LangCurrent'];
$GLOBALS['TemplWf'] = str_replace('{{NavBar}}', '<span class="Headline"><a href="index.php" class="MenuLink">'.$GLOBALS['LangCurrentWf']->Global->Start.'</a></span><a class="MenuLink" href="?Mode=AutoDoc">'.$GLOBALS['LangCurrent']->Workflow->AutoDoc.'</a><br /><br /><a class="MenuLink" href="?Mode=Run">'.$GLOBALS['LangCurrentWf']->Workflow->Run.'</a>', $GLOBALS['TemplWf']);
if(!isset($_GET['Mode'])) { $_GET['Mode'] = 'Start'; }
switch($_GET['Mode']) {
	case 'Start':
		//@H:Modus "Start";
		//@D:Der Start-Modus lädt lediglich eine leere Einstiegsmaske.;
		$GLOBALS['TemplWf'] = str_replace('{{LeftContent}}', '<span class="Headline">'.$GLOBALS['LangCurrentWf']->Workflow->Action.'</span>', $GLOBALS['TemplWf']);
		$GLOBALS['TemplWf'] = str_replace('{{RightContent}}', '<span class="Headline">'.$GLOBALS['LangCurrentWf']->Workflow->Log.'</span>', $GLOBALS['TemplWf']);
		break;
	case 'AutoDoc':
		//@H:Modus "AutoDoc";
		//@D:In diesem Modus kann man Dokumentationen auswählen, die dann beim Workflow automatisch exportiert werden.;
		$Cfg = json_decode(file_get_contents('my.config'));
		$List = '<form method="POST">';
		foreach ($GLOBALS['DocS'] as $Doc) {
			//@c:2015-08-05:Es werden leere Objektbäume erzeugt, damit dann ohne Fehlermeldung ein initialer Anlegeprozess stattfinden kann.;
			if(!isset($Cfg->Workflow) || !isset($Cfg->Workflow->AutoDocRun)) {
				$Cfg->Workflow = new stdClass();
				$Cfg->Workflow->AutoDocRun = new stdClass();
			}
			if(isset($_POST['Save']) && isset($_POST['DocS'][$Doc]) && $_POST['DocS'][$Doc] == 'on') {
				$Cfg->Workflow->AutoDocRun->$Doc = true;
			} elseif(isset($_POST['Save']) && isset($Cfg->Workflow->AutoDocRun->$Doc) && $Cfg->Workflow->AutoDocRun->$Doc == true) {
				unset($Cfg->Workflow->AutoDocRun->$Doc);
			}
			if(isset($Cfg->Workflow->AutoDocRun->$Doc) && $Cfg->Workflow->AutoDocRun->$Doc == true) {
				$Selected = 'checked="true"';
			} else {
				$Selected = '';
			}
			$List .= '<input type="checkbox" '.$Selected.' name="DocS['.$Doc.']"></input>'.$Doc.'<br />';
		}
		$List .= '<button name="Save" value="Save">'.$GLOBALS['LangCurrentWf']->Workflow->Save.'</button></form>';
		if(isset($_POST['Save'])) {
			$Cfg->update = date('Y-m-d H:i',time());
			//@c:Prettyfy der JSON-Ausgabe für die my.config.;
			file_put_contents('my.config', json_encode($Cfg, JSON_PRETTY_PRINT));
		}
		$GLOBALS['TemplWf'] = str_replace('{{LeftContent}}', '<span class="Headline">'.$GLOBALS['LangCurrentWf']->Workflow->AutoDoc.'</span>', $GLOBALS['TemplWf']);
		$GLOBALS['TemplWf'] = str_replace('{{RightContent}}', '<span class="Headline">'.$GLOBALS['LangCurrentWf']->Workflow->Selection.'</span>'.$List, $GLOBALS['TemplWf']);
		break;
	case 'Run':
		//@H:Modus "Run";
		//@D:In diesem Modus kann man einen Workflow ausführen.;
		$Action = 'Run Workflow<br />';
		//@c:2015-08-04:Pack wird nur durchgeführt, wenn das Zusatzmodul existiert;
		if(is_file('_pack.php')) {
			$Action .= 'packRun ( all )<br />';
			require_once('_pack.php');
			$Pack = packRun();
		} else {
			//@c:2015-08-06:Wenn kein Package-Modul vorhanden ist, dann wurde ein Fehler ausgegeben, weil der Log nicht definiert wurde.;
			$Pack['Log'] = 'NoPack';
		}
		$Action .= 'ComposerRun ( all )<br />';
		require_once('composer.php');
		$Composer = ComposerRun('all');
		$Action .= 'GrabberRun ( all )<br />';
		require_once('grabber.php');
		$Grabber = GrabberRun('all');
		$Cfg = json_decode(file_get_contents('my.config'));
		if(isset($Cfg->Workflow->AutoDocRun) && count($Cfg->Workflow->AutoDocRun) > 0) {
			foreach($Cfg->Workflow->AutoDocRun as $Doc=>$State) {
				if($State == true) {
					$AutoDocS[] = $Doc;	
				}
			}
			$Action .= 'AutoDocRun ( some )<br />';
			require_once('autodoc.php');
			if(isset($AutoDocS) && count($AutoDocS) > 0) {
				$AutoDoc = AutoDocRun($AutoDocS);	
			} else {
				$AutoDoc['Log'] = '+ <b>No</b> Export';
			}
		} else {
			$Action .= 'AutoDocRun ( all )<br />';
			require_once('autodoc.php');
			$AutoDoc = AutoDocRun('all');
		}
		$GLOBALS['TemplWf'] = str_replace('{{LeftContent}}', '<span class="Headline">'.$GLOBALS['LangCurrentWf']->Workflow->Action.'</span>'.$Action, $GLOBALS['TemplWf']);
		$GLOBALS['TemplWf'] = str_replace('{{RightContent}}', '<span class="Headline">'.$GLOBALS['LangCurrentWf']->Workflow->Log.'</span>'.'<h3>Pack</h3>'.$Pack['Log'].'<h3>Composer</h3>'.$Composer['Log'].'<h3>Grabber</h3>'.$Grabber['RemoveList'].$Grabber['CopyList'].'<h3>AutoDoc</h3>'.$AutoDoc['Log'].'<h3>Finished</h3>', $GLOBALS['TemplWf']);
		break;
	default:
} 
$GLOBALS['TemplWf'] = str_replace('{{AddStyle}}', '#LeftContent { padding: 5px; } #RightContent { padding: 5px; }', $GLOBALS['TemplWf']);
echo $GLOBALS['TemplWf'];
?>