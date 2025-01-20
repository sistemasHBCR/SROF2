
$(document).ready(function () {

    var btnreadSheet = $('#btnread');
    var titlesheets = $('#title-listsheets');
    var modalsheet = $('#listsheets');
    var tabSheets = $('#tab-sheets');
    var tabTemplate = $("#tab-template");
    var template = $("#template");
    var tabSheetValidate = $('#tab-SheetValidate');
    var sheets = $('#sheets');
    var sheetValidate = $('#SheetValidate');
    var tabImportData = $('#tab-importdata');
    var importData = $('#importdata');
    var btn1Next = $('#btn1-next');
    var btn2Next = $('#btn2-next');
    var btn2Back = $('#btn2-back');
    var btn3Next = $('#btn3-next');
    var btn3Back = $('#btn3-back');
    var btn4Back = $('#btn4-back');
    var btnImport = $('#btn-import');
    var checktemplate = $("input[name='checktemplate']");
    var closeImportModal = $('#close-importmodal');
    var excelData = [];
    var Totalsvalidations = $('[id^=validation]');
    var allValidationSuccess = 0; //variable de estado (Conteo si todas las validaciones estan completas)
    var shouldContinueValidation = false; // Variable de estado (detener validacion si estaba en progreso)
    var validationCompleted = false; // Variable de estado (validaciones aun no realizadas)
    var tableimportCompleted = false; // Variable de estado (tabla importacion aun no generada)


    //funcion leer hojas disponibles de un excel
    btnreadSheet.click(function () {
        // Obtener el valor del input file
        var fileInput = $('#documento')[0].files[0];
        // Verificar si el input file está vacío
        if (!fileInput) {
            // Mostrar un mensaje de error al usuario indicando que no ha seleccionado ningún archivo
            toastr.error('Por favor, seleccione un archivo para importar.', { progressBar: true, showDuration: 1500, hideDuration: 1500 });
            return; // Salir de la función si no hay archivo seleccionado
        }
        // Crear un objeto FormData con el formulario
        var formData = new FormData($('#ReadSheetForm')[0]);
        var csrfToken = $('meta[name="csrf-token"]').attr('content'); // Obtener el token CSRF del head

        $.ajax({
            url: readsheets,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            beforeSend: function () {
                btnreadSheet.prop('disabled', true);
                // Mostrar el spinner de carga
                updateValidationIcon('#btnread', 'in-progress');
            },
            success: function (response) {
                updateValidationIcon('#btnread', 'read-success');
                var sheetsradio = '';
                var sheetsall = response.sheetnames;
                sheetsall.forEach(function (sheetname, index) {
                    // Agregar un radio input para cada nombre de hoja al HTML
                    sheetsradio += '<div class="form-check form-check-inline mt-3">' +
                        '<input class="form-check-input" type="radio" name="radiosheetsname" id="radiosheetsname' + index + '" value="' + sheetname + '">' +
                        '<label class="form-check-label" for="radiosheetsname' + index + '">' + sheetname + '</label>' +
                        '</div>';
                });

                if (sheetsall.length > 1) {
                    titlesheets.removeClass('sr-only');
                } else {
                    setTimeout(function () {
                        $('[id^="radiosheetsname"]').first().prop('checked', true).trigger('change');
                    }, 500);
                    setTimeout(function () {
                        btn1Next.click();
                    }, 1000);
                }
                // Construir el contenido del modal-body
                titlesheets.after(sheetsradio);
                // Mostrar el modal después de generar el contenido
                modalsheet.modal('show');
            },
            error: function (response) {
                toastr.error('Ocurrió un error al leer el archivo.', { progressBar: true, showDuration: 1500, hideDuration: 1500 });
            }
        });
    });

    // Función botón #bt-import (Importar datos del array)
    btnImport.click(function () {

        // Cambiamos el estado del botón al iniciar la petición
        btnImport.prop('disabled', true);
        btnImport.html('<span class="spinner-border me-1" role="status" aria-hidden="true"></span> Subiendo...');
        var checktemplate = $('input[name="checktemplate"]:checked').val();

        var start = $("#start").text();
        var end = $("#end").text();
        // Dividiendo la fecha en año, mes y día
        var startParts = start.split('/');
        var endParts = end.split('/');
        // Creando nuevas fechas en el formato deseado
        var start = startParts[2] + '-' + startParts[1] + '-' + startParts[0];
        var end = endParts[2] + '-' + endParts[1] + '-' + endParts[0];

        $.ajax({
            url: importExcelRoute,
            type: 'POST',
            data: { 'excelData': excelData, 'start': start, 'end': end, checktemplate: checktemplate },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
                // Restauramos el botón al estado inicial
                btnImport.prop('disabled', false);
                btnImport.html('<span class="d-sm-inline-block d-none me-sm-1">Importar</span><i class="bx bx-upload bx-sm me-sm-n2"></i>');
            },
            error: function (response) {
                toastr.error(response.responseJSON.error, { progressBar: true, showDuration: 1500, hideDuration: 1500 });
                // Restauramos el botón al estado inicial en caso de error
                btnImport.prop('disabled', false);
                btnImport.html('<span class="d-sm-inline-block d-none me-sm-1">Importar</span><i class="bx bx-upload bx-sm me-sm-n2"></i>');
            }
        });
    });


    // Funcionalidad cerrar modal
    closeImportModal.click(function () {
        //**controles de estado
        allValidationSuccess = 0;
        shouldContinueValidation = false;
        validationCompleted = false;
        tableimportCompleted = false;
        //**divs y contenido
        //reset validaciones
        Totalsvalidations.each(function (i) {
            const $icon = $(this).find('.icon');
            const $error = $(this).find('.error');
            $icon.html(`
                <i class="bx bx-circle text-secondary"></i>
            `);
            $error.text(''); // Limpiar mensaje de error
        });
        // Destruir la instancia de DataTables
        $('#tb-data tbody').empty();
        var table = $('#tb-data').DataTable();
        if (table !== null) {
            table.destroy();
        }
        //limpiar radios sheet
        titlesheets.nextAll().remove();
        checktemplate.prop('disabled', false)
        //**botones
        btnreadSheet.prop('disabled', false)
        btn1Next.prop("disabled", true);
        btn2Next.prop("disabled", false);
        btn3Next.prop("disabled", true);
        btn2Back.prop("disabled", false);
        btn3Back.prop("disabled", true);
        btn4Back.prop("disabled", true);
        //**tabs
        tabSheets.addClass('active').prop("disabled", false);
        tabTemplate.removeClass('active').prop("disabled", true);
        tabSheetValidate.removeClass('active').prop("disabled", true);
        tabImportData.removeClass('active').prop('disabled', true);
        sheets.addClass('show active');
        template.removeClass('show active');
        sheetValidate.removeClass('show active');
        importData.removeClass('show active');

    });


    // Funcionalidad onChange después de seleccionar un radio en modal
    $(document).on('change', '[id^="radiosheetsname"]', function () {
        btn1Next.prop('disabled', false);
    });

    // Función botón #btn1-next
    btn1Next.click(function () {
        tabSheets.removeClass('active').prop('disabled', false);
        tabTemplate.addClass('active').prop('disabled', false);
        sheets.removeClass('show active');
        template.addClass('show active');

    });

    // Función botón #btn2-next
    btn2Next.click(function () {
        if ($('[id^="radiosheetsname"]:checked').length === 0) {
            alert('Por favor, seleccione un valor del radio.');
        } else {
            $('input[name="radiosheetsname"]').prop('disabled', true);
            tabTemplate.removeClass('active').prop('disabled', false);
            tabSheetValidate.addClass('active').prop('disabled', false);
            template.removeClass('show active');
            sheetValidate.addClass('show active');
            checktemplate.prop('disabled', true);
            //si anteriormense no se ha inicializado la validación generarla
            if (!validationCompleted) {
                //variable de control para indicar que inicialize validacion
                shouldContinueValidation = true;
                validationCompleted = true; // Marcar la validación como realizada
                setTimeout(startValidation, 500);
            }
        }

    });

    // Función botón #btn2-back
    btn2Back.click(function () {
        tabSheets.addClass('active');
        sheets.addClass('show active');
        tabTemplate.removeClass('active');
        template.removeClass('show active');
    });

    // Función botón #btn3-next
    btn3Next.click(function () {
        if (Totalsvalidations.length != allValidationSuccess) {
            alert("No puedes continuar con la importación si la estroctura de la hoja excel no esta validada")
            return
        }

        var checktemplate = $('input[name="checktemplate"]:checked').val();
        tabImportData.addClass('active').prop('disabled', false);
        tabSheetValidate.removeClass('active');
        importData.addClass('show active');
        sheetValidate.removeClass('show active');
        btn4Back.prop('disabled', false);
        btnImport.prop('disabled', false);


        // si anteriormente la tabla no se ha inicializado, generarlo.
        if (!tableimportCompleted) {
            tableimportCompleted = true; // Marcar la validación como realizada
            var headerIds = [];
            $('#tb-data thead th').each(function () {
                var id = $(this).attr('id');
                if (id) {
                    headerIds.push(id);
                }
            });
            $.each(excelData, function (index, item) {
                var row = '<tr>';
                row += '<td>' + "<b>" + (index + 1) + "</b>" + '</td>';
                for (var i = 0; i < headerIds.length; i++) {
                    var cellValue = item[i] !== null && item[i] !== undefined ? item[i] : '';
                    if (i >= 5 && i <= 15) {
                        cellValue = parseFloat(cellValue).toFixed(2);
                    }
                    if (i >= 13 && i <= 15) {
                        cellValue = formatCurrency(cellValue);
                    }
                    row += '<td data-cell-id="' + item.id + '-' + headerIds[i] + '">' + cellValue + '</td>';
                }
                row += '</tr>';
                $('#tb-data tbody').append(row);
            });


            setTimeout(function () {
                var table = $('#tb-data').DataTable({
                    paging: false,
                    scrollY: 800,
                    scrollCollapse: true,
                    scroller: true,
                    scrollX: true,
                    sScrollXInner: "100%",
                    autoWidth: true,
                    fixedHeader: true,
                    keys: true
                });

                if (checktemplate == 1) {
                    for (var i = 8; i < 17; i++) {
                        table.column(i).visible(false);
                    }
                } else {
                    for (var i = 0; i < 17; i++) {
                        table.column(i).visible(true);
                    }
                }

                table.columns.adjust().draw();

            }, 100);
        }
    });

    // Función botón #btn3-back
    btn3Back.click(function () {
        tabTemplate.addClass('active');
        template.addClass('show active');
        tabSheetValidate.removeClass('active');
        sheetValidate.removeClass('show active');
    });

    // Función botón #btn3-back
    btn4Back.click(function () {
        tabImportData.removeClass('active');
        tabSheetValidate.addClass('active');
        importData.removeClass('show active');
        sheetValidate.addClass('show active');
    });

    function startValidation() {
        var seleccionado = $('input[name="radiosheetsname"]:checked').val();
        if (seleccionado) {
            var formData = new FormData($('#ReadSheetForm')[0]);
            var period = $('#period').val();
            var checktemplate = $('input[name="checktemplate"]:checked').val();

            formData.append('sheetname', seleccionado);
            formData.append('period', period);
            formData.append('template', checktemplate); // Agregarlo al FormData

            validateSheet(1, formData);
        } else {
            toastr.error('Por favor, seleccione la hoja que deseas importar.', { progressBar: true, showDuration: 1500, hideDuration: 1500 });
        }
    }

    function validateSheet(validationIndex, formData) {
        if (!shouldContinueValidation) {
            return; // Detener el proceso de validación si el modal se cerró
        }
        // Agregar el índice de validación a los datos del formulario
        const validationId = `#validation${validationIndex}`;
        formData.append('validationIndex', validationIndex);

        $.ajax({
            url: processSheet,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            beforeSend: function () {
                // Mostrar el spinner de carga
                updateValidationIcon(validationId, 'in-progress');
            },
            success: function (response) {
                if (!shouldContinueValidation) {
                    return; // Detener el proceso si el modal se cerró durante la validación
                }
                const res = response.validations[0];

                if (res.status === 'success') {
                    updateValidationIcon(validationId, 'success', res.message);
                    allValidationSuccess++;

                    if (Totalsvalidations.length === allValidationSuccess) {
                        btn3Next.prop("disabled", false);
                        btn3Back.prop("disabled", false);
                        excelData = response.excelData;
                    }
                } else {
                    updateValidationIcon(validationId, 'error', res.message);
                    btn3Back.prop("disabled", false);
                    return; // Detener el proceso en caso de error
                }

                if (validationIndex < Totalsvalidations.length) {
                    setTimeout(() => {
                        if (shouldContinueValidation) { // Verificar antes de la próxima validación
                            validateSheet(validationIndex + 1, formData);
                        }
                    }, 1000); // Espera 1 segundo antes de la siguiente validación
                }
            },
            error: function (response) {
                if (!shouldContinueValidation) {
                    return; // Detener el proceso en caso de error si el modal se cerró
                }

                const errorMessage = response.responseJSON?.error || 'An error occurred.';
                //updateValidationIcon(`validation${validationIndex + 1}`, 'error', errorMessage);
                toastr.warning(errorMessage, {
                    progressBar: true,
                    showDuration: 1500,
                    hideDuration: 1500
                });
            }
        });
    }

    function updateValidationIcon(validationId, status, message = '') {
        const $validationItem = $(validationId);
        const $icon = $validationItem.find('.icon');
        const $error = $validationItem.find('.error');

        switch (status) {
            case 'in-progress':
                $icon.html(`
                    <div class="spinner-border spinner-border-sm text-secondary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                `);
                $error.text(''); // Limpiar mensaje de error
                break;
            case 'success':
                $icon.html(`
                    <i class="bx bx-check-circle text-success"></i>
                `);
                $error.text(''); // Limpiar mensaje de error
                break;
            case 'read-success':
                $icon.html(`Leer`);
                break;
            case 'error':
                $icon.html(`
                    <i class="bx bx-info-circle text-danger"></i>
                `);
                $error.text(message); // Mostrar mensaje de error
                break;
            default:
                console.warn('Estado de validación desconocido:', status);
        }
    }

    function formatCurrency(value) {
        return '$' + parseFloat(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
});

