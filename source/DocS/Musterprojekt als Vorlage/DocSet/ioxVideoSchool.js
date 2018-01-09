// @D:VideoSchool ist ein einfaches Skript, welches eine normale SharePoint Wiki-Seite in einen VideoPlayer verwandelt.::de;
// @A:Markus Hottenrott;
// @R:Besserer Videosupport::de;
// @m:In Vorbereitung für Videoschulung::de;
// @D:VideoSchool is a very simple script which transform normal SharePoint Wiki-Sites into a VideoPlayer.::en;
// @R:Better Support for video::en;

// @M:
console.log('ioxVideoSchool.js v0.0.1');

$('document').ready($.ioxVideoSchoolBuilder);

(function( $ ) {
    // @V:CfgO::en;
    var CfgO;

    $.ioxVideoSchoolMarkLinks = function(CfgO) {
        // @F:ioxVideoSchoolMarkLinks;
        // @D:Diese Funktion wird aufgerufen, um die Linkliste zu generieren.<br />Diese Funktion markiert die Links in einer SharePoint Liste auf einer Wiki-Seite.;
        // @m:Eine Portierung der VideoSchool auf andere Seiten wäre hier zu regeln.;
        // @I:Id!:VideoSchool;
        // @O:Direkte DOM Änderungen;
        CfgO = $.extend( {}, $.ioxVideoSchoolMarkLinks.Defaults, CfgO );
        console.log('ioxVideoSchoolMarkLinks');

    };
    $.ioxVideoSchoolMarkLinks.Defaults = {
        Id: 'VideoSchool'
    };

    $.ioxVideoSchoolBuilder = function(CfgO) {
        // @F:ioxVideoSchoolBuilder;
        // @D:Die VideoSchool wird recht einfach aufgebaut. Die Eingangsliste wird nach verwertbaren Links durchsucht und in den Navigationsbereich "verschoben". In der Inhaltsebene wird dann das Video eingebunden und entsprechend abgespielt.<br />Mit der Variablen AutoPlay wird definiert, ob nach dem Abspielende automatisch das nächste Video abgespielt werden (1) oder anhalten (0) soll.;
        // @I:LinkClass:VideoSchoolItem,<br />AutoPlayDefault:true;<br />MoveContentTable:true<br />AppendTo:VideoSchool;
        // @m:<b>LinkClass</b> beinhaltet die Klasse, die durch die Funktion "MarkLinks" den Elementen zugewiesen wurde.<br /><b>AutoPlayDefault</b> definiert den Startzustand für den Player. Dieser kann vom Nutzer übersteuert werden, wenn er vorher auf true stand. Andernfalls ist er immer false.<br /><b>MoveContentTable</b> wird auf false gesetzt, wenn das Inhaltsverzeichnis da bleiben soll, wo es ist. In diesem Fall wird "nur" ein Content-Bereich platziert.<br /><b>AppendTo</b> gibt die ID an, wo der ContentBereich angehängt werden soll.;
        // @O:Direkte DOM Änderungen;
        CfgO = $.extend( {}, $.ioxVideoSchoolBuilder.Defaults, CfgO );
        console.log('ioxVideoSchoolBuilder');

    };
    $.ioxVideoSchoolBuilder.Defaults = {
        LinkClass: 'VideoSchoolItem',
        AutoPlayDefault: true,
        MoveContentTable: true,
        AppendTo:'VideoSchool'
    };

}( jQuery ));

/*
$('document').ready( function () {
    if (!$('div').is('.ms-rte-layoutszone-inner-editable')) {
            var AutoPlay = 1;
            var List = $('#VideoSchool');
            $('#VideoSchool').before('<div><div id="VideoList" style="padding: 5px; float: left; border: 1px solid #999999; width: 30%;  overflow-y: scroll;"></div><div id="VideoScreen" style="float: right; width: calc( 70% - 20px ); border: 1px solid #999999; text-align: center;"><video id="VideoTag" style="max-height: 100%; max-width: 100%;" autoplay controls><source id="VideoSource" src="" type="video/mp4">Your browser does not support the video tag.</video><iframe id="FrameTag" src="" style="max-height: 720px; border: 1px solid #999999;" width="100%" /></div></div><style>.ActiveTag { background-color: #DDDDDD; padding: 0px 5px 0px 5px;} .HiddenContent { display: none; }</style>')
            $('#VideoSchool').remove();
            $('#VideoList').html(List);
            $('#VideoTag').hide();
            $('#FrameTag').hide();

            var VideoId=0;
            var AutoPlayVideoId=0;
            $('#VideoSchool a').each(function () {
              var Url = $(this).attr('href').split('.');
              var Url2 = $(this).attr('href').split('/');
              switch(Url[Url.length-1]) {
                    case 'mp4': // Achtung ... für mp4 und avi wird der gleiche Block ausgeführt, da ein "break" fehlt
                    case 'avi':
                            if($(this).text().substr(0,1) !== "(") {
                                $(this).addClass('VideoToAutoPlay');
                                $(this).attr('data-VideoAutoPlayId',AutoPlayVideoId);
                                AutoPlayVideoId++;
                            }
                            $(this).addClass('VideoToPlay');
                            $(this).attr('data-VideoId',VideoId);
                            $(this).after(' <small>[<a href="'+window.location.pathname+'?VideoName='+Url2[Url2.length-1]+'" style="color:inherit;">Video</a>]</small>');
                            // @c:2014-10-01:Direktlink zu einem Video ist direkt abrufbar.;
                            VideoId++;
                            break;
                    case 'docx':
                    case 'pptx':
                    case 'xlsx':
                            $(this).addClass('DataToShow');
                            $(this).after(' <small>[Doc-Link]</small>');
                            break;
                    default:
                            $(this).after(' <small>[Link]</small>');
              }
              $(this).css({'color':'#0072c6'});
            });
            
            $('#VideoSchool li').each(function () {
                var ContentSplit = $(this).html().split('<br>'); //$('br',this);
                //console.log(ContentSplit);
                var NewHtml = ContentSplit.shift();
                ContentSplit.forEach(function (Item) {
                  NewHtml += '<span class="HiddenContent"><br>'+Item+'</span>';
                });
                //console.log(NewHtml);
                $(this).html(NewHtml);
              });

            // @D:Nach dem Aufruf der Seite wird das erste Video gestartet;
            if(spix_GetUrlPara('VideoName') == '') {
                    $('#VideoSource').attr('src',$('.VideoToPlay[data-videoid="0"]').attr('href')).attr('data-videoid','0');
                    $('#VideoSource').attr('data-VideoAutoPlayId','0');
                    $('.VideoToPlay[data-VideoId="0"]').addClass('ActiveTag');
                    spix_VideoSchool_ToggleHiddenContent('0');
                    $('#VideoTag').show();
            } else {
                    var StartVideoId = $('.VideoToPlay[href*="'+spix_GetUrlPara('VideoName')+'"]').attr('data-VideoId');
                    var StartVideoAutoPlayId = $('.VideoToPlay[href*="'+spix_GetUrlPara('VideoName')+'"]').attr('data-VideoAutoPlayId');
                    $('#VideoSource').attr('src',$('.VideoToPlay[data-videoid="'+StartVideoId+'"]').attr('href')).attr('data-videoid',StartVideoId);
                    $('#VideoSource').attr('data-VideoAutoPlayId',StartVideoAutoPlayId);
                    $('.VideoToPlay[data-VideoId="'+StartVideoId+'"]').addClass('ActiveTag');
                    spix_VideoSchool_ToggleHiddenContent(StartVideoId);
                    $('#VideoTag').show();
            }

            // @D:Nach dem Ende eines Videos wird das nächste abgespielt, sofern AutoPlay aktiv ist;
            $('#VideoTag').bind('ended',function(){
                    var NextId = parseInt($('#VideoSource').attr('data-VideoAutoPlayId')) + 1;
                    console.log(NextId);
                    if($('.VideoToAutoPlay').length > NextId && AutoPlay == 1) {
                            $('#VideoSource').attr('src',$('.VideoToAutoPlay[data-VideoAutoPlayId="'+NextId+'"]').attr('href')).attr('data-VideoAutoPlayId',NextId);
                            $('#VideoTag').load();
                            $('a').removeClass('ActiveTag');
                            $('.VideoToAutoPlay[data-VideoAutoPlayId="'+NextId+'"]').addClass('ActiveTag');
                            spix_VideoSchool_ToggleHiddenContent($('.VideoToAutoPlay[data-VideoAutoPlayId="'+NextId+'"]').attr('data-videoid'));
                    }
            });

            // @D:Wenn ein Videolink angeklickt wird, dann startet dieses im Videobereich;
            $('.VideoToPlay').bind('click', function(event) {
                    event.preventDefault();
                    $('#VideoSource').attr('src',$(this).attr('href')).attr('data-VideoId',$(this).attr('data-VideoId'));
                    $('#VideoSource').attr('data-VideoAutoPlayId',$(this).attr('data-VideoAutoPlayId'));
                    $('#FrameTag').attr('src','').hide();
                    $('#VideoTag').show().load();
                    $('a').removeClass('ActiveTag');
                    $(this).addClass('ActiveTag');
                    spix_VideoSchool_ToggleHiddenContent($(this).attr('data-videoid'));
            });

            // @D:Klickt man auf einen "normalen" Link, dann wird die Adresse in einem iFrame eingebunden;
            $('.DataToShow').bind('click', function(event) {
                    event.preventDefault();
                    $('#VideoSource').attr('src','');
                    $('#VideoTag').hide().load();
                    $('#FrameTag').show().attr('src',$(this).attr('href'));
                    $('a').removeClass('ActiveTag');
                    $(this).addClass('ActiveTag');
                    spix_VideoSchool_ToggleHiddenContent($(this).attr('data-videoid'));
            });

            // @D:Video AutoPlay Mechanismus. Wenn dieser aktiv ist, dann wird ein Video nach dem Anderen aus der Liste abgespielt;
            $('#VideoList').prepend('<p id="AutoPlay" style="text-align: right; cursor: pointer; color: #008000;" data-autoplay="1">Automatische Wiedergabe läuft</p>');
            $('#AutoPlay').bind('click', function () {
                    if($(this).attr('data-autoplay') == 1) {
                            $(this).attr('data-autoplay','0');
                            $(this).css({'color':'#FF0000'});
                            $(this).text('Automatische Wiedergabe pausiert');
                            AutoPlay = 0;
                    } else {
                            $(this).attr('data-autoplay','1');
                            $(this).css({'color':'#008000'});
                            $(this).text('Automatische Wiedergabe läuft');
                            AutoPlay = 1;
                    }
            });

            // @D:Die Wiedergabengröße passt sich der Fensterhöhe an;
            $(window).bind('resize',function () {
                    var Ch1 = $(window).height()-$('#VideoList').position().top - 80;
                    var Ch2 = Ch1-10;
                    $('#VideoList').css({'height':Ch2+'px'});
                    $('#VideoScreen').css({'height':Ch1+'px'});
                    $('#FrameTag').css({'height':Ch1+'px'});
            });
            var Ch1 = $(window).height()-$('#VideoList').position().top - 80;
            var Ch2 = Ch1-10;
            var Ch3 = (Ch1-$('#VideoTag').height())/2;
            $('#VideoList').css({'height':Ch2+'px'});
            $('#VideoScreen').css({'height':Ch1+'px'});
            $('#FrameTag').css({'height':Ch1+'px'});
    }
});

function spix_VideoSchool_ToggleHiddenContent(VideoId) {
    // @F:spix_VideoSchool_ToggleHiddenContent;
    // @D:Wechselt die Ansicht eines versteckten Ergänzungsfeldes;
    // @m:VideoId;
    $('.HiddenContent').hide();
    //console.log('ShowHidden for Video '+VideoId);
    $('.HiddenContent',$('.VideoToPlay[data-VideoId="'+VideoId+'"]').closest('li')).show();
}
*/