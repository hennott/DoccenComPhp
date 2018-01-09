// @D:Der FormHelper bietet einfache Funktionen zur Manipulation des Standardformulars an.;
// @A:Markus Hottenrott @ share2brain.com;
// @c:2014-10-21:Final für Videoschulung;

// @E:ACHTUNG! Diese Version ist in einem experimentellen Zustand!;

// @M:
console.log('ioxFormHelper.js v0.0.9');

(function( $ ) {
	// @V:CfgO;
	var CfgO;

	$.ioxFormHelperAddStyle = function(CfgO) {
		// @F:ioxFormHelperAddStyle;
		// @c:2014-10-19:Funktion wurde erstellt;
		// @D:Mit dieser Funktion kann ein Feld anhands seines Namens manipuliert werden.;
		// @O:Direkte DOM Änderungen;		
		CfgO = $.extend( {}, $.ioxFormHelperAddStyle.Defaults, CfgO );
		switch(CfgO.Ctrl) {
			case 'HrAfter':
				// @H:HrAfter;			
				// @D:Fügt nach dem genannten Feld eine horizontale Trennlinie ein;
				// @I:Ctrl:<b>HrAfter</b><br />Name!:'';
				// @S:$.ioxFormHelperAddStyle({<br />Name: 'Titel',<br />Ctrl: 'HrAfter'<br />});
				try {
					$("h3:contains('"+CfgO.Name+"')", CfgO.PreSelect).closest('tr').after('<tr><td colspan="2"><hr /></td></tr>');
				} catch (e) { console.log(e); }		
				break;
			case 'PinToTab':
				// @H:PinToTab;
				// @D:Mit dieser Funktion kann ein Feld in ein Tab verschoben werden. Das Feld kann auch in mehreren Tabs vorkommen;
				// @m:Die Reihenfolge kann über SharePoint Listeneinstellung geändert werden;
				// @E:Wird ein Feld in ein Tab verschoben, so sind alle anderen keinem Tab zugeordnet und damit unsichtbar;
				// @R:Alle überigen Felder sollen in einen DefaultTab verschoben werden;
				// @I:Ctrl:<b>PinToTab</b><br />Name:'' oder NameS:'',<br />AppendTo!:'',<br />ShowByDefault:false (false,true);
				// @S:$.ioxFormHelperAddStyle({ <br />NameS: 'Titel,Zusammenfassung', <br />AppendTo: 'Stamm', <br />Ctrl: 'PinToTab', <br />ShowByDefault: true <br />});
				if($('#ioxFormHelperTabbar').length == 0) {
					$("h3.ms-standardheader").closest('table').before('<div id="ioxFormHelper"><div id="ioxFormHelperTabbar" style="background-color: #DDDDDD; padding: 5px 0px 5px 0px;"></div><br /></div>');
					// @c:2014-10-21:Es können alle Tabs mit NameS aufgebaut werden. Die Eigenschaft Name muss nicht verwendet werden.;
					// @m:Die Tabbar wird jetzt abhängig von h3.ms-standardheader eingehängt.;
					//$('tr',$('#ioxFormHelper').next('table')).hide();
				}
				if($('#ioxFormHelperTabbar_'+CfgO.AppendTo).length == 0) {
					$('#ioxFormHelperTabbar').append('<button id="ioxFormHelperTabbar_'+CfgO.AppendTo+'" class="ioxFormHelperTabBarS" style="">'+CfgO.AppendTo+'</button>');
				}
				if(CfgO.Name !== '') {
					$("h3:contains('"+CfgO.Name+"')").closest('tr').addClass('ioxFormHelperTabfield_'+CfgO.AppendTo).addClass('ioxFormHelperTabfield').hide();
				}
				if(CfgO.NameS !== '') {
					var NameS = CfgO.NameS.split(',');
					NameS.forEach(function (Name) {
						$("h3:contains('"+Name+"')").closest('tr').addClass('ioxFormHelperTabfield_'+CfgO.AppendTo).addClass('ioxFormHelperTabfield').hide();
					});
				}
				if(CfgO.ShowByDefault == true) {
					$('.ioxFormHelperTabfield_'+CfgO.AppendTo).show();		
				}
				break;
		}
		$('.ioxFormHelperTabBarS').on('click', function (event) {
			event.preventDefault();
			// @c:2014-10-21:Die Tabbar wurde auf Buttons umgestellt;
			$('.ioxFormHelperTabfield').hide();
			// @E:Der Wert im Tabbutton muss genau dem Filterwert entsprechen;
			$('.ioxFormHelperTabfield_'+$(this).text()).show();
		});
	};
	$.ioxFormHelperAddStyle.Defaults = {
		Name: '',
		NameS: '',
		PreSelect: '',
		ShowByDefault: false,
		AppendTo: 'Stamm',
		Ctrl: ''
	};

	$.ioxFormHelperFieldCtrl = function(CfgO) {
		// @F:ioxFormHelperFieldCtrl;
		// @c:2014-10-19:Funktion wurde erstellt;
		// @D:Mit dieser Funktion kann ein Feld anhands seines Namens manipuliert werden.;
		// @S:$.ioxFormHelperFieldCtrl({<br />Name: 'Anzahl Besucher',<br />Ctrl: 'disable'<br />});
		// @I:Name!:Titel,<br />Ctrl!:enable (clear,hide,remove,read,readonly,disable,enable);
		// @O:Direkte DOM Änderungen;
		CfgO = $.extend( {}, $.ioxFormHelperFieldCtrl.Defaults, CfgO );
		//console.log(CfgO);
		switch(CfgO.Ctrl) {
		case 'clear':
			try {
				$('[title="'+CfgO.Name+'"]').val('');
				$("h3:contains('"+CfgO.Name+"')").closest('tr').hide();
			} catch (e) { console.log(e); }
			break;	
		case 'hide':
			try {
				$("h3:contains('"+CfgO.Name+"')").closest('tr').hide();
			} catch (e) { console.log(e); }
			break;
		case 'show':
			try {
				$("h3:contains('"+CfgO.Name+"')").closest('tr').show();
			} catch (e) { console.log(e); }
			break;			
		case 'remove':
			try {
				$("h3:contains('"+CfgO.Name+"')").closest('tr').remove();
			} catch (e) { console.log(e); }
			break;
		case 'read':
			try {
				var Content = $.ioxFormHelperGetVal({Name: CfgO.Name});
				if($('[title="'+CfgO.Name+'"]').is('input')) {
					$('[title="'+CfgO.Name+'"]').hide().after(Content);
				} else {
					$('[title="'+CfgO.Name+'"]').closest('span').hide().after(Content);
				}
			} catch (e) { console.log(e); }
			break;
		case 'readonly':
			try {
				if ($('[title="'+CfgO.Name+'"]').is('input') && !$('[title="'+CfgO.Name+'"]').is('div')) {
					$('[title="'+CfgO.Name+'"]').closest('td').html($.ioxFormHelperGetVal({Name: CfgO.Name}));
				} else if ($('div[title="'+CfgO.Name+'"] span.sp-peoplepicker-resolveList').is('span')) {
					$('[title="'+CfgO.Name+'"]').closest('td').html($('[title="'+CfgO.Name+'"] span.sp-peoplepicker-resolveList').html());
				} else if ($('h3:contains("'+CfgO.Name+'")').closest('tr').find('.ms-rtestate-write')) {
					$('h3:contains("'+CfgO.Name+'")').closest('tr').find('.ms-rtestate-write').closest('td').html($.ioxFormHelperGetVal({Name: CfgO.Name}));
				} else {
					$('[title="'+CfgO.Name+'"]').closest('td').html($.ioxFormHelperGetVal({Name: CfgO.Name}));
				}
				
			} catch (e) { console.log(e); }
			break;	
		case 'disable':
			try {
				$('[title="'+CfgO.Name+'"]').attr('disabled', 'disabled');
			} catch (e) { console.log(e); }
			break;	
        case 'enable':
			try {
				$('[title="'+CfgO.Name+'"]').removeAttr('disabled');
			} catch (e) { console.log(e); }
			break;	                    
		}
	};
	$.ioxFormHelperFieldCtrl.Defaults = {
		Name: 'Titel',
		Ctrl: 'enable'
	};

	$.ioxFormHelperGetVal = function(CfgO) {
		// @F:ioxFormHelperGetVal;
		// @c:2014-10-19:Funktion wurde erstellt;
		// @D:Die Funktion gibt den Feldinhalt aus;
		// @I:Name!:Titel;
		// @O:String;	
		// @c:2014-10-23:Fehlerkorrektur für das Ersetzen von Zeilenumbrüchen (jetzt werden alle erkannt);
		CfgO = $.extend( {}, $.ioxFormHelperGetVal.Defaults, CfgO );
		try{
			if($('[title="'+CfgO.Name+'"]').is("select")) {
				// @c:2014-10-23:Ausgewählten Wert aus einem Auswahlmenü;
				return $(':selected','[title="'+CfgO.Name+'"]').text().replace(/\n/g,'<br />');
			} else if ($('[title="'+CfgO.Name+'"]').parent().next().is('div')) {
				// @c:2014-10-23:Erkennt Felder mit angehängtem Inhalt;
				return $('[title="'+CfgO.Name+'"]').val().replace(/\n/g,'<br />') + '<br />' + $('[title="'+CfgO.Name+'"]').parent().next().text().replace(/\n/g,'<br />');
			} else if ($('.ms-entity-resolved','[title="'+CfgO.Name+'"]').length == 1) {
				// @c:2014-10-23:Erkennt People-Pickerfelder;
				return $('.ms-entity-resolved','[title="'+CfgO.Name+'"]').attr('title');
			} else if ($('[title="'+CfgO.Name+'"]').attr('type') == 'checkbox') {
				// @c:2014-10-23:Erkennt Checkboxen;
				return $('[title="'+CfgO.Name+'"]').is(':checked');
			} else if ($('[title="'+CfgO.Name+'"]').is("input")) {
				return $('[title="'+CfgO.Name+'"]').val().replace('\n','<br />');
			} else if ($('h3:contains("'+CfgO.Name+'")').closest('tr').find('.ms-rtestate-write')) {
				return $('h3:contains("'+CfgO.Name+'")').closest('tr').find('.ms-rtestate-write').html();
			} else {
				return $('[title="'+CfgO.Name+'"]').val().replace('\n','<br />');
			}
		} catch (e) { console.log(e); }
	};
	$.ioxFormHelperGetVal.Defaults = {
		Name: 'Titel'
	};

	$.ioxFormHelperSetVal = function(CfgO) {
		// @F:ioxFormHelperSetVal;
		// @c:2014-10-19:Funktion wurde erstellt;
		// @D:Die Funktion setzt den Feldinhalt;
		// @I:Name!:Titel<br />Val:'';
		// @O:String;	
		CfgO = $.extend( {}, $.ioxFormHelperSetVal.Defaults, CfgO );
		try{
			// @c:2014-10-22:Erweiterung für Select Felder;
			// @c:2014-12-18:Erweiterung für Textarea und Fehlerkorrektur;
			// @c:2014-12-19:Erweiterung für Optionsauswahl;
			if($('[title="'+CfgO.Name+'"]').is("select")) {
				$('option:contains("'+CfgO.Val+'")','[title="'+CfgO.Name+'"]').attr('selected','selected');
			} else if($('[title="'+CfgO.Name+'"]').is('textarea')) {
				$('[title="'+CfgO.Name+'"]').text(CfgO.Val);
			} else if ($('h3:contains("'+CfgO.Name+'")').closest('tr').find('.ms-rtestate-write').legth > 0) {
				$('h3:contains("'+CfgO.Name+'")').closest('tr').find('.ms-rtestate-write').html(CfgO.Val);
			} else if($('h3:contains("'+CfgO.Name+'")').closest('tr').find('.ms-RadioText').length > 0) {
				$('h3:contains("'+CfgO.Name+'")').closest('tr').find('.ms-RadioText').find('[value="'+CfgO.Val+'"]').prop('checked',true);
			} else {
				$('[title="'+CfgO.Name+'"]').val(CfgO.Val);
			}
		} catch (e) { console.log(e); }
	};
	$.ioxFormHelperSetVal.Defaults = {
		Name: 'Titel',
		Val: ''
	};

	$.ioxFormHelperGetTableContent = function(CfgO) {
		// @F:ioxFormHelperGetTableContent;
		// @c:2014-12-17:Funktion wurde erstellt;
		// @D:Die Funktion versucht das Standformular in etwas lesbares umzuwandeln zum Zwecke der Wandlung in PDF-Dateien.;
		// @I:Selector!:table.ms-formtable;
		// @O:String;	
		CfgO = $.extend( {}, $.ioxFormHelperGetTableContent.Defaults, CfgO );
		try{
			var Result = '<table>';
			$('tr',CfgO.Selector).each(function () {
				Result = Result + '<tr><td>'+$('td',this)[0].text()+'</td><td>'+$('td',this)[1].text()+'</td></tr>';
			});
			Result = Result + '</table>';
			return Result;
		} catch (e) { console.log(e); }
	};
	$.ioxFormHelperGetTableContent.Defaults = {
		Selector: 'table.ms-formtable'
	};

}( jQuery ));