// @A:Markus Hottenrott;
// @m:
console.log('App.js v0.0.7');

// @D:Diese Datei manipuliert die Ansicht der Dokumentenmappe.;

var clientContext = '';
var oList = '';
var listName = '';
var listUrl = '';
var siteUrl = '';
var url = '';

$(document).ready(function () {
    // @V:siteUrl:'/sites/XrmDocSet'; 
    siteUrl = '/sites/XrmDocSet';
    // @V:listName: automatisch ermittelt (z.B. "OpportunityFolder_TEST");
    //listName = 'OpportunityFolder_DEV'; //$('#DeltaPlaceHolderPageTitleInTitleArea').text().replace(/\s*$/g,'').replace(/^\s*/g,'');
    // @c:2015-11-11:Umstellung für die Ermittlung des aktuellen Listennamens und Ableitung aus der URL;
    // @E:Die Ableitung des Listennamens ist noch nicht generisch aufgebaut.;
    listUrl = docsetHomePageData.urlDocSet.match(/(OpportunityDocSet)(.*)(?=\/)/)[0];
    // @V:url: abhängig von "listName";
    if(listUrl == 'OpportunityDocSet_Dev') {
        // @M:OpportunityFolder_DEV > 'https://xrmdev.iabg.de/xRMSAPTest/';
        url = 'https://xrmdev.iabg.de/xRMSAPTest/';
        listName = 'OpportunityFolder_DEV';
    } else if (listUrl == 'OpportunityDocSet_Test') {
        // @M:OpportunityFolder_TEST > 'https://xrmdev.iabg.de/xRMSAPTest/';
        url = 'https://xrmdev.iabg.de/xRMSAPTest/';    
        listName = 'OpportunityFolder_TEST';
    } else if (listUrl == 'OpportunityDocSet') {
        // @M:OpportunityFolder > 'https://xrm.iabg.de/xrmentwicklung/';
        url = 'https://xrm.iabg.de/xrmentwicklung/';    
        listName = 'OpportunityFolder';
    }
    SP.SOD.executeFunc('sp.js', 'SP.ClientContext', spix_Xrm_RefLink);
});

function spix_Xrm_RefLink () {
    // @F:spix_Xrm_RefLink;
    // @D:Die Funktion wird aufgerufen, sobald der SP-Kontext fertig geladen wurde.;
    // @c:2015-11-11:Umbau der ID-Ermittlung, damit diese ebenso in Unterordnern funktioniert.;
    if($.urlParam('ID') !== null) {
        var DocId = $.urlParam('ID');
    } else if(docsetHomePageData.idDocSet !== undefined || docsetHomePageData.idDocSet !== null) {
        var DocId = docsetHomePageData.idDocSet;
    }
    clientContext = new SP.ClientContext(siteUrl);
    oList = clientContext.get_web().get_lists().getByTitle(listName);    
    this.oListItem = oList.getItemById(DocId);
    clientContext.load(oListItem, "XrmGuid");
    clientContext.executeQueryAsync(
        Function.createDelegate(this, this.spix_Xrm_RefLinkShow),    
        Function.createDelegate(this, this.spix_Xrm_RefLinkLog)
    );
}

function spix_Xrm_RefLinkShow () {
    // @F:spix_Xrm_RefLinkShow;
    // @D:Diese Funktion erzeugt den Link zum XRM-System.;
    console.log('spix_Xrm_RefLinkShow');
    oListItem.get_item("XrmGuid");
    //('http://xrmdev/xrmentwicklung/main.aspx?etn=opportunity&pagetype=entityrecord&id=%7BFA764965-DE13-E411-8C3C-00155D5DCB10%7D');
    // @m:
    var Link = url+'main.aspx?etn=opportunity&pagetype=entityrecord&id=%7B'+oListItem.get_item("XrmGuid")+'%7D';
    $('#idDocSetPropertiesWebPart a').last().after('<br /><a href="'+Link+'">Opportunity im XRM öffnen</a>');
    // @M:Am Ende wird noch die Funktion aufgerufen:
    spix_Xrm_CreateFolderS();
}

function spix_Xrm_RefLinkLog (a,b) {
    // @F:spix_Xrm_RefLinkLog;
    // @D:Diese Funktion macht nichts und wird nur im Fehlerfall aufgerufen, was in der Console angezeigt wird.;    
    console.log('spix_Xrm_RefLinkLog');
    console.log(b.get_message());
}

function spix_Xrm_CreateFolderS () {
    // @F:spix_Xrm_CreateFolderS;
    // @D:Diese Funktion wird gestartet und legt die Unterordner entsprechend dem FolderTemplate an.;    
    var CurrentFolder = $('#idDocsetName').text();
    // @M:Es wird geprüft, ob die Liste schon Ordner hat und wenn nicht, dann werden die Ordner angelegt.;
    var ListData = spix_ListData_GetAllItems('/sites/XrmDocSet/_vti_bin/Listdata.svc/'+listName,'substringof(%27'+CurrentFolder+'%27,Pfad)','','');
    if(ListData.length == 0) {
        // @V:FolderTemplate:
        //var FolderTemplate = [{'Name':CurrentFolder+'/Ordner 3'},{'Name':CurrentFolder+'/Ordner 3/Ordner 3.1'},{'Name':CurrentFolder+'/Ordner 2'}];
        var FolderTemplate = [{'Name':CurrentFolder+'/22 Spezifikation (Kunde)'},{'Name':CurrentFolder+'/24 Teilnahmeantrag (in Arbeit)'},{'Name':CurrentFolder+'/25 Teilnahmeantrag (final)'},{'Name':CurrentFolder+'/31 Angebot (in Arbeit)'},{'Name':CurrentFolder+'/41 Angebot (final)'},{'Name':CurrentFolder+'/42 Vertrag (in Arbeit)'},{'Name':CurrentFolder+'/42 Vertrag/Bestellung (final)'},{'Name':CurrentFolder+'/99 Gates'}];
        // @M:Die Ordner werden angelegt durch:
        spix_CreateSubfolderByTemplate({'LibName':listName,'Push':'true','FolderTemplate':FolderTemplate});
        // @c:2015-11-10:Meldung über erfolgreich angelegt Ordner wurde entfernt, um einen Klick zu sparen.;
        //alert('Ordner wurden angelegt!');
        window.location.href = window.location.href;
    }
    return true;
}

$.urlParam = function(name){
    // @F:$.urlParam;
    // @D:Auslesen eines URL-Patameters;
    // @I:Name;
    var results = new RegExp('[\\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
    // @c:2015-11-11:Änderung der Rückgabe zur Fehlerbehandlung;
    // @O:Value|null;
    if(results == null) {
        return null;
    } else {
        return results[1];
    }
}