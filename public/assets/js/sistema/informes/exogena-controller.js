var exogena_table = null;
var $comboFormatoExogena = null;
var $comboConceptoExogena = null;
var $comboYearExogena = null;
var $comboNitExogena = null;

function exogenaInit() {
    $('.water').hide();

    initTablesExogena();
    initCombosExogena();
}

function initCombosExogena() {
    $comboFormatoExogena = $('#id_formato_exogena').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un formato",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Debes ingresar más caracteres...";
            }
        },
        ajax: {
            url: 'api/exogena/formato',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboConceptoExogena = $('#id_concepto_exogena').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un concepto",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Debes ingresar más caracteres...";
            }
        },
        ajax: {
            url: 'api/exogena/concepto',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboYearExogena = $('#id_year_exogena').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un año",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Debes ingresar más caracteres...";
            }
        },
        ajax: {
            url: 'api/year-combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboNitExogena = $('#id_nit_exogena').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cédula/nit",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Debes ingresar más caracteres...";
            }
        },
        ajax: {
            url: 'api/nit/combo-nit',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
}

function initTablesExogena() {
    exogena_table = $('#ExogenaInformeTable').DataTable({
        pageLength: 100,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        ordering: false,
        sScrollX: "100%",
        scrollX: true,
        scroller: {
            displayBuffer: 20,
            rowHeight: 50,
            loadingIndicator: true
        },
        deferRender: true,
        fixedHeader : {
            header : true,
            footer : true,
            headerOffset: 45
        },
        ajax:  {
            type: "GET",
            url: base_url + 'exogena',
            headers: headers,
            data: function ( d ) {
                d.year = $('#id_year_exogena').val();
                d.id_formato = $('#id_formato_exogena').val();
                d.id_concepto = $('#id_concepto_exogena').val();
                d.id_nit = $('#id_nit_exogena').val();
            }
        },
        "columns": [
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
            {data: 'id'},
        ]
    });
}

$(document).on('click', '#generarExogena', function () {

    var form = document.querySelector('#exogenaFilterForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#generarExogena").hide();
    $("#generarExogenaLoading").show();

    var url = base_url + 'exogena';
    url+= '?year='+$('#id_year_exogena').val();
    url+= '&id_formato='+$('#id_formato_exogena').val();
    url+= '&id_concepto='+$('#id_concepto_exogena').val();
    url+= '&id_nit='+$('#id_nit_exogena').val();

    exogena_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando informe', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
});
