<?php

function ab5_CreateKey($Abic = '') {
    if (!isset($Abic['Length'])) {
        $Abic['Length'] = 10;
    }
    if (!isset($Abic['Mode'])) {
        $Abic['Mode'] = 1;
    }
    $Return = '';
    if ($Abic['Mode'] = "1") {
        $Zeichen = "1234567890abcdefghijklmnopqrstuvwxyzABCZEFGHIJKLMNOPQRSTUVWXYZ";
    } elseif ($Abic['Mode'] = "2") {
        $Zeichen = "1234567890abcdefghijklmnopqrstuvwxyzABCZEFGHIJKLMNOPQRSTUVWXYZ!&/()=??$*+-.:-_";
    }
    //Zeichenkette wird generiert
    mt_srand((double) microtime() * 1000000);
    for ($i = 1; $i <= $Abic['Length']; $i++) {
        $Return .= $Zeichen[mt_rand(0, strlen($Zeichen) - 1)];
    }
    if (isset($Abic['Direct']) && $Abic['Direct'] == 1) {
        return $Return;
    } else {
        $Abic['Result'] = $Return;
        return $Abic;
    }
}

function ab5_GetRootPathDyn($Force = 0) {
    /*
     * C:2013-11-29;
     * D:Ermittelt den Relativen Pfad bezogen auf den echten Ausführungsort;
     * I:;
     * O:direct;
     */
    if (!isset($_SESSION['ab5s']['Storage']['RootPathDyn']) || (isset($_SESSION['ab5s']['Storage']['ReLoad']) && $_SESSION['ab5s']['Storage']['ReLoad'] == "1") || $Force == 1) {
        $AB5Path = ab5_GetRootPath(1);
        if (substr_count($AB5Path, "/") > 0) {
            $Seperator = "/";
        } else {
            $Seperator = "\\";
        }
      $Seperator = DIRECTORY_SEPARATOR;
        for ($i = 0; $i <= strlen($AB5Path); $i++) {
            if (substr($AB5Path, 0, $i) !== substr(getcwd(), 0, $i)) {
                $CommonPath = substr($AB5Path, 0, $i);
                break;
            }
        }
        $PrePath = "";
        for ($j = 0; $j < substr_count(substr(getcwd(), strlen(substr($AB5Path, 0, strrpos($CommonPath, $Seperator)))), $Seperator); $j++) {
            $PrePath = ".." . $Seperator . $PrePath;
        }
        $PrePath = $PrePath . substr($AB5Path, strlen(substr($AB5Path, 0, strrpos($CommonPath, $Seperator))) + 1);
        $_SESSION['ab5s']['Storage']['RootPathDyn'] = $PrePath;
    } else {
        $PrePath = $_SESSION['ab5s']['Storage']['RootPathDyn'];
    }
    return $PrePath;
}

function ab5_GetRootPath($Force = 0) {
    /*
     * C:2013-11-29;
     * D:Ermittelt den absoluten Pfad bezogen auf den echten Ausführungsort;
     * I:;
     * O:direct;
     * E: Pfadreferenz;
     */
    if (!isset($_SESSION['ab5s']['Storage']['RootPath']) || (isset($_SESSION['ab5s']['Storage']['ReLoad']) && $_SESSION['ab5s']['Storage']['ReLoad'] == "1" ) || $Force == 1) {
        $IncludedFiles = get_included_files();
        foreach ($IncludedFiles as $Path) {
            //Prüft auf eine Hilfsdatei
            if (strpos($Path, "ab5_root_folder") !== false) {
                break;
            }
        }
        if (substr_count($Path, "/") > 0) {
            $Seperator = "/";
        } else {
            $Seperator = "\\";
        }
      //ERR: Trennung muss einheitlich ausgetauscht werden an ALLEN Stellen im Code ;
      $Seperator = DIRECTORY_SEPARATOR;
        $Count = substr_count($Path, $Seperator);
        for ($i = 0; $i < $Count; $i++) {
            if (is_file($Path . $Seperator . 'ab5_root_folder') !== FALSE) {
                $AB5Path = $Path . $Seperator;
                break;
            }
            $Pos = strrpos($Path, $Seperator);
            $Path = substr($Path, 0, $Pos);
        }
        $_SESSION['ab5s']['Storage']['RootPath'] = $AB5Path;
    } else {
        $AB5Path = $_SESSION['ab5s']['Storage']['RootPath'];
    }
    return $AB5Path;
}

function ab5_AppendHistory($Abic) {
    /* C:2014-01-07;
     * D:Ergänzt einen HistoryLog;
     * I:Pos(Before),Msg,Log;
     * O:Log;
     * E:;
     */
    $Timestamp = date("Y-m-d H:i:s", time());
    if (!isset($Abic['Pos'])) {
        $Abic['Pos'] = 'Before';
    }
    $NewPost = '-- ' . $Timestamp . '\n' . visux_RetLabel($Abic['Msg']);
    if ($Abic['Pos'] == 'Before') {
        $Abic['Log'] = $NewPost . '\n\n' . $Abic['Log'];
    } else {
        $Abic['Log'] = $Abic['Log'] . '\n\n' . $NewPost;
    }
    return $Abic;
}

