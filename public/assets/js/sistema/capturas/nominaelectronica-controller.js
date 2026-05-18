var nomina_electronica_table = null;

function nominaelectronicaInit() {
    console.log('nominaelectronicaInit');

    initTablasNE();
    initCombosNE();
    initFechasNE();
}

function initTablasNE() {
    nomina_electronica_table = $('#nominaElectronicaTable').DataTable({
        pageLength: 15,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'nomina-electronica',
            data: function ( d ) {
                d.meses = $('#meses_nomina_electronica_filter').val();
            }
        },
        columns: [
            {
                "data": function (row, type, set){
                    if (row.electronica) {
                        return row.electronica.id;
                    }
                    return '';
                }
            },
            {"data":'numero_documento'},
            {"data":'nombre_completo'},
            {
                "data": function (row, type, set){
                    if (row.electronica) {
                        return row.electronica.cune;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if (row.electronica) {
                        return row.electronica.mes;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    var botonEnviarNE = ``;
                    if (row.estado != 0 && enviarNominaElectronica) {
                        botonEnviarNE=`
                        <span id="enviarne_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-primary enviar-ne" style="margin-bottom: 0rem !important; min-width: 50px;">Enviar Nomina Electronica</span>
                        <span id="enviandone_${row.id}" class="badge bg-gradient-primary" style="margin-bottom: 0rem !important; min-width: 50px; display: none;">
                            <b style="opacity: 0.3; text-transform: math-auto;">Enviar nomina electronica</b>
                            <i style="position: absolute; color: white; font-size: 15px; margin-left: -70px; margin-top: -2px;" class="fas fa-spinner fa-spin"></i>
                        </span>
                        `;
                    }
                    var botonDetalle = `
                        <span id="detalleenviarne_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success detalle-enviar-ne" style="margin-bottom: 0rem !important; min-width: 50px;">Ver detalle</span>
                        <span id="detallandoenviarne_${row.id}" class="badge bg-gradient-success" style="margin-bottom: 0rem !important; min-width: 50px; display: none;">
                            <b style="opacity: 0.3; text-transform: math-auto;">Ver detalle</b>
                            <i style="position: absolute; color: white; font-size: 15px; margin-left: -35px; margin-top: -2px;" class="fas fa-spinner fa-spin"></i>
                        </span>
                    `;
                    return `
                        ${botonEnviarNE}
                        ${botonDetalle}
                    `;
                }
            },
        ]
    });

    if (nomina_electronica_table) {
        nomina_electronica_table.on('click', '.enviar-ne', function() {
            const id = this.id.split('_')[1];

            $(`#enviarne_${id}`).hide();
            $(`#enviandone_${id}`).show();

            $('#causarNominaBtn').hide();
            $('#causarNominaLoading').show();

            // Llamada AJAX para causar nómina
            $.ajax({
                url: base_url + 'nomina-electronica',
                method: 'POST',
                data: JSON.stringify({
                    id_empleado: id,
                    fecha: $("#meses_nomina_electronica_filter").val()
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){
                    agregarToast('exito', 'Causación exitosa', 'Nómina causada con éxito!', true);
                    causar_nomina_table.ajax.reload();
                } else {
                    agregarToast('error', 'Causación errada', res.message);
                }
            }).fail((err) => {
                var mensaje = err.responseJSON.message;
                var errorsMsg = arreglarMensajeError(mensaje);
                agregarToast('error', 'Causación errada', errorsMsg);
            }).always(() => {
                $('#causarNominaBtn').show();
                $('#causarNominaLoading').hide();

                $(`#enviarne_${id}`).show();
                $(`#enviandone_${id}`).hide();
            });
        });
    }
}

function initCombosNE() {
    $(`#meses_nomina_electronica_filter`).select2({
        theme: 'bootstrap-5',
        delay: 250,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/causar-meses-combo',
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

function initFechasNE() {
    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = fecha.getMonth(); // 0-11
    const mesStr = (mes + 1).toString().padStart(2, '0'); // "06"
    const valor = `${anio}-${mesStr}`;
    const texto = `${anio} - ${meses[mes]}`;

    // Crear la opción y asignarla al select2
    const nuevaOpcion = new Option(texto, valor, false, false);
    $(`#meses_nomina_electronica_filter`).append(nuevaOpcion).val(valor).trigger('change'); 
}

$(document).on('change', '#meses_nomina_electronica_filter', function () {
    const meses = $('#meses_nomina_electronica_filter').val();
    if (meses) {
        nomina_electronica_table.ajax.reload();
    }
});