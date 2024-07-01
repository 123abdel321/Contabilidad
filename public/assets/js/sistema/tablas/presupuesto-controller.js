let inputAction = null;
let presupuesto_table = null;
var searchValuePresupuesto = null;
var dataPresupuestoRow = [];
let mesesPresupuesto = [
    'enero',
    'febrero',
    'marzo',
    'abril',
    'mayo',
    'junio',
    'julio',
    'agosto',
    'septiembre',
    'octubre',
    'noviembre',
    'diciembre',
];
var mesActual = null;
var focusNextRow = null;

function presupuestoInit() {
    presupuesto_table = $('#presupuestoTable').DataTable({
        pageLength: 15,
        dom: 'Brtip',
        responsive: false,
        processing: false,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        ordering: false,
        sScrollX: "100%",
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'presupuesto',
            data: function ( d ) {
                d.periodo = $("#periodo_presupuesto").val(),
                d.tipo = $("#tipo_presupuesto").val(),
                d.id_presupuesto = $("#id_presupuesto_up").val(),
                d.search = searchValuePresupuesto
            }
        },
        'rowCallback': function(row, data, index){
            if (!data.auxiliar) {
                $('td', row).css('background-color', 'rgb(64 164 209 / 20%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if (parseInt(data.diferencia)) {
                $('td', row).css('background-color', '#ffc30029');
                return;
            }
        },
        columns: [
            {"data":'cuenta', orderable: false},
            {"data":'nombre'},
            {"data": function (row, type, set){
                return `<div id="presupuesto_${row.id}" ondblclick="focusPresupuesto(${row.id}, ${row.presupuesto})">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.presupuesto).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
            {"data":'diferencia', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){
                return `<div id="enero_${row.id}" ondblclick="focusMesPresupuesto(${row.id}, ${row.enero}, 0)">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.enero).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){
                return `<div id="febrero_${row.id}" ondblclick="focusMesPresupuesto(${row.id}, ${row.febrero}, 1)">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.febrero).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){
                return `<div id="marzo_${row.id}" ondblclick="focusMesPresupuesto(${row.id}, ${row.marzo}, 2)">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.marzo).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){
                return `<div id="abril_${row.id}" ondblclick="focusMesPresupuesto(${row.id}, ${row.abril}, 3)">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.abril).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){
                return `<div id="mayo_${row.id}" ondblclick="focusMesPresupuesto(${row.id}, ${row.mayo}, 4)">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.mayo).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){
                return `<div id="junio_${row.id}" ondblclick="focusMesPresupuesto(${row.id}, ${row.junio}, 5)">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.junio).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){
                return `<div id="julio_${row.id}" ondblclick="focusMesPresupuesto(${row.id}, ${row.julio}, 6)">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.julio).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){
                return `<div id="agosto_${row.id}" ondblclick="focusMesPresupuesto(${row.id}, ${row.agosto}, 7)">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.agosto).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){
                return `<div id="septiembre_${row.id}" ondblclick="focusMesPresupuesto(${row.id}, ${row.septiembre}, 8)">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.septiembre).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){
                return `<div id="octubre_${row.id}" ondblclick="focusMesPresupuesto(${row.id}, ${row.octubre}, 9)">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.octubre).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){
                return `<div id="noviembre_${row.id}" ondblclick="focusMesPresupuesto(${row.id}, ${row.noviembre}, 10)">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.noviembre).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){
                return `<div id="diciembre_${row.id}" ondblclick="focusMesPresupuesto(${row.id}, ${row.diciembre}, 11)">
                    ${new Intl.NumberFormat("ja-JP", {minimumFractionDigits: 2}).format(parseFloat(row.diciembre).toFixed(2))}
                </div>`;
            }, className: 'dt-body-right'},
        ]
    });

    $("#periodo_presupuesto").on('change', function(){
        reloadPresupuesto();
    });

    $("#tipo_presupuesto").on('change', function(){
        reloadPresupuesto();
    });

    reloadPresupuesto();
}

