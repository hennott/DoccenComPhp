// @D:Dies ist die zentrale Datei der CampusApp inkl. aller Module.
console.log('CampusAppNewsTube_ListMod.js v0.0.2');

var Cfg = new Array();
Cfg.ListName = "CampusApp_NewsTube";
Cfg.ListId = "CampusApp_NewsTube";
Cfg.DataTableColumns = 'Titel,Kurzbeschreibung';
Cfg.ListRestPath = '/_vti_bin/listdata.svc/'+Cfg.ListName;
Cfg.DataTableDefaultSort = [[1,"asc"]];

Cfg.DataTableColumnO = Cfg.DataTableColumns.split(',');

var Load = '';
if(Load == '') {
    spix_CampusAppNewsTube_Init();
}

function spix_CampusAppNewsTube_Init(Mode,PreFilter,FilterCol,FilterVal) {
    Mode = Mode || 'SpNative';
    Cfg.Mode = Mode;
    Cfg.PreFilter = PreFilter;
    Cfg.FilterCol = FilterCol;
    Cfg.FilterVal = FilterVal;
    switch(Mode) {
        case "SpNative": 
            $('document').ready(function () {
                if(spix_GetUrlPara('RootFolder') == '') {
                    $('#idHomePageNewItem').attr('href','/Lists/CampusApp_NewsTube/NewForm.aspx?ContentTypeId=0x0120005FBC210FC76CEC43AB38DECF0DD36F5A00B878A106B22A144796A2E84B1A2D9780').attr('onclick','');
                    $('#idHomePageNewItem span:contains("Neues Element")').text('Neue NewsTube erstellen');
                    $('#CSRListViewControlDivWPQ2').show();
                } else {
                    $('#idHomePageNewItem span:contains("Neues Element")').text('Neue Nachricht hinzufügen');
                    $('#CSRListViewControlDivWPQ2').hide();
                }
            });
            break;
        case "DataTables":
            window.setInterval(function() {
            alert('Die Anzeige wird jetzt aktualisiert, da neuere Daten verfügbar sind!');
            window.location.href = window.location.pathname + '?FilterCol=' + $('#CampusAppInputFilterField').val()+'&FilterVal=' + $('#CampusAppTable_filter label input').val();
    }, 60000*60*24); //Reload nach 1h ohne Datenrefresh
            spix_CampusAppNewsTube_BuildDataTables(Cfg);
            break;
    }

}

