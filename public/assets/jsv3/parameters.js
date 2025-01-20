'use strict';
// Variables globales para la instancias
var dt_residence;
var dateNow = $('#dateNow').val();
var minYear = 2023;
var yearNow = moment(dateNow, 'YYYY-MM-DD').year();
var maxyear = moment().year();
var yearpicker = $('#yearPicker');
//Declaración de variables cards periodos
var cardsperiods = $(".cardsperiods");
var contentparameters = $('.content-parameters');
var contentwithoutdata = $('.content-withoutdata');
var contentdataperiod = $('.content-dataperiod');
//Declaración de variables seccion costos/servicios
var cardsCosts = $('.cards-costs')
let currentCard = null;
let serviceName = null;
let serviceId = null;
var tc = $('#tc');
var tax = $('#tax');
var addcostServicelabel = $('#addCostServiceModalLabel');
var addcostService = $('#addCostServiceModal');
var calculateVolum = $('.calculate-volum');
var calculateCost = $('.calculate-cost');
var resultCost = $('.result-cost');
var editTcBtn = $('.edit-tc-btn');
var editTaxBtn = $('.edit-tax-btn');
var editVolumeBtn = $('.edit-volume-btn');
var editCalculateBtn = $('.edit-calculate-btn');
const numeralMasks = $('.numeral-mask');
// Declaración de variables seccion residencias
var residencesTariffInfo = $("#residencesTariffInfo");
var ModalResidence = $('#dataResidenceModal');
var ModalResidencettl = $("#dataResidenceTitle");
var btncloseMdlRes = $("#btncloseMdlRes");
var btnsaveinresidence = $('#btnsaveinresidence');
var btnsavegeneralres = $('#btnsavegeneralres');
var typeRate = $('#type_rate');
var txtlistcosts = $('#txtlistcosts');
var listcosts = $('#listcosts');
var cntfixedrate_value = $('.contentfixedrate_value');
var fixedrate_value = $('#fixedrate_value');
var txtConditionConsumption = $('.txt_condition_consumption');
var conditionConsumption = $('#condition_consumption');
var txtConditionValue = $('.txt_condition_value');
var conditionValue = $('#condition_value');
var enableCondition = $('#enable_condition');
var fixedrateValue = $("#fixedrate_value");
var cntlistresidences = $('.content-listresidences');
var selectlistresidences = $('#select_listresidences');
// Duplicacion de parametros
var btnduplicate = $("#duplicate");
var saveduplicatepar = $("#saveduplicatepar");
var checkduplicateParameters = $("#checkduplicateParameters ");
var parameters_listmonths = $("#parameters_listmonths");
var duplicateModal = $("#duplicateModal");
var duplicateModalTitle = $("#duplicateModalTitle");
var month_origenperiod = $("#month_origenperiod");
var year_origenoeriod = $("#year_origenoeriod");
var month_transperiod = $("#month_transperiod");
var year_transperiod = $("#year_transperiod");
// --------------------------------------------------------------------
// *DEFINIENDO METODOS

selectlistresidences.selectpicker();