function ab5_LogAdd($Message, $Level = "2") {
    /* C:2013-11-29;
     * D:Schreibt eine Log-Nachricht in die Session;
     * I:;
     * O:;
     * E:;
     */

    if ((isset($_SESSION['ab5s']['SettingS']['SpeedMode']) && $_SESSION['ab5s']['SettingS']['SpeedMode'] == "0") || (isset($_SESSION['ab5s']['SettingS']['DevMode']) && $_SESSION['ab5s']['SettingS']['DevMode'] == "1") || (isset($_SESSION['ab5s']['SettingS']['Logging']['DbSave']) && $_SESSION['ab5s']['SettingS']['Logging']['DbSave'] == "1")) {
        $Message = str_replace(":", ":</b>", str_replace("-", "<b>-", $Message));
        $Timestamp = date("Y-m-d H:i:s", time());
        $TimeNow = microtime();

        if (isset($_SESSION['ab5s']['Storage']['Logging']['UTimeDiff']) && strlen($_SESSION['ab5s']['Storage']['Logging']['UTimeDiff']) >= 5) {
            $TimeLast = $_SESSION['ab5s']['Storage']['Logging']['UTimeDiff'];
            list($start_usec, $start_sec) = explode(" ", $TimeLast);
            list($end_usec, $end_sec) = explode(" ", $TimeNow);
            $diff_sec = intval($end_sec) - intval($start_sec);
            $diff_usec = floatval($end_usec) - floatval($start_usec);
            $TimeDiff = number_format((floatval($diff_sec) + $diff_usec) * 1000, 2, ',', '.');
            //$TimeDiff = number_format(($TimeNew - $TimeLast)/1000, 2, ',', '.');
        } else {
            $TimeLast = 0;
            $TimeNow = 0;
            $TimeDiff = 0;
        }
        $_SESSION['ab5s']['Storage']['Logging']['UTimeDiff'] = $TimeNow;
        if (isset($_SESSION['ab5s']['SettingS']['Logging']['Level']) && $Level <= $_SESSION['ab5s']['SettingS']['Logging']['Level']) {
            $ActLoggingContent = "";
            if (!isset($_SESSION['ab5s']['Storage']['Logging']['Content'])) {
                $ActLoggingContent = "";
            } else {
                $ActLoggingContent = $_SESSION['ab5s']['Storage']['Logging']['Content'];
            }
            $ActLoggingContent = $ActLoggingContent . '<br /> (' . $TimeDiff . ')<br /><b>' . $Timestamp . " -L" . $Level . " >> </b>" . $Message;
            $_SESSION['ab5s']['Storage']['Logging']['Content'] = $ActLoggingContent;
            //Sicherung des Track
            //ERR: Kein DB-Logging;
        }
    }
}

