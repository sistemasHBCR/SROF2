

/**
 * Format value
 */

function formatCurrency(value) {
    return '$' + parseFloat(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}


/**
 *  Datatable
 */
$(document).ready(function () {
    var dateNow = $('#dateNow').val();
    var minYear = 2021;
    var yearNow = moment(dateNow, 'YYYY-MM-DD').year(); //2022
    var maxyear = moment().year();

    $('#tb-imports').DataTable({
        paging: true,
        ordering: false,
        searching: true,
        lengthChange: true,
        pageLength: 25,
        buttons: ['pageLength', 'colvis'],
        language: {
            paginate: {
                next: '<i class="fas fa-chevron-right"></i>',
                previous: '<i class="fas fa-chevron-left"></i>'
            }
        },
        order: [[0, 'asc']],
        // Personalización del DOM utilizando la opción 'dom'
        dom:
            '<"row mx-2"' +
            '<"col-md-2"<"me-3"l>>' +
            '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
            '>t' +
            '<"row mx-2"' +
            '<"col-sm-12 col-md-6"i>' +
            '<"col-sm-12 col-md-6"p>' +
            '>',

        // Función initComplete para manipular el DOM después de la inicialización
        initComplete: function () {
            // Definir la URL base y el año actual

            var selectHtml = `
            <div class="input-group date me-2" id="yearPicker" data-target-input="nearest">
                <input id="yearInput" name="year" type="text" class="form-control form-control-sm datetimepicker-input" data-target="#yearPicker"
                value="${yearNow}" readonly>
            <div class="input-group-append" data-target="#yearPicker" data-toggle="datepicker"></div>
            </div>
            <button id="filterButton" onclick="window.location.href='${logimportsUrl}?year=${yearNow}';" type="button" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-filter" style="margin-right: 5px;"></i>
                <span class="d-none d-md-inline">Filtrar</span>
            </button>`;

            // Agregar el HTML al DOM
            document.querySelector('.dt-buttons').innerHTML = selectHtml;
        }
    });


    //***PICKEAR ONLY YEAR***/
    $('#yearPicker').datepicker({
        format: "yyyy",
        viewMode: "years",
        minViewMode: "years",
        autoclose: true,
        startDate: new Date(minYear, 0, 1), // Define el primer día del año mínimo
        endDate: new Date(maxyear, 11, 31),  // Define el último día del año actual
        defaultViewDate: { year: yearNow }
    }).on('changeDate', function (e) {
        // Obtener el nuevo año seleccionado
        var selectedYear = e.date.getFullYear();
        // Actualizar el valor del input
        $('#yearInput').val(selectedYear);
        // Actualizar la URL del botón
        var url = new URL(logimportsUrl);
        url.searchParams.set('year', selectedYear);
        $('#filterButton').attr('onclick', `window.location.href='${url.href}';`);
    });

    /**
     *  MODAL
     */
    var infoCheck = $('#info-check');
    var infoParameter = $('#info-parameter');
    var idImport = $('#idimport');
    var radioMaintenance = $('input[name="maintenance"]');
    var radioFinance = $('input[name="finance"]');
    var txtMaintenance = $('#maintenance_description');
    var txtFinance = $('#finance_description');
    var btnMaintenance = $('#updatechkMaintenance');
    var btnFinance = $('#updatechkFinance');
    var selectresesidence = $("#select-res");
    var selectmetrics = $("#select-metrics");
    var cuadroMetricsMin = $("#cuadro-metricas-min");
    var cuadroMetricsMax = $("#cuadro-metricas-max");
    var metric_kwmin = '';
    var metric_aguamin = '';
    var metric_gasmin = '';
    var metric_kwmax = '';
    var metric_aguamax = '';
    var metric_gasmax = '';
    var crtlkwMin = $('#KwMin');
    var crtlaguaMin = $('#AguaMin');
    var crtlgasMin = $('#GasMin');
    var crtlkwMax = $('#KwMax');
    var crtlaguaMax = $('#AguaMax');
    var crtlgasMax = $('#GasMax');
    var btnrfparameters = $('#btn-refreshparameters');
    var btnreset = $('#btn-resetparameters');


    // Función para deshabilitar controles
    function limpiarControles() {
        radioMaintenance.prop('checked', false).prop('disabled', false);
        radioFinance.prop('checked', false).prop('disabled', false);
        txtMaintenance.val('').prop('readonly', false);
        txtFinance.val('').prop('readonly', false);
        btnMaintenance.show();
        btnFinance.show();

    }



    function disableControls() {
        radioMaintenance.prop('disabled', true);
        radioFinance.prop('disabled', true);
        txtMaintenance.prop('readonly', true);
        txtFinance.prop('readonly', true);
        btnMaintenance.hide();
        btnFinance.hide();
    }
    // Función para habilitar controles
    function enableControls() {
        radioMaintenance.prop('disabled', false);
        radioFinance.prop('disabled', false);
        txtMaintenance.prop('readonly', false);
        txtFinance.prop('readonly', false);
        btnMaintenance.show();
        btnFinance.show();
    }

    function EnableControlshasPermissions(permissions) {


        /*** MANTENIMIENTO */

        //1- DESHABILITAR/OCULTAR SEGUN PERMISOS

        // deshabilitar radioMaintenance si  no tiene permisos checkmaintenance o cancel
        if (permissions.checkmaintenance == false || permissions.cancel == false) {
            //deshabilitar radios
            radioMaintenance.prop('disabled', true);

            //habilitar segun permisos

            if (permissions.cancel == true) {
                $('input[name="maintenance"][value="canceled"]').prop('disabled', false);
            }

            if (permissions.checkmaintenance == true) {
                $('input[name="maintenance"][value="disapproved"]').prop('disabled', false);
                $('input[name="maintenance"][value="approved"]').prop('disabled', false);
            }
        }

        // deshabilitar txtMaintenance si no tiene ninguno de los permisos checkmaintenance y cancel
        if (permissions.checkmaintenance == false & permissions.cancel == false) {
            txtMaintenance.prop('readonly', true);
        }

        // Ocultar btnMaintenance si no tiene ninguno de los permisos checkmaintenance y cancel
        if (permissions.checkmaintenance == false & permissions.cancel == false) {
            btnMaintenance.hide();
        }

        //2- DESHABILITAR TODOS LOS RADIOS SI YA HAY VALOR ASIGNADO(PARA EVITAR QUE USUARIO NO VOLVER A CAMBIAR OPCION)
        if ($('input[name="maintenance"]:checked').val()) {
            radioMaintenance.prop('disabled', true);
        }

        /***CONTROLES FINANZA*/

        //1- DESHABILITAR/OCULTAR SEGUN PERMISOS

        // Deshabilitar radioFinance y txtFinance si no tiene permiso checkfinance
        if (permissions.checkfinance == false) {
            radioFinance.prop('disabled', true);
            txtFinance.prop('readonly', true);
        }
        // Ocultar btnFinance si no tiene permiso checkfinance
        if (permissions.checkfinance == false) {
            btnFinance.hide();
        }

        //2- DESHABILITAR RADIOS SI YA HAY VALOR ASIGNADO(NO VOLVER A CAMBIAR OPCION)
        if ($('input[name="finance"]:checked').val()) {
            radioFinance.prop('disabled', true);
        }

    }


    // Función para limpiar y configurar el div informativo
    function setupinfoCheck(alertClass, alertHeading, alertText) {
        infoCheck.removeClass('alert-warning alert-info alert-success alert-danger');
        infoCheck.addClass(alertClass);
        infoCheck.find('.alert-heading').text(alertHeading);
        infoCheck.find('span').text(alertText);
    }


    function obtenerNombreCompleto(persona) {
        if (persona && persona.name !== null) {
            if (persona.last_name !== null) {
                return persona.name + ' ' + persona.last_name;
            } else {
                return persona.name;
            }
        } else {
            return '';
        }
    }


    var before_maintenance = "";
    var before_finance = "";
    var typetemplate = "";
    var statusparameters = "";
    $('[data-bs-toggle="tooltip"]').tooltip();
    //leer datos de importacion en especifico
    $('button.data-import').click(function () {

        //**validar si permitir continuar**/
        typetemplate = $(this).attr('template');
        statusparameters = $(this).attr('statusparameters');
        if (typetemplate == 'onlyconsumption' && statusparameters == 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Validación no permitida',
                text: 'Aún no se puede visualizar esta importación debido a que se está trabajando sobre una plantilla de solo consumos/parametros. Los parámetros aún no se han asignado al periodo.',
                confirmButtonText: 'Aceptar'
            });
            return;
        }


        $.blockUI({
            message: '<div class="spinner-border text-white" role="status"></div>',
            css: {
                backgroundColor: 'transparent',
                border: 'none',
                padding: '15px',
                fontSize: '20px',
                'z-index': 9999,
            },
            overlayCSS: {
                opacity: 0.5,
            }
        });
        $.ajax({
            url: viewImportUrl,
            type: 'GET',
            data: { token: $(this).attr('token') },
            success: function (response) {
                $.unblockUI();
                moment.locale('es'); // Establece el idioma en español
                $('#tb-data tbody').empty();
                var nowversion = response.import[0].version;
                var highestversion = response.import[0].highestversion;
                var maintenanceAction = response.import[0].maintenance_action;
                var maintenanceUser = response.import[0].maintenance_approved_by;
                var maintenanceActionDate = (maintenanceUser && maintenanceUser !== null && response.import[0].maintenance_aproved_date) ? moment(response.import[0].maintenance_aproved_date).format('DD/MM/YYYY HH:mm') : '';
                var maintenanceUserName = (maintenanceUser && maintenanceUser !== null && maintenanceUser.name && maintenanceUser.last_name) ? obtenerNombreCompleto(maintenanceUser) : '';
                var financeAction = response.import[0].finance_action;
                var financeUser = response.import[0].finance_approved_by;
                var financeActionDate = (financeUser && financeUser !== null && response.import[0].finance_aproved_date) ? moment(response.import[0].finance_aproved_date).format('DD/MM/YYYY HH:mm') : '';
                var financeUserName = (financeUser && financeUser !== null && financeUser.name && financeUser.last_name) ? obtenerNombreCompleto(financeUser) : '';
                var permissions = response.permissions;
                metric_kwmin = response.metrics.kwmin;
                metric_aguamin = response.metrics.aguamin;
                metric_gasmin = response.metrics.gasmin;
                metric_kwmax = response.metrics.kwmax;
                metric_aguamax = response.metrics.aguamax;
                metric_gasmax = response.metrics.gasmax;

                //**Asignar valores Campos y elementos**/
                limpiarControles();
                idImport.val(response.import[0].id)
                if (maintenanceUserName && maintenanceActionDate) { $("#usermaintenance").text("Actualizado por " + maintenanceUserName + " (" + maintenanceActionDate + ")"); }
                else { $("#usermaintenance").text("N/A"); }
                if (financeUserName && financeActionDate) { $("#userfinance").text("Actualizado por " + financeUserName + " (" + financeActionDate + ")"); }
                else { $("#userfinance").text("N/A"); }
                $('input[name="maintenance"][value="' + response.import[0].maintenance_action + '"]').prop('checked', true);
                $('input[name="finance"][value="' + response.import[0].finance_action + '"]').prop('checked', true);
                before_maintenance = $('input[name="maintenance"]:checked').val();
                before_finance = $('input[name="finance"]:checked').val();
                txtMaintenance.val(response.import[0].maintenance_description);
                txtFinance.val(response.import[0].finance_description);



                //**Div informativos**/
                //++Validación/check
                // Caso: Versión no es la más actualizada
                if (nowversion < highestversion) {
                    setupinfoCheck('alert-warning', 'Solo lectura', `No es posible interactuar con importaciones de versiones anteriores del periodo. La importación más reciente es de la versión V${highestversion}`);
                    disableControls();
                }
                // Caso: Versión es reciente
                else {
                    if (maintenanceAction == null && financeAction == null || maintenanceAction == "approved" && financeAction == null || maintenanceAction == null && financeAction == "approved") {
                        setupinfoCheck('alert-info', 'En revisión', 'En espera de que los datos presentados hayan sido aprobados entre departamentos.');
                        EnableControlshasPermissions(permissions);
                    } else if (maintenanceAction == "canceled") {
                        setupinfoCheck('alert-danger', 'Solo lectura', 'No es posible interactuar con estos datos puesto que está cancelado por el usuario. En espera de una nueva versión para su revisión');
                        disableControls();
                    } else if (maintenanceAction == "disapproved" || financeAction == "disapproved") {
                        setupinfoCheck('alert-danger', 'Solo lectura', 'No es posible interactuar con esta versión puesto que no fue aprobado. En espera de una nueva versión para su revisión');
                        disableControls();
                    } else if (maintenanceAction == "approved" && financeAction == "approved") {
                        setupinfoCheck('alert-success', 'Solo lectura', 'Esta importación fue aprobada y el periodo correspondiente fue cerrado');
                        disableControls();
                    } else {

                    }
                }

                //**Apartado metricas de consumo**/
                response.data.forEach(function (optionData) {
                    var $option = $('<option></option>').val(optionData.residencia).text(optionData.residencia);
                    selectresesidence.append($option);
                });

                //**Datos de la tabla** 
                //texto titulo tabla
                $('#text-data').text("Periodo " + moment(response.import[0].period.start).format('MMMM') + " | importación V" + response.import[0].version + " | " + response.import[0].status.name);

                var headerIds = [];
                $('#tb-data thead th').each(function () {
                    var id = $(this).attr('id');
                    if (id) {
                        headerIds.push(id);
                    }
                });

                var tooltipAttributes = 'data-bs-toggle="tooltip" data-bs-offset="1,0" data-bs-placement="top" data-bs-custom-class="tooltip-secondary"';

                $.each(response.data, function (index, item) {
                    var row = '<tr>';
                    row += '<td>' + "<b>" + (index + 1) + "</b>" + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[0] + '">' + item.residencia + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[1] + '">' + item.room + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[2] + '">' + item.owner + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[3] + '">' + item.ocupacion + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[4] + '">' + item.kw + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[5] + '">' + item.agua + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[6] + '">' + item.gas + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[7] + '" ' + tooltipAttributes + ' data-bs-original-title="' + (item.calculo_total_kw !== undefined ? item.calculo_total_kw : 'Obtenido del archivo importado') + '">' + item.total_kw + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[8] + '" ' + tooltipAttributes + ' data-bs-original-title="' + (item.calculo_total_kwfee !== undefined ? item.calculo_total_kwfee : 'Obtenido del archivo importado') + '">' + item.total_kwfee + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[9] + '" ' + tooltipAttributes + ' data-bs-original-title="' + (item.calculo_total_gas !== undefined ? item.calculo_total_gas : 'Obtenido del archivo importado') + '">' + item.total_gas + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[10] + '" ' + tooltipAttributes + ' data-bs-original-title="' + (item.calculo_total_gasfee !== undefined ? item.calculo_total_gasfee : 'Obtenido del archivo importado') + '">' + item.total_gasfee + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[11] + '" ' + tooltipAttributes + ' data-bs-original-title="' + (item.calculo_total_agua !== undefined ? item.calculo_total_agua : 'Obtenido del archivo importado') + '">' + item.total_agua + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[12] + '" ' + tooltipAttributes + ' data-bs-original-title="' + (item.calculo_total_sewer !== undefined ? item.calculo_total_sewer : 'Obtenido del archivo importado') + '">' + item.total_sewer + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[13] + '" ' + tooltipAttributes + ' data-bs-original-title="' + (item.calculo_subtotal !== undefined ? item.calculo_subtotal : 'Obtenido del archivo importado') + '">' + item.subtotal + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[14] + '" ' + tooltipAttributes + ' data-bs-original-title="' + (item.calculo_tax !== undefined ? item.calculo_tax : 'Obtenido del archivo importado') + '">' + item.tax + '</td>';
                    row += '<td class="cellcolor" data-cell-id="' + item.id + '-' + headerIds[15] + '" ' + tooltipAttributes + ' data-bs-original-title="' + (item.calculo_total !== undefined ? item.calculo_total : 'Obtenido del archivo importado') + '">' + item.total + '</td>';
                    row += '</tr>';
                    $('#tb-data tbody').append(row);
                });


                //pinta las celdas que cumplan con las condiciones(PARAMETROS MANUALES)
                selectmetrics.trigger("change");
                // Agregar clase "cell-with-comment" a las celdas con comentarios
                $.each(response.hascomments, function (index, comment) {
                    var cellId = comment.row;
                    $('[data-cell-id="' + cellId + '"]').addClass('cell-with-comment');
                });
                // Agregar clase "cell-with-comment" a las celdas con comentarios
                $.each(response.hascomments, function (index, comment) {
                    var cellId = comment.row;
                    $('[data-cell-id="' + cellId + '"]').addClass('cell-with-comment');
                });
                // Re-inicializar popovers después de agregar las filas
                $('[data-bs-toggle="popover"]').popover();


                //+++abrir modal
                $('#dataUtilities').modal('show');
                // Inicializar tooltips después de añadir las filas
                $('#tb-data [data-bs-toggle="tooltip"]').tooltip();
                //inicializar datatable con scroll, 2msg de retraso para darle tiempo al DOM de generar datos del each anterior.
                setTimeout(function () {
                    var table = $('#tb-data').DataTable({
                        paging: false,
                        scrollY: 800,
                        scrollCollapse: true,
                        scroller: true,
                        scrollX: true,
                        sScrollXInner: "100%",
                        autoWidth: true,
                        fixedHeader: true
                    });

                    table.columns.adjust().draw();
                }, 200);

            },
            error: function (xhr, status, error) {
                $.unblockUI();
                toastr.error(xhr.responseJSON.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
        });
    });

    //destruir tabla
    $('button.btn-close').click(function () {
        // Destruir la instancia de DataTables
        var table = $('#tb-data').DataTable();
        if (table !== null) {
            table.destroy();
        }

        // Destruir la instancia de FixedColumns si está definida
        var fixedColumns = $('#tb-data').data('fixedColumns');
        if (typeof fixedColumns !== 'undefined') {
            fixedColumns.fnDestroy();
        }
    });


    /***CHECK IMPORT**/
    function makeImportCheckRequest(department, value, description) {
        var token = $('meta[name="csrf-token"]').attr('content');
        var requestData = {
            token: token,
            department: department,
            action: value,
            description: description,
            idimport: $('#idimport').val(),
            typetemplate: typetemplate,
            statusparameters: statusparameters
        };

        $.ajax({
            url: checkimportUrl,
            headers: { 'X-CSRF-TOKEN': token },
            type: 'PUT',
            data: requestData,
            success: function (response) {

                var nowversion = response.import.version;
                var highestversion = response.import.highestversion;
                var maintenanceAction = response.import.maintenance_action;
                var financeAction = response.import.finance_action;
                var permissions = response.permissions;

                //**Div informativo**/
                // Caso: Versión no es la más actualizada
                if (nowversion < highestversion) {
                    setupinfoCheck('alert-warning', 'Solo lectura', `No es posible interactuar con importaciones de versiones anteriores del periodo. La importación más reciente es de la versión V${highestversion}`);
                    disableControls();
                }
                // Caso: Versión es reciente
                else {
                    if (maintenanceAction == null && financeAction == null || maintenanceAction == "approved" && financeAction == null || maintenanceAction == null && financeAction == "approved") {
                        setupinfoCheck('alert-info', 'En revisión', 'En espera de que los datos presentados hayan sido aprobados entre departamentos.');
                        EnableControlshasPermissions(permissions);
                    } else if (maintenanceAction == "canceled") {
                        setupinfoCheck('alert-danger', 'Solo lectura', 'No es posible interactuar con estos datos puesto que está cancelado por el usuario. En espera de una nueva versión para su revisión');
                        disableControls();
                    } else if (maintenanceAction == "disapproved" || financeAction == "disapproved") {
                        setupinfoCheck('alert-danger', 'Solo lectura', 'No es posible interactuar con esta versión puesto que no fue aprobado. En espera de una nueva versión para su revisión');
                        disableControls();
                    } else if (maintenanceAction == "approved" && financeAction == "approved") {
                        setupinfoCheck('alert-success', 'Solo lectura', 'Esta importación fue aprobada y el periodo correspondiente fue cerrado');
                        disableControls();
                    } else {
                        // Manejar otros casos o condiciones según sea necesario
                    }
                }


                /**actualizar tabla**/
                var fila = $('tr[row="' + response.import.id + '"]');
                fila.find('.maintenanceApprovedBy').text(obtenerNombreCompleto(response.import.maintenanceApprovedBy));
                fila.find('.financeApprovedBy').text(obtenerNombreCompleto(response.import.financeApprovedBy));
                fila.find('.status').text(response.import.status.name);
                fila.find('.status').removeClass().addClass('status badge bg-label-' + response.import.status.class + ' me-1');

                $('#text-data').text("Periodo " + moment(response.period.start).format('MMMM') + " | importación V" + response.import.version + " | " + response.import.status.name);
                toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000, positionClass: 'toast-top-right' });
            },
            error: function (xhr, status, error) {
                toastr.error(xhr.responseJSON.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
        });
    }


    function mostrarConfirmacionValidacion(department, value, description) {
        Swal.fire({
            title: 'Confirmar para actualizar',
            text: '¿Estás seguro de validar esta opción?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            allowEnterKey: false,
            backdrop: 'static',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                makeImportCheckRequest(department, value, description)
            }
            else {
                if (department == "maintenance")
                    $('input[name="maintenance"][value="' + before_maintenance + '"]').prop('checked', true);
                if (department == "finance")
                    $('input[name="finance"][value="' + before_finance + '"]').prop('checked', true);
            }
        });
    }

    //check import
    $('#updatechkMaintenance').click(function () {
        var department = "maintenance";
        var maintenanceValue = $('input[name="maintenance"]:checked').val();
        var description = $('textarea#maintenance_description').val();
        mostrarConfirmacionValidacion(department, maintenanceValue, description);
    });

    $('#updatechkFinance').click(function () {
        var department = "finance";
        var financeValue = $('input[name="finance"]:checked').val();
        var description = $('textarea#finance_description').val();
        mostrarConfirmacionValidacion(department, financeValue, description);
    });

    //**FUNCIONALIDADES METRICAS**/

    function pintarCeldas(Kwmin, AguaMin, GasMin, kwMax, aguaMax, gasMax) {
        $('#tb-data tbody tr').each(function () {
            var kw = parseFloat($(this).find('td').eq(5).text());
            var agua = parseFloat($(this).find('td').eq(6).text());
            var gas = parseFloat($(this).find('td').eq(7).text());

            // Establecer el color y texto para la celda de kw
            $(this).find('td').eq(5).css({
                'background-color': kw < Kwmin ? '#fdee41' : (kw > kwMax ? '#ff6262' : ''),
                'color': kw < Kwmin || kw > kwMax ? '#ffffff' : '' // Texto blanco para los casos de mínimo o máximo
            });

            // Establecer el color y texto para la celda de agua
            $(this).find('td').eq(6).css({
                'background-color': agua < AguaMin ? '#fdee41' : (agua > aguaMax ? '#ff6262' : ''),
                'color': agua < AguaMin || agua > aguaMax ? '#ffffff' : '' // Texto blanco para los casos de mínimo o máximo
            });

            // Establecer el color y texto para la celda de gas
            $(this).find('td').eq(7).css({
                'background-color': gas < GasMin ? '#fdee41' : (gas > gasMax ? '#ff6262' : ''),
                'color': gas < GasMin || gas > gasMax ? '#ffffff' : '' // Texto blanco para los casos de mínimo o máximo
            });
        });
    }

    selectmetrics.on('change', function () {
        switch ($(this).val()) {
            case 'ninguno':
                cuadroMetricsMin.parents().filter('div').first().addClass('hidden');
                cuadroMetricsMax.parents().filter('div').first().addClass('hidden');
                btnrfparameters.parents().filter('div').first().addClass('hidden');
                btnreset.parents().filter('div').first().addClass('hidden');
                crtlkwMin.val("").prop('readonly', true).parents().filter('div').first().addClass('hidden');
                crtlaguaMin.val("").prop('readonly', true).parents().filter('div').first().addClass('hidden');
                crtlgasMin.val("").prop('readonly', true).parents().filter('div').first().addClass('hidden');
                crtlkwMax.val("").prop('readonly', true).parents().filter('div').first().addClass('hidden');
                crtlaguaMax.val("").prop('readonly', true).parents().filter('div').first().addClass('hidden');
                crtlgasMax.val("").prop('readonly', true).parents().filter('div').first().addClass('hidden');
                $('#tb-data tbody tr td').css('background-color', '').css('color', '#516377');
                break;
            case 'metricas_manuales':
                cuadroMetricsMin.parents().filter('div').first().removeClass('hidden');
                cuadroMetricsMax.parents().filter('div').first().removeClass('hidden');
                btnrfparameters.parents().filter('div').first().removeClass('hidden');
                btnreset.parents().filter('div').first().removeClass('hidden');
                crtlkwMin.val("").prop('readonly', false).parents().filter('div').first().removeClass('hidden');
                crtlaguaMin.val("").prop('readonly', false).parents().filter('div').first().removeClass('hidden');
                crtlgasMin.val("").prop('readonly', false).parents().filter('div').first().removeClass('hidden');
                crtlkwMax.val("").prop('readonly', false).parents().filter('div').first().removeClass('hidden');
                crtlaguaMax.val("").prop('readonly', false).parents().filter('div').first().removeClass('hidden');
                crtlgasMax.val("").prop('readonly', false).parents().filter('div').first().removeClass('hidden');
                btnreset.trigger("click");
                break;
            default:
        }
    });

    btnrfparameters.click(function () {
        var kwMin = parseFloat(crtlkwMin.val()) || metric_kwmin;
        var aguaMin = parseFloat(crtlaguaMin.val()) || metric_aguamin;
        var gasMin = parseFloat(crtlgasMin.val()) || metric_gasmin;
        var kwMax = parseFloat(crtlkwMax.val()) || metric_kwmax;
        var aguaMax = parseFloat(crtlaguaMax.val()) || metric_aguamax;
        var gasMax = parseFloat(crtlgasMax.val()) || metric_gasmax;

        // Limpiar colores anteriores
        $('#tb-data tbody tr td').css('background-color', '');

        // Llamar a la función con los nuevos valores
        pintarCeldas(kwMin, aguaMin, gasMin, kwMax, aguaMax, gasMax);
    });

    btnreset.click(function () {
        // Restablecer los valores originales de los inputs
        crtlkwMin.val(crtlkwMin.attr('original'));
        crtlaguaMin.val(crtlaguaMin.attr('original'));
        crtlgasMin.val(crtlgasMin.attr('original'));
        crtlkwMax.val(crtlkwMax.attr('original'));
        crtlaguaMax.val(crtlaguaMax.attr('original'));
        crtlgasMax.val(crtlgasMax.attr('original'));

        // Limpiar colores anteriores
        $('#tb-data tbody tr td').css('background-color', '').css('color', '#516377');

        // Llamar a la función con los valores originales
        var kwMin = parseFloat(crtlkwMin.attr('original'));
        var aguaMin = parseFloat(crtlaguaMin.attr('original'));
        var gasMin = parseFloat(crtlgasMin.attr('original'));
        var kwMax = parseFloat(crtlkwMax.attr('original'));
        var aguaMax = parseFloat(crtlaguaMax.attr('original'));
        var gasMax = parseFloat(crtlgasMax.attr('original'));

        pintarCeldas(kwMin, aguaMin, gasMin, kwMax, aguaMax, gasMax);
    });


    //**FUNCIONALIDADES MODAL COMMENTS**/
    //1. Al darle click en una celda se marcara: se registra que celda con variable: selectedCell.
    //2: Se deshabilita en click derecho el menu contextual del navegador si celda esta marcada, en su lugar abre modal comentario
    //2.1 Si no hay celda marcada(1), abre el menu contextual del navegador
    //3: Si modal comentario se abre: almacenar en variable isModalOpen valor true
    //....

    let selectedCell = null;
    let isModalOpen = false;

    //pintar celda clickeada
    $(document).on('click', '#tb-data td', function () {

        // Verificar si la celda clicada ya está resaltada
        if ($(this).hasClass('highlighted')) {
            // Si la celda ya está resaltada, eliminar la clase
            $(this).removeClass('highlighted');
            selectedCell = null;
        } else {
            // Si la celda no está resaltada, eliminar la clase de la celda previamente seleccionada
            $('#tb-data td.highlighted').removeClass('highlighted');
            // Agregar la clase a la celda clicada
            selectedCell = $(this);
            selectedCell.addClass('highlighted');
        }

    });

    // Manejo del clic derecho
    $(document).on('contextmenu', function (e) {
        if (selectedCell && !isModalOpen) {
            e.preventDefault(); // Evitar el menú contextual predeterminado
            $('#comment-modal .modal-body').empty(); // Limpiar contenido previo
            $('#comment-modal').modal('show'); // Mostrar el modal
        }
    });

    // Cambiar el estado de la variable isModalOpen al abrir y cerrar el modal
    $('#comment-modal').on('show.bs.modal', function () {
        isModalOpen = true;
    });

    $('#comment-modal').on('hide.bs.modal', function () {
        isModalOpen = false;
        selectedCell = null;
        $('#tb-data td.highlighted').removeClass('highlighted');
    });

    // limpiar la selección al cerrar el modal
    $('#comment-modal').on('hide.bs.modal', function () {
        selectedCell = null;
        $('#tb-data td.highlighted').removeClass('highlighted');
    });


    //Abrir modal, celdas que tienen ya comentario
    $(document).on('click', '.cell-with-comment', function () {

        var data = $(this).attr('data-cell-id');
        var parts = data.split('-');
        var row = parts[0];
        var column = parts[1];

        $.ajax({
            url: readcommentsIntableUrl,
            type: 'GET',
            data: { row: row, column: column },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                var modalContent = ''; // Inicializa modalContent
                var formattedDate = '';
                var name = '';
                var email = '';
                var avatarClass = '';
                var alias = '';
                // Itera sobre cada objeto en el array `response.data`
                $.each(response.data, function (index, item) {
                    $.each(item.comments, function (commentIndex, comment) {
                        let { date, dateinfo } = getDateInfo(comment.created_at, comment.updated_at);
                        formattedDate = moment(date).format('Do MMMM YYYY, h:mm A');
                        name = comment.user.name + ' ' + comment.user.last_name;
                        email = (comment.user.email) ? comment.user.email : '';
                        avatarClass = comment.user.avatar_class;
                        alias = comment.user.alias;
                        modalContent += `
                            <form>
                                <div class="card email-card-last border shadow-none">
                                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                        <div class="d-flex align-items-center mb-sm-0 mb-3">
                                         <!--- <img src="{{ asset('assets/img/avatars/1.png') }}" alt="user-avatar" class="flex-shrink-0 rounded-circle me-3" height="40" width="40">--->
                                          <div class="avatar me-2">
                                                <span class="avatar-initial rounded-circle bg-label-${avatarClass}">${alias}</span>
                                             </div>
                                            <div class="flex-grow-1 ms-1">
                                                <h6 class="m-0">${name}</h6>
                                                <small class="text-muted">${email}</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <p class="mb-0 me-3 text-muted">${formattedDate} ${dateinfo}</p>
                                            <div class="dropdown me-3">
                                                <button class="btn p-0" type="button" id="dropdownEmail${index}_${commentIndex}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded fs-4 lh-1"></i>
                                                </button>
                                                <div class="comment-edit dropdown-menu dropdown-menu-end" aria-labelledby="dropdownEmail${index}_${commentIndex}">
                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                        <i class="bx bx-message-edit me-1 scaleX-n1 scaleX-n1-rtl"></i>
                                                        <span class="align-middle">Editar</span>
                                                    </a>
                                                    <a class="comment-remove dropdown-item" href="javascript:void(0)">
                                                        <i class="bx bx-message-x me-1"></i>
                                                        <span class="align-middle">Eliminar</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="textarea form-control mb-2" rows="3" style="border: none;" readonly data-original="${comment.comment}" data-id='${comment.id}'>${comment.comment}</textarea>
                                        <div class="col-12 text-end" style="display: none;">
                                            <button type="button" class="btn btn-sm btn-primary me-1 comment-save">Guardar</button>
                                            <button type="button" class="btn btn-sm btn-danger comment-cancel">Cancelar</button>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                            </form>
                        `;
                    });
                });
                $('#comment-modal .modal-body').html(modalContent);
                $('#comment-modal').modal('show');
            },

            error: function (xhr, status, error) {
                toastr.error(xhr.responseJSON.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
        });

    });

    // Función para añadir nuevo comentario
    $(document).on('click', '.comment-add', function () {
        var $textarea = $("#textarea-newcomment");
        var $comentario = $textarea.val();
        var $data = selectedCell.attr('data-cell-id');
        var $parts = $data.split('-');
        var $row = $parts[0];
        var $column = $parts[1];

        $.ajax({
            url: newcommentIntableUrl,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { row: $row, column: $column, 'comment': $comentario },
            success: function (response) {
                var index = ($('.modal-body form').length - 1);
                var comment = response.data.comment;
                var data = response.data;
                var name = data.user.name + ' ' + data.user.last_name;
                var email = (data.user.email) ? data.user.email : '';
                var avatarClass = data.user.avatar_class;
                var alias = data.user.alias;
                let { date, dateinfo } = getDateInfo(comment.created_at, comment.updated_at);
                var formattedDate = moment(date).format('Do MMMM YYYY, h:mm A');
                var newForm = `<form>
                                <div class="card email-card-last border shadow-none">
                                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                        <div class="d-flex align-items-center mb-sm-0 mb-3">
                                         <!--- <img src="{{ asset('assets/img/avatars/1.png') }}" alt="user-avatar" class="flex-shrink-0 rounded-circle me-3" height="40" width="40">--->
                                          <div class="avatar me-2">
                                                <span class="avatar-initial rounded-circle bg-label-${avatarClass}">${alias}</span>
                                             </div>
                                            <div class="flex-grow-1 ms-1">
                                                <h6 class="m-0">${name}</h6>
                                                <small class="text-muted">${email}</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <p class="mb-0 me-3 text-muted">${formattedDate} ${dateinfo}</p>
                                            <div class="dropdown me-3">
                                                <button class="btn p-0" type="button" id="dropdownEmail${index}_${index}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded fs-4 lh-1"></i>
                                                </button>
                                                <div class="comment-edit dropdown-menu dropdown-menu-end" aria-labelledby="dropdownEmail${index}_${index}">
                                                    <a class="dropdown-item" href="javascript:void(0)">
                                                        <i class="bx bx-message-edit me-1 scaleX-n1 scaleX-n1-rtl"></i>
                                                        <span class="align-middle">Editar</span>
                                                    </a>
                                                    <a class="comment-remove dropdown-item" href="javascript:void(0)">
                                                        <i class="bx bx-message-x me-1"></i>
                                                        <span class="align-middle">Eliminar</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="textarea form-control mb-2" rows="3" style="border: none;" readonly data-original="${comment.comment}" data-id='${comment.id}'>${comment.comment}</textarea>
                                        <div class="col-12 text-end" style="display: none;">
                                            <button type="button" class="btn btn-sm btn-primary me-1 comment-save">Guardar</button>
                                            <button type="button" class="btn btn-sm btn-danger comment-cancel">Cancelar</button>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                            </form>`;

                $('#comment-modal .modal-body').append(newForm); // Añadir el nuevo formulario
                $textarea.val(''); // Resetear el valor del textarea

                // Verificar si la clase 'cell-with-comment' está presente
                if (!$(selectedCell).hasClass('cell-with-comment')) {
                    selectedCell.addClass('cell-with-comment');
                }
                toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000, positionClass: 'toast-top-right' });
            },
            error: function (xhr, status, error) {
                toastr.error(xhr.responseJSON.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
        });
    });


    // Función para eliminar el comentario
    $(document).on('click', '.comment-remove', function () {
        var $currentForm = $(this).closest('form'); // Almacenar el formulario actual
        var $card = $(this).closest('.card');
        var $textarea = $card.find('.textarea');
        var $id = $textarea.data('id');
        if (confirm('¿Estás seguro de que deseas eliminar este comentario?')) {
            $.ajax({
                url: destroycommentIntableUrl,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { 'id': $id },
                success: function (response) {
                    $currentForm.remove();
                    // Verificar si el modal está vacío y eliminar la clase 'cell-with-comment' de la celda resaltada
                    if ($('#comment-modal .modal-body').children().length === 0) {
                        if (selectedCell) {
                            selectedCell.removeClass('cell-with-comment');
                        }
                    }
                    toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000, positionClass: 'toast-top-right' });
                },
                error: function (xhr, status, error) {
                    toastr.error(xhr.responseJSON.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                }
            });
        }
    });

    // Función para editar el comentario
    $(document).on('click', '.comment-edit', function () {
        var $textarea = $(this).closest('.card').find('.textarea');
        var $buttons = $(this).closest('.card').find('.col-12.text-end');
        var $showmore = $(this).closest('.card').find('.showmore');
        $textarea.css('border', '').prop('readonly', false);
        $showmore.hide();
        $buttons.show();
    });

    // Función para cancelar la edición del comentario
    $(document).on('click', '.comment-cancel', function () {
        var $card = $(this).closest('.card');
        var $textarea = $card.find('.textarea');
        var $buttons = $card.find('.col-12.text-end');

        // Restaurar el comentario original
        $textarea.val($textarea.data('original'));

        // Ocultar el borde del textarea y los botones guardar/cancelar
        $textarea.css('border', 'none').prop('readonly', true);
        $buttons.hide();
    });

    // Función para guardar el comentario
    $(document).on('click', '.comment-save', function () {
        var $card = $(this).closest('.card');
        var $textarea = $card.find('.textarea');
        var $id = $textarea.data('id');
        var $buttons = $card.find('.col-12.text-end');
        var $comentario = $textarea.val();
        // Ocultar el borde del textarea y los botones guardar/cancelar
        $textarea.css('border', 'none').prop('readonly', true);
        $buttons.hide();

        $.ajax({
            url: savecommentIntableUrl,
            type: 'PUT',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { id: $id, 'comment': $comentario },
            success: function (response) {
                $textarea.data('original', $comentario);
                if (response.error) {
                    $textarea.val($textarea.data('original'));
                    toastr.error(response.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                } else {
                    toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                }
            },

            error: function (xhr, status, error) {
                //si falla, comentario original nuevamente.
                $textarea.val($textarea.data('original'));
                toastr.error(xhr.responseJSON.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
        });
    });

    // Función para calcular la diferencia en minutos y determinar la fecha y la información
    function getDateInfo(created_at, updated_at) {
        let date;
        let dateinfo = '';

        if (updated_at) {
            // Calcular la diferencia en minutos
            let diffMinutes = (new Date(updated_at) - new Date(created_at)) / (1000 * 60); // Convertir a minutos

            // Comparar la diferencia con 3 minutos
            if (diffMinutes > 3) {
                dateinfo = '(actualizado)';
                date = updated_at;
            } else {
                dateinfo = '';
                date = created_at;
            }
        } else {
            // Si updated_at no existe, usar created_at
            dateinfo = '';
            date = created_at;
        }

        return { date, dateinfo };
    }
});