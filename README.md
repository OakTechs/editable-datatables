# 📝 Editable DataTable Plugin

A **jQuery plugin** to create editable DataTables with **inline**, **bubble**, and **full-row editing** modes. Built using **jQuery**, **DataTables**, and **Bootstrap**, it allows users to interactively **add, edit, and delete** table records with various input types like text, date, and select.

---

## 🚀 Features

- ✏️ **Multiple Editing Modes**: Inline, bubble, and row-based editing.
- ➕ **Add & Delete Records**: Manage records via modals and checkboxes.
- 🛠️ **Customizable Columns**: Supports `text`, `date`, and `select` input types.
- ☑️ **Checkbox Selection**: Bulk-select rows for deletion.
- 💲 **Currency Formatting**: Format salary fields as currency.
- 🆓 **Open Source**: MIT License – free to use and modify.

---

## 📦 Prerequisites

Ensure the following dependencies are included:

```bash
jQuery        : v3.7.1+
DataTables    : v1.13.6+
Bootstrap     : v5.3.2+ (for modals & styling)
jQuery UI     : v1.13.2+ (for datepicker)
```

---

## ⚙️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/editable-datatable.git
```

### 2. Include Dependencies in HTML

```html
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- Bootstrap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery UI (Datepicker) -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
```

### 3. Include the Plugin

```html
<script src="path/to/editableTable.js"></script>
```

### 4. Backend Setup (e.g. `data.php`)

Ensure you have a backend endpoint (e.g., `data.php`) that handles:
- `GET` for fetching data
- `POST` for adding/updating/deleting records

---

## 📖 Usage

### 1. Create the HTML Table

```html
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
```

### 2. Add CSS

```html
<link rel="stylesheet" href="style.css">
```

### 3. Initialize the Plugin

```html
<script>
$(document).ready(function() {
  $('#myTable').makeEditableTable({
    dataUrl: 'data.php',
    editableModes: ['inline', 'bubble', 'row'],
    enableAdd: true,
    enableDelete: true,
    columns: [
      {
        data: null,
        orderable: false,
        className: 'select-checkbox',
        defaultContent: '',
        render: function(data, type, row) {
          return `<input type="checkbox" class="row-select" data-id="${row.id}">`;
        }
      },
      { data: 'id' },
      { data: 'first_name', editType: 'text' },
      { data: 'last_name', editType: 'text' },
      { data: 'position', editType: 'select', options: ['Software Developer', 'Data Analyst', 'IT'] },
      { data: 'office', editType: 'text' },
      { data: 'start_date', editType: 'date' },
      { data: 'salary', editType: 'number' }
    ]
  });
});
</script>
```

---

## 🛠 Configuration Example

```js
{
  dataUrl: 'data.php',              // AJAX data source
  editableModes: ['inline', 'row'], // Editing modes
  enableAdd: true,                  // Show 'Add' button
  enableDelete: true,               // Show 'Delete' button
  columns: [
    { data: 'first_name', editType: 'text' },
    { data: 'start_date', editType: 'date' },
    { data: 'position', editType: 'select', options: ['Option1', 'Option2'] }
  ]
}
```

---

## 📄 License

MIT © [Your Name or Organization]
