$(document).ready(function(){
    $("#tableFilter").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#filter tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});