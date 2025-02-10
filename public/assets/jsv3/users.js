
/**
 *  Datatable
 */
$uregisters = $("#usersregisters");
$uactives = $("#usersactives");
$ususpends = $("#userssuspends");
$uwithoutroles = $("#userswithoutroles");


$(document).ready(function () {
  dt_users = $('#tb-users').DataTable({
    paging: true, // Enable pagination
    ordering: true, // Enable ordering
    searching: true, // Enable search box
    lengthChange: true, // Show page size selector
    pageLength: 50, // Default page size
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
              columns: [1, 2, 3, 4, 5],
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
              columns: [1, 2, 3, 4, 5],
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
              columns: [1, 2, 3, 4, 5],
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
              columns: [1, 2, 3, 4, 5],
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
              columns: [1, 2, 3, 4, 5],
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
      {
        text: '<i class="bx bx-plus me-0 me-lg-2"></i><span class="d-none d-lg-inline-block">Nuevo usuario</span>',
        className: 'add-new btn btn-primary ms-n1',
        attr: {
          'data-bs-toggle': 'offcanvas',
          'data-bs-target': '#offcanvasAddUser'
        }
      }
    ],
    language: {
      paginate: {
        next: '<i class="fas fa-chevron-right"></i>', // Custom next button icon
        previous: '<i class="fas fa-chevron-left"></i>' // Custom previous button icon
      }
    },
    order: [
      [0, 'asc']
    ],
    columnDefs: [{
      targets: [6],
      orderable: false
    }]
  });

  // Función para limpiar el modal
  /**
  *  Modal CRUD Residencia
  */

  function checked(input) {
    if (input.is(':checked')) {
      $('#password').prop('disabled', false);
      $('#password_confirmation').prop('disabled', false);
      $('#change_next_login').prop('disabled', false);
    }
    else {
      $('#password').prop('disabled', true);
      $('#password_confirmation').prop('disabled', true);
      $('#password').val("");
      $('#password_confirmation').val("");
      $('#change_next_login').prop('disabled', true).prop('checked', false);
    }
  }

  $('#change_password').change(function () {
    checked($(this));
  });

  //abrir form - crear
  let reset = "crear"
  $(document).on('click', 'button.add-new', function () {
    //limpiar campos si cambiamos entre formulario nuevo usuario y formulario edicion
    if (reset == "editar") {
      limpiar();
      reset = "crear";
    }
    $('#change_password').prop('checked', true);
    checked($('#change_password'));
    $('.change_password').hide();
    $('.change_next_login').hide();
    $('#offcanvasAddUserLabel').text("Nuevo usuario");
    $('#btnupdate').hide();
    $('#btnnew').show();
  });


  //abrir form - editar
  let sameuser = 0;
  let actualrol;
  $(document).on('click', 'button.data-user', function () {
    limpiar();
    if (reset == "crear") {
      reset = "editar";
    }
    var id = $(this).attr('id');
    //reset campos passwords si se dio editar a usuario, ocultaron formulario y cambiaron de usuario a editar nuevamente.
    if (id != sameuser) {
      $('#change_password').prop('checked', false);
      checked($('#change_password'));
      $('#password').val("");
      $('#password_confirmation').val("");
      sameuser = id;
    }
    $('#btnupdate').show();
    $('#btnnew').hide();
    $('.change_password').show();
    $('.change_next_login').show();
    $.ajax({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      url: './users/' + id + '/edit',
      type: 'GET',
      data: { id: id },
      success: function (response) {
        // Mostrar los datos en el formulario
        console.log()
        if (response.authid == response.user[0].id) {
          $("#contentroles").hide()
        }
        else {
          $("#contentroles").show()
        }
        const notifications = response.user[0].notifications;
        notifications.forEach(notification => {
          $(`input[name="notifications"][value="${notification.id}"]`).prop('checked', true);
        });
        $('#offcanvasAddUserLabel').text("Editar usuario " + response.user[0].name + ' ' + response.user[0].last_name);
        $('#userid').val(response.user[0].id);
        $('#name').val(response.user[0].name);
        $('#last_name').val(response.user[0].last_name);
        $('#email').val(response.user[0].email);
        $('#username').val(response.user[0].username);
        $('#roles').val(response.user[0].roles[0].name).trigger('change');
        actualrol = response.user[0].roles[0].name;
      },
      error: function (xhr, status, error) {
        console.error(error);
      }
    });
  });


  //limpiar modal
  function limpiar() {
    // Muestra el contenido de la pestaña "Datos Generales" y oculta los demás
    $('#userTabs .nav-link').removeClass('active');
    $('#general-tab').addClass('active');
    $('#userTabsContent .tab-pane').removeClass('show active');
    $('#general').addClass('show active'); 

    //reset ampos
    $('#name').val("");
    $('#last_name').val("");
    $('#email').val("");
    $('#username').val("");
    $('#password').val("");
    $('#password_confirmation').val("");
    $('#roles').val("").trigger('change');
    $('#btnupdate').hide();
    $('#btnnew').hide();
    $('#change_password').prop('checked', false);
    $('#change_next_login').prop('disabled', true).prop('checked', false);
    $('input[name="notifications"]').prop('checked', false);
  }

  //cerrar form
  $('button.btn-close', 'button.btn-cancel').click(function () {
    limpiar();
  });



  $('#formusers').on('submit', function (event) {
    event.preventDefault();

    if (!this.checkValidity()) {
      event.stopPropagation();
      this.classList.add('was-validated');
      return;
    }

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
          var id = $('#userid').val();
          var name = $('#name').val().trim();
          var last_name = $('#last_name').val().trim();
          var email = $('#email').val().trim();
          var username = $('#username').val().trim();
          var password = $('#password').val().trim();
          var password_confirmation = $('#password_confirmation').val().trim();
          var roles = $("#roles option:selected").val();
          var change_password = $('#change_password').is(':checked') ? 'Y' : 'N';
          var change_next_login = $('#change_next_login').is(':checked') ? 'Y' : 'N';
          var notifications = [];
          $("input:checkbox[name=notifications]:checked").each(function () {
            notifications.push($(this).val());
          });
          $.ajax({
            url: './users/' + id,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: 'PUT',
            data: {
              name: name, last_name: last_name, email: email, username: username, roles: roles,
              notifications: notifications, password: password, password_confirmation: password_confirmation,
              change_password, change_next_login
            },
            success: function (response) {
              //reset campos passwords
              $('#change_password').prop('checked', false);
              checked($('#change_password'));

              //actualizar fila
              var htmlname = '' +
                '  <div class="d-flex justify-content-start align-items-center order-name text-nowrap  ">' +
                '    <div class="avatar-wrapper">' +
                '      <div class="avatar me-2"><span class="avatar-initial rounded-circle bg-label-' + response.avatar_class + '">' + response.name.charAt(0) + ' ' + response.last_name.charAt(0) + '</span></div>' +
                '    </div>' +
                '    <div class="d-flex flex-column">' +
                '      <h6 class="m-0"><a href="pages-profile-user.html" class="text-body">' + response.name + ' ' + response.last_name + '</a></h6>' +
                '      <small class="text-muted">' + (response.email !== null ? response.email : '') + '</small>' +
                '    </div>' +
                '  </div>';

              var id = response.id;
              var fila = $('tr[row="' + id + '"]');
              fila.find('.name').html(htmlname);
              fila.find('.roles').text(response.roles);
              fila.find('.username').text(response.username);
              fila.find('.email_verified').text(response.email_verified);

              //actualizamos cards
              if (actualrol == "Suspendido" && response.roles != "Suspendido") {
                var uactives = parseInt($uactives.text()) + 1;
                var ususpends = parseInt($ususpends.text()) - 1;
                $uactives.text(uactives)
                $ususpends.text(ususpends)
              }
              else if (actualrol != "Suspendido" && response.roles == "Suspendido") {
                var uactives = parseInt($uactives.text()) - 1;
                var ususpends = parseInt($ususpends.text()) + 1;
                $uactives.text(uactives)
                $ususpends.text(ususpends)
              }


              $('#offcanvasAddUser').offcanvas('hide');
              toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
              limpiar();
            },
            error: function (xhr, status, error) {
              toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
          });
        }
      });
    } else {
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
          var name = $('#name').val().trim();
          var last_name = $('#last_name').val().trim();
          var email = $('#email').val().trim();
          var username = $('#username').val().trim();
          var password = $('#password').val().trim();
          var password_confirmation = $('#password_confirmation').val().trim();
          var roles = $("#roles option:selected").val();
          var notifications = [];
          $('input[name="notifications[]"]:checked').each(function () {
            notifications.push($(this).val());
          });

          $.ajax({
            url: './users',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: 'POST',
            data: {
              name: name, last_name: last_name, email: email, username: username,
              notifications: notifications, roles: roles, password: password,
              password_confirmation: password_confirmation
            },
            success: function (response) {

              var numRows = dt_users.rows().count() + 1;
              var created_at = moment(response.created_at).format('DD MMM YYYY HH:mm');

              var newRow = '<tr row="' + response.id + '">' +
                '<td class="sorting_1">' + numRows + '</td>' +
                '<td class="date">' + created_at + '</td>' +
                '<td class="name">' +
                '  <div class="d-flex justify-content-start align-items-center order-name text-nowrap  ">' +
                '    <div class="avatar-wrapper">' +
                '      <div class="avatar me-2"><span class="avatar-initial rounded-circle bg-label-' + response.avatar_class + '">' + response.name.charAt(0) + ' ' + response.last_name.charAt(0) + '</span></div>' +
                '    </div>' +
                '    <div class="d-flex flex-column">' +
                '      <h6 class="m-0"><a href="pages-profile-user.html" class="text-body">' + response.name + ' ' + response.last_name + '</a></h6>' +
                '      <small class="text-muted">' + (response.email !== null ? response.email : '') + '</small>' +
                '    </div>' +
                '  </div>' +
                '</td>' +
                '<td class="roles">' + response.roles + '</td>' +
                '<td class="username">' + response.username + '</td>' +
                '<td class="email_verified">' + (response.email_verified !== null ? 'No' : 'Si') + '</td>' +
                '<td><button type="button" class="data-user btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#" id="' + response.id + '">Editar</button></td>' +
                '</tr>';

              dt_users.row.add($(newRow)).draw();

              //actualizamos cards
              var uregisters = parseInt($uregisters.text()) + 1;
              console.log(roles);
              var uactives = (roles != 'Suspendido' ? parseInt($uactives.text()) + 1 : $uactives.text());
              var ususpends = (roles == 'Suspendido' ? parseInt($ususpends.text()) + 1 : $ususpends.text());
              $uregisters.text(uregisters)
              $uactives.text(uactives)
              $ususpends.text(ususpends)

              $('#offcanvasAddUser').offcanvas('hide');
              toastr.success(response.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
              limpiar();
            },
            error: function (xhr, status, error) {
              toastr.error(xhr.responseJSON.message, { progressBar: true, showDuration: 1000, hideDuration: 1000 });
            }
          });
        }
      });
    }
    this.classList.add('was-validated');
  });


});