function ab5_LogHistory() {
    /* C:2013-11-29;
     * D:Die Funktion legt einen Verlauf des Nutzers an;
     * I:;
     * O:;
     * E:;
     */
    $_SESSION['ab5s']['Storage']['PageHistory'][] = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function visux_RetLabel($String) {
    /* C:2013-11-29;
     * D:Die Funktion gibt die Funktion zurück;
     * I:;
     * O:;
     * E:Noch wird keine andere Sprache ausgegeben! Zudem fehlt ein dynamisches Encoding!;
     */
    //echo utf8_decode($_SESSION['Storage']['Lang'][$SearchKey]);
    if (isset($_SESSION['ab5s']['Storage']['Translate']['De'][$String])) {
        $String = $_SESSION['ab5s']['Storage']['Translate']['De'][$String];
    }
    if (isset($_SESSION['ab5s']['Storage']['LabelEncoding'])) {
        switch (strtoupper($_SESSION['ab5s']['Storage']['LabelEncoding'])) {
            case 'UTF8':
                return utf8_decode($String);
                break;
            case 'NO':
                return $String;
                break;
            default:
                if (preg_match('!!u', $String)) {
                    return utf8_decode($String);
                } else {
                    return $String;
                }
        }
    } else {
        return utf8_decode($String);
    }
}

function visux_EchoArrayAsTree($Array, $Level = '1') {
    /* C:2013-12-03;
     * D:This converts an multidimensional array into an simple text-tree.;
     * I:;
     * O:(echo only);
     * E:;
     */
    foreach ($Array as $Key => $Value) {
        if (is_array($Value)) {
            //echo '<hr />';
            echo str_repeat(' | ', $Level) . '+ ' . htmlentities(utf8_decode($Key)) . '<br />';
            //$Path = $Path .' / '. $Key;
            //echo '<b>' . $Path . '.. </b><br />';
            visux_EchoArrayAsTree($Value, $Level + 1);
        } else {
            echo str_repeat(' | ', $Level) . '- ' . htmlentities(utf8_decode($Key)) . ': ' . htmlentities(utf8_decode($Value)) . '<br />';
        }
    }
}

function visux_ToggleDivShowByLink($Abic) {
    /* C:2013-11-29;
     * D:Gibt einen Link aus, welcher ein DIV-Element ein und wieder ausblendet;
     * I:LinkLabel!,DivId!;
     * O:echo only;
     * E:;
     */
    echo '<a href="void(0)" onclick="var Div = document.getElementById(\'' . $Abic['DivId'] . '\'); if(Div.style.display == \'none\') { Div.style.display = \'block\'; } else { Div.style.display = \'none\';} return false;">' . visux_RetLabel($Abic['LinkLabel']) . '</a>';
}

function ab5_IncludeStylesheet($Abic) {
    $FileContent = file_get_contents($Abic['FilePath']);
    echo '<style>' . $FileContent . '</style>';
}

function ab5_IncludeJavaScript($Abic) {
    $FileContent = file_get_contents($Abic['FilePath']);
    echo '<script>' . $FileContent . '</script>';
}

function ab5_ConvertArrayToString($Array) {
    /* C:2013-11-29;
     * D:wandelt ein Array in einen Text um;
     * I:;
     * O:direct > string;
     * E:;
     */
    $String = '';
    if (is_array($Array)) {
        foreach ($Array as $Key => $Value) {
            $String = $String . ' | ' . $Key . ': ' . $Value;
        }
        $String = substr($String, 2);
    }
    return $String;
}

function ab5_ConvertDateToMysql($Date) {
    /* C:2013-11-29;
     * D:wandelt ein deutsches Datum in mySQL konformes Datum um;
     * I:;
     * O:direct > string;
     * E:;
     */
    $D = explode('.', $Date);
    return sprintf('%04d-%02d-%02d', $D[2], $D[1], $D[0]);
}

//#########################################################################

function ab5_ConvertNumToMysql($Num) {
    /* C:2013-11-29;
     * D:wandelt eine KommaZahl in Punktschreibweise f?r mySQL um;
     * I:;
     * O:direct > string;
     * E:;
     */
    $N = explode(',', $Num);
    return sprintf('%d.%02d', $N[0], $N[1]);
}

function ab5_SortArrayByColumn($Arr, $Col, $Dir = 'SORT_ASC') {
    /* C:2013-11-29;
     * D:Sort an multi-dimensional Array by coloumn;
     * I:;
     * O:direct > array;
     * E:;
     */
    foreach ($Arr as $Key => $Row) {
        $SortCol[$Key] = $Row[$Col];
    }
    $Dir = constant($Dir);
    array_multisort($SortCol, $Dir, $Arr);
    return $Arr;
}

function ab5_GetFile($Abic) {
    /* C:2013-12-02;
     * D:Returns an File;
     * I:Path!;
     * O:Response [OK|ERR],Results [content of the file];
     * E:;
     */
    $Path = $Abic['Path'];
    if (substr($Path, 0, 1) == '/') {
        $File = $Path;
    } else {
        $File = ab5_GetRootPath() . $Path;
    }
    $FileExt = strtoupper(end(explode(".", $File)));
    $FileName = end(explode("/", $File));

    switch ($FileExt) {
        case 'JPG':
            $Size = getimagesize($File);
            header('Content-Disposition: inline;filename=' . $FileName);
            header('Content-Type: {$Size["mime"]}');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * 1)) . ' GMT');
            echo readfile($File);
            break;
        case 'ZIP':
            header('Content-Disposition: inline;filename=' . $FileName);
            header('Content-Type: {application/zip}');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * 1)) . ' GMT');
            echo readfile($File);
            break;
        case 'CSS':
            header('Content-Type: text/css');
            header('Content-Disposition: inline;filename=' . $FileName);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * 1)) . ' GMT');
            echo readfile($File);
            break;
        case 'JS':
            header('Content-Disposition: inline;filename=' . $FileName);
            header('Content-Type: application/x-javascript');
            //header("Cache-Control: must-revalidate");
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * 1)) . ' GMT');
            echo readfile($File);
            break;
        case 'JSON':
            header('Content-Disposition: inline;filename=' . $FileName);
            header('Content-Type: application/json');
            //header("Cache-Control: must-revalidate");
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * 1)) . ' GMT');
            //echo readfile($File);
            echo file_get_contents($File);
            break;
        default:
            header('Content-Disposition: inline;filename=' . $FileName);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (60 * 60 * 24 * 1)) . ' GMT');
            echo readfile($File);
    }
}

function ab5_CrawleDir($Abic) {
    /* C:2013-12-02;
     * D:Crawling an directory.;
     * I:Path!,Mode [dir|file|all],(Index);
     * O:Response [OK|ERR],Results [Array with filepaths];
     * E:;
     */
    $ActDir = $Abic['Path'];
    if (substr($ActDir, strlen($ActDir) - 1) == DIRECTORY_SEPARATOR) {
        $ActDir = substr($ActDir, 0, strlen($ActDir) - 1);
    }
    if (isset($Abic['Mode'])) {
        $Mod = $Abic['Mode'];
    } else {
        $Mod = 'file';
    }
    if (isset($Abic['Index'])) {
        $Index = $Abic['Index'];
    } else {
        $Index = '';
    }
    $Handle = opendir($ActDir);
    while ($Item = readdir($Handle)) {
        if ($Item != "." && $Item != "..") {
            if (is_dir($ActDir . DIRECTORY_SEPARATOR . $Item)) {
                if ($Mod == "all" || $Mod == "dir") {
                    $Index[] = $ActDir . DIRECTORY_SEPARATOR . $Item;
                }
                //$Index = ab5_CrawleDir($ActDir . "/" . $Item, $Mod, $Index);
                $PathNew = $ActDir . DIRECTORY_SEPARATOR . $Item;
                $AbicNew = array('Path' => $PathNew, 'Mode' => $Mod, 'Index' => $Index);
                $Index = ab5_CrawleDir($AbicNew);
                $Index = $Index['Result'];
            } else {
                if (($Mod == "file") || ($Mod == "all")) {
                    $Index[] = $ActDir . DIRECTORY_SEPARATOR . $Item;
                }
            }
        }
    }
    closedir($Handle);
    if (!isset($Index)) {
        $Index[] = "";
    }
    $Abic['Response'] = 'OK';
    $Abic['Result'] = $Index;
    return $Abic;
}

