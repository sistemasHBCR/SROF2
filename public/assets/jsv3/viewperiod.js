/**
 *  Datatable
 */
$(document).ready(function () {

    /**
    * DATATABLE CAPTURA OPERADORES
    */
    $('#status').DataTable({
        paging: true, // Enable pagination
        ordering: true, // Enable ordering
        searching: true, // Enable search box
        lengthChange: true, // Show page size selector
        pageLength: 25, // Default page size
        buttons: ['pageLength', 'colvis'], // Additional buttons
        language: {
            paginate: {
                next: '<i class="fas fa-chevron-right"></i>', // Custom next button icon
                previous: '<i class="fas fa-chevron-left"></i>' // Custom previous button icon
            }
        },
        order: [
            [0, 'asc']
        ], // Ordenar por la primera columna (Residencia) de forma ascendente
        columnDefs: [{
            targets: [1, 2, 3, 4], // Aplicar a las columnas 1, 2 y 3
            orderable: false // Desactivar ordenamiento en las columnas 1, 2 y 3
        }]
    });

    /**
     * DATATABLE UTILITIES
     */
    var table = $('#tb-data').DataTable({
        paging: true, // Habilita la paginación
        ordering: true, // Habilita la ordenación
        searching: true, // Habilita el cuadro de búsqueda
        lengthChange: true, // Muestra el selector de tamaño de página
        pageLength: 50, // Tamaño de página predeterminado
        buttons: ['pageLength', 'colvis'], // Botones adicionales
        language: {
            paginate: {
                next: '<i class="fas fa-chevron-right"></i>', // Icono personalizado para siguiente
                previous: '<i class="fas fa-chevron-left"></i>' // Icono personalizado para anterior
            }
        },
        order: [
            [0, 'asc'] // Ordenar por la primera columna de forma ascendente
        ],
    });

    var editingCell = null; // Variable para almacenar la celda actualmente en edición
    var previousValue = null; // Almacenar el valor anterior para restaurarlo en caso de cancelación

    // Lista de columnas editables
    var editableColumns = [2, 3, 4];

    $('#tb-data tbody').on('dblclick', 'td', function () {
        // Verifica si la columna es editable
        if (!editableColumns.includes($(this).index())) return;

        $(this).removeClass('editing'); // Eliminar la clase para no editar
        var cell = table.cell(this); // Obtener la celda seleccionada
        var currentValue = cell.data(); // Valor actual de la celda

        // Si la celda ya está siendo editada, no hacer nada
        if ($(this).hasClass('editing')) return;

        // Si hay una celda en edición, cancélala y restaura su valor original
        if (editingCell !== null && editingCell !== this) {
            var previousCell = $(editingCell);
            // Restablecer la celda anterior a su valor original
            previousCell.html(previousValue).removeClass('editing');
        }

        // Marcar la celda como en edición
        $(this).addClass('editing');
        editingCell = this; // Actualizar la celda en edición
        previousValue = currentValue; // Guardar el valor original de la celda

        // Crear un campo de entrada para la edición
        var input = $('<input>', {
            type: 'text',
            value: currentValue,
            class: 'form-control form-control-sm'
        });

        // Crear los botones de Cancelar y Aplicar
        var cancelButton = $('<button>', {
            text: 'Cancelar',
            class: 'btn btn-danger btn-sm ml-2',
            click: function () {
                // Restablecer el valor original
                $(this).parent().html(previousValue);
                editingCell = null; // Limpiar la celda en edición
            }
        });

        var applyButton = $('<button>', {
            text: 'Aplicar',
            class: 'btn btn-success btn-sm ml-2',
            click: function () {
                var newValue = input.val(); // Obtener el valor editado
                cell.data(newValue).draw(); // Actualizar la celda en la tabla
                editingCell = null; // Limpiar la celda en edición
            }
        });

        // Reemplazar la celda con el campo de entrada y botones
        $(this).html(input).append(cancelButton).append(applyButton);

        // Si el usuario presiona "Enter", guarda la edición automáticamente
        input.on('keydown', function (e) {
            if (e.which == 13) { // Si se presiona "Enter"
                var newValue = input.val();
                cell.data(newValue).draw(); // Actualiza la celda
                editingCell = null; // Limpiar la celda en edición
            }
        });
    });

    // Aplicar las clases de cursor al cargar la tabla
    $('#tb-data tbody td').each(function () {
        if (editableColumns.includes($(this).index())) {
            $(this).addClass('editable');
        } else {
            $(this).addClass('non-editable');
        }
    });
    /**
    // Card Toggle fullscreen
    // --------------------------------------------------------------------
    */
    const expandElementList = [].slice.call(document.querySelectorAll('.card-expand'));
    if (expandElementList) {
        expandElementList.map(function (expandElement) {
            expandElement.addEventListener('click', event => {
                event.preventDefault();
                // Toggle class bx-fullscreen & bx-exit-fullscreen
                Helpers._toggleClass(expandElement.firstElementChild, 'bx-fullscreen',
                    'bx-exit-fullscreen');

                expandElement.closest('.card').classList.toggle('card-fullscreen');
            });
        });
    }
    /**
     *  Modal Example Wizard
     */
    const appModal = document.getElementById('Modal-Utilities');
    appModal.addEventListener('show.bs.modal', function (event) {
        const wizardUtilities = document.querySelector('#wizard-create-app');
        if (typeof wizardUtilities !== undefined && wizardUtilities !== null) {
            // Wizard next prev button
            const wizardUtilitiesNextList = [].slice.call(wizardUtilities.querySelectorAll(
                '.btn-next'));
            const wizardUtilitiesPrevList = [].slice.call(wizardUtilities.querySelectorAll(
                '.btn-prev'));
            const wizardUtilitiesBtnSubmit = wizardUtilities.querySelector('.btn-submit');

            const createAppStepper = new Stepper(wizardUtilities, {
                linear: false
            });
            if (wizardUtilitiesNextList) {
                wizardUtilitiesNextList.forEach(wizardUtilitiesNext => {
                    wizardUtilitiesNext.addEventListener('click', event => {
                        createAppStepper.next();
                    });
                });
            }
            if (wizardUtilitiesPrevList) {
                wizardUtilitiesPrevList.forEach(wizardUtilitiesPrev => {
                    wizardUtilitiesPrev.addEventListener('click', event => {
                        createAppStepper.previous();
                    });
                });
            }
            if (wizardUtilitiesBtnSubmit) {
                wizardUtilitiesBtnSubmit.addEventListener('click', event => {
                    toastr.success('¡Los datos han sido registrados!', '', {
                        positionClass: 'toast-top-right',
                        progressBar: true,
                        close: true,
                        closeButton: true
                    });
                });
            }
        }
    });
});
