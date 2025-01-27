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

    var table = $('#tb-data').DataTable({
        paging: true,
        ordering: true,
        searching: true,
        lengthChange: true,
        pageLength: 50,
        buttons: ['pageLength', 'colvis'],
        language: {
            paginate: {
                next: '<i class="fas fa-chevron-right"></i>',
                previous: '<i class="fas fa-chevron-left"></i>'
            }
        },
        order: [
            [0, 'asc']
        ],
    });
    
    var editingCell = null;
    var previousValue = null;
    var registroId = null;
    var columnaName = null;
    var editableColumns = [2, 3, 4];
    
    $('#tb-data tbody').on('dblclick', 'td', function () {
        if (!editableColumns.includes($(this).index())) return;
    
        var cell = table.cell(this);
        var currentValue = cell.data();
    
        if (editingCell !== null && editingCell !== this) {
            var previousCell = $(editingCell);
            previousCell.html(previousValue).removeClass('editing');
        }
    
        editingCell = this;
        previousValue = currentValue;
        registroId = $(this).attr('id');
        columnaName = $(this).attr('column');
    
        var input = $('<input>', {
            type: 'text',
            value: currentValue,
            class: 'form-control form-control-sm'
        });
    
        var cancelButton = $('<button>', {
            text: 'Cancelar',
            class: 'btn btn-danger btn-sm ml-2',
            click: function () {
                $(editingCell).html(previousValue);
                editingCell = null;
            }
        });
    
        var applyButton = $('<button>', {
            text: 'Aplicar',
            class: 'btn btn-success btn-sm ml-2',
            click: function () {
                var newValue = input.val();
                $.ajax({
                    url: utilitiesupdate,
                    type: 'PATCH',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        id: registroId,
                        columna: columnaName,
                        newValue: newValue
                    },
                    success: function (response) {
                        cell.data(newValue).draw();
                        editingCell = null;
                    },
                    error: function (xhr, status, error) {
                        $(editingCell).html(previousValue);
                        editingCell = null;
                        alert('Error al guardar los cambios. Los datos se revertiran. : ' + error );
                    }
                });
            }
        });
    
        $(this).html(input).append(cancelButton).append(applyButton);
    
        input.on('keydown', function (e) {
            if (e.which == 13) {
                var newValue = input.val();
                cell.data(newValue).draw();
                editingCell = null;
            }
        });
    });
    
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