function spix_CampusAppNewsTube_BuildDataTables (Cfg) {
    if($('script[src="CampusApp.js"]').length == 0 && $('script[src="CampusApp.min.js"]').length == 0) {
        $('head').append('<script type="text/javascript" src="'+SPiXscMaster+'CampusApp/dev/CampusApp.js"></script>');
    }
    if($('#CampusApp'+Cfg.ListId).length == 0) { 
        $('script[src*="CampusApp'+Cfg.ListId+'.js"]').parent().append('<div id="CampusApp'+Cfg.ListId+'"></div>');
    }
    Cfg.ListData = spix_ListData_GetAllItems(Cfg.ListRestPath,Cfg.PreFilter,'',Cfg.DataTableColumns);
    Cfg.ListDataO = new Array();
    Cfg.ListDataRawO = new Array();
    Cfg.ListData.forEach(function (ThisItem) {
        spix_CampusAppNewsTube_ItemViewMod(spix_CampusAppNewsTube_ItemDataMod(ThisItem));
    });
    Cfg.FilterCol = Cfg.FilterCol || spix_GetUrlPara('FilterCol');
    Cfg.FilterVal = Cfg.FilterVal || decodeURIComponent(spix_GetUrlPara('FilterVal'));
    
    spix_CampusApp_BuildTableDefinition();
    var Thead = '<td>Titel</td>';
    
    var AppFrame = '<table id="CampusApp'+Cfg.ListId+'Table" class="display" cellspacing="0" width="100%"><thead><th><input type="checkbox" class="ItemSelect" id="ItemSelectAll" value="All" /></th>'+Cfg.Thead+'</thead><tbody></tbody></table><br /><!--<div id="CampusAppToolBar"><input type="button" name="AppendMailList" id="AppendMailList" value="Mailverteiler" title="Markieren Sie Einträge, um daraus einen Mailverteiler für Outlook zu erzeugen." onclick="spix_CampusApp_AppendMailList(); return false;" /><input type="button" name="PrintList" id="PrintList" value="Druckansicht" title="Markieren Sie Einträge, um diese in eine Druckansicht zu bringen." onclick="spix_CampusApp_PrintList(); return false;" /><input type="button" name="GetDirectlink" id="GetDirectlink" value="Direktlink" title="Mit diesem Link können Sie Ihre Recherche speichern." onclick="spix_CampusApp_GetDirectlink(); return false;" /></div>--><style>@media print{#CampusApp{display: none;} } .no-wrap { white-space: nowrap; }.PersDetailsBtn { margin-left: 10px; margin-right: 5px; padding: 0px 5px 0px 5px; position: relative; top: 3px; outline: 1px solid #888888; border: 1px solid #FFFFFF; }.ms-imnImg{ margin-right: 10px; margin-left: 10px; top: 3px; outline: 1px solid #888888; border: 1px solid #FFFFFF;} table.dataTable thead .sorting{background:url("'+SPiXscMaster+'Images/jquery.dataTables/sort_both.png") no-repeat center right}table.dataTable thead .sorting_asc{background:url("'+SPiXscMaster+'Images/jquery.dataTables/sort_asc.png") no-repeat center right}table.dataTable thead .sorting_desc{background:url("'+SPiXscMaster+'Images/jquery.dataTables/sort_desc.png") no-repeat center right} table.dataTable thead th, table.dataTable thead td { padding: 10px 10px; }</style>';
    
    $('#CampusApp'+Cfg.ListId).html(AppFrame);
    console.log(Cfg.ListDataO);
    var CampusAppTableO = $('#CampusApp'+Cfg.ListId+'Table').dataTable({
		//fnDrawCallback: spix_CampusApp_ModView,
		order: Cfg.DataTableDefaultSort,
		aoColumns: Cfg.ColDef,
		lengthMenu: [ [50, 100, 200, -1], [50, 100, 200, "Alle"] ],
		language: LanguageGer,
		autoWidth: false,
		bProcessing: true,
		bDeferRender: true,
		aaData: Cfg.ListDataO
	});
    
        
    //$('#CampusApp'+Cfg.ListId).after('<br /><div id="CampusAppToolBar"><input type="button" name="AppendMailList" id="AppendMailList" value="Mailverteiler" title="Markieren Sie Einträge, um daraus einen Mailverteiler für Outlook zu erzeugen." onclick="spix_CampusApp_AppendMailList(); return false;" /><input type="button" name="PrintList" id="PrintList" value="Druckansicht" title="Markieren Sie Einträge, um diese in eine Druckansicht zu bringen." onclick="spix_CampusApp_PrintList(); return false;" /><input type="button" name="GetDirectlink" id="GetDirectlink" value="Direktlink" title="Mit diesem Link können Sie Ihre Recherche speichern." onclick="spix_CampusApp_GetDirectlink(); return false;" /></div>');
}

function spix_CampusAppNewsTube_AddItem (Content) {
    function AddListItem() {
      var listTitle = "CampusApp_NewsTube";

      //Get the current client context
      context = SP.ClientContext.get_current();
      var airportList = context.get_web().get_lists().getByTitle(listTitle);

      //Create a new record
      var listItemCreationInformation = new SP.ListItemCreationInformation();
      var listItem = airportList.addItem(listItemCreationInformation);

      //Set the values
      listItem.set_item('Title', 'Seattle/Tacoma');
      listItem.set_item('Kurzbeschreibung', 'SEA');
      //listItem.set_item('Title', 'SEATAC');

      listItem.update();
      context.load(listItem);

      context.executeQueryAsync(AddListItemSucceeded, AddListItemFailed);
    }

    function AddListItemSucceeded() {
      alert('List Item Added.');
    }

    function AddListItemFailed(sender, args) {
      alert('Request failed. ' + args.get_message() + '\n' + args.get_stackTrace());
    }
    AddListItem();
}

function spix_CampusAppNewsTube_ItemDataMod (ThisItem) {
    if(ThisItem.Titel == '') {
        ThisItem.Title = 'kein Titel';
    }
    
    ThisItemO = new Array();
    for(i=0;i<Cfg.DataTableColumnO.length;i++) {
        ThisItemO = ThisItem;
    }
    Cfg.ListDataRawO.push(ThisItemO);
    return ThisItem; 
}

function spix_CampusAppNewsTube_ItemViewMod (ThisItem) {
    var ThisItemO = new Array();
    
    ThisItem.Titel = '<b>'+ThisItem.Titel+'</b>';

    ThisItemO.push(ThisItem.ID);
    for(i=0;i<Cfg.DataTableColumnO.length;i++) {
        ThisItemO.push(ThisItem[Cfg.DataTableColumnO[i]]);
    }
    Cfg.ListDataO.push(ThisItemO);
    return ThisItem; 
}