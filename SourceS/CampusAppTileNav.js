// @D:Über diese Datei wird in der CampusApp die linke Navigation geladen
console.log('CampusApptileNav.js v0.0.3');

$('document').ready(function () {
	if(!$('div').is('.ms-rte-layoutszone-inner-editable')) {
		var ButtonS = '';
		ButtonS = ButtonS+'<a href="/SitePages/CampusApp_TelBook.aspx"><img class="CampusAppNavTile" src="/CampusApp_Assets/TileTelBook.png"></img></a>';
		ButtonS = ButtonS+'<a href="/SitePages/CampusApp_AbisZ.aspx"><img class="CampusAppNavTile" src="/CampusApp_Assets/TileAbisZ.png"></img></a>';
		ButtonS = ButtonS+'<a href="/SitePages/CampusApp_NewsTube.aspx"><img class="CampusAppNavTile" src="/CampusApp_Assets/TileForum.png"></img></a>';
		ButtonS = ButtonS+'<a href="/SitePages/CampusApp_Speisekarten.aspx"><img class="CampusAppNavTile" src="/CampusApp_Assets/TileSpeiseKarten.png"></img></a>';
		ButtonS = ButtonS+'<a href="/sites/k2b/SitePages/SP%20CampusApp.aspx"><img class="CampusAppNavTile" src="/CampusApp_Assets/TileVideoK2b.png"></img></a>';
		$('#sideNavBox').html(ButtonS);
	}	
	$('head').append('<style>.CampusAppNavTile {margin: 5px; }</style>')
});