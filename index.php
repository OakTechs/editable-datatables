<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Tabulator Editable Table Example</title>
  <link href="https://unpkg.com/tabulator-tables@5.5.0/dist/css/tabulator.min.css" rel="stylesheet">
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
    }
    h2 {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

  <h2>Employee List (Editable)</h2>
  <div id="example-table"></div>

  <script src="https://unpkg.com/tabulator-tables@5.5.0/dist/js/tabulator.min.js"></script>
  <script>
    // Sample data
    var tableData = [
      { id: 1, name: "John Doe", position: "Developer", start_date: "2024-01-15" },
      { id: 2, name: "Jane Smith", position: "Analyst", start_date: "2023-09-10" },
      { id: 3, name: "Sam Lee", position: "Manager", start_date: "2022-05-20" }
    ];

    // Initialize Tabulator
    var table = new Tabulator("#example-table", {
      height: "300px",
      layout: "fitColumns",
      data: tableData,
      columns: [
        { title: "ID", field: "id", width: 50, hozAlign: "center" },
        { title: "Name", field: "name", editor: "input" },
        {
          title: "Position",
          field: "position",
          editor: "select",
          editorParams: {
            values: ["Developer", "Manager", "Analyst", "IT Support"]
          }
        },
        {
          title: "Start Date",
          field: "start_date",
          editor: "input",
          editorParams: {
            elementAttributes: {
              type: "date" // Native date picker
            }
          }
        }
      ],
      cellEdited: function(cell) {
        // Called whenever a cell is edited
        var updatedRow = cell.getRow().getData();
        console.log("Updated row data:", updatedRow);

        // You can send updatedRow to your backend like this:
        /*
        fetch("update.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify(updatedRow)
        }).then(res => res.json())
          .then(response => {
            console.log("Saved successfully", response);
          });
        */
      }
    });
  </script>

</body>
</html>
