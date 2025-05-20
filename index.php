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
    <link rel="stylesheet" href="style.css">
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
    enableAdd: true, // New option to control Add button
    enableDelete: true, // New option to control Delete button
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
      { data: 'id' , editType: 'number'},
      { data: 'first_name', editType: 'text' },
      { data: 'last_name', editType: 'text' },
      { data: 'position', editType: 'select', options: ['Software Developer', 'Seltos', 'Data Analyst', 'IT'] },
      { data: 'office', editType: 'text' },
      { data: 'start_date', editType: 'date' },
      {data: 'salary', editType: 'number'}
    ]

  });

});
</script>


</body>
</html>