function reloadPresupuesto () {
    $("#div-valor_presupuesto").hide();
    
    $("#generarPresupuesto").hide();
    $("#generarPresupuestoLoading").show();
    
    if ($("#id_presupuesto_up").val()) {
        $("#div-buscar_presupuesto").hide();
    }

    presupuesto_table.ajax.reload(function(res) {
        if (!res.data.length) {
            $("#generarPresupuesto").show();
            $("#generarPresupuestoLoading").hide();
        } else {
            $("#div-buscar_presupuesto").show();
            $("#generarPresupuesto").hide();
            $("#generarPresupuestoLoading").hide();
        }

        if (res.id_presupuesto) {
            $("#id_presupuesto_up").val(res.id_presupuesto);
        } else {
            $("#id_presupuesto_up").val();
        }

        if (focusNextRow) {
            var valor = 0;
            var mesNuevo = mesesPresupuesto[focusNextRow+1];
            if (focusNextRow < 11) {
                if (mesNuevo == 'febrero') valor = dataPresupuestoRow.febrero;
                if (mesNuevo == 'marzo') valor = dataPresupuestoRow.marzo;
                if (mesNuevo == 'abril') valor = dataPresupuestoRow.abril;
                if (mesNuevo == 'mayo') valor = dataPresupuestoRow.mayo;
                if (mesNuevo == 'junio') valor = dataPresupuestoRow.junio;
                if (mesNuevo == 'julio') valor = dataPresupuestoRow.julio;
                if (mesNuevo == 'agosto') valor = dataPresupuestoRow.agosto;
                if (mesNuevo == 'septiembre') valor = dataPresupuestoRow.septiembre;
                if (mesNuevo == 'octubre') valor = dataPresupuestoRow.octubre;
                if (mesNuevo == 'noviembre') valor = dataPresupuestoRow.noviembre;
                if (mesNuevo == 'diciembre') valor = dataPresupuestoRow.diciembre;
                focusMesPresupuesto(dataPresupuestoRow.id, valor, focusNextRow+1);
            }
            focusNextRow = false;
        }
    });
}

function focusPresupuesto (id, valor) {
    var input = document.createElement('input');

    input.setAttribute("type", "text");
    input.setAttribute("class", "form-control form-control-sm text-align-right");
    input.setAttribute("id", "presupuesto_input_"+id);
    input.setAttribute("value", new Intl.NumberFormat("ja-JP").format(valor));
    input.setAttribute("style", "padding-right: 5px !important;");
    input.setAttribute("onfocusout", "actualizarPresupuesto("+id+")");

    document.getElementById('presupuesto_'+id).innerHTML = "";
    document.getElementById('presupuesto_'+id).insertBefore(input, null);

    setTimeout(function(){
        $('#presupuesto_input_'+id).focus();
        $('#presupuesto_input_'+id).select();
    },10);

    inputAction = document.getElementById("presupuesto_input_"+id);

    inputAction.addEventListener('keydown', function(event) {
        console.log(event.keyCode);
    });
    inputAction.addEventListener('keyup', function(event) {
        if (event.keyCode >= 96 && event.keyCode <= 105 || event.keyCode == 110 || event.keyCode == 8 || event.keyCode == 46) {
            formatCurrency($(this));
        }
    });
    inputAction.addEventListener('blur', function() {
        formatCurrency($(this), "blur");
    });

    dataPresupuestoRow = getDataById(id, presupuesto_table);
}

function focusMesPresupuesto (id, valor, mes) {
    mesActual = mesesPresupuesto[mes];
    var idNewInput = mesActual+"_input_"+id;

    var input = document.createElement('input');
    
    input.setAttribute("type", "text");
    input.setAttribute("class", "form-control form-control-sm text-align-right");
    input.setAttribute("id", idNewInput);
    input.setAttribute("value", new Intl.NumberFormat("ja-JP").format(valor));
    input.setAttribute("style", "padding-right: 5px !important;");

    document.getElementById(mesActual+'_'+id).innerHTML = "";
    document.getElementById(mesActual+'_'+id).insertBefore(input, null);

    setTimeout(function(){
        $('#'+idNewInput).focus();
        $('#'+idNewInput).select();
    },10);

    dataPresupuestoRow = getDataById(id, presupuesto_table);

    inputAction = document.getElementById(idNewInput);

    inputAction.addEventListener('keyup', function(event) {
        if (event.keyCode >= 96 && event.keyCode <= 105 || event.keyCode == 110 || event.keyCode == 8 || event.keyCode == 46) {
            formatCurrency($(this));
        }
    });
    inputAction.addEventListener('blur', function() {
        formatCurrency($(this), "blur");
    });
    inputAction.addEventListener('focusout', function() {
        formatCurrency($(this), "blur");
        actualizarMesActual(id);
    });
    inputAction.addEventListener('keydown', function(event) {
        if (event.keyCode == 13) {
            focusNextRow = mes;
            inputAction = document.getElementById(idNewInput);
            inputAction.removeEventListener('focusout', actualizarMesActual);
            actualizarMesActual(id);
            //FOCUS HERE
        }
    });
}