function ab5_DeleteDir($Abic) {
    /* C:2013-12-02;
     * D:Deleting an directory.;
     * I:Path!;
     * O:Response [OK|ERR];
     * E:;
     */
    $FileS = glob($Abic['Path'] . '*', GLOB_MARK);
    foreach ($FileS as $File) {
        if (substr($File, -1) == DIRECTORY_SEPARATOR) {
            ab5_DeleteDir(array('Path' => $File));
        } else {
            unlink($File);
        }
    }
    rmdir($Abic['Path']);
    return $Abic['Response'] = 'OK';
}

function ab5_Translate_LoadTranslation($Abic = '') {
    ab5_LogAdd('ab5_Translate_LoadTranslation: -Do: start loading', 4);
    if (!isset($Abic['Lang'])) {
        $Abic['Lang'] = $_SESSION['ab5s']['SettingS']['DefaultLanguage'];
    }
    $Path = ab5_GetRootPathDyn() . 'sites/' . 'default' . '/files/languages/' . $Abic['Lang'] . '.php';
    if (is_file($Path)) {
        require_once($Path);
    }
    ab5_LogAdd('ab5_Translate_LoadTranslation: -Do: finished', 4);
}

function ab5_LoadConfig($XmlFile, $ReLoad = '1') {
    $_SESSION['ab5s']['SettingS']['LoadedConfig'] = $_SERVER['REQUEST_TIME'];
    $_SESSION['ab5s']['SettingS']['LoadedConfigPath'] = ab5_GetRootPath(1);

    if ($ReLoad == '1') {
        ab5_LogAdd('ab5_LoadConfig: -Reload: 1', 2);
      $Temp = array();
      if(isset($_SESSION['ab5s']['Storage'])) { $Temp['Storage'] = $_SESSION['ab5s']['Storage']; }
  if(isset($_SESSION['ab5s']['SettingS'])) { $Temp['SettingS'] = $_SESSION['ab5s']['SettingS']; }
      if(isset($_SESSION['ab5s']['Auth'])) { $Temp['Auth'] = $_SESSION['ab5s']['Auth']; }
        unset($_SESSION['ab5s']);
        $_SESSION['ab5s'] = $Temp;

        $_SESSION['ab5s']['SettingS']['NodeId'] = (string) $XmlFile->SettingS->NodeId;
        $_SESSION['ab5s']['SettingS']['TimeZone'] = (string) $XmlFile->SettingS->TimeZone;
        $_SESSION['ab5s']['SettingS']['DefaultLanguage'] = (string) $XmlFile->SettingS->DefaultLanguage;
        foreach ($XmlFile->SettingS->LanguageS->Language as $Item) {
            $Attr = $Item->attributes();
            $_SESSION['ab5s']['SettingS']['LanguageS'][(string) $Attr['Id']] = (string) $Item;
        }
        ab5_LogAdd('ab5_LoadConfig: -Init Translate: start', 3);
        ab5_Translate_LoadTranslation();
        ab5_LogAdd('ab5_LoadConfig: -Init Translate: finished', 3);
        $_SESSION['ab5s']['SettingS']['Address'] = (string) $XmlFile->SettingS->Address;
        $_SESSION['ab5s']['SettingS']['StaticSessionId'] = (string) $XmlFile->SettingS->StaticSessionId;
        $_SESSION['ab5s']['SettingS']['CronKey'] = (string) $XmlFile->SettingS->CronKey;

        $_SESSION['ab5s']['SettingS']['ExtLoader'] = (string) $XmlFile->SettingS->ExtLoader;

        $_SESSION['ab5s']['SettingS']['SpeedMode'] = (string) $XmlFile->SettingS->SpeedMode;
        $_SESSION['ab5s']['SettingS']['DevMode'] = (string) $XmlFile->SettingS->DevMode;
        $_SESSION['ab5s']['SettingS']['Lock'] = (string) $XmlFile->SettingS->Lock;

        foreach ($XmlFile->SettingS->Logging->children() as $Item) {
            $_SESSION['ab5s']['SettingS']['Logging'][(string) $Item->getName()] = (string) $Item;
        }

        foreach ($XmlFile->SettingS->License->children() as $Item) {
            $_SESSION['ab5s']['SettingS']['License'][(string) $Item->getName()] = (string) $Item;
        }

        foreach ($XmlFile->SettingS->DefaultControlS->children() as $Item) {
            $_SESSION['ab5s']['SettingS']['DefaultControlS'][(string) $Item->getName()] = (string) $Item;
        }

        ab5_LogAdd('ab5_LoadConfig: -Init Registry: 1', 4);
        //Registry
        foreach ($XmlFile->Registry->Entry as $Entry) {
            $CallS = explode(',', (string) $Entry->CallS);
            foreach ($Entry->Content->children() as $Content) {
                switch ($Content->getName()) {
                    case "FileS":
                        foreach ($Content->File as $File) {
                            $FilePath = ab5_GetRootPathDyn() . $File;
                            //$_SESSION['ab5s']['RegEntrieS']['ByPosition'][(string) $Entry->Position][] = "require_once('" . $FilePath . "');";
                            foreach ($CallS as $Call) {
                                $_SESSION['ab5s']['RegEntrieS']['ByCall'][$Call][] = "require_once('" . $FilePath . "');";
                            }
                        }
                        break;
                    case "ConnectX":
                        foreach ($Content->children() as $Param) {
                            $_SESSION['ab5s']['ConnectS'][(string) $Content->Control][(string) $Param->getName()] = (string) $Param;
                        }
                        $File = ab5_GetRootPathDyn() . "connects/connect." . $Content->Connect . "/connect.inc.php";
                        $Pos = strrpos($File, "/");
                        $Path = substr($File, 0, $Pos);
                        $LibraryXmlFile = simplexml_load_file($Path . '/connector.xml');
                        foreach ($LibraryXmlFile->Function as $Function) {
                            $_SESSION['ab5s']['ConnectS'][(string) $Content->Control][(string) $Function->attributes()->Name] = (string) $Function;
                        }
                        foreach ($CallS as $Call) {
                            $_SESSION['ab5s']['RegEntrieS']['ByCall'][$Call][] = "require_once('" . $File . "');";
                        }
                        break;
                    case "ParamS":
                        foreach ($Content->children() as $Param) {
                            /* foreach($CallS as $Call) {
                              $_SESSION['ab5s']['RegEntrieS']['ByCall'][$Call][(string) $Param->getName()] = (string) $Param;
                              } */
                            $_SESSION['ab5s']['ParamS'][(string) $Content->attributes()->SetName][(string) $Param->getName()] = (string) $Param;
                            //if ((string) $Entry->Id !== "" && !isset($Content->Id)) {
                            //$_SESSION['ab5s']['Params']['ById'][(string) $Entry->Id][(string) $Param->getName()] = (string) $Param;
                            // $_SESSION['ab5s']['Params']['ByPosition'][(string) $Entry->Position][(string) $Param->getName()] = (string) $Param;
                        }

                        break;
                    case "FunctionS":
                        foreach ($Content->children() as $Func) {
                            switch ($Func->getName()) {
                                case "CallFunc":
                                    $CallValue = (string) $Func . ";";
                                    foreach ($CallS as $Call) {
                                        $_SESSION['ab5s']['RegEntrieS']['ByCall'][$Call][] = $CallValue;
                                    }
                                    break;
                                case "Cron":
                                    //CronJobS;
                                    $CallValue = (string) $Func . ";";
                                    foreach ($CallS as $Call) {
                                        $_SESSION['ab5s']['CronJobS']['ByCall'][$Call][] = $CallValue;
                                    }
                                    break;
                            }
                        }
                        break;
                }
            }
        }
    }
    ab5_LogAdd('ab5_LoadConfig: -Init Database: 1', 4);
    ab5_RegEntry('autorun');
    ab5_LogAdd('ab5_LoadConfig: -Loaded: 1', 4);
    if (isset($_SESSION['ab5s']['SettingS']['TimeZone'])) {
        date_default_timezone_set($_SESSION['ab5s']['SettingS']['TimeZone']);
    }
}

