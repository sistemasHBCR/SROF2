/**
 * DATA TABLE Auditoria
 * 
 */

'use strict';
$(document).ready(function () {
  var dt_audit; // Variable global para la instancia de DataTable
  // Inicializar DataTable
  dt_audit = $('#tb-audit').DataTable({
    paging: true,
    ordering: true,
    searching: true,
    lengthChange: true,
    info: true,
    pageLength: 25,
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
        extend: 'collection',
        className: 'btn btn-label-secondary dropdown-toggle mx-3',
        text: '<i class="bx bx-export me-1"></i>Exportar',
        buttons: [
          {
            extend: 'print',
            text: '<i class="bx bx-printer me-2" ></i>Imprimir',
            className: 'dropdown-item',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6, 7],
            },
          },
          {
            extend: 'csv',
            text: '<i class="bx bx-file me-2" ></i>Csv',
            className: 'dropdown-item',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6, 7],

            }
          },
          {
            extend: 'excel',
            text: '<i class="bx bxs-file-export me-2"></i>Excel',
            className: 'dropdown-item',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6, 7],

            }
          },
          {
            extend: 'pdf',
            text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
            className: 'dropdown-item',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6, 7],
            }
          },
          {
            extend: 'copy',
            text: '<i class="bx bx-copy me-2" ></i>Copiar',
            className: 'dropdown-item',
            exportOptions: {
              columns: [0, 1, 2, 3, 4, 5, 6, 7],
            }
          }
        ]
      },
    ],
    order: [[0, 'desc']], // Ordenar por la primera columna en orden ascendente
    language: {
      paginate: {
        next: '<i class="fas fa-chevron-right"></i>',
        previous: '<i class="fas fa-chevron-left"></i>'
      }
    },
    language: {
      paginate: {
        next: '<i class="fas fa-chevron-right"></i>',
        previous: '<i class="fas fa-chevron-left"></i>'
      }
    },
  });

  // Evento para cambiar el filtro al seleccionar una opción del select
  $('#select_username').on('change', function () {
    var username = $(this).val();
    // Aplicar filtro al DataTable a la columna username
    dt_audit.columns(1).search(username).draw();
  });

  $('#select_panel').on('change', function () {
    var panel = $(this).val();
    // Aplicar filtro al DataTable a la columna panel
    dt_audit.columns(3).search(panel).draw();
  });

  $('#select_module').on('change', function () {
    var modulo = $(this).val();
    // Aplicar filtro al DataTable a la columna Modulo
    dt_audit.columns(4).search(modulo).draw();
  });




  //**ABRIR MODAL***/
  $("#details").click(function () {

    var id = $(this).attr('data-id');
    var csrfToken = $('meta[name="csrf-token"]').attr('content'); // Obtener el token CSRF del head

    $.ajax({
      url: metadata,
      headers: { 'X-CSRF-TOKEN': csrfToken },
      type: 'GET',
      data: { 'id': id },
      success: function (response) {
        console.log(response);
        $("#txtbefore").val(response.audit.databefore)
        $("#txtafter").val(response.audit.dataafter)
      },
      error: function (xhr, status, error) {

      }
    });
  });


});
