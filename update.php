<?php
//@E:Still in development! Only for experimental use!;
//@A:Markus Hottenrott @ share2brain.com;
//@m:
$GLOBALS['AppVersion']['Update'] = 'v0.0.9 (2015-02-01)';
//@D:Die Aufgabe des Update-Moduls ist die automatisierte Aktualisierung aller Module direkt vom zentralen Server aus.;
//@M:Für dieses Modul bietet die set.config einige Einstellmöglichkeiten. Die Api-Schlüsselinformationen müssen eingetragen werden.;
//@c:2015-08-30:Umbenennung zu Doccen.com;
require_once('lib.php');
if(!isset($GLOBALS['SetCfg'])) {
	ab5_InitDoccen();
	session_start();
	$ListLog = $_SESSION['DoccenUpdate']['Log'];
	$ListAction = $_SESSION['DoccenUpdate']['Action'];
	
	$GLOBALS['Templ'] = str_replace('{{AddStyle}}', '#LeftContent { padding: 5px; } #RightContent { padding: 5px; }', $GLOBALS['Templ']);
	//@c:2015-11-11:Prüfung, ob es sich um eine Quelle handelt. Wenn ja, dann wird das Update verhindert. Die Prüfung erfolgt anhand der Verfügbarkeit von _pack.php Datei.;
	//@c:2016-02-01:Falsch interpretierter Zustand 'true' korrigiert.;
	if((!isset($GLOBALS['SetCfg']->UpdActivated) || $GLOBALS['SetCfg']->UpdActivated == 'true' || $GLOBALS['SetCfg']->UpdActivated == 'auto') && !is_file('_pack.php')) {
		$GLOBALS['Templ'] = str_replace('{{NavBar}}', '<span class="Headline"><a href="index.php" class="MenuLink">'.$GLOBALS['LangCurrent']->Global->Start.'</a></span><a class="MenuLink" href="?">Update (0)</a><br /><a class="MenuLink" href="?Step=get">Update (1)</a><br /><a class="MenuLink" href="?Step=runupd">Update (2)</a>', $GLOBALS['Templ']);
	
		if(!isset($_GET['Step'])) {
			$ListAction .= '+ <b>Start Update</b>'.'<br />';
		}
		if(isset($_GET['Step']) && $_GET['Step'] == 'get') {
			//@H:Laden der Ressourcen;
			$ListAction .= '+ <b>Load UpdFile</b>'.'<br />';
			if(!is_file('DoccenPkg.current.zip')) {
				$Url = $GLOBALS['SetCfg']->UpdServer;
				$ListLog .= '- UpdUrl: '.$Url.'<br />';
				$Data = array('Access'=>'doccen.update.repo','Account'=>$GLOBALS['SetCfg']->UpdAccount,'ApiKey'=>$GLOBALS['SetCfg']->UpdApiKey,'Get'=>true,'GetFile'=>$GLOBALS['SetCfg']->UpdFile);
				$ListLog .= '- UpdFile: '.$GLOBALS['SetCfg']->UpdAccount.'<br />';
				$ListLog .= '- UpdFile: '.$GLOBALS['SetCfg']->UpdApiKey.'<br />';
				$ListLog .= '- UpdFile: '.$GLOBALS['SetCfg']->UpdFile.'<br />';
				$Connect = curl_init();
				curl_setopt($Connect, CURLOPT_URL, $Url);
				if(isset($GLOBALS['SetCfg']->UpdProxy) && $GLOBALS['SetCfg']->UpdProxy == 'true' && isset($GLOBALS['SetCfg']->UpdProxyAddr) && $GLOBALS['SetCfg']->UpdProxyAddr !== '') {
					curl_setopt($Connect, CURLOPT_HTTPPROXYTUNNEL, (string) $GLOBALS['SetCfg']->UpdProxy);
					curl_setopt($Connect, CURLOPT_PROXY, (string) $GLOBALS['SetCfg']->UpdProxyAddr);
					$ListLog .= '- UpdProxy'.'<br />';
					if($GLOBALS['SetCfg']->UpdProxyAuth == 'NTLM') {
						curl_setopt($Connect, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);	
						$ListLog .= '- UpdProxy with NTLM'.'<br />';
					} elseif($GLOBALS['SetCfg']->UpdProxyAuth !== 'false') {
						curl_setopt($Connect, CURLOPT_PROXYUSERPWD, (string) $GLOBALS['SetCfg']->UpdProxyAuth);
						$ListLog .= '- UpdProxy with credentials'.'<br />';
					}
				}
				curl_setopt($Connect, CURLOPT_POST, true);
				curl_setopt($Connect, CURLOPT_POSTFIELDS, $Data);
				curl_setopt($Connect, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($Connect, CURLOPT_RETURNTRANSFER, true);
				$Return = curl_exec($Connect);
				curl_close($Connect);
				file_put_contents('DoccenPkg.current.zip', $Return);
				$ListLog .= '- UpdFile Length: '.strlen($Return).'<br />';
			}
			$ListAction .= '+ <b>Extract UpdFile</b>'.'<br />';
			$Zip = new ZipArchive;
			$Zip->open('DoccenPkg.current.zip');
			$Zip->extractTo(getcwd());
			$Zip->close();
			$ListLog .= '- Zip extracted'.'<br />';
			unlink('DoccenPkg.current.zip');
			$ListLog .= '- TempFile deleted'.'<br />';
			$_SESSION['DoccenUpdate']['Log'] = $ListLog;
			$_SESSION['DoccenUpdate']['Action'] = $ListAction;
			//header('Location: '.$_SERVER['[SCRIPT_NAME]'].'?Step=runupd');
		}
		if(isset($_GET['Step']) && $_GET['Step'] == 'runupd') {
			//@H:Abarbeiten update.config;
			//@E:Vor dem Abarbeiten der update.config sollte die update.php neu geladen werden;
			$ListAction .= '+ <b>Load set.config</b>'.'<br />';
			$UpdCfg = json_decode(file_get_contents('update.config'));
			$ListLog .= '- UpdFile: '.$GLOBALS['SetCfg']->UpdFile.'<br />';
			if($GLOBALS['SetCfg']->UpdFile == 'latest') {
				$UpdCfg = $UpdCfg->update->latest;
			} elseif ($GLOBALS['SetCfg']->UpdFile == 'stable') {
				$UpdCfg = $UpdCfg->update->stable;
			} else {
				$UpdCfg = $UpdCfg->ActionS = '';
			}
			$SetCfg = json_decode(file_get_contents('set.config'));
			foreach ($UpdCfg->ActionS as $Action) {
				switch($Action->Do) {
					case 'MkFile':
						$Name = (string) $Action->Name;
						$ListLog .= '- MkFile '.$Name.'<br />';
						if(!is_file($Name)) {
							file_put_contents($Name);
						}
						break;
					case 'MkFolder':
						$Name = (string) $Action->Name;
						$ListLog .= '- MkFolder '.$Name.'<br />';
						//echo $Name;
						if(!is_dir($Name)) {
							mkdir($Name);
						}
						break;
					case 'AddField_Set':
						$Key = (string) $Action->Key;
						$ListLog .= '- AddField_Set '.$Key.' > '.(string) $Action->Value.'<br />';
						if(!isset($SetCfg->$Key)) {
							$ListLog .= '- AddField_Set '.$Key.' > '.(string) $Action->Value.'<br />';
							$SetCfg->$Key = (string) $Action->Value;	
						}
						break;
					case 'SetField_Set':
						$Key = (string) $Action->Key;
						$ListLog .= '- SetField_Set '.$Key.' > '.(string) $Action->Value.'<br />';
						$SetCfg->$Key = (string) $Action->Value;
						break;
				}
			}
			$ListLog .= '= Schreibe set.config'.'<br />';
			file_put_contents('set.config', json_encode($SetCfg, JSON_PRETTY_PRINT));
			$ListAction .= '+ <b>Finished Update</b>'.'<br />';
		}
	} else {
		$ListLog .= 'Kein Update möglich!';
		$GLOBALS['Templ'] = str_replace('{{NavBar}}', '<span class="Headline"><a href="index.php" class="MenuLink">'.$GLOBALS['LangCurrent']->Global->Start.'</a></span>', $GLOBALS['Templ']);
	}
	$GLOBALS['Templ'] = str_replace('{{LeftContent}}', '<span class="Headline">Aktionen</span>'.$ListAction, $GLOBALS['Templ']);
	$GLOBALS['Templ'] = str_replace('{{RightContent}}', '<span class="Headline">Protokoll</span>'.$ListLog, $GLOBALS['Templ']);
	echo $GLOBALS['Templ'];
	$_SESSION['DoccenUpdate']['Log'] = '';
	$_SESSION['DoccenUpdate']['Action'] = '';
}
?>