function ab5_RegEntry($Call) {
    ab5_LogAdd("ab5_RegEntry: -Pos: " . $Call, 3);
    $CallArr = explode("::", $Call);
    $Position = $CallArr[0];
    if (isset($CallArr[1])) {
        $Id = $CallArr[1];
    } else {
        $Id = "";
    }

    if (isset($_SESSION['ab5s']['RegEntrieS']['ByCall'][$Call])) {
        foreach ($_SESSION['ab5s']['RegEntrieS']['ByCall'][$Call] as $Function) {
            $Response = eval($Function);
            ab5_LogAdd("ab5_RegEntry: -Function: " . $Function . " -Response: " . $Response, 4);
        }
    }

    /* if (isset($_SESSION['ab5s']['RegEntrieS']['ByPosition'][$Position])) {
      foreach ($_SESSION['ab5s']['RegEntrieS']['ByPosition'][$Position] as $Function) {
      $Response = eval($Function);
      ab5_LogAdd("ab5_RegEntry: -Function: " . $Function . " -Response: " . $Response, 4);
      }
      }

      if (isset($_SESSION['ab5s']['Params']['ById'][$Id])) {
      foreach ($_SESSION['ab5s']['Params']['ById'][$Id] as $Key => $Value) {
      $_SESSION['ab5s']['Params']['ByPosition'][$Position][$Key] = $Value;
      ab5_LogAdd("ab5_RegEntry: -Key: " . $Key . " -Value: " . $Value, 4);
      }
      } */

    if (isset($_SESSION['ab5s']['CronJobS']['ByCall'][$Call])) {
        foreach ($_SESSION['ab5s']['CronJobS']['ByCall'][$Call] as $Value) {
            $Function = explode("::", $Value);
            if (ab5_CronRunNow($Function[0])) {
                $Response = eval($Function[1]);
                ab5_LogAdd("ab5_RegEntry: -CronTime: " . $Function[0] . " -Function: " . $Function[1] . " -Response: " . $Response, 4);
            }
        }
    }
}

