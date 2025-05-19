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

    <!-- Buttons -->
      <div class="edit-buttons">
        <button class="btn btn-primary btn-sm edit-mode-btn" data-mode="row">Full Row Editing</button>
        <button class="btn btn-success btn-sm edit-mode-btn" data-mode="bubble">Bubble Editing</button>
        <button class="btn btn-warning btn-sm edit-mode-btn" data-mode="inline">Inline Editing</button>
        <button id="addBtn" class="btn btn-info btn-sm">Add</button>
        <button id="deleteBtn" class="btn btn-danger btn-sm">Delete</button>
      </div>

      <!-- Table -->
      <table id="myTable" class="display" width="100%">
        <thead>
          <tr>
            <th>Id</th>
            <th>First name</th>
            <th>Last name</th>
            <th>Position</th>
            <th>Office</th>
            <th>Start date</th>
            <th>Salary</th>
          </tr>
        </thead>
      </table>

<!-- Modal for full row editing -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editForm">
          <input type="hidden" id="rowIndex">
          <div class="mb-3"><label>First name</label><input type="text" class="form-control" id="first_name"></div>
          <div class="mb-3"><label>Last name</label><input type="text" class="form-control" id="last_name"></div>
          <div class="mb-3"><label>Position</label>
            <select class="form-control" id="position">
              <option value="Software Developer">Software Developer</option>
              <option value="Seltos">Seltos</option>
              <option value="Data Analyst">Data Analyst</option>
              <option value="IT">IT</option>
            </select>
          </div>
          <div class="mb-3"><label>Office</label><input type="text" class="form-control" id="office"></div>
          <div class="mb-3"><label>Start date</label><input type="date" class="form-control" id="start_date"></div>
          <div class="mb-3"><label>Salary</label><input type="text" class="form-control" id="salary"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="saveBtn" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<script src="editableTable.js"></script>
<script>
$(document).ready(function() {
  $('#myTable').makeEditableTable({
    dataUrl: 'data.php',
    columns: [
      { data: 'id' },
      { data: 'first_name', editType: 'text' },
      { data: 'last_name', editType: 'text' },
      { data: 'position', editType: 'select', options: ['Software Developer', 'Seltos', 'Data Analyst', 'IT'] },
      { data: 'office', editType: 'text' },
      { data: 'start_date', editType: 'date' },
      {
        data: 'salary',
        editType: 'text',
        render: function(data, type, row) {
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

  $('#addBtn').click(function () {
    $('#editForm')[0].reset();
    $('#rowIndex').val('');
    $('#editModal .modal-title').text('Add New Record');
    $('#saveBtn').data('mode', 'add');
    new bootstrap.Modal(document.getElementById('editModal')).show();
  });
});
</script>


</body>
</html>