function actualizarMesActual (id) {
    var valorInput = stringToNumberFloat($("#"+mesActual+"_input_"+id).val());

    if (mesActual == 'enero') dataPresupuestoRow.enero = valorInput;
    if (mesActual == 'febrero') dataPresupuestoRow.febrero = valorInput;
    if (mesActual == 'marzo') dataPresupuestoRow.marzo = valorInput;
    if (mesActual == 'abril') dataPresupuestoRow.abril = valorInput;
    if (mesActual == 'mayo') dataPresupuestoRow.mayo = valorInput;
    if (mesActual == 'junio') dataPresupuestoRow.junio = valorInput;
    if (mesActual == 'julio') dataPresupuestoRow.julio = valorInput;
    if (mesActual == 'agosto') dataPresupuestoRow.agosto = valorInput;
    if (mesActual == 'septiembre') dataPresupuestoRow.septiembre = valorInput;
    if (mesActual == 'octubre') dataPresupuestoRow.octubre = valorInput;
    if (mesActual == 'noviembre') dataPresupuestoRow.noviembre = valorInput;
    if (mesActual == 'diciembre') dataPresupuestoRow.diciembre = valorInput;
    
    var totalMeses = sumaTotalDeYears(id);
    dataPresupuestoRow.diferencia = parseFloat(dataPresupuestoRow.presupuesto) - totalMeses;
    
    inputAction = document.getElementById(mesActual+"_input_"+id);

    inputAction.removeEventListener('keyup', formatCurrency);
    inputAction.removeEventListener('blur', formatCurrency);
    inputAction.removeEventListener('keydown', actualizarMesActual);
    inputAction.removeEventListener('focusout', actualizarMesActual);
    
    var clearInput = document.getElementById(mesActual+'_'+id);
    console.log(mesActual+'_'+id);
    if (clearInput) clearInput.innerHTML = new Intl.NumberFormat("ja-JP").format(valorInput);

    actualizarColumna();
}

function actualizarPresupuesto (id) {
    var valorInput = stringToNumberFloat($("#presupuesto_input_"+id).val());
    var totalMeses = sumaTotalDeYears(id);
    // dataPresupuestoRow
    Swal.fire({
        title: 'Repartir: '+$("#presupuesto_input_"+id).val()+' en los 12 meses?',
        type: 'warning',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Repartir!',
        reverseButtons: true,
    }).then((result) => {
        if (result.value){
            dataPresupuestoRow.presupuesto = valorInput;
            dataPresupuestoRow.diferencia = 0;
            dataPresupuestoRow.enero = valorInput / 12;
            dataPresupuestoRow.febrero = valorInput / 12;
            dataPresupuestoRow.marzo = valorInput / 12;
            dataPresupuestoRow.abril = valorInput / 12;
            dataPresupuestoRow.mayo = valorInput / 12;
            dataPresupuestoRow.junio = valorInput / 12;
            dataPresupuestoRow.julio = valorInput / 12;
            dataPresupuestoRow.agosto = valorInput / 12;
            dataPresupuestoRow.septiembre = valorInput / 12;
            dataPresupuestoRow.octubre = valorInput / 12;
            dataPresupuestoRow.noviembre = valorInput / 12;
            dataPresupuestoRow.diciembre = valorInput / 12;
        } else {
            dataPresupuestoRow.presupuesto = valorInput;
            dataPresupuestoRow.diferencia = valorInput - totalMeses;
        }

        inputAction = document.getElementById("presupuesto_input_"+id);
        inputAction.removeEventListener('keyup', formatCurrency);
        inputAction.removeEventListener('blur', formatCurrency);
        inputAction.removeEventListener('keydown', actualizarMesActual);
        inputAction.removeEventListener('focusout', actualizarMesActual);

        document.getElementById('presupuesto_'+id).innerHTML = new Intl.NumberFormat("ja-JP").format(valorInput);

        actualizarColumna();
    })
}

function searchPresupuesto (event) {
    if (event.keyCode == 20 || event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18) {
        return;
    }
    var botonPrecionado = event.key.length == 1 ? event.key : '';
    searchValuePresupuesto = $('#searchInputInmuebles').val();
    searchValuePresupuesto = searchValuePresupuesto+botonPrecionado;
    if(event.key == 'Backspace') searchValuePresupuesto = searchValuePresupuesto.slice(0, -1);
    if(presupuesto_table)presupuesto_table.context[0].jqXHR.abort();
    reloadPresupuesto();
}

function sumaTotalDeYears() {
    let data = dataPresupuestoRow;
    var suma = 0;

    suma+= parseFloat(data.enero) + parseFloat(data.febrero) + parseFloat(data.marzo) + parseFloat(data.abril) + parseFloat(data.mayo) + parseFloat(data.junio) + parseFloat(data.julio) + parseFloat(data.agosto) + parseFloat(data.septiembre) + parseFloat(data.octubre) + parseFloat(data.noviembre) + parseFloat(data.diciembre);
    return suma;
}

function actualizarColumna() {
    $.ajax({
        url: base_url + 'presupuesto',
        method: 'PUT',
        data: JSON.stringify(dataPresupuestoRow),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        reloadPresupuesto();
    }).fail((res) => {
        agregarToast('error', 'Error al actualizar presupuesto', '');
    });
}

$(document).on('click', '#generarPresupuesto', function () {
    $("#generarPresupuesto").hide();
    $("#generarPresupuestoLoading").show();

    let data = {
        periodo: $("#periodo_presupuesto").val(),
        tipo: $("#tipo_presupuesto").val()
    };

    $.ajax({
        url: base_url + 'presupuesto',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $("#generarPresupuesto").show();
        $("#generarPresupuestoLoading").hide();
        agregarToast('exito', 'Creación exitosa', 'Presupuesto generado con exito!', true);
        reloadPresupuesto();
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
        $("#generarPresupuesto").show();
        $("#generarPresupuestoLoading").hide();
    });
});

