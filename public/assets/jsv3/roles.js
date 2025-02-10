/**
 * DATA TABLE ROLES
 */

'use strict';
$(document).ready(function () {
  var dt_permissions; // Variable global para la instancia de DataTable
  // Inicializar DataTable
  dt_permissions = $('#tb-permissions').DataTable({
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
              columns: [0, 1, 2, 3],
              // prevent avatar to be print
              format: {
                body: function (inner, coldex, rowdex) {
                  if (inner.length <= 0) return inner;
                  var el = $.parseHTML(inner);
                  var result = '';
                  $.each(el, function (index, item) {
                    if (item.classList !== undefined && item.classList.contains('user-name')) {
                      result = result + item.lastChild.firstChild.textContent;
                    } else if (item.innerText === undefined) {
                      result = result + item.textContent;
                    } else result = result + item.innerText;
                  });
                  return result;
                }
              }
            },
            customize: function (win) {
              //customize print view for dark
              $(win.document.body)
                .css('color', headingColor)
                .css('border-color', borderColor)
                .css('background-color', bodyBg);
              $(win.document.body)
                .find('table')
                .addClass('compact')
                .css('color', 'inherit')
                .css('border-color', 'inherit')
                .css('background-color', 'inherit');
            }
          },
          {
            extend: 'csv',
            text: '<i class="bx bx-file me-2" ></i>Csv',
            className: 'dropdown-item',
            exportOptions: {
              columns: [0, 1, 2, 3],
              // prevent avatar to be display
              format: {
                body: function (inner, coldex, rowdex) {
                  if (inner.length <= 0) return inner;
                  var el = $.parseHTML(inner);
                  var result = '';
                  $.each(el, function (index, item) {
                    if (item.classList !== undefined && item.classList.contains('user-name')) {
                      result = result + item.lastChild.firstChild.textContent;
                    } else if (item.innerText === undefined) {
                      result = result + item.textContent;
                    } else result = result + item.innerText;
                  });
                  return result;
                }
              }
            }
          },
          {
            extend: 'excel',
            text: '<i class="bx bxs-file-export me-2"></i>Excel',
            className: 'dropdown-item',
            exportOptions: {
              columns: [0, 1, 2, 3],
              // prevent avatar to be display
              format: {
                body: function (inner, coldex, rowdex) {
                  if (inner.length <= 0) return inner;
                  var el = $.parseHTML(inner);
                  var result = '';
                  $.each(el, function (index, item) {
                    if (item.classList !== undefined && item.classList.contains('user-name')) {
                      result = result + item.lastChild.firstChild.textContent;
                    } else if (item.innerText === undefined) {
                      result = result + item.textContent;
                    } else result = result + item.innerText;
                  });
                  return result;
                }
              }
            }
          },
          {
            extend: 'pdf',
            text: '<i class="bx bxs-file-pdf me-2"></i>Pdf',
            className: 'dropdown-item',
            exportOptions: {
              columns: [0, 1, 2, 3],
              // prevent avatar to be display
              format: {
                body: function (inner, coldex, rowdex) {
                  if (inner.length <= 0) return inner;
                  var el = $.parseHTML(inner);
                  var result = '';
                  $.each(el, function (index, item) {
                    if (item.classList !== undefined && item.classList.contains('user-name')) {
                      result = result + item.lastChild.firstChild.textContent;
                    } else if (item.innerText === undefined) {
                      result = result + item.textContent;
                    } else result = result + item.innerText;
                  });
                  return result;
                }
              }
            }
          },
          {
            extend: 'copy',
            text: '<i class="bx bx-copy me-2" ></i>Copiar',
            className: 'dropdown-item',
            exportOptions: {
              columns: [0, 1, 2, 3],
              // prevent avatar to be display
              format: {
                body: function (inner, coldex, rowdex) {
                  if (inner.length <= 0) return inner;
                  var el = $.parseHTML(inner);
                  var result = '';
                  $.each(el, function (index, item) {
                    if (item.classList !== undefined && item.classList.contains('user-name')) {
                      result = result + item.lastChild.firstChild.textContent;
                    } else if (item.innerText === undefined) {
                      result = result + item.textContent;
                    } else result = result + item.innerText;
                  });
                  return result;
                }
              }
            }
          }
        ]
      },
    ],
    language: {
      paginate: {
        next: '<i class="fas fa-chevron-right"></i>',
        previous: '<i class="fas fa-chevron-left"></i>'
      }
    },
    rowGroup: {
      "dataSrc": 2
    },
    columnDefs: [
      {
        targets: [2],
        visible: false,
        searchable: true
      },
      {
        targets: [3],
        orderable: false
      }]

  });

  // Evento para cambiar el filtro al seleccionar una opción del select
  $('#modules').on('change', function () {
    var module = $(this).val();

    // Aplicar filtro al DataTable a la columna Modulo
    dt_permissions.columns(2).search(module).draw();
  });

  /**
   *Generar lista de roles
   */


  function actualizarListaRoles(rol, users) {

  }

  actualizarListaRoles();


  /**
   *CRUD ROLES
   */

  function verificarCheckboxes() {
    var todosMarcados = true;
    $('.form-check-input.permission').each(function () {
      if (!$(this).prop('checked')) {
        todosMarcados = false;
        return false; // Salir del bucle si uno no está marcado
      }
    });
    // Marcar o desmarcar el checkbox selectAll
    $('.selectAll').prop('checked', todosMarcados);
  }



  // Cuando se hace clic en cualquier checkbox con clase .permission
  $('.form-check-input permission').on('click', function () {
    verificarCheckboxes();
  });

  // También verificar inicialmente al cargar la página
  verificarCheckboxes();

  //limpiar modal
  function limpiar() {
    $("#modalRoleName").val("");
    $('.form-check-input').prop('checked', false);
    $('.role-title').html('Añadir nuevo rol');
    $('#btnupdate').hide();
    $('#btnnew').hide();
    $("#roleid").val("");
  }



  //cerrar form
  $('button.btn-close, button.btn-cancel').click(function () {
    limpiar();
  });


  //FORMULARIO - NUEVO 
  $('.add-new-role').on('click', function () {
    $('#btnupdate').hide();
    $('#btnnew').show();
  });



  //FORMULARIO -  EDITAR
  $('.role-edit-modal').each(function () {
    $(this).on('click', function () {
      limpiar()
      $('.role-title').html('Editar Rol');
      $('#btnupdate').show();
      $('#btnnew').hide();
      var id = $(this).attr("data-id");
      $("#roleid").val(id);
      $.ajax({
        url: './roles/' + id + '/edit',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        type: 'GET',
        data: { id: id },
        success: function (response) {
          var modalRoleName = response.role.name;
          var permissions = response.permissions;
          $("#modalRoleName").val(modalRoleName);
          // Recorremos cada permiso y marcamos los checkboxes correspondientes
          permissions.forEach(function (permission) {
            var id = permission.id;
            $('#permission' + id).prop('checked', true);
          });
          verificarCheckboxes();
        },
        error: function (xhr, status, error) {
          toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
        }
      });
    });
  });

  $(document).on('click', '#btnupdate, #btnnew', function (event) {
    event.preventDefault();

    if ($('#btnupdate').is(':visible')) {
      // Actualizar registro existente
      Swal.fire({
        title: 'Guardar registro',
        text: '¿Estás seguro de guardar cambios?',
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
          var id = $("#roleid").val();
          var name = $("#modalRoleName").val();
          var permissions = [];
          $('input.permission:checked').each(function () {
            permissions.push($(this).val());
          });
          $.ajax({
            url: './roles/' + id,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: 'PUT',
            data: { name: name, permissions: permissions },
            success: function (response) {
              var RoleName = response.role.name;
              $('#CardRolename' + id).text(RoleName);
              $('#addRoleModal').modal('hide');
              toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
              limpiar();
            },
            error: function (xhr, status, error) {
              toastr.error(xhr.responseJSON.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
          });
        }
      });
    } else {
      console.log
      // Crear nuevo registro
      Swal.fire({
        title: 'Crear registro',
        text: '¿Estás seguro de guardar cambios?',
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
          if ($("#modalRoleName").val() == "") {
            return;
          }
          var name = $("#modalRoleName").val();
          var permissions = [];
          $('input.permission:checked').each(function () {
            permissions.push($(this).val());
          });
          $.ajax({
            url: './roles',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: 'POST',
            data: { name: name, permissions: permissions },
            success: function (response) {
              $('#addRoleModal').modal('hide');
              toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
              limpiar();
            },
            error: function (xhr, status, error) {
              toastr.error(xhr.responseJSON.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
          });
        }
      });
    }
    this.classList.add('was-validated');
  });

  //eliminar rol
  $(document).on('click', '.role-remove-modal', function (event) {
    Swal.fire({
      title: 'Eliminar',
      text: '¿Estás seguro de eliminar este rol?',
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
        var id = $(this).attr("data-id");
        $.ajax({
          url: './roles/' + id,
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          type: 'DELETE',
          data: {},
          success: function (response) {
            $('#addRoleModal').modal('hide');
            toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            limpiar();
          },
          error: function (xhr, status, error) {
            toastr.error(xhr.responseJSON.error, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
          }
        });
      }
    });
  });
});
