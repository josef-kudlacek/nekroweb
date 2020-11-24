(function(window, document, $) {
    // On DT Initialization
    $(document).on('init.dt', function(e, dtSettings) {
        if ( e.namespace !== 'dt' )
            return;

        var options = dtSettings.oInit.hideEmptyCols;

        if ($.isArray(options) || options === true) {
            var config     = $.isPlainObject(options) ? options : {},
                api        = new $.fn.dataTable.Api( dtSettings );

            var emptyCount = 0;

            api.columns().every( function () {
                // Check if were only hiding specific columns
                if($.isArray(options) && ($.inArray(this.index(), options) === -1 || $.inArray(api.column(this.index()).dataSrc(), options) === -1))
                    return;

                var data = this.data();

                for (var i = 0; i < data.toArray().length; i++)
                    if (data.toArray()[i] === null || data.toArray()[i].length === 0)
                        emptyCount++;


                // If the # of empty is the same as the length, then no values in col were found
                if(emptyCount === data.toArray().length)
                    api.column( this.index() ).visible( false );

                emptyCount = 0;
            } );
        }
    });
})(window, document, jQuery);



$(document).ready(function(){
    $("#tableFilter").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#filter tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    jQuery.fn.filterByText = function(textbox) {
        return this.each(function() {
            var select = this;
            var options = [];
            $(select).find('option').each(function() {
                options.push({
                    value: $(this).val(),
                    text: $(this).text()
                });
            });
            $(select).data('options', options);

            $(textbox).bind('change paste keyup search', function() {
                var options = $(select).empty().data('options');
                var search = $.trim($(this).val());
                var regex = new RegExp(search, "gi");

                $.each(options, function(i) {
                    var option = options[i];
                    if (option.text.match(regex) !== null) {
                        $(select).append(
                            $('<option>').text(option.text).val(option.value)
                        );
                    }
                });
            });
        });
    };

    jQuery.fn.filterDivByParams = function(divToHide, divForFilter) {
        var filter = $(this).val().toLowerCase();

        divToHide.each(function() {
            var _this = $(this);
            var title = _this.find(divForFilter).text().toLowerCase();
            if (title.indexOf(filter) < 0) {
                _this.hide();
            } else {
                _this.show();
            }
        });
    }

    $(function() {
        $("#filter select").filterByText($("#selectFilter"));
    });

    $('.StarsCount input').click(function () {
        $("#frm-evaluationForm-StarsCount").val($(this).val());

        $(".StarsCount span").removeClass('checked');
        $(this).parent().addClass('checked');
    });

    $('.data-table').DataTable( {
        stateSave: true,
        info: false,
        stateDuration: 60 * 60 * 24 * 7,
        hideEmptyCols: true,
        "language": {
            "lengthMenu": "Zobrazit _MENU_ záznamů na stránku",
            "zeroRecords": "Nenalezen žádný záznam. Omlouváme se.",
            "info": "Zobrazeno _PAGE_ stránek z _PAGES_",
            "infoEmpty": "Žádné záznamy nejsou k dispozici.",
            "infoFiltered": "(Filtrováno z celkového počtu _MAX_ záznamů.)",
            "search": "Filtrovat:",
            "paginate": {
                "previous": "Předchozí",
                "next": "Následující",
            }
        },
        "columnDefs": [
            { "type": "natural", targets: '_all' }
        ],
        "lengthMenu": [[5, 10, 15, 20, -1], [5, 10, 15, 20, "Vše"]],
        "pageLength": -1,
    } );

    $("#criteria").hide();
    $("#show-criteria").on('click', function(){
        if($("#criteria").is(":hidden")){
            $("#criteria").show();
            $("#show-criteria").text("[Skrýt kritéria hodnocení.]");
        }else{
            $("#criteria").hide();
            $("#show-criteria").text("[Zobrazit kritéria hodnocení.]");
        }
    });
});