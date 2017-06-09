// @D:Diese Datei beinhaltet die Anzeige des IABG Forum´s.;
// @M:
console.log('CampusAppNewsTubeWidget.js v0.1.0');

// @c:2015-01-02:Massiver Umbau des gesamten Verzeichnisses;

var ThisWidget;
var CfgO;
var ListData;
var ListDataO;
var ListFilterKategorie;
var PreFilter;

var Load = '';
if(Load == '') {
    spix_CampusApp_Init();
}

window.setInterval(function() {
	alert('Die Anzeige wird jetzt aktualisiert, da neuere Daten verfügbar sind!');
	window.location.href = window.location.href;
}, 60000*60*12); // @M:Reload nach 12h ohne Datenrefresh;

// Load Data
function spix_CampusApp_Init() {
	
		ThisWidgetS = $('.SPiX.NewsTube');
		console.log(ThisWidgetS);
		var i = 0;
		ThisWidgetS.each(function(ThisWidgetNum) {
			console.log('ThisWidget');
			console.log(ThisWidgetS[ThisWidget]);
			ThisWidget = ThisWidgetS[ThisWidgetNum]
			if($(ThisWidget).attr('id') == undefined) {
				$(ThisWidget).attr('id','CampusAppWidgetId'+i);
			}
			if(!$('div').is('.ms-rte-layoutszone-inner-editable')) {
				spix_CampusApp_InitList($(ThisWidget).attr('id'));
			} else {
				//spix_CampusApp_InitCfg();
			}
			i++;
		});
		
}

function spix_CampusApp_InitCfg(WidgetId) {
	/*if($('#CampusAppCfg').length == 0) {
		$('#CampusApp').after('<div id="CampusAppCfg"></div>');
	} else {
		$('#CampusAppCfg').show();
	}
	$('#CampusApp').html('');

	if($('#CampusApp').closest('div.ms-webpartzone-cell').attr('wptoolpaneopen') == 'true') {
		if($('#CampusApp').closest('div.ms-rte-embedcode').length !== 0) {
			$('#CampusAppCfg').html('Skript Editor Webparts müssen direkt geändert werden.');
		} else {
			var CfgJson = $('#CampusApp').attr('data-cfg');
			try { var CfgO = $.parseJSON(CfgJson); }
				catch (e) { var CfgO = new Array();	}
			if(CfgO.PreFilter == undefined) { CfgO.PreFilter = ''; }
			if(CfgO.FilterCol == undefined) { CfgO.FilterCol = ''; }
			if(CfgO.FilterVal == undefined) { CfgO.FilterVal = ''; }
			var CfgMenu = '<table><tr><td>Vorfilter</td><td><input id="PreFilter" class="CfgDataInput" type="text" value="'+CfgO.PreFilter+'" /></td></tr><tr><td>Spaltenname</td><td><input id="FilterCol" class="CfgDataInput" type="text" value="'+CfgO.FilterCol+'" /></td></tr><tr><td>Filterwert</td><td><input id="FilterVal" class="CfgDataInput" type="text" value="'+CfgO.FilterVal+'" /></td></tr></table>';
			$('#CampusAppCfg').html(CfgMenu);//$('#CampusApp').attr('data-cfg'));
			$('.CfgDataInput').change(function() {
				var CfgJson = '{"PreFilter":"'+$('input#PreFilter').val()+'","FilterCol":"'+$('input#FilterCol').val()+'","FilterVal":"'+$('input#FilterVal').val()+'"}';
				$('#CampusApp').attr('data-cfg', CfgJson);		
			});
			
			$('#CampusAppCfgUpdate').on('click',function () {
				var CfgJson = '{"PreFilter":"'+$('input#PreFilter').val()+'","FilterCol":"'+$('input#FilterCol').val()+'","FilterVal":"'+$('input#FilterVal').val()+'"}';
				$('#CampusApp').attr('data-cfg', CfgJson);
			});
		}
	} else {
		$('#CampusAppCfg').html('Bearbeiten Sie das WebPart um Einstellungen vornehmen zu können.');
	}*/
}

function spix_CampusApp_InitList(WidgetId) {
	console.log(ThisWidget);

    setTimeout(function() {
    	$('head').append('<style>.spixDialogBtn { display: inline; line-height: 25px; margin-right: 5px; padding: 0px 10px 0px 10px; background-color: #888888; color: #FFFFFF; }</style>');
    	$('#'+WidgetId).prop('data-filterval');
    	$('#'+WidgetId).prop('data-startpage');
    	$('#'+WidgetId).prop('data-filtercol');
    	$('#'+WidgetId).prop('data-filterkey');



        ListData = spix_ListData_GetAllItems('/_vti_bin/listdata.svc/CampusApp_NewsTube','Inhaltstyp%20eq%20%27NewsTube_Forum%27%20and%20Titel%20ne%20%27tbd%27%20and%20AufStartseiteZeigen%20eq%20true','NewsTubeKategorie,GeändertVon','Titel,NewsTubeKategorie,Inhaltstyp,Geändert,GeändertVon','Geändert desc','10');
        var WidgetAppend = '<table class="" style="width: 100%"><thead style="background-color: #EEEEEE;"><tr class="" ><th class="ms-vh2">Mehr</th><th class="ms-vh2">Thema | Betreff</th><th class="ms-vh2">Kategorie</th><th class="ms-vh2">Autor</th></td></tr></thead><tbody>';
        ListData.forEach(function loop(ThisItem) {
        	console.log(ThisItem);
        	if(ThisItem.NewsTubeKategorie == null || ThisItem.NewsTubeKategorie.Titel == null) { 
				ThisItem.NewsTubeKategorie = '';
				ThisItem.NewsTubeKategorie.Titel = ''; 
			}
        	var AuthorLync = spix_UserProfile_ShowLyncByMail(ThisItem.GeändertVon.GeschäftlicheEMailAdresse,ThisItem.ID+WidgetId);
        	WidgetAppend += '<tr><td style="width: 80px;"><a href="https://portal.iabg.de/SitePages/CampusApp_NewsTube.aspx?ItemId='+ThisItem.ID+'"><div class="spixDialogBtn">Mehr</div></a></td><td>'+ThisItem.Titel+'</td><td style="width: 150px;">'+ThisItem.NewsTubeKategorie.Titel+'</td><td  style="width: 150px;">'+AuthorLync+ThisItem.GeändertVon.Titel+'</td></tr>';
        });
        WidgetAppend += '</tbody></table>';
        $('#'+WidgetId).append(WidgetAppend);
        $('table tr:even','#'+WidgetId).addClass('ms-alternating');
        //spix_CampusApp_Load(PreFilter,FilterCol,FilterVal);
    },0);
}