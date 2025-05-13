<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DataTable Full Row, Bubble, and Inline Editing</title>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables CSS + JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap CSS + JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery UI CSS + JS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <style>
        #bubbleEditor {
            display: none;
            position: absolute;
            background: white;
            padding: 10px;
            border: 1px solid black;
            z-index: 1000;
        }
        .edit-buttons {
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="p-4">

    <div class="edit-buttons">
        <button id="fullRowBtn" class="btn btn-primary btn-sm">Full Row Editing</button>
        <button id="bubbleBtn" class="btn btn-success btn-sm">Bubble Editing</button>
        <button id="inlineBtn" class="btn btn-warning btn-sm">Inline Editing</button>
        <button id="addBtn" class="btn btn-info btn-sm">Add</button>
        <button id="deleteBtn" class="btn btn-danger btn-sm">Delete</button>
    </div>

    <table id="example" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th> Id </th>
                <th>First name</th>
                <th>Last name</th>
                <th>Position</th>
                <th>Office</th>
                <th>Start date</th>
                <th>Salary</th>
            </tr>
        </thead>
    </table>

    <!-- Bootstrap Modal for full row edit -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Row</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="editForm">
              <input type="hidden" id="rowIndex">
              <div class="mb-3">
                <label>First name</label>
                <input type="text" class="form-control" id="firstName">
              </div>
              <div class="mb-3">
                <label>Last name</label>
                <input type="text" class="form-control" id="lastName">
              </div>
              <div class="mb-3">
                <label>Position</label>
                <select class="form-control" id="position">
                    <option value="Software Developer">Software Developer</option>
                    <option value="Seltos">Seltos</option>
                    <option value="Data Analyst">Data Analyst</option>
                    <option value="IT">IT</option>
                </select>
               </div>
              <div class="mb-3">
                <label>Office</label>
                <input type="text" class="form-control" id="office">
              </div>
              <div class="mb-3">
                <label>Start date</label>
                <input type="date" class="form-control" id="startDate">
              </div>
              <div class="mb-3">
                <label>Salary</label>
                <input type="text" class="form-control" id="salary">
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" id="saveBtn" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Popup for bubble editing -->
    <div id="bubbleEditor">
      <input type="text" id="bubbleInput" class="form-control mb-2">
      <button id="bubbleSaveBtn" class="btn btn-sm btn-success">Save</button>
    </div>

    <script>
    $(document).ready(function() {
        var selectedRow;
        var table = $('#example').DataTable({
            ajax: {
              url: 'data.php',
              type: 'GET',
              dataSrc: 'data'
          },
            columns: [
                { data: 'id' },
                { data: 'first_name' },
                { data: 'last_name' },
                { data: 'position' },
                { data: 'office' },
                { data: 'start_date' },
                { data: 'salary',
                    render: function (data, type, row) {
                        let cleaned = (data + '').replace(/,/g, '');
                        let number = parseFloat(cleaned);
                        if (type === 'display' || type === 'filter') {
                            return new Intl.NumberFormat('en-US', {
                                style: 'currency',
                                currency: 'USD',
                                minimumFractionDigits: 2
                            }).format(isNaN(number) ? 0 : number);
                        }
                    return data;
                    }
                }
            ]
        });

        $('#example tbody').on('click', 'tr', function () {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
                selectedRow = null;
            } else {
                table.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
                selectedRow = table.row(this);
            }
        });

        $('#deleteBtn').click(function () {
        if (!selectedRow) {
            alert('Please select a row to delete.');
            return;
        }

        var rowData = selectedRow.data();
        if (confirm('Are you sure you want to delete this record?')) {
            $.post('data.php', { id: rowData.id, mode: 'delete' }, function (response) {
                if (response.success) {
                    table.ajax.reload(null, false);
                    selectedRow = null;
                } else {
                    alert('Failed to delete the record.');
                }
            }, 'json');
        }
    });


        var currentEditMode = '';

        $('#fullRowBtn').click(function() { currentEditMode = 'row'; });
        $('#bubbleBtn').click(function() { currentEditMode = 'bubble'; });
        $('#inlineBtn').click(function() { currentEditMode = 'inline'; });

        // Full Row Editing
        $('#example tbody').on('click', 'tr', function() {
            if (currentEditMode !== 'row') return;
            var rowData = table.row(this).data();
            $('#rowIndex').val(table.row(this).index());
            $('#firstName').val(rowData.first_name);
            $('#lastName').val(rowData.last_name);
            $('#position').val(rowData.position);
            $('#office').val(rowData.office);
            $('#startDate').val(rowData.start_date);
            $('#salary').val(rowData.salary);
            var editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        });

        $('#saveBtn').click(function() {
            var mode = $(this).data('mode') || 'edit';
            var idx = $('#rowIndex').val();
            var rowData = {
                id: table.row(idx).data().id,
                first_name: $('#firstName').val(),
                last_name: $('#lastName').val(),
                position: $('#position').val(),
                office: $('#office').val(),
                start_date: $('#startDate').val(),
                salary: $('#salary').val()
            };

            if (mode === 'edit') {
                rowData.id = table.row(idx).data().id;
            }

            rowData.mode = mode;
            $.post('data.php', rowData, function(response) {
                if (response.success) {
                    table.ajax.reload(null, false);
                }
            }, 'json');

            var editModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
            editModal.hide();
        });

        // Add Button Click
        $('#addBtn').click(function () {
            $('#editForm')[0].reset(); // Clear form
            $('#rowIndex').val(''); // Clear row index
            $('#editModal .modal-title').text('Add New Record');
            $('#saveBtn').data('mode', 'add');
            new bootstrap.Modal(document.getElementById('editModal')).show();
        });


        // Bubble Editing
        $('#example tbody').on('click', 'td', function(e) {
            if (currentEditMode !== 'bubble') return;
            var cell = table.cell(this);
            var columnIdx = cell.index().column;
            var columnName = table.settings().init().columns[columnIdx].data;
            var inputField;
            if (columnName === 'position') {
                inputField = $('<select class="form-control mb-2" id="bubbleInput">\
                    <option value="Software Developer">Software Developer</option>\
                    <option value="Seltos">Seltos</option>\
                    <option value="Data Analyst">Data Analyst</option>\
                    <option value="IT">IT</option>\
                </select>');
                inputField.val(cell.data());
            } else {
                inputField = $('<input type="text" class="form-control mb-2" id="bubbleInput">').val(cell.data());
            }
            $('#bubbleEditor').html('').append(inputField).append('<button id="bubbleSaveBtn" class="btn btn-sm btn-success">Save</button>');

            $('#bubbleEditor').css({
                top: e.pageY + 10,
                left: e.pageX + 10
            }).show().data('cell', cell).data('column', cell.index().column);

            if (columnName === 'start_date') {
                $('#bubbleInput').datepicker({
                    dateFormat: 'yy-mm-dd',
                    onClose: function() {
                        $('#bubbleInput').blur();
                    }
                }).datepicker('show');
            } else {
                $('#bubbleInput').datepicker('destroy');
            }

            e.stopPropagation();
        });

        $('#bubbleSaveBtn').click(function() {
          var cell = $('#bubbleEditor').data('cell');
          var rowData = table.row(cell.index().row).data();
          var columnIdx = cell.index().column;
          var columnName = table.settings().init().columns[columnIdx].data;
          var newValue = $('#bubbleInput').val();
          console.log("row data id is: ", rowData.id);
          var postData = {
              id: rowData.id
          };
          postData[columnName] = newValue;

          $.post('data.php', postData, function(response) {
              if (response.success) {
                  table.ajax.reload(null, false);
              }
          }, 'json');

          $('#bubbleEditor').hide();
      });


        $(document).click(function(e) {
            if (!$(e.target).closest('#bubbleEditor').length) {
                $('#bubbleEditor').hide();
            }
        });

        // Inline Editing
        $('#example tbody').on('click', 'td', function() {
            if (currentEditMode !== 'inline') return;
            var cell = table.cell(this);
            var originalData = cell.data();
            var rowData = table.row(cell.index().row).data();
            var columnIdx = cell.index().column;
            var columnName = table.settings().init().columns[columnIdx].data;
            var input;
            if (columnName === 'position') {
                input = $('<select class="form-control">\
                    <option value="Software Developer">Software Developer</option>\
                    <option value="Seltos">Seltos</option>\
                    <option value="Data Analyst">Data Analyst</option>\
                    <option value="IT">IT</option>\
                </select>');
                input.val(originalData);
            } else if (columnName === 'start_date') {
                input = $('<input type="text" class="form-control" value="' + originalData + '" />');
            } else {
                input = $('<input type="text" class="form-control" value="' + originalData + '" />');
            }
            $(this).html(input);
            input.focus();

            if (columnName === 'start_date') {
                input.datepicker({
                    dateFormat: 'yy-mm-dd',
                    onSelect: function (dateText) {
                        input.val(dateText);
                        input.data('selected-date', dateText);
                    },
                    onClose: function() {
                        input.blur();
                    }
                }).datepicker('show');
            }

            input.blur(function() {
                var newValue = columnName === 'start_date' ? (input.data('selected-date') || input.val()) : input.val();
                var postData = {
                    id: rowData.id
                };
                postData[columnName] = newValue;

                $.post('data.php', postData, function(response) {
                    if (response.success) {
                        cell.data(newValue).draw();
                    } else {
                        cell.data(originalData).draw();
                    }
                }, 'json');
            });

            input.keypress(function(e) {
                if (e.which == 13) { // Enter key
                    input.blur();
                }
            });
        });
    });
    </script>

</body>
</html>
