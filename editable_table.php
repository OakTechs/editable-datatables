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
    </div>

    <table id="example" class="display nowrap" style="width:100%">
        <thead>
            <tr>
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
                <input type="text" class="form-control" id="position">
              </div>
              <div class="mb-3">
                <label>Office</label>
                <input type="text" class="form-control" id="office">
              </div>
              <div class="mb-3">
                <label>Start date</label>
                <input type="text" class="form-control" id="startDate">
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
        var yourDataArray = [
            { first_name: "Tiger", last_name: "Nixon", position: "System Architect", office: "Edinburgh", start_date: "2011-04-25", salary: "$320,800" },
            { first_name: "Garrett", last_name: "Winters", position: "Accountant", office: "Tokyo", start_date: "2011-07-25", salary: "$170,750" },
            { first_name: "Ashton", last_name: "Cox", position: "Junior Technical Author", office: "San Francisco", start_date: "2009-01-12", salary: "$86,000" }
        ];

        var table = $('#example').DataTable({
            data: yourDataArray,
            columns: [
                { data: 'first_name' },
                { data: 'last_name' },
                { data: 'position' },
                { data: 'office' },
                { data: 'start_date' },
                { data: 'salary' }
            ]
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
            var idx = $('#rowIndex').val();
            table.row(idx).data({
                first_name: $('#firstName').val(),
                last_name: $('#lastName').val(),
                position: $('#position').val(),
                office: $('#office').val(),
                start_date: $('#startDate').val(),
                salary: $('#salary').val()
            }).draw();
            var editModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
            editModal.hide();
        });

        // Bubble Editing
        $('#example tbody').on('click', 'td', function(e) {
            if (currentEditMode !== 'bubble') return;
            var cell = table.cell(this);
            $('#bubbleInput').val(cell.data());
            $('#bubbleEditor').css({
                top: e.pageY + 10,
                left: e.pageX + 10
            }).show().data('cell', cell);
            e.stopPropagation();
        });

        $('#bubbleSaveBtn').click(function() {
            var cell = $('#bubbleEditor').data('cell');
            cell.data($('#bubbleInput').val()).draw();
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
            var input = $('<input type="text" class="form-control" value="' + originalData + '"/>');
            $(this).html(input);
            input.focus();
            input.blur(function() {
                cell.data(this.value).draw();
            });
            input.keypress(function(e) {
                if (e.which == 13) { // Enter key
                    cell.data(this.value).draw();
                }
            });
        });
    });
    </script>

</body>
</html>