// Mask numeral
function applyMask() {formulaUsage
    $('.mask-money').maskMoney();
}
// Función para habilitar el input
function enableInput(input, button) {
    input.prop('disabled', false);
    button.html('<i class="bx bx-save"></i>');
}
// Función para deshabilitar el input
function disableInput(input, button, status = '') {
    if (input.val().trim() === '') {
        alert('El campo no puede estar vacío.');
        return;
    }
    input.prop('disabled', true);

    if (status === 'in-progress') {
        button.html(`
            <div class="spinner-border spinner-border-sm text-secondary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `);
    } else if (status == 'success') {
        // Reset to "edit" icon if status is not "in-progress"
        button.html('<i class="bx bx-edit"></i>');
    }
    else if (status == 'error') {
        button.html('<i class="bx bx-edit"></i>');
    }
}
// Función para verificar si hay errores en la expresión
function validateExpression(expression, period) {
    return new Promise((resolve, reject) => {
        if (!expression.trim()) {
            reject("Error: El campo no puede estar vacío.");
            return;
        }

        // Verificar si la expresión contiene códigos
        const hasCodes = /\(([^)]+)\)/.test(expression);

        if (!hasCodes) {
            // Evaluar la expresión directamente en JavaScript
            try {
                const result = eval(expression);
                resolve(result);
            } catch (e) {
                reject("Error: Sintaxis incorrecta en la expresión.");
            }
        } else {
            // Enviar la expresión al controlador para validación y cálculo
            $.ajax({
                url: find_costbycode, // Ruta en tu servidor Laravel
                type: 'POST',
                data: { expression: expression, period: period },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (!response.valid) {
                        reject(response.error);
                    } else {
                        resolve(response.result);
                    }
                },
                error: function (xhr, status, error) {
                    reject(`Error al evaluar la expresión.`);
                }
            });
        }
    });
}
//Recalcular todos los result h5 de los cards, de los elementos activos
function recalculateAllResults(period) {
    // Array para almacenar los servicios con sus respectivos serviceId y serviceCode
    var servicesData = [];
    // Buscar todas las tarjetas
    $('.cards-costs').each(function () {
        // Buscar el item de paginación activo dentro de la tarjeta
        var activePage = $(this).find('.pagination .page-item.active');

        // Si hay un item activo
        if (activePage.length > 0) {
            // Obtener el valor de data-serviceid y data-servicecode del item activo
            var serviceId = activePage.find('a').data('serviceid');
            var serviceCode = activePage.find('a').data('servicecode');

            // Crear un objeto con serviceId y serviceCode y agregarlo al array
            servicesData.push({ serviceId: serviceId, serviceCode: serviceCode });
        }
    });

    $.ajax({
        url: load_resultcosts,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'GET',
        data: {
            period: period,
            services: servicesData // Enviamos todos los servicios como un array
        },
        success: function (response) {
            // Asegurarse de que la respuesta es un arreglo
            if (Array.isArray(response)) {
                // Recorrer todos los servicios en la respuesta
                response.forEach(function (serviceData) {
                    // Buscar la tarjeta correspondiente con el service_id y el code (serviceCode)
                    $('.cards-costs').each(function () {
                        var activePage = $(this).find('.pagination .page-item.active');

                        // Verificar si el servicio actual coincide con el serviceId y serviceCode de la tarjeta activa
                        var serviceId = activePage.find('a').data('serviceid');
                        var serviceCode = activePage.find('a').data('servicecode');

                        // Si encontramos la tarjeta activa con el mismo serviceId y serviceCode
                        if (serviceId == serviceData.service_id && serviceCode == serviceData.code) {
                            // Actualizar el costo en el h5 correspondiente
                            $(this).find('.result-cost').text('$' + serviceData.cost); // Actualiza el h5 con el costo
                        }
                    });
                });
            } else {

            }
        },
        error: function (xhr, status, error) {
            toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
        }
    });

}
//funcion para mostrar/ocultar columnsa de la tabla, segun el servicio elejido en el radio input.
function SwitchService(valorSeleccionado) {
    dt_residence
        .column(1)  // Asumiendo que "servicename" está en la columna 2 (índice 2)
        .search(valorSeleccionado) // Filtramos por el nombre del servicio
        .draw(); // Redibujamos la tabla para aplicar el filtro

    // Mostrar solo los elementos que coinciden con el servicio seleccionado
    $('#residencesTariffInfo li').each(function () {
        if ($(this).attr('data-service') === valorSeleccionado) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}
// Función para formatear valor numerico
function formatNumber(value) {
    // Validar si es un número
    if (isNaN(value)) {
        return 'El valor no es un número';
    }

    // Formatear el número
    return parseFloat(value).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

}
// Función para generar el nuevo item de paginación
let nextServiceCode = '';
function generatePaginationItem(currentCard, serviceId) {
    // Paso 1: Encuentra todos los elementos page-item, excluyendo 'prev' y 'next'
    var pageItems = currentCard.find('.pagination .page-item').not('.prev, .next');

    // Paso 2: Obtener el último código de servicio de la lista de paginación
    var lastPageItem = pageItems.last();
    var lastServiceCode = lastPageItem.find('.page-link').data('servicecode');  // Ejemplo: 'a3'

    if (lastServiceCode) {
        // Paso 3: Extraer el prefijo (alfabético) y el número del código de servicio (Ejemplo: 'a' y '3')
        var prefix = lastServiceCode.match(/[a-zA-Z]+/)[0];  // 'a'
        var number = parseInt(lastServiceCode.match(/\d+/)[0]);  // '3'

        // Paso 4: Incrementar el número
        var nextNumber = number + 1;

        // Crear el siguiente código de servicio
        nextServiceCode = prefix + nextNumber;
    }

    // Paso 5: El nuevo número de la página será el siguiente número en la secuencia de paginación
    var newPageNumber = pageItems.length + 1;

    // Paso 6: Crear el nuevo item de paginación
    var newPageItem = `
        <li class="page-item" data-page="${newPageNumber}">
            <a class="page-link" data-serviceid="${serviceId}" data-servicecode="${nextServiceCode}" href="javascript:void(0);">
                ${newPageNumber}
            </a>
        </li>`;

    // Paso 7: Agregar el nuevo elemento de paginación al final de la lista
    currentCard.find('.pagination').append(newPageItem);

    // Paso 8: Desmarcar la página actual activa
    // pageItems.removeClass('active');

    // Paso 9: Marcar el nuevo item como 'active'
    //  currentCard.find('.pagination .page-item').last().addClass('active');
}
// Funcion generar cards costo - servicios
function htmlCardServices(services) {
    let html = '';
    html += '<div class="row mb-3">';

    Object.keys(services).forEach((key, index) => {
        var serviceArray = services[key];
        var numOfPaginationItems = serviceArray.length;
        var rows = Array.from({ length: numOfPaginationItems }, (_, i) => i + 1);

        if (serviceArray.length > 0) {
            var item = serviceArray[0];
            var colClass = index % 2 === 0 ? 'col-md mb-md-0 mb-2' : 'col-md';

            html += `<div class="${colClass} cards-costsservices">
                        <div class="cards-costs form-check custom-option custom-option-basic checked">
                            <label class="form-check-label custom-option-content" for="servicecost_${item.serviceid}">
                                <input name="servicecost" class="form-check-input" type="radio" value="${serviceArray[0].servicename}" id="servicecost_${serviceArray[0].serviceid}" 
                                ${index === 0 ? 'checked' : ''}>
                                <span class="custom-option-header">
                                    <span class="fw-medium">${item.servicename}</span>
                                    <span class="badge bg-label-${item.serviceclass}"><i class="${item.serviceicon}"></i></span>
                                </span>
                                <span class="custom-option-body">
                                    <label><small class="text-light fw-medium">Volumen / <code class="code-service" data-code="${item.servicecode}">codigo: ${item.servicecode}1</code></small></label>
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text"><i class='bx bxs-component'></i></span>
                                        <input type="text" value="${item.volume}" data-code="${item.servicecode}1" data-service="${item.serviceid}" class="form-control calculate-volum mask-money" step="0.01" min="1" id="${item.servicecode}1" placeholder="Parametro del servicio" disabled>
                                        <button class="btn btn-outline-secondary edit-volume-btn" type="submit"><i class='bx bx-edit'></i></button>
                                    </div>
                                    <label><small class="text-light fw-medium">Costo</small></label>
                                    <div class="input-group input-group-sm mb-2">
                                        <span class="input-group-text"><i class='bx bx-dollar-circle'></i></span>
                                        <input type="text" value="${item.cost_formula}" data-code="${item.servicecode}1" data-service="${item.serviceid}" class="form-control calculate-cost" placeholder="Calculo del costo" disabled>
                                        <button class="btn btn-outline-secondary edit-calculate-btn" type="button"><i class='bx bx-edit'></i></button>
                                    </div>
                                    <div class="text-center">
                                        <h5 class="result-cost card-title mb-0 me-2">$${formatNumber(item.cost)}</h5>
                                        <small class="text-muted">Costo x volumen</small>
                                    </div>
                                    <span class="my-3 border-bottom d-block"></span>
                                    <span class="d-flex justify-content-between">
                                        <div>
                                            <a href="javascript:void(0)"><i class="bx bx-trash sm delete-cost"></i></a>
                                            <a class="me-2" href="javascript:void(0)"> <i class="bx bx-plus sm add-cost"></i></a>
                                        </div>
                                        <nav aria-label="Page navigation">
                                            <ul class="pagination pagination-sm mb-0">`;
            //****START Generar los elementos de paginación****
            /*if (numOfPaginationItems >= 4) {
                html +=
                    `<li class="page-item prev">
                                                <a class="page-link" href="javascript:void(0);">
                                                    <i class="tf-icon bx bx-chevrons-left"></i>
                                                </a>
                                            </li>`;
            }*/
            rows.forEach((row, rowIndex) => {
                // Asignación dinámica de los atributos data-*
                let activeClass = rowIndex === 0 ? 'active' : '';
                let dataServiceId = serviceArray[rowIndex].serviceid;
                let dataServiceCode = `${serviceArray[rowIndex].servicecode}${row}`;

                // Insertar un elemento de paginación con los atributos correspondientes
                html += `<li class="page-item ${activeClass}" data-page="${row}">
                                            <a class="page-link"  
                                            data-serviceid="${dataServiceId}"
                                            data-servicecode="${dataServiceCode}"
                                            href="javascript:void(0);">${row}</a>
                                        </li>`;
            });
            /*if (numOfPaginationItems >= 4) {
                html +=
                    `<li class="page-item next">
                        <a class="page-link" href="javascript:void(0);">
                            <i class="tf-icon bx bx-chevrons-right"></i>
                        </a>
                    </li>`;
            }*/
            //****END Generar los elementos de paginación****
            html +=
                `</ul>
                                                            </nav>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>`;
            // Cada dos elementos, cerrar el div de fila y abrir uno nuevo
            if (index % 2 === 1 || index === Object.keys(services).length - 1) {
                html += '</div>'; // Cierra el contenedor de cards
                if (index < Object.keys(services).length - 1) {
                    html += '<div class="row mb-3">'; // Abre un nuevo contenedor de cards
                }
            }
        }
    });

    html += '</div>'; // Cierra el contenedor principal
    return html;
}

function updateValidationIcon(validationId, status, message = '') {
    const $validationItem = $(validationId);
    const $icon = $validationItem.find('.icon');
    const $error = $validationItem.find('.error');

    switch (status) {
        case 'in-progress':
            $icon.html(`
                <div class="spinner-border spinner-border-sm text-secondary" role="status">
                    <span class="visually-hidden">Cargando..</span>
                </div>
            `);
            $error.text('');
            break;
        case 'success':
            $icon.html(`
                <i class="bx bx-check-circle text-success"></i>
            `);
            $error.text('');
            break;
        case 'read-success':
            $icon.html(`Aplicar`);
            $error.text('');
            break;
        case 'error':
            $icon.html(`
                <i class="bx bx-refresh text-warning"></i> Reintentar
            `);
            $error.text('Reintentar'); // Mostrar mensaje de error
            break;
        default:
            console.warn('Estado de validación desconocido:', status);
    }
}


//***PICKEAR ONLY YEAR***/
// --------------------------------------------------------------------
yearpicker.datepicker({
    format: "yyyy",
    viewMode: "years",
    minViewMode: "years",
    autoclose: true,
    startDate: new Date(minYear, 0, 1), // Define el primer día del año mínimo
    endDate: new Date(maxyear, 11, 31),  // Define el último día del año actual
    defaultViewDate: { year: yearNow }
});


//***COMPARTAMIENTO CARDS PERIODS***/
// --------------------------------------------------------------------
// Variables globales
var period = '';
var periodmonth = '';
var periodyear = '';

// Función para cambiar el color de fondo de las tarjetas
function setCardBackgroundColor(card, color) {
    card.css({ backgroundColor: color });
}

// Función para deshabilitar o habilitar clics en las tarjetas
function toggleCardClicks(enable) {
    cardsperiods.css('pointer-events', enable ? 'auto' : 'none');
}

// Función para mostrar mensaje de alerta
function showAlert(message) {
    alert(message);
}

// Función para bloquear y desbloquear el elemento objetivo
function toggleBlockElement(element, block) {
    if (block) {
        element.block({
            message: '<div class="sk-fold sk-primary"><div class="sk-fold-cube"></div><div class="sk-fold-cube"></div><div class="sk-fold-cube"></div><div class="sk-fold-cube"></div></div><h5>LOADING...</h5>',
            css: { backgroundColor: 'transparent', border: '0' },
            overlayCSS: { backgroundColor: $('html').hasClass('dark-style') ? '#000' : '#fff', opacity: 0.55 }
        });
    } else {
        element.unblock();
    }
}

// Función para manejar la selección de tarjetas y la carga de datos
function handleCardSelection($this) {
    let bcgcolor = $('html').hasClass('dark-style') ? '#2e363d' : '#c7cfd6';
    setCardBackgroundColor(cardsperiods, '');
    setCardBackgroundColor($this, bcgcolor);

    // Asignar valor a variables
    period = $this.data('period');
    periodmonth = $this.data('month');
    periodyear = $this.data('year');

    var statusId = $this.data('status');

    if (statusId === '1') {
        showAlert('Este card no se puede seleccionar.');
    } else {
        toggleCardClicks(false);
        var $targetElement = contentparameters;
        toggleBlockElement($targetElement, true);

        setTimeout(function () {
            toggleBlockElement($targetElement, false);

            if ($targetElement.find('.card-alert').length) {
                $targetElement.find('.card-alert').html('<div class="alert alert-solid-success alert-dismissible fade show" role="alert"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button><span class="fw-medium">Carga finalizada. </span> Sección parametros.</div>').show();
            }

            setTimeout(function () {
                $targetElement.find('.card-alert').hide("slow");
            }, 1500);

            toggleCardClicks(true);

            if (!contentwithoutdata.hasClass('sr-only')) {
                contentwithoutdata.addClass('sr-only');
            }
            if (contentdataperiod.hasClass('sr-only')) {
                contentdataperiod.removeClass('sr-only');
            }

            if (statusId == 2) { btnduplicate.show(); } else { btnduplicate.hide(); }
            tc.val(0).prop('disabled', true);
            tax.val(0).prop('disabled', true);
            calculateVolum.val(0).prop('disabled', true);
            calculateCost.val(0).prop('disabled', true);
            resultCost.text('$0.00');
            editTcBtn.html('<i class="bx bx-edit"></i>');
            editTaxBtn.html('<i class="bx bx-edit"></i>');
            editVolumeBtn.html('<i class="bx bx-edit"></i>');
            editCalculateBtn.html('<i class="bx bx-edit"></i>');

            $.ajax({
                url: load_costs_inperiod,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'GET',
                data: { period: period },
                success: function (res) {
                    var currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('period', period);
                    window.history.pushState({}, '', currentUrl);

                    tc.val(res.datatc && res.datatc.length > 0 ? formatNumber(res.datatc[0].tc) : 0);
                    tax.val(res.datatc && res.datatc.length > 0 ? formatNumber(res.datatc[0].tax) : 0);

                    residencesTariffInfo.empty();
                    $.each(res.infos, function (index, message) {
                        $('<li>')
                            .text(message.message)
                            .attr('data-service', message.service)
                            .css('color', $('html').hasClass('dark-style') ? '#fff' : '#1d3a5b')
                            .appendTo('#residencesTariffInfo');
                    });

                    if ($.fn.dataTable.isDataTable('#tb-residence')) {
                        dt_residence.clear().destroy();
                    }
                    $('#tb-residence tbody').empty();
                    $.each(res.residences, function (index, item) {
                        var status = (item.status == 0) ? '<p class="text-danger"></i>Aún sin registrar <i class="bx bx-info-circle"></p>' :
                            '<p class="text-success"></i>Registrado <i class="bx bx-check-circle"></p>';

                        var tarifaContenido = (item.rateid) ?
                            `<div class="me-2">
                                <p class="mb-0 lh-1">${item.ratename}</p>
                                ${(item.rateid == 1)
                                ? `<small class="text-muted">Costo ${item.codevolume.replace(/[a-zA-Z]/, '')}</small>`
                                : (item.rateid == 2
                                    ? `<small class="text-muted">${item.fixedrate} USD</small>`
                                    : 'n/a')
                            }
                            </div>` : 'n/a';

                        var isconditional = (item.isconditional == 1 && item.status == 1) ?
                            `<div class="me-2">
                                    <p class="mb-0 lh-1">${item.ifislower} ${item.servicevolume}</p>
                                    <small class="text-muted">Tarifa fija:  $${item.ratevaluenew} USD</small>
                                </div>` : 'n/a';

                        var row = `
                                <tr data-id="${item.id}">
                                <td>${item.residencename}</td>
                                <td><div class="me-2"><p class="mb-0 lh-1 fw-bold"><b>${item.servicename}</p></div></td>
                                <td>${status}</td>
                                <td>${tarifaContenido}</td>
                                <td>${isconditional}</td>
                                <td><button type="button" data-residence="${item.residencename}" data-residenceid="${item.residenceid}" class="edit-residence btn btn-outline-primary btn-sm"><i class="fa-solid fa-file-signature"></i></button></td>
                            </tr>`;
                        $('#tb-residence tbody').append(row);
                    });
                    dt_residence = $('#tb-residence').DataTable({
                        paging: true,
                        ordering: true,
                        searching: true,
                        lengthChange: true,
                        pageLength: 100,
                        language: {
                            paginate: {
                                next: '<i class="fas fa-chevron-right"></i>',
                                previous: '<i class="fas fa-chevron-left"></i>'
                            }
                        },
                        dom:
                            '<"row mx-2"' +
                            '<"col-md-2"<"me-3"l>>' +
                            '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
                            '>t' +
                            '<"row mx-2"' +
                            '<"col-sm-12 col-md-6"i>' +
                            '<"col-sm-12 col-md-6"p>' +
                            '>',
                        buttons: [
                            {
                                text: '<i class="fa-solid fa-file-invoice"></i>',
                                className: 'edit-someresidences btn btn-secondary btn-sm mx-2',
                                action: function (e, dt, button, config) { }
                            }
                        ],
                        columnDefs: [{
                            targets: [4],
                            orderable: false
                        }]
                    });

                    $('#CardsServices').empty();
                    if (res.data && (Array.isArray(res.data) ? res.data.length > 0 : typeof res.data === 'object' && Object.keys(res.data).length > 0)) {
                        const keys = Object.keys(res.data);
                        const firstCode = keys[0];
                        const firstItem = res.data[firstCode][0];
                        $('#CardsServices').html(htmlCardServices(res.data));
                        SwitchService(firstItem.servicename);
                        applyMask();
                    } else {
                        $('#CardsServices').html('<p>No hubo ningún servicio activo en la apertura del periodo</p>');
                    }

                },
                error: function (xhr, status, error) {
                    toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                }
            });
        }, 2500);
    }
}

// Evento de clic en las tarjetas
cardsperiods.on('click', function (e) {
    e.preventDefault();
    handleCardSelection($(this));
});

// Función para simular clic en la tarjeta correspondiente al cargar la página
function autoClickCard() {
    var urlParams = new URLSearchParams(window.location.search);
    var periodParam = urlParams.get('period');

    if (periodParam) {
        var $card = cardsperiods.filter(`[data-period="${periodParam}"]`);
        if ($card.length) {
            handleCardSelection($card);
        }
    }
}

// Llamar a la función autoClickCard al cargar la página
$(document).ready(function () {
    autoClickCard();
});

//**DUPLICACION DE TARIFAS***/
// --------------------------------------------------------------------
//abrir modal
btnduplicate.on('click', function (event) {
    parameters_listmonths.val("");

    saveduplicatepar.prop('disabled', true);
    checkduplicateParameters.prop({
        'checked': false,
        'disabled': true
    });
    //asignar cards
    month_origenperiod.text("Seleccionar");
    year_origenoeriod.text("*");
    month_transperiod.text(periodmonth);
    year_transperiod.text(periodyear)

    duplicateModal.modal("show");
    duplicateModalTitle.text("Duplicación de parametros");
});

parameters_listmonths.on('change', function (event) {
    var opcionSeleccionada = $(this).find('option:selected');
    var value = opcionSeleccionada.val().trim();
    var month = opcionSeleccionada.text().trim();
    var year = opcionSeleccionada.data('year');
    if (value == "") {
        saveduplicatepar.prop('disabled', true);
        checkduplicateParameters.prop({
            'checked': false,
            'disabled': true
        });
        month_origenperiod.text("Seleccionar");
        year_origenoeriod.text("*");
    }
    else {
        checkduplicateParameters.prop('disabled', false);
        month_origenperiod.text(month);
        year_origenoeriod.text(year);
    }
});

//check confirmar
checkduplicateParameters.on('click', function (event) {
    var $checkbox = $('#checkduplicateParameters');
    saveduplicatepar.prop('disabled', !$checkbox.is(':checked'));
});

saveduplicatepar.on('click', function (event) {
    if (parameters_listmonths.val() === "") {
        alert("Por favor, selecciona un parametro mensual a duplicar en este periodo.");
        return;
    }
    $.ajax({
        url: duplicateparameters,
        type: 'POST',
        data: { checkduplicateParameters: checkduplicateParameters.prop('checked'), origenperiod: parameters_listmonths.val(), transperiod: period },
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        beforeSend: function () {
            saveduplicatepar.prop('disabled', true);
            // Mostrar el spinner de carga
            updateValidationIcon('#saveduplicatepar', 'in-progress');

        },
        success: function (res) {
            toastr.success(res.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            saveduplicatepar.prop('disabled', true);
            checkduplicateParameters.prop('disabled', true);
            $('div[data-period="' + period + '"] .parameterstatus').text(res.statusparameter.message);
            updateValidationIcon('#saveduplicatepar', 'read-success');
            setTimeout(function () {
                location.reload();
            }, 500);
        },
        error: function (res) {
            if (res.responseJSON && res.responseJSON.error) {
                toastr.error(res.responseJSON.error, { progressBar: true, showDuration: 1500, hideDuration: 1500 });
            } else {
                toastr.error('Ocurrió un error desconocido.', { progressBar: true, showDuration: 1500, hideDuration: 1500 });
            }
            saveduplicatepar.prop('disabled', false);
            updateValidationIcon('#saveduplicatepar', 'error');
            toastr.error('Ocurrió un error al realizar la solicitud.', { progressBar: true, showDuration: 1500, hideDuration: 1500 });
        }
    });
});


//***MODAL TARIFA POR RESIDENCIAS***/
// --------------------------------------------------------------------

//abrir modal parametros en residencia
var residenceid = '';
$(document).on('click', '.edit-residence', function () {
    var service = $('input[name=servicecost]:checked').val();
    var serviceid = $('input[name=servicecost]:checked').attr('id').split('_')[1];
    var residence = $(this).data('residence');
    residenceid = $(this).data('residenceid');
    $.ajax({
        url: load_databyresidence,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'GET',
        data: { service: service, serviceid: serviceid, residenceid: residenceid, period: period },
        success: function (res) {
            var rateid = res.datares[0].rate_id;
            var servicetf = res.datares[0].parameters_cost_id;
            var fixedRate_conditional = (res.datares[0].fixedrate_isconditional == 1) ? true : false;
            var consumptionlower_is = res.datares[0].consumptionlower_is;
            var flaterate_consumption_islower = res.datares[0].flaterate_consumption_islower;
            var fixedrate = res.datares[0].fixedrate_value;
            if (rateid == 2) {
                $('input[name="servicetf"]').prop('checked', false).trigger('change');
                typeRate.val(2).trigger('change');
                enableCondition.prop('checked', false).trigger('change');
                conditionConsumption.val("")
                conditionValue.val("")
                fixedrate_value.val(fixedrate);
            }
            else {
                $('input[name="servicetf"][value="' + servicetf + '"]').prop('checked', true).trigger('change');
                typeRate.val(1).trigger('change');
                enableCondition.prop('checked', fixedRate_conditional).trigger('change');
                conditionConsumption.val(consumptionlower_is)
                conditionValue.val(flaterate_consumption_islower)
                fixedrate_value.val("");
            }
            var costs = res.avaiblecosts;
            costs.forEach(function (item, index) {
                var row = $('<tr>');
                row.append($('<td>').text("Costo " + (index + 1))); // Nombre
                row.append($('<td>').text(item.volume_code)); // Código
                row.append($('<td>').text(item.volume)); // Volumen
                row.append($('<td>').text(item.formula ? item.formula : 'N/A')); // Fórmula
                row.append($('<td>').text(item.cost)); // Costo
                var radioButton = $('<input class="form-check-input" type="radio" name="servicetf">').val(item.id);
                console.log(item.id, servicetf);
                if (item.id == servicetf) {
                    radioButton.prop('checked', true).trigger('change');
                }
                var radioButtonCell = $('<td>').append(
                    $('<div class="form-check">').append(
                        radioButton,
                        $('<label class="form-check-label" for="tarifa' + index + '"></label>')
                    )
                );
                row.append(radioButtonCell);
                $('#listcosts tbody').append(row);
            });
        },
        error: function (xhr, status, error) {
            toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
        }
    });

    cntlistresidences.hide();
    btnsavegeneralres.hide();
    btnsaveinresidence.show();
    ModalResidencettl.html("Residencia " + residence + " | Calculo para la tarifa del serivicio <br>" + service);
    ModalResidence.modal({
        backdrop: 'static',
        keyboard: false
    }).modal('show');
});

//abrir modal parametros general
$(document).on('click', '.edit-someresidences', function () {
    var service = $('input[name=servicecost]:checked').val();
    var serviceid = $('input[name=servicecost]:checked').attr('id').split('_')[1];
    $.ajax({
        url: load_costs_inservice,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'GET',
        data: { serviceid: serviceid, period: period },
        success: function (res) {
            $('input[name="servicetf"]:first').prop('checked', true).trigger('change');
            typeRate.val(1).trigger('change');
            enableCondition.prop('checked', false).trigger('change');
            conditionConsumption.val("")
            conditionValue.val("")
            fixedrate_value.val("");
            var costs = res.avaiblecosts;
            costs.forEach(function (item, index) {
                var row = $('<tr>');
                row.append($('<td>').text("Costo " + (index + 1))); // Nombre
                row.append($('<td>').text(item.volume_code)); // Código
                row.append($('<td>').text(item.volume)); // Volumen
                row.append($('<td>').text(item.formula ? item.formula : 'N/A')); // Fórmula
                row.append($('<td>').text(item.cost)); // Costo
                var radioButton = $('<input class="form-check-input" type="radio" name="servicetf">').val(item.id);
                var radioButtonCell = $('<td>').append(
                    $('<div class="form-check">').append(
                        radioButton,
                        $('<label class="form-check-label" for="tarifa' + index + '"></label>')
                    )
                );
                row.append(radioButtonCell);
                $('#listcosts tbody').append(row);
            });
        },
        error: function (xhr, status, error) {
            toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
        }
    });

    cntlistresidences.show();
    btnsavegeneralres.show();
    btnsaveinresidence.hide();
    ModalResidencettl.html("Asignación de tarifa general | " + service);
    ModalResidence.modal({
        backdrop: 'static',
        keyboard: false
    }).modal('show');
});


//guardar en modal residencia determinada
btnsaveinresidence.on('click', function (e) {
    e.preventDefault();
    var serviceid = $('input[name=servicecost]:checked').attr('id').split('_')[1];
    var flaterateid = typeRate.val();
    var flaterate_value = $('input[name="servicetf"]:checked').val(); //tarifa consumo en tabla
    var fixedRate_conditional = enableCondition.is(':checked') ? 1 : 0; //tarifa condicional (?)
    var consumptionlower_is = conditionConsumption.val().replace(',', '').trim(); //si consumo es menor a
    var flaterate_consumption_islower = conditionValue.val().replace(',', '').trim(); //valor de la tarifa fija (condicional, si consumo es menor a)
    var fixedRateValue = fixedrate_value.val().replace(',', '').trim(); //valor de la tarifa fija

    if (flaterateid === '1') {
        if (!flaterate_value) {
            alert('Por favor, seleccione una tarifa en la tabla.');
            return false;
        }
        if (fixedRate_conditional === 1) {
            if (!consumptionlower_is || !flaterate_consumption_islower) {
                alert('La tarifa condicional está marcada. Por favor, llene los campos de "Si consumo es menor a" y "Tarifa fija de ($)".');
                return false;
            }
        }
    } else {
        if (!fixedRateValue) {
            alert('Por favor, ingrese un valor para la tarifa fija.');
            return false;
        }
    }



    $.ajax({
        url: sparres_utilities,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        data: {
            periodid: period,
            serviceid: serviceid,
            residenceid: residenceid,
            flaterateid: flaterateid,
            flaterate_value: flaterate_value,
            fixedRate_conditional: fixedRate_conditional,
            consumptionlower_is: consumptionlower_is,
            flaterate_consumption_islower: flaterate_consumption_islower,
            fixedRateValue: fixedRateValue
        },
        success: function (res) {
            //actualizar infos
            var service = $('input[name=servicecost]:checked').val();
            residencesTariffInfo.empty();


            residencesTariffInfo.empty();
            $.each(res.infos, function (index, message) {
                var listItem = $('<li>')
                    .text(message.message)
                    .attr('data-service', message.service)
                    .appendTo('#residencesTariffInfo');
                if (message.service === service) {
                    listItem.show(); // Muestra si coincide el servicio
                } else {
                    listItem.hide(); // Oculta si no coincide
                }
            });


            //actualizar fila datatable
            var item = res.residence;
            var $row = $('#tb-residence tbody tr[data-id="' + item.id + '"]'); // Buscar la fila con data-id específico

            if ($row.length > 0) {
                // Actualizar contenido de la fila existente
                $row.html(`
                        <td>${item.residencename}</td>
                        <td><div class="me-2"><p class="mb-0 lh-1 fw-bold"><b>${item.servicename}</p></div></td>
                        <td>${(item.status == 0) ?
                        '<p class="text-danger"></i>Aún sin registrar <i class="bx bx-info-circle"></p>' :
                        '<p class="text-success"></i>Registrado <i class="bx bx-check-circle"></p>'}</td>
                        <td>${item.rateid ?
                        `<div class="me-2">
                                <p class="mb-0 lh-1">${item.ratename}</p>
                                ${(item.rateid == 1) ?
                            `<small class="text-muted">Costo ${item.codevolume.replace(/[a-zA-Z]/, '')}</small>` :
                            (item.rateid == 2 ?
                                `<small class="text-muted">${item.fixedrate} USD</small>` : 'n/a')}
                            </div>` : 'n/a'}</td>
                        <td>${(item.isconditional == 1 && item.status == 1) ?
                        `<div class="me-2">
                                <p class="mb-0 lh-1">${item.ifislower} ${item.servicevolume}</p>
                                <small class="text-muted">Tarifa fija:  $${item.ratevaluenew} USD</small>
                            </div>` : 'n/a'}</td>
                        <td>
                            <button type="button" 
                                    data-residence="${item.residencename}" 
                                    data-residenceid="${item.residenceid}" 
                                    class="edit-residence btn btn-outline-primary btn-sm">
                                <i class="fa-solid fa-file-signature"></i>
                            </button>
                        </td>
                    `);
            }
            // Redibujar la tabla para reflejar cambios
            if (typeof dt_residence !== 'undefined') {
                dt_residence.draw(false); // Redibujar sin reiniciar la paginación
            }

            //actualizar div periodo
            $('div[data-period="' + period + '"] .parameterstatus').text(res.statusparameter.message);

            //mensaje
            toastr.options = {
                "positionClass": "toast-top-center",  // Centrado en la parte superior
                "progressBar": true,  // Muestra la barra de progreso
                "showDuration": 1000,  // Duración de la animación de entrada
                "hideDuration": 1000,  // Duración de la animación de salida
                "timeOut": 3000,  // El toast desaparecerá después de 3 segundos
            };
            toastr.success('Formulario enviado con éxito', null, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
        },
        error: function (xhr, status, error) {
            toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
        }
    });




});

btnsavegeneralres.on('click', function (e) {
    e.preventDefault();
    var serviceid = $('input[name=servicecost]:checked').attr('id').split('_')[1];
    var service = $('input[name=servicecost]:checked').val();
    var flaterateid = typeRate.val();
    var flaterate_value = $('input[name="servicetf"]:checked').val(); //tarifa consumo en tabla
    var fixedRate_conditional = enableCondition.is(':checked') ? 1 : 0; //tarifa condicional (?)
    var consumptionlower_is = conditionConsumption.val().replace(',', '').trim(); //si consumo es menor a
    var flaterate_consumption_islower = conditionValue.val().replace(',', '').trim(); //valor de la tarifa fija (condicional, si consumo es menor a)
    var fixedRateValue = fixedrate_value.val().replace(',', '').trim(); //valor de la tarifa fija

    if (flaterateid === '1') {
        if (!flaterate_value) {
            alert('Por favor, seleccione una tarifa en la tabla.');
            return false;
        }
        if (fixedRate_conditional === 1) {
            if (!consumptionlower_is || !flaterate_consumption_islower) {
                alert('La tarifa condicional está marcada. Por favor, llene los campos de "Si consumo es menor a" y "Tarifa fija de ($)".');
                return false;
            }
        }
    } else {
        if (!fixedRateValue) {
            alert('Por favor, ingrese un valor para la tarifa fija.');
            return false;
        }
    }

    var selectedResidences = $('#select_listresidences option:selected').map(function () {
        return $(this).attr('id'); // Obtener el id de cada opción seleccionada
    }).get();
    if (!selectedResidences || selectedResidences.length === 0) {
        alert('Por favor, seleccione al menos una residencia.');
        return false;
    }


    $.ajax({
        url: sparsomeres_utilities,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'POST',
        data: {
            periodid: period,
            service: service,
            serviceid: serviceid,
            flaterateid: flaterateid,
            flaterate_value: flaterate_value,
            fixedRate_conditional: fixedRate_conditional,
            consumptionlower_is: consumptionlower_is,
            flaterate_consumption_islower: flaterate_consumption_islower,
            fixedRateValue: fixedRateValue,
            selectedResidences: selectedResidences
        },
        success: function (res) {
            //actualizar infos
            var service = $('input[name=servicecost]:checked').val();
            residencesTariffInfo.empty();
            $.each(res.infos, function (index, message) {
                var listItem = $('<li>')
                    .text(message.message)
                    .attr('data-service', message.service)
                    .appendTo('#residencesTariffInfo');
                if (message.service === service) {
                    listItem.show(); // Muestra si coincide el servicio
                } else {
                    listItem.hide(); // Oculta si no coincide
                }
            });

            //actualizar filas datatable
            $.each(res.dataresidences, function (index, item) {
                var $row = $('#tb-residence tbody tr[data-id="' + item.id + '"]'); // Buscar la fila con data-id específico

                if ($row.length > 0) {
                    // Actualizar contenido de la fila existente
                    $row.html(`
            <td>${item.residencename}</td>
            <td><div class="me-2"><p class="mb-0 lh-1 fw-bold"><b>${item.servicename}</p></div></td>
            <td>${(item.status == 0) ?
                            '<p class="text-danger"></i>Aún sin registrar <i class="bx bx-info-circle"></p>' :
                            '<p class="text-success"></i>Registrado <i class="bx bx-check-circle"></p>'}</td>
            <td>${item.rateid ?
                            `<div class="me-2">
                    <p class="mb-0 lh-1">${item.ratename}</p>
                    ${(item.rateid == 1) ?
                                `<small class="text-muted">Costo ${item.codevolume.replace(/[a-zA-Z]/, '')}</small>` :
                                (item.rateid == 2 ?
                                    `<small class="text-muted">${item.fixedrate} USD</small>` : 'n/a')}
                </div>` : 'n/a'}</td>
            <td>${(item.isconditional == 1 && item.status == 1) ?
                            `<div class="me-2">
                    <p class="mb-0 lh-1">${item.ifislower} ${item.servicevolume}</p>
                    <small class="text-muted">Tarifa fija:  $${item.ratevaluenew} USD</small>
                </div>` : 'n/a'}</td>
            <td>
                <button type="button" 
                        data-residence="${item.residencename}" 
                        data-residenceid="${item.residenceid}" 
                        class="edit-residence btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-file-signature"></i>
                </button>
            </td> `
                    );
                }
            });

            // Redibujar la tabla para reflejar cambios
            if (typeof dt_residence !== 'undefined') {
                dt_residence.draw(false); // Redibujar sin reiniciar la paginación
            }


            //actualizar div periodo
            $('div[data-period="' + period + '"] .parameterstatus').text(res.statusparameter.message);

            //mensaje
            toastr.options = {
                "positionClass": "toast-top-center",  // Centrado en la parte superior
                "progressBar": true,  // Muestra la barra de progreso
                "showDuration": 1000,  // Duración de la animación de entrada
                "hideDuration": 1000,  // Duración de la animación de salida
                "timeOut": 3000,  // El toast desaparecerá después de 3 segundos
            };
            toastr.success('Formulario enviado con éxito', null, { progressBar: true, showDuration: 1000, hideDuration: 1000 });

            //refresh
            setTimeout(function () {
                location.reload();
            }, 500);
        },
        error: function (xhr, status, error) {
            toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
        }
    });
});


//cerrar modal en residencia o general
btncloseMdlRes.on('click', function () {
    conditionConsumption.val("");
    conditionValue.val("");
    fixedrateValue.val("");
    ModalResidencettl.html("");
    enableCondition.prop('checked', false).trigger('change');
    conditionConsumption.val(""); // Limpiar el valor del campo de consumo
    conditionValue.val(""); // Limpiar el valor del campo de valor
    fixedrate_value.val(""); // Limpiar el valor de la tarifa fija
    selectlistresidences.selectpicker('deselectAll')
    $("#listcosts tbody > tr").remove();
    $('input[name="servicetf"]').prop('checked', false).trigger('change');
    ModalResidence.modal("hide").modal('hide');
    ModalResidencettl.text("Parametros para residencias");
});


//mostrar ocultar inputs segun tarifa
// Función modificada
typeRate.change(function () {
    var selectedOption = $(this).val();
    if (selectedOption === '1') {
        txtlistcosts.show();
        listcosts.show();
        cntfixedrate_value.hide();
        fixedrate_value.hide();
        txtConditionConsumption.show();
        conditionConsumption.show();
        txtConditionValue.show();
        conditionValue.show();
        enableCondition.show();
    } else if (selectedOption === '2') {
        txtlistcosts.hide();
        listcosts.hide();
        cntfixedrate_value.show();
        fixedrate_value.show();
        txtConditionConsumption.hide();
        conditionConsumption.hide();
        txtConditionValue.hide();
        conditionValue.hide();
        enableCondition.hide();
        $('input[name="servicetf"]').prop('checked', false).trigger('change');
        enableCondition.prop('checked', false).trigger('change'); //desmarcar
        conditionConsumption.val(""); // Limpiar el valor del campo de consumo
        conditionValue.val(""); // Limpiar el valor del campo de valor
        fixedrate_value.val(""); // Limpiar el valor de la tarifa fija
    }
});

// Habilitar o deshabilitar campos según se requiera condición
enableCondition.change(function () {
    if (enableCondition.prop('checked')) {
        conditionConsumption.prop('disabled', false);
        conditionValue.prop('disabled', false);
    } else {
        conditionConsumption.prop('disabled', true);
        conditionValue.prop('disabled', true);
    }
});

//***COMPORTAMIENTO CARDS CONSUMOS - COSTOS - SERVICIOS***/
// --------------------------------------------------------------------
//contorno card al click radio
cardsCosts.on('click', function () {
    $cardsCosts.removeClass('checked');
    $(this).addClass('checked');
});

//paginazion por card
$(document).on('click', '.pagination .page-item', function () {
    var $pagination = $(this).closest('.pagination');
    $pagination.find('.page-item').removeClass('active');
    $(this).addClass('active');
});

//***SECCION N PARAMETROS GENERALES TC/TAX***/
// Evento para el botón de editar TC
editTcBtn.click(function () {
    var input = $(this).siblings('input');
    var button = $(this);
    var tc = $('#tc').val().replace(',', '').trim();

    if (input.prop('disabled')) {
        enableInput(input, button);
    } else {
        $.ajax({
            url: spartc_utilities,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: 'POST',
            data: { tc: tc, period: period },
            beforeSend: function () {
                disableInput(input, button, 'in-progress');
            },
            success: function (response) {
                // Llama a la función para recalcular todos los resultados
                recalculateAllResults(period);
                disableInput(input, button, 'success');
                toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                $('div[data-period="' + period + '"] .parameterstatus').text(res.statusparameter.message);
            },
            error: function (xhr, status, error) {
                disableInput(input, button, 'error');
                toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
        });
    }
});
// Evento para el botón de editar TAX
editTaxBtn.click(function () {
    var input = $(this).siblings('input');
    var button = $(this);
    var tax = $('#tax').val().replace(',', '').trim();

    if (input.prop('disabled')) {
        enableInput(input, button);
    } else {
        $.ajax({
            url: spartc_utilities,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: 'POST',
            data: { tax: tax, period: period },
            beforeSend: function () {
                disableInput(input, button, 'in-progress');
            },
            success: function (res) {
                // Llama a la función para recalcular todos los resultados
                recalculateAllResults(period);
                disableInput(input, button, 'success');
                toastr.success(res.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                $('div[data-period="' + period + '"] .parameterstatus').text(res.statusparameter.message);
            },
            error: function (xhr, status, error) {
                disableInput(input, button, 'error');
                toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
        });
    }
});

//***SECCION SERVICIOS: CALCULO PRECIO X CONSUMO: VOLUMEN/COSTO***/
// Evento dinamico para mostrar/ocultar datos de la tabla segun el serivicio elejido en los cards
$(document).on('click', 'input[name="servicecost"]', function () {
    var service = $(this).val();
    SwitchService(service)
});
//evento para cargar los datos por cada pegasize de costos servicios
$(document).on('click', '.page-item > a', function () {

    // Obtener los datos asociados con el clic
    var $this = $(this);
    var service = $(this).attr('data-serviceid');
    var code = $(this).attr('data-servicecode');

    // Verificar si el elemento ya está activo 
    if ($this.parent().hasClass('active')) { return false; }

    // Obtener el contenedor principal del cards-costsservices
    var parentContainer = $(this).closest('.cards-costsservices');
    // Mostrar el efecto de carga en el contenedor principal
    parentContainer.block({
        message: '<div class="spinner-border text-white" role="status"></div>',
        css: {
            backgroundColor: 'transparent',
            border: '0'
        },
        overlayCSS: {
            opacity: 0.5
        }
    });

    // Realizar la solicitud AJAX
    $.ajax({
        url: loadcosts,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        method: 'GET',
        data: {
            period: period, service: service, code: code  // Enviar el código de servicio
        },
        success: function (res) {
            var item = res.data[0];

            setTimeout(function () {
                parentContainer.unblock();
                //asignar en los campos
                $this.closest('.col-md').find('.code-service').text(`codigo: ${item.volume_code}`);
                $this.closest('.col-md').find('.calculate-volum').val(item.volume).attr('data-code', code);
                $this.closest('.col-md').find('.calculate-cost').val(item.cost_formula).attr('data-code', code);
                $this.closest('.col-md').find(".result-cost").text(`$${item.cost}`);
            }, 500);
        },
        error: function (xhr, status, error) {
            // Lógica en caso de error
            alert('Error: ' + xhr.responseText);
            setTimeout(function () { parentContainer.unblock(); }, 500);
        }
    });
});
// Evento para el botón de editar volumen
$(document).on('click', '.edit-volume-btn', function () {
    var $this = $(this);
    var input = $this.siblings('input');
    if (input.prop('disabled')) {
        enableInput(input, $this);
    } else {
        var parameter = 'volume';
        var service = $this.closest('.input-group').find('input').data('service');
        var value = $this.closest('.input-group').find('input').val().replace(',', '').trim();;
        var code = $this.closest('.input-group').find('input').data('code');
        var result = $this.closest('.col-md').find('.result-cost').text().replace(/[\$,]/g, '').trim();
        // Enviar valores actualizados al servidor
        $.ajax({
            url: sparcost_utilities,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: 'POST',
            data: { parameter: parameter, service: service, value: value, result: result, code: code, period: period },
            beforeSend: function () {
                disableInput(input, $this, 'in-progress');
            },
            success: function (response) {
                recalculateAllResults(period); //re-imprimir los resultados de los otros cards
                disableInput(input, $this, 'success');
                toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            },
            error: function (xhr, status, error) {
                disableInput(input, $this, 'error');
                toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
        });
    }
});
// Evento para el botón de editar cálculo
$(document).on('click', '.edit-calculate-btn', async function () {
    var input = $(this).siblings('input');
    var button = $(this);
    if (input.prop('disabled')) {
        enableInput(input, $(this));
    } else {
        const expression = input.val();

        try {
            // Esperamos la validación de la expresión
            const resultsuccess = await validateExpression(expression, period);
            // Si la validación es exitosa::
            input.closest('.custom-option-body').find('.result-cost').text('$' + formatNumber(resultsuccess)); //imprimir resultado
            var parameter = 'cost';
            var service = $(this).closest('.input-group').find('input').data('service');
            var value = $(this).closest('.input-group').find('input').val();
            var code = $(this).closest('.input-group').find('input').data('code');
            var result = $(this).closest('.input-group').next('.text-center').find('.result-cost').text().replace(/[\$,]/g, '').replace(',', '').trim();

            $.ajax({
                url: sparcost_utilities,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                data: { parameter: parameter, service: service, value: value, code: code, result: result, period: period },
                beforeSend: function () {
                    disableInput(input, button, 'in-progress');
                },
                success: function (response) {
                    disableInput(input, button, 'success');
                    toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                },
                error: function (xhr, status, error) {
                    disableInput(input, button, 'error');
                    toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                }
            });
        } catch (errorMessage) {
            // Si ocurre un error en la validación, mostramos el mensaje
            alert(errorMessage);
            return; // Interrumpir el flujo y no continuar con la desactivación ni el cálculo
        }
    }
});
// Manejar el ingreso en el campo de cálculo
$(document).on('keydown keyup', '.calculate-cost, .new-calculate-cost', function () {
    var value = this.value;
    var formattedValue = '';
    var insideParentheses = false;
    var hasError = false; // Variable para controlar errores

    // Validar cada carácter
    for (var i = 0; i < value.length; i++) {
        var char = value[i];

        // Permitir números y operadores
        if (/^[0-9+\-*/.]+$/.test(char)) {
            formattedValue += char;
        }
        // Permitir letras solo si están dentro de paréntesis
        else if (char === '(') {
            insideParentheses = true;
            formattedValue += char;
        }
        else if (char === ')') {
            insideParentheses = false;
            formattedValue += char;
        }
        // Permitir letras si estamos dentro de paréntesis
        else if (insideParentheses && /^[a-zA-Z0-9]+$/.test(char)) {
            formattedValue += char;
        }
    }

    // Verificar si hay un código sin un operador previo después de un número
    const lastOperatorIndex = Math.max(
        formattedValue.lastIndexOf('+'),
        formattedValue.lastIndexOf('-'),
        formattedValue.lastIndexOf('*'),
        formattedValue.lastIndexOf('/')
    );

    const numberBeforeCode = /\d+\s*\(?[a-zA-Z]/.test(formattedValue);
    const codeFollowedByNumber = /\([a-zA-Z]+\)\d/.test(formattedValue);
    const startsWithCode = /^[a-zA-Z]/.test(formattedValue);
    const codeAfterParenthesis = /\)\s*\(?[a-zA-Z0-9]/.test(formattedValue);

    // Validar número seguido de código
    if (numberBeforeCode && lastOperatorIndex < 0) {
        hasError = true;
        alert("Error: No se puede escribir un código sin un operador previo después de un número.\n" +
            "Ejemplos válidos:\n" +
            "1. 12+(C1)\n" +
            "Nota: Si inicia con un código, debe ir precedido de un operador.");

        // Limitar la entrada hasta el último número válido
        const lastParenthesisIndex = formattedValue.indexOf('(');
        if (lastParenthesisIndex !== -1) {
            // Borrar hasta el paréntesis y el mismo paréntesis
            this.value = formattedValue.slice(0, lastParenthesisIndex);
        } else {
            // Borrar todo si hay un código no permitido
            this.value = formattedValue.replace(/[a-zA-Z].*/, '');
        }
        return; // Salir de la función para no seguir procesando
    }

    // Validar si hay un código seguido de un número sin un operador
    if (codeFollowedByNumber) {
        hasError = true;
        alert("Error: No se puede escribir un número inmediatamente después de un código sin un operador previo.\n" +
            "Ejemplos válidos:\n" +
            "1. (C1)+12");

        // Borrar el código y el número
        const lastParenthesisIndex = formattedValue.indexOf('(');
        if (lastParenthesisIndex !== -1) {
            this.value = formattedValue.slice(0, lastParenthesisIndex);
        } else {
            this.value = formattedValue.replace(/[a-zA-Z].*/, '');
        }
        return; // Salir de la función para no seguir procesando
    }

    // Validar si hay un código o número inmediatamente después de un paréntesis
    if (codeAfterParenthesis) {
        hasError = true;
        alert("Error: No se puede escribir un número o código inmediatamente después de un paréntesis cerrado.\n" +
            "Ejemplos válidos:\n" +
            "1. (C1)+12\n" +
            "2. (C1)+(B1)\n" +
            "Nota: Asegúrate de incluir un operador entre ellos.");

        // Borrar lo que sigue al paréntesis
        const lastParenthesisIndex = formattedValue.lastIndexOf(')');
        this.value = formattedValue.slice(0, lastParenthesisIndex + 1);
        return; // Salir de la función para no seguir procesando
    }

    // Formatear solo la parte numérica del valor
    const numericPart = formattedValue.replace(/[^0-9.]/g, '');
    const formattedNumber = numericPart;//aplicar numberformat
    // Reemplazar la parte numérica en el valor formateado
    this.value = formattedValue.replace(numericPart, formattedNumber);

});
// Eventos para abrir modal nuevo costo
$(document).on('click', '.add-cost', function () {
    // Obtén el contenedor de la tarjeta donde se hizo clic
    currentCard = $(this).closest('.cards-costsservices');
    serviceName = currentCard.find('input[name="servicecost"]').val(); //nombre servicio
    // Asignar el nombre del servicio al título del modal
    addcostServicelabel.text(`Nuevo costo de servicio ${serviceName}`);
    // Abre el modal
    addcostService.modal({
        backdrop: 'static',
        keyboard: false
    }).modal('show');
});
//evento para guardar el nuevo costo del modal
$(document).on('click', '#saveServiceBtn', async function () {
    // Obtener los elementos del modal
    var inputVolume = addcostService.find('.new-calculate-volum');
    var inputCost = addcostService.find('.new-calculate-cost');
    var expression = inputCost.val();
    var resultCostElement = addcostService.find('.result-cost');
    var serviceId = currentCard.find('input[name="servicecost"]').attr('id').split('_')[1]; //id servicio

    // 1. Validar si el volumen es válido
    var volume = parseFloat(inputVolume.val().replace(/[^0-9.-]+/g, "")); // Limpiar caracteres no numéricos
    if (isNaN(volume) || volume <= 0) {
        alert('Por favor, ingresa un volumen válido.');
        return; // Salir si el volumen es inválido
    }

    // 2. Validar la expresión antes de proceder
    try {
        // Utilizamos la función de validación de expresión con `await` para esperar la respuesta
        await validateExpression(expression, period);
        // Esperamos la validación de la expresión
        const resultsuccess = await validateExpression(expression, period);
        // Si la validación es exitosa:: imprimir resultado
        resultCostElement.text('$' + formatNumber(resultsuccess));


        // 3. Generar el nuevo item de paginación utilizando la nueva función
        generatePaginationItem(currentCard, serviceId);

        // 4. Ejecutar la llamada AJAX si el cálculo fue exitoso
        var cost = resultCostElement.text().replace(/[\$,]/g, '').replace(',', '').trim();

        $.ajax({
            url: nparcost_utilities, // Reemplaza con tu URL de servidor
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                serviceId: serviceId, code: nextServiceCode, period: period, volume: volume, cost_formula: expression, cost: cost
            },
            success: function (response) {
                alert('Datos guardados exitosamente.');
                addcostService.modal('hide');
            },
            error: function (error) {
                alert('Error al guardar los datos.');
            }
        });

    } catch (errorMessage) {
        // Si ocurre un error en la validación de la expresión
        alert(errorMessage);
        return; // Interrumpir el flujo si la expresión es inválida
    }
});

$(document).on('click', '.delete-cost', function () {
    var container = $(this).closest('.cards-costsservices');
    var activePageItem = container.find('.pagination .page-item.active');
    var serviceId = activePageItem.find('a').data('serviceid');
    var serviceCode = activePageItem.find('a').data('servicecode');

    Swal.fire({
        title: '¿Estás seguro?',
        text: "Si este costo ya tiene asociado residencias o formula se hara saber en el proceso (No se eliminara registros en tal caso).",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: find_cost_residences,
                method: 'GET',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { period: period },
                success: function (response) {
                    if (response.residenciasAsociadas > 0) {
                        Swal.fire({
                            title: 'No es posible eliminar este registro',
                            text: 'Hay residencias asociadas a este costo. Primero, debes moverlas a otro antes de eliminar este.',
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                    } else {
                        eliminarCosto(serviceId, serviceCode, activePageItem, container);
                    }
                },
                error: function () {
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema al verificar las residencias.',
                        icon: 'error',
                        confirmButtonText: 'Intentar de nuevo'
                    });
                }
            });
        }
    });
});

function eliminarCosto(serviceId, serviceCode, activePageItem, container) {
    $.ajax({
        url: dparcost_utilities,
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: { period: period, serviceId: serviceId, serviceCode: serviceCode },
        success: function (response) {
            if (response.error) {
                Swal.fire({
                    title: 'Error',
                    html: response.error,
                    icon: 'error',
                    confirmButtonText: 'Intentar de nuevo'
                });
            } else if (response.message) {
                Swal.fire({
                    title: '¡Eliminado!',
                    text: 'El costo ha sido eliminado correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Cerrar'
                }).then(() => {

                    // Eliminar el elemento activo
                    activePageItem.remove();
                    // Reordenar los elementos restantes dentro del contenedor específico
                    container.find('.pagination .page-item').each(function (index) {
                        // Actualizar el atributo data-page del <li>
                        $(this).attr('data-page', index + 1);
                        // Actualizar el número del ítem (texto dentro del <a>)
                        $(this).find('a').text(index + 1);
                        // Obtener el valor del atributo data-servicecode
                        var serviceCode = $(this).find('.page-link').attr('data-servicecode');
                        // Usar una expresión regular para separar la parte alfabética y la numérica
                        var match = serviceCode.match(/^([a-zA-Z]+)(\d+)$/);  // Captura letras seguidas de números
                        if (match) {
                            // match[1] -> parte alfabética, match[2] -> parte numérica
                            var letters = match[1];
                            var newNumber = index + 1;  // el número debe ser el índice + 1 (empieza en 1)
                            // Construir el nuevo data-servicecode con las letras y el número actualizado
                            var newServiceCode = letters + newNumber;
                            $(this).find('.page-link').attr('data-servicecode', newServiceCode);
                        }
                    });
                    // Activar el primer elemento dentro del contenedor específico
                    container.find('.pagination .page-item').first().addClass('active');
                    // Cargar los datos del primer elemento dentro del contenedor específico
                    container.find('.pagination .page-item').first().find('a').trigger('click');

                    // Actualizar card
                    var item = response.firstdata;
                    currentCard.find('.code-service').text(`codigo: ${item.volume_code}`); // Solo este funciona
                    currentCard.find('.calculate-volum').val(item.volume).attr('data-code', item.volume_code);
                    currentCard.find('.calculate-cost').val(item.cost_formula).attr('data-code', item.volume_code);
                    currentCard.find(".result-cost").text(`$${item.cost}`);
                });
            }
        },
        error: function (xhr) {
            let errorMessage = 'No se puede eliminar el registro porque está siendo utilizado en las siguientes fórmulas: ';
            if (xhr.status === 400) {
                errorMessage = xhr.responseJSON.error || errorMessage;
            }
            if (xhr.responseJSON.items && xhr.responseJSON.items.length > 0) {
                errorMessage += '<br>';
                xhr.responseJSON.items.forEach(function (item) {
                    errorMessage += '• ' + item.replace(/\n/g, '<br>') + '<br>';
                });
            }
            Swal.fire({
                title: 'Error',
                html: `<div style="max-height: 300px; overflow-y: auto;">
                           ${errorMessage}
                       </div>`,
                icon: 'error',
                confirmButtonText: 'Intentar de nuevo'
            });
        }
    });
}