function ab5_CronRunNow($CronTime) {
    $ThisTime = date("Y-m-d_H:i:00_T", time());
    if (ab5_CronRunNextTs($CronTime) == $ThisTime) {
        return true;
    } else {
        return false;
    }
}

function ab5_CronRunNextTs($CronTime) {
    $VarTime = time();
    list($VarCronMin, $VarCronHour, $VarCronDay, $VarCronMonth, $VarCronDow) = preg_split('/ +/', $CronTime);
    do {
        list( $VarTimeMin, $VarTimeHour, $VarTimeDay, $VarTimeMonth, $VarTimeDow ) = preg_split("/ +/ ", date("i H d n N", $VarTime));
        if ($VarCronMonth != '*') {
            if (!ab5_CronHelperFit($VarCronMonth, $VarTimeMonth)) {
                $VarCronMonth = (int) $VarTimeMonth + 1;
                $VarTime = mktime(0, 0, 0, $VarTimeMonth, 1, date("Y", $VarTime));
                continue;
            }
        }
        if ($VarCronDay != '*') {
            if (!ab5_CronHelperFit($VarCronDay, $VarTimeDay)) {
                $VarTimeDay = (int) $VarTimeDay + 1;
                $VarTime = mktime(0, 0, 0, $VarTimeMonth, $VarTimeDay, date("Y", $VarTime));
                continue;
            }
        }
        if ($VarCronHour != '*') {
            if (!ab5_CronHelperFit($VarCronHour, $VarTimeHour)) {
                $VarTimeHour = (int) $VarTimeHour + 1;
                $VarTime = mktime($VarTimeHour, 0, 0, $VarTimeMonth, $VarTimeDay, date("Y", $VarTime));
                continue;
            }
        }
        if ($VarCronMin != '*') {
            if (!ab5_CronHelperFit($VarCronMin, $VarTimeMin)) {
                $VarTimeMin = (int) $VarTimeMin + 1;
                $VarTime = mktime($VarTimeHour, $VarTimeMin, 0, $VarTimeMonth, $VarTimeDay, date("Y", $VarTime));
                continue;
            }
        }
        if ($VarCronDow != '*') {
            if (!ab5_CronHelperFit($VarCronDow, $VarTimeDow)) {
                $VarTimeDay = (int) $VarTimeDay + 1;
                $VarTime = mktime(0, 0, 0, $VarTimeMonth, $VarTimeDay, date("Y", $VarTime));
                continue;
            }
        }
        break;
    } while (true);
    $Return = date("Y-m-d_H:i:00_T", $VarTime);
    return $Return;
}

function ab5_CronHelperFit($VarCron, $VarTime) {
    if (strpos($VarCron, ',')) {
        $ArrList = explode(',', $VarCron);
        foreach ($ArrList as $Item) {
            if (b5_CronHelperFit($Item, $VarTime)) {
                return true;
            }
        }
        return false;
    }
    if (strpos($VarCron, '-')) {
        //list($low, $high)=split('-',$VarCron);
        $ArrRange = explode('-', $VarCron);
        if ($VarTime == (int) $ArrRange[0]) {
            return true;
        } else {
            return false;
        }
    }
    if (strpos($VarCron, '/')) {
        //list($pre, $pos)=split('/',$VarCron);
        $ArrDiv = explode('/', $VarCron);
        if ($ArrDiv[0] == '*') {
            if ($VarTime % (int) $ArrDiv[1] == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($VarTime % (int) $ArrDiv[1] == (int) $ArrDiv[0]) {
                return true;
            } else {
                return false;
            }
        }
    }
    if ((int) $VarCron == $VarTime) {
        return true;
    }
    return false;
}
  /*
  function ab5_CronRunWorker() {
    $Worker = ab5_connect_GetItem(array('Control' => 'CoreDB', 'Table' => 'SysQueueWorker', 'Where'=>'`State`=\'pend\'', 'Direct' =>'0'));
    if(is_array($Worker['Result'])) {
      $i=0;
      foreach($Worker['Result'] as $Worker) {
        $i++;
        echo '<hr>';
        $Response = '';
        echo "Key ".$Worker['Key'].'<br />';
        exec('php '.ab5_GetRootPath().'wrk.php '.ab5_GetRootPath().' '.$Worker['Key'].' > /dev/null &2 ', $Response); //> /dev/null &
        visux_EchoArrayAsTree($Response);
        if($i > 30) { break; }
      }
    }
  }*/

/* function ab5_connect_DbConnect($Abic) {
  if(!isset($Abic['Control'])) {
  $Abic['Control'] = $_SESSION['ab5s']['SettingS']['DefaultControlS']['Database'];
  }
  $Function = "return " . $_SESSION['ab5s']['ConnectS'][$Abic['Control']]['DbConnect'] . '($Abic);';
  ab5_LogAdd('ab5_connect_DbConnect: -Control: '.$Abic['Control'].' -Function: ' . $Function, 4);
  $Return = eval($Function);
  $Abic['Result'] = $Return;
  return $Abic;
  } */
  
  function ab5_connect_GetItem($Abic) {
    if (!isset($Abic['Device'])) {
      $Abic['Device'] = "";
    }
    if (!isset($Abic['Channel'])) {
      $Abic['Channel'] = "";
    }
    ab5_LogAdd('ab5_connect_GetItem: -Control: ' . $Abic['Control'] . ' -Device: ' . $Abic['Device'] . ' -F: ' . $_SESSION['ab5s']['ConnectS'][$Abic['Control']]['GetItem'], 2);
    $Function = "return " . $_SESSION['ab5s']['ConnectS'][$Abic['Control']]['GetItem'] . '($Abic);';
    ab5_LogAdd('ab5_connect_GetItem: -Function: ' . $Function, 2);
    return eval($Function);
  }

function ab5_connect_SetItem($Abic) {
    if (!isset($Abic['Device'])) {
        $Abic['Device'] = "";
    }
    if (!isset($Abic['Channel'])) {
        $Abic['Channel'] = "";
    }
    ab5_LogAdd('ab5_connect_SetItem: -Control: ' . $Abic['Control'] . ' -Device: ' . $Abic['Device'] . ' -F: ' . $_SESSION['ab5s']['ConnectS'][$Abic['Control']]['SetItem'], 2);
    $Function = "return " . $_SESSION['ab5s']['ConnectS'][$Abic['Control']]['SetItem'] . '($Abic);';
    ab5_LogAdd('ab5_connect_SetItem: -Function: ' . $Function, 2);
    return eval($Function);
}

function ab5_EchoCacheHeader($Abic) {
    /* C:2013-12-02;
     * D:Echo the "304 Not Modified" header if the file is cached and returns true or false;
     * I:FileName!,Mtime!;
     * O:Response [OK|ERR];
     * E:;
     */
    $FileName = $Abic['FileName'];
    $Time = $Abic['Mtime'];
    header('ETag: "' . md5($Time . $FileName) . '"');
    header('Last-Modified: ' . $Time);
    header('Cache-Control: public');

    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
        if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $Time || str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == md5($Time . $FileName)) {
            header('HTTP/1.1 304 Not Modified');
            return true;
        } else {
            return false;
        }
    }
}

