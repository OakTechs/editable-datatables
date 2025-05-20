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

      <!-- Table -->
      <table id="myTable" class="display" width="100%">
        <thead>
          <tr>
            <th><input type="checkbox" id="selectAll"></th> 
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



<script src="editableTable.js"></script>
<script>
$(document).ready(function() {
  $('#myTable').makeEditableTable({
    dataUrl: 'data.php',
    editableModes: ['inline', 'bubble', 'row'],
    
    columns: [
      {
        data: null,
        orderable: false,
        className: 'select-checkbox',
        defaultContent: '',
        render: function (data, type, row) {
          return `<input type="checkbox" class="row-select" data-id="${row.id}">`;
        }
      },
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
