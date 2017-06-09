<?php
//@E:Still in development! Only for experimental use!;
//@A:Markus Hottenrott @ share2brain.com;
//@m:
$GLOBALS['AppVersion']['Lib'] = 'v0.1.3 (2015-08-05)';
//@D:Alle Module haben Gemeinsamkeiten, welche in einer Lib abgelegt sind. Diese Lib wird am Anfang eingebunden und es wird eine Initialisierungsfunktion geladen.;
function ab5_InitAutoDoc() {
	//@F:ab5_InitAutoDoc;
	//@V:$GLOBALS['SetCfg'] < set.config;
	$GLOBALS['SetCfg'] = json_decode(file_get_contents('set.config'));
	//@R:Ergebnis sollte responsive sein, auch wenn man technische Dokumentationen nicht auf dem Handy sinnvoll verwenden kann.;
	//@R:Sprachsteuerung mit Sprachversionen;
	//@R:Verfügbare Updates von AutoDoc anzeigen inkl. automatisches Update der Applikation;
	//@V:$GLOBALS['LangS'];
	$GLOBALS['LangS'] = explode(',', $GLOBALS['SetCfg']->DOC_Lang);
	//@M:Es werden alle Sprachen geladen, die als verfügbar konfiguriert wurden.;
	//@M:Wenn eine Sprache konfigurierte wurde, jedoch keine Sprachdatei hinterlegt ist, wird alternativ englisch verwendet.;
	foreach ($GLOBALS['LangS'] as $LangCode) {
		//@V:$GLOBALS['Locale'];
		if(is_file($LangCode.'.lang')) {
			$GLOBALS['Locale'][$LangCode] = json_decode(file_get_contents($LangCode.'.lang'));
			//@D:Es kann eine eigene Sprachdatei erstellt werden, die die ursprüngliche ergänzt. Dabei müssen nur die Abweichungen enthalten sein.<br />Dafür reicht es, wenn man eine JSON-Datei entsprechend der "en.lang" anlegt. Der neue Dateiname lautet dann "my.en.lang".;
			//@c:2015-02-20:Es werden individuelle Sprachvarianten unterstützt, die sich in einer my.en.lang Datei entsprechend befinden müssen.;
			if(is_file('my.'.$LangCode.'.lang')) {
				$GLOBALS['Locale'][$LangCode] = json_decode(json_encode(array_replace_recursive(json_decode(file_get_contents($LangCode.'.lang'),true), json_decode(file_get_contents('my.'.$LangCode.'.lang'),true))),false);
			}
		} else {
			$GLOBALS['Locale'][$LangCode] = json_decode(file_get_contents('en.lang'));
			if(is_file('my.en.lang')) {
				$GLOBALS['Locale'][$LangCode] = json_decode(json_encode(array_replace_recursive(json_decode(file_get_contents('en.lang'),true), json_decode(file_get_contents('my.en.lang'),true))),false);
			}
		}
	}
	//@m:Für besonders umfangreiche Projekte sollte die max_execution_time angepasst werden;
	ini_set('max_execution_time', 1440);
	//@c:2015-08-05:Falsche Variable wurde verwendet und der Fehler korrigiert.;
	date_default_timezone_set($GLOBALS['SetCfg']->SET_TimeZone);
	//@m:ErrorReporting für PHP kann de/aktiviert werden.;
	if($GLOBALS['SetCfg']->SET_ErrorReporting == true) {
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
	}
	//@m:Festlegung einer Sprache oder setzen der Standardsprache;
	//@V:$GLOBALS['LangCurrent'] (alle Sprachinformationen der aktuellen Sprache)<br />$GLOBALS['LangCurrentCode']: en (den aktuell verwendeten Sprachecode "en");
	if(isset($_GET['L'])) { $GLOBALS['LangCurrent'] = $GLOBALS['Locale'][$_GET['L']]; $GLOBALS['LangCurrentCode'] = $_GET['L']; } else { $GLOBALS['LangCurrent'] = $GLOBALS['Locale'][$GLOBALS['LangS'][0]];	$GLOBALS['LangCurrentCode'] = $GLOBALS['LangS'][0]; }
	//@m:Sind mehr als eine Sprache aktiv, wird MultiLang auf true gesetzt;
	//@V:$GLOBALS['MultiLang']: true/false;
	if(count($GLOBALS['LangS']) > 1) {	$GLOBALS['MultiLang'] = true; } else { $GLOBALS['MultiLang'] = false; }
	//@c:2014-11-17:Erweiterung für mehrere DocS;
	//@D:Wenn mehrere Dokumentation in der gleichen Umgebung erstellt werden soll, was die Übersichtlichkeit steigern kann, so kann eine Unterordnerstruktur angelegt werden. In den Ordner "DocS" werden dann Ordner mit dem entsprechenden Projektnamen gelegt. In diese "Projektordner" werden dann die Ordner "DocSet" und "DocAdd", sowie die "index.html" gelegt.;
	//@V:$GLOBALS['DocS'] beinhaltet alle Unterpfade von DocS<br />$GLOBALS['MultiDoc']: true/false;
	if(is_dir('DocS')) {
		$GLOBALS['MultiDoc'] = true;
		$GLOBALS['DocS'] = ab5_CrawleDir(array('Path'=>'DocS','Mode'=>'dir','Level'=>'1'));
		$GLOBALS['DocS'] = $GLOBALS['DocS']['Result'];
		natcasesort($GLOBALS['DocS']);
	} else {
		$GLOBALS['MultiDoc'] = false;
	}
	//@V:$GLOBALS['Templ'] <- template.config;
	$GLOBALS['Templ'] = file_get_contents('template.config');
}
function ab5_CrawleDir($Abic) {
	//@F:ab5_CrawleDir;
	//@I:Array<br />[Path] (Pfad der durchsucht werden soll)<br />[Mode] file,all,dir<br />[Level]<br />[Index]<br />[Filter] (welche Dateiformate berücksichtigt werden "pnd,png");
	$ActDir = $Abic['Path'];
	if (substr($ActDir, strlen($ActDir) - 1) == DIRECTORY_SEPARATOR) {
		$ActDir = substr($ActDir, 0, strlen($ActDir) - 1);
	}
	if (isset($Abic['Mode'])) {
		$Mod = $Abic['Mode'];
	} else {
		$Mod = 'file';
	}
	if (isset($Abic['Level'])) {
		$Level = $Abic['Level'];
	} else {
		$Level = '0';
	}
	if (isset($Abic['Index'])) {
		$Index = $Abic['Index'];
	} else {
		$Index = '';
	}
	if (isset($Abic['Filter'])) {
		$Filter = $Abic['Filter'];
		$FilterS = explode(',',$Abic['Filter']);
	} else {
		$Filter = '';
	}
	//@c:2014-11-20:Added a utf8 support for directory names;
	//@c:2015-03-04:Removed the utf8 support because of solving the problem at server side;
	$ActDir = $ActDir;
	if(is_dir($ActDir)) {
		$Handle = opendir($ActDir);
		while ($Item = readdir($Handle)) {
			if ($Item != "." && $Item != "..") {
				$FileFormat = explode('.', $Item);
				$FileFormat = strtolower(end($FileFormat));
				if (is_dir($ActDir . DIRECTORY_SEPARATOR . $Item)) {
					if ($Mod == "all" || $Mod == "dir") {
							$Index[] = $ActDir . DIRECTORY_SEPARATOR . $Item;
					}
					if($Level > 1 || $Level == 0) {
						$PathNew = $ActDir . DIRECTORY_SEPARATOR . $Item;
						$AbicNew = array('Path' => $PathNew, 'Mode' => $Mod, 'Index' => $Index, 'Filter' => $Filter);
						$Index = ab5_CrawleDir($AbicNew);
						if(isset($Index['Result'])) {
							$Index = $Index['Result'];
						} else {
							$Index = '';
						}
					}
				} else {
					if (($Mod == "file") || ($Mod == "all")) {
						//@c:2015-08-04:Kann jetzt einen Dateiformatfilter verwenden.;
						if(isset($FilterS)) {
							if(in_array($FileFormat, $FilterS)) {
								$Index[] = $ActDir . DIRECTORY_SEPARATOR . $Item;	
							}
						} else {
							$Index[] = $ActDir . DIRECTORY_SEPARATOR . $Item;
						}
					}
				}
			}
		}
		closedir($Handle);
	}
	if($Index !== '') {
		$Abic['Result'] = $Index;
	}
	$Abic['Response'] = 'OK';
	//@O:Array<br />[Result] Array mit Index als Liste<br />[Response] OK;
	return $Abic;
}
function ab5_SortArrayByColumn($Arr, $Col = 0, $Dir = 'SORT_ASC') {
	//@F:ab5_SortArrayByColumn;
	//@D:Mittels dieser Funktion kann ein multidimmensionales Array entsprechend einer Spalte sortiert werden.;
	//@I:Arr,Col (int 0),Dir (SORT_ASC);
	//@m:Dir: SORT_ASC oder SORT_DESC;
	//@O:Arr;
	//@c:2015-02-20:Korrektur des Sortiermechanismus. Jetzt ist er CaseNeutral.;
	if(count($Arr) > 0 && $Arr !== '') {
		foreach ($Arr as $Key => $Row) {
			if(is_array($Row)) {
				$SortCol[$Key] = strtoupper($Row[$Col]);
			} else {
				$SortCol[$Key] = strtoupper($Row);
			}
		}
		$Dir = constant($Dir);
		array_multisort($SortCol, $Dir, SORT_STRING, $Arr);
	}
	return $Arr;
}
function ab5_ReturnArrayAsTree($Array, $Level = '1', $Result = '') {
	//@F:ab5_ReturnArrayAsTree;
	//@D:Mittels einfacher Funktionen wird ein Array in eine visuelle Baumstruktur gewandelt.;
	//@I:Array<br />Für die interne Verarbeitung: Level: 1, Result: '';
	//@O:Result (Text);
	if(count($Array)>0 && $Array !== ''){
		foreach ($Array as $Key => $Value) {
			if (is_array($Value)) {
				$Result = $Result . str_repeat(' | ', $Level) . '+ ' . htmlentities(utf8_decode($Key)) . '<br />';
				$Result = ab5_ReturnArrayAsTree($Value, $Level + 1, $Result);
			} else {
				$Result = $Result . str_repeat(' | ', $Level) . '- ' . htmlentities(utf8_decode($Key)) . ': ' . htmlentities(utf8_decode($Value)) . '<br />';
			}
		}
	}
	return $Result;
}
?>