function visux_ComposeAllProfiles($Abic = '') {
    /* C:2013-12-02;
     * D:Searchs for all possible profiles and build an cached version for the actual site;
     * I:Profile;
     * O:Response [OK|ERR],(fwrite only);
     * E:;
     */
  /*if (!isset($Abic['Profile'])) {
        $Abic['Profile'] = "default";
}*/
    if (isset($Abic['Site'])) {
        $Abic['Site'] = $Abic['Site'];
    } elseif (isset($_SESSION['ab5s']['SettingS']['Site'])) {
        $Abic['Site'] = $_SESSION['ab5s']['SettingS']['Site'];
    } else {
        $Abic['Site'] = "default";
    }
    $Path = ab5_GetRootPathDyn() . 'sites/' . $Abic['Site'] . '/files/compose.profile/';
    $FileS = ab5_CrawleDir(array('Path' => $Path, 'Mode' => 'dir'));
    foreach ($FileS['Result'] as $File) {
        visux_ComposeHelperFrame(array('Profile' => end(explode('/', $File))));
    }

    return $Abic['Response'] = 'OK';
}

function visux_ComposeAllImageJson($Abic = '') {
    if (!isset($Abic['Dir'])) {
        $Abic['Dir'] = ab5_GetRootPathDyn()."sites/default/cust/";
    }
    if (!isset($Abic['Prefix'])) {
        $Abic['Prefix'] = '_';
    }  
    if (isset($Abic['Site'])) {
        $Abic['Site'] = $Abic['Site'];
    } elseif (isset($_SESSION['ab5s']['SettingS']['Site'])) {
        $Abic['Site'] = $_SESSION['ab5s']['SettingS']['Site'];
    } else {
        $Abic['Site'] = "default";
    }

    $Path = $Abic['Dir'];
    $DirS = ab5_CrawleDir(array('Path' => $Path, 'Mode' => 'dir'));
          //print_r($DirS);
  if(!is_array($DirS['Result'])) {
    $DirS['Result'][] = $Path;
  }
    foreach ($DirS['Result'] as $Dir) {
        $FileS = ab5_CrawleDir(array('Path' => $Dir, 'Mode' => 'file'));
        $FileS = $FileS['Result'];
        $Result = '';
        foreach ($FileS as $File) {
            $Result[substr($File, strrpos($File, '/') + 1)] = base64_encode(file_get_contents($File));
        }
        $JsonArr = json_encode($Result);
        $FileHandle = fopen(ab5_GetRootPathDyn().'sites/' . $Abic['Site'] . '/files/cache/'.$Abic['Prefix'].'composed.' . str_replace(' ', '', substr($Dir, strrpos($Dir, '/') + 1)) . '.img.json', "w");
        fwrite($FileHandle, $JsonArr);
        fclose($FileHandle);
    }
    return $Abic['Response'] = 'OK';
}

function visux_ComposeHelperFrame($Abic = '') {
    /* C:2013-12-02;
     * D:This function compose two files out of a bundle of files by an profile. The name of the profile must be the name of the subfolder where the CSS and JS files are located.;
     * I:Profile;
     * O:Response [OK|ERR],(fwrite only);
     * E:;
     */
    if (!isset($Abic['Profile'])) {
        $Abic['Profile'] = "default";
    }
    if (isset($Abic['Site'])) {
        $Abic['Site'] = $Abic['Site'];
    } elseif (isset($_SESSION['ab5s']['SettingS']['Site'])) {
        $Abic['Site'] = $_SESSION['ab5s']['SettingS']['Site'];
    } else {
        $Abic['Site'] = "default";
    }
    $Path = ab5_GetRootPathDyn() . 'sites/' . $Abic['Site'] . '/files/compose.profile/' . $Abic['Profile'];
    $FileS = ab5_CrawleDir(array('Path' => $Path));

    $ContentJs = "";
    $ContentCss = "";
    
    
    $FileS['Result'] = ab5_SortArrayByColumn($FileS['Result'], '1', $Dir = 'SORT_ASC');

    foreach ($FileS['Result'] as $File) {
        $FileExt = strtoupper(end(explode(".", $File)));
        if ($FileExt == "JS") {
            $ContentJs = $ContentJs . ' ' . file_get_contents($File);
        }
        if ($FileExt == "CSS") {
            $ContentCss = $ContentCss . ' ' . file_get_contents($File);
        }
    }
    $FileHandle = fopen(ab5_GetRootPathDyn().'sites/' . $Abic['Site'] . '/files/cache/_composed.' . $Abic['Profile'] . '.css', "w");
    fwrite($FileHandle, $ContentCss);
    fclose($FileHandle);
    $FileHandle = fopen(ab5_GetRootPathDyn().'sites/' . $Abic['Site'] . '/files/cache/_composed.' . $Abic['Profile'] . '.js', "w");
    fwrite($FileHandle, $ContentJs);
    fclose($FileHandle);
    return $Abic['Response'] = 'OK';
}

function visux_IncludeHelperFrame($Abic = '') {
    /* C:2013-12-02;
     * D:This Script includes a composed set of files which represents the HelperFrame for UI-Design. The Frame is loaded by profile and includes an JS and CSS file.;
     * I:Profile;
     * O:Response [OK|ERR],(link files only);
     * E:;
     */
    if (!isset($Abic['Profile'])) {
        $Abic['Profile'] = "default";
    }
    //ab5_IncludeStylesheet(array('FilePath'=>'libraries/lib.visux/_composed.'.$Abic['Profile'].'.css'));
    //ab5_IncludeJavaScript(array('FilePath'=>'libraries/lib.visux/_composed.'.$Abic['Profile'].'.js'));
    echo '<link rel="stylesheet" type="text/css" href="' . $_SESSION['ab5s']['SettingS']['ExtLoader'] . '?Load=Cache&File=_composed.' . $Abic['Profile'] . '.css">';
    echo '<script language="javascript" src="' . $_SESSION['ab5s']['SettingS']['ExtLoader'] . '?Load=Cache&File=_composed.' . $Abic['Profile'] . '.js"></script>';
    return $Abic['Response'] = 'OK';
}

function visux_form_FillArray($Abic) {
    if (!isset($Abic['Size'])) {
        $Abic['Size'] = "";
    }
    if (!isset($Abic['Value'])) {
        $Abic['Value'] = "";
    }
    if (!isset($Abic['Target'])) {
        $Abic['Target'] = "";
    }
    if (!isset($Abic['ValueS'])) {
        $Abic['ValueS'] = "";
    }
    return $Abic;
}

function visux_formTextMultipleAutoComplete($Abic) {
    // 0.3
    // D: Eine Textzeile mit Komma getrennter Eingabe für mehrere Werte (Vorschlag je Einzelwert)
    // I: FieldName!, Value, TagS!, Size
    // O: echo only
    $Abic = visux_formFillArray($Abic);

    echo '<input id="' . $Abic['FieldName'] . '" size="' . $Abic['Size'] . '" value="' . $Abic['Value'] . '" />';
    echo '<script>visux_formTextMultipleAutoComplete(' . json_encode($Abic) . ');</script>';
}

function visux_formTextAutoComplete($Abic) {
    // 0.3
    // D: Eine Textzeile (Vorschlagswerte)
    // I: FieldName!, Value, TagS!, Size
    // O: echo only
    $Abic = visux_formFillArray($Abic);

    echo "<input id=\"" . $Abic['FieldName'] . "\" size=\"" . $Abic['Size'] . "\" value=\"" . $Abic['Value'] . "\" />";
    echo "<script>visux_formTextAutoComplete(" . json_encode($Abic) . ");</script>";
}

function visux_formDatePicker($Abic) {
    // 0.3
    // D: Ein Datumsfeld
    // I: FieldName!, Value, Size
    // O: echo only
    $Abic = visux_formFillArray($Abic);

    echo "<input id=\"" . $Abic['FieldName'] . "\" size=\"" . $Abic['Size'] . "\" value=\"" . $Abic['Value'] . "\" />";
    echo "<script>visux_formDatePicker(" . json_encode($Abic) . ");</script>";
}

function visux_formButton($Abic) {
    // 0.3
    // D: Ein Button (z.B. zum Absenden des Formulars)
    // I: FieldName!, Value
    // O: echo only
    $Abic = visux_formFillArray($Abic);

    echo '<input type="submit" value="' . $Abic['Value'] . '" />';
}

?>