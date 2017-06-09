// @A:Markus Hottenrott;
// @m:
console.log('EditForm.js v0.0.3');

// @D:Diese Datei manipuliert den Bearbeitendialog für die Mappeneigenschaften.;

$(document).ready(function () {
    // @M:Ausblenden der Felder "Name", "XrmGuid", "Inhaltstyp", "Beschreibung";
    //$('[title="Name Pflichtfeld"]').closest('tr').hide();
    $('[title="Name Pflichtfeld"]').prop("disabled", true);
    $('[title="XrmGuid"]').closest('tr').hide();
    $('[title="Inhaltstyp"]').closest('tr').hide();
    //$('[title="Beschreibung"]').prop("disabled", true);
    $('[title="Ablaufdatum."]').prop("disabled", true);
    $('[title="WfStatus"]').closest('tr').hide();
});
