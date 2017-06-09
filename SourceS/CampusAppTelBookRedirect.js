// @D:Für die Umstellung des Telefonbuchs auf eine neue Seite wurde eine Weiterleitung eingerichtet. Damit sollen die Nutzer auf die Änderung der URL hingewiesen werden;
// @A:Markus Hottenrott;

// @M:Version: 
console.log('CampusAppTelBookRedirect v0.0.1');

var UrlOld = window.location.href;
var UrlNew = UrlOld.replace('https://portal.iabg.de/infothek/PubPages/Telefonbuch.aspx','https://portal.iabg.de/SitePages/CampusApp_Telefonbuch.aspx');
//var UrlNew = UrlOld.replace('https://portal.iabg.de/SitePages/CampusApp_TelefonbuchUmleitung.aspx','https://portal.iabg.de/SitePages/CampusApp_Telefonbuch.aspx');
console.log(UrlNew);

// @M:Es wird das DIV "CampusApp" für die Benutzerinteraktion erzeugt.;
if($('#CampusApp').length == 0) { $('script[src*="CampusAppTelBookRedirect.js"]').parent().append('<div id="CampusApp"></div>'); }

// @D:Die Weiterleitung wird nicht automatisch durchgeführt. Es wird ein Hinweis mit Button eingeblendet. Der Klick auf den Button leitet die vollständige Anfrage inklusive der Parameter weiter.;
$('#CampusApp').html('<h1>Telefonbuch - UMLEITUNG</h1><b>Die URL für das Telefonbuch hat sich geändert.</b><br /><br /><button name="ReDirect" style="background-color: #004993; color: #FFFFFF;" onclick="window.location.href = UrlNew; return false;">JETZT zum neuen Telefonbuch weiterleiten</button><br /><br /><hr /><br /><b>ALT</b> : '+UrlOld+'<br /><b>NEU</b>  : '+UrlNew+'<br /><br />Die Umleitung ist <b>zeitlich begrenzt</b>.<br />Bitte passen Sie ihre Favoriten oder anderen Verweise an. Sollten Sie einen Link angeklickt haben, dann weisen Sie bitte den Autor der Seite auf die Änderung hin.');

// @E:Die Weiterleitung sollte zeitlich begrenzt entfernt werden;