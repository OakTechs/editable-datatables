(function($) {
  $.fn.makeEditableTable = function(options) {
    const settings = $.extend({
      dataUrl: 'data.php',
      editableModes: ['row', 'bubble', 'inline'],
      columns: [],
    }, options);

    // Apply number formatting to columns with editType: 'number'
    settings.columns.forEach(column => {
      if (column.editType === 'number') {
        column.render = function(data, type, row) {
          if (type === 'display' || type === 'filter') {
            let cleaned = (data + '').replace(/,/g, '');
            let number = parseFloat(cleaned);
            return new Intl.NumberFormat('en-US', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            }).format(isNaN(number) ? 0 : number);
          }
          return data;
        };
      }
    });

      // Create buttons dynamically before initializing DataTable
        const $buttonContainer = $('<div class="edit-buttons"></div>');
        const buttonConfigs = [
            { mode: 'row', text: 'Full Row Editing', class: 'btn-primary' },
            { mode: 'bubble', text: 'Bubble Editing', class: 'btn-success' },
            { mode: 'inline', text: 'Inline Editing', class: 'btn-warning' }
        ];

        // Add edit mode buttons based on editableModes
        buttonConfigs.forEach(config => {
            if (settings.editableModes.includes(config.mode)) {
                const $button = $(`<button class="btn btn-sm ${config.class} edit-mode-btn" data-mode="${config.mode}">${config.text}</button>`);
                $buttonContainer.append($button);
            }
        });

        // Add 'Add' button if enabled
        if (settings.enableAdd) {
            const $addBtn = $('<button id="addBtn" class="btn btn-info btn-sm">Add</button>');
            $buttonContainer.append($addBtn);
        }

        // Add 'Delete' button if enabled
        if (settings.enableDelete) {
            const $deleteBtn = $('<button id="deleteBtn" class="btn btn-danger btn-sm">Delete</button>');
            $buttonContainer.append($deleteBtn);
        }

        // Insert buttons directly into the body, before the table
        $('body').prepend($buttonContainer);

    const table = this.DataTable({
      ajax: {
        url: settings.dataUrl,
        type: 'GET',
        dataSrc: 'data'
      },
      columns: settings.columns
    });

    let selectedRow = null;
    let currentEditMode = settings.editableModes.length === 1 ? settings.editableModes[0] : '';

    const $bubbleEditor = $('<div id="bubbleEditor" style="display:none; position:absolute; background:white; padding:10px; border:1px solid black; z-index:1000;"></div>');
    $('body').append($bubbleEditor);

    // Create modal dynamically
        const $modal = $(`
            <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Record</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editForm">
                                <!-- Form fields will be dynamically generated -->
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="saveBtn" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        $('body').append($modal);
    
    // Create delete confirmation modal dynamically
    const $deleteModal = $(`
      <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title">Confirm Deletion</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p id="deleteModalMessage">Are you sure you want to delete <span id="deleteCount"></span> selected record(s)? </p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
            </div>
          </div>
        </div>
      </div>
    `);
    $('body').append($deleteModal);

    // Dynamically generate modal form fields based on columns
        function generateModalForm() {
            const $form = $('#editForm').empty(); // Clear existing form content
            $form.append('<input type="hidden" id="rowIndex">'); // Hidden input for row ID

            settings.columns.forEach((col, index) => {
                if (col.data && col.editType && col.data !== 'id') { // Skip non-editable columns and ID
                    const label = col.data.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                    const $formGroup = $('<div class="mb-3"></div>');
                    $formGroup.append(`<label>${label}</label>`);

                    let input;
                    if (col.editType === 'select' && Array.isArray(col.options)) {
                        input = $(`<select class="form-control" id="${col.data}"></select>`);
                        col.options.forEach(opt => input.append(`<option value="${opt}">${opt}</option>`));
                    } else if (col.editType === 'date') {
                        input = $(`<input type="date" class="form-control" id="${col.data}">`);
                    } else if (col.editType === 'number') {
                      input = $(`<input type="number" step="0.01" class="form-control" id="${col.data}">`);
                    } else {
                        input = $(`<input type="text" class="form-control" id="${col.data}">`);
                    }

                    $formGroup.append(input);
                    $form.append($formGroup);
                }
            });
        }

        // Initialize modal form on table creation
        if (settings.editableModes.includes('row') || settings.enableAdd) {
            generateModalForm();
        }

    // Button Event Binding
    $(document).on('click', '.edit-mode-btn', function() {
      if (settings.editableModes.includes($(this).data('mode'))) {
        currentEditMode = $(this).data('mode');
      }
    });

    if (settings.enableAdd) {
      $(document).on('click', '#addBtn', function () {
        $('#editForm')[0].reset();
        $('#rowIndex').val('');
        $('#editModal .modal-title').text('Add New Record');
        $('#saveBtn').data('mode', 'add');
        new bootstrap.Modal(document.getElementById('editModal')).show();
      });
    }

    // Row Select
    this.on('click', 'tbody tr', function(e) {
      if ($(e.target).is('input.row-select')) {
        return;
      }
      if (currentEditMode === 'row') {
        const rowData = table.row(this).data();
        const sortingValue = rowData.id;
        console.log("hidden id:", sortingValue);
        showModal(rowData, sortingValue);
      } else {
        if ($(this).hasClass('selected')) {
          $(this).removeClass('selected');
          selectedRow = null;
        } else {
          table.$('tr.selected').removeClass('selected');
          $(this).addClass('selected');
          selectedRow = table.row(this);
        }
      }
    });

    // Bubble Editing
  if (settings.editableModes.includes('bubble')) {
    this.on('click', 'tbody td', function(e) {
    if ($(this).hasClass('select-checkbox') || $(e.target).is('input.row-select')) {
          return;
    }
    if (currentEditMode !== 'bubble') return;

    const cell = table.cell(this);
    const columnIdx = cell.index().column;
    const columnDef = settings.columns[columnIdx];
    const columnName = columnDef.data;
    const cellValue = cell.data();

    const input = createInputElement(columnDef, cellValue, 'bubble');

      $bubbleEditor.html('').append(input).append('<button id="bubbleSaveBtn" class="btn btn-sm btn-success mt-2">Save</button>');

      $bubbleEditor.css({
        top: e.pageY + 10,
        left: e.pageX + 10
      }).show().data('cell', cell).data('column', columnName);

      e.stopPropagation();
    });

    $(document).on('click', '#bubbleSaveBtn', function() {
      const cell = $bubbleEditor.data('cell');
      const column = $bubbleEditor.data('column');
      const newValue = $('#bubbleInput').val();
      const rowData = table.row(cell.index().row).data();

      const payload = { id: rowData.id };
      payload[column] = newValue;

      $.post(settings.dataUrl, payload, function(response) {
        if (response.success) {
          table.ajax.reload(null, false);
        }
        $bubbleEditor.hide();
      }, 'json');
    });

    $(document).on('click', function(e) {
      if (!$(e.target).closest('#bubbleEditor').length) {
        $bubbleEditor.hide();
      }
    });
  }

    // Inline Editing
  if (settings.editableModes.includes('inline')) {
    this.on('click', 'tbody td', function (e) {
      if ($(this).hasClass('select-checkbox') || $(e.target).is('input.row-select')) {
          return;
      }
        if (currentEditMode !== 'inline') return;

        const cell = table.cell(this);
        const originalData = cell.data();
        const rowData = table.row(cell.index().row).data();
        const columnIdx = cell.index().column;
        const columnDef = settings.columns[columnIdx];
        const columnName = columnDef.data;

        if ($(this).hasClass('editing') || columnName === 'id') return;

        const $cell = $(this);
        $cell.addClass('editing');

        const editor = createInputElement(columnDef, originalData, 'inline');

        // Step 1: Clear cell and insert the input
        $cell.empty().append(editor);

        // Step 2: Defer focus to next render cycle
        setTimeout(() => {
            editor.trigger('focus');
        }, 0);

        editor.on('blur change', function () {
            const newValue = editor.val();
            const payload = { id: rowData.id };
            payload[columnName] = newValue;

            if (newValue !== originalData) {
            $.post(settings.dataUrl, payload, function (response) {
                if (response.success) {
                cell.data(newValue).draw();
                } else {
                cell.data(originalData).draw();
                }
                $cell.removeClass('editing');
            }, 'json');
            } else {
            cell.data(originalData).draw();
            $cell.removeClass('editing');
            }
        });

        editor.on('keypress', function (e) {
            if (e.which === 13) {
            editor.blur();
            }
        });
        });
      }

    // External API: Delete
    if (settings.enableDelete) {
      $(document).on('click', '#deleteBtn', function() {
        const selectedIds = [];

        $('.row-select:checked').each(function() {
          selectedIds.push($(this).data('id'));
        });

        if (selectedIds.length === 0) {
          // Show modal instead of alert
          $('#deleteModalMessage').text('Please select at least one record to delete.');
          $('#deleteCount').text('');
          $('#confirmDeleteBtn').hide();
          new bootstrap.Modal(document.getElementById('deleteModal')).show();
          return;
        }

        // Show confirmation modal
        $('#deleteModalMessage').text(`Are you sure you want to delete ${selectedIds.length} selected record(s)?`);
        $('#deleteCount').text(selectedIds.length);
        $('#confirmDeleteBtn').show();
        $('#confirmDeleteBtn').data('ids', selectedIds);
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
      });

      // Handle delete confirmation
      $(document).on('click', '#confirmDeleteBtn', function() {
        const selectedIds = $(this).data('ids');

        $.post(settings.dataUrl, { ids: selectedIds, mode: 'bulk-delete' }, function(response) {
          if (response.success) {
            $('#myTable').DataTable().ajax.reload(null, false);
          } else {
            // Show error in modal
            $('#deleteModalMessage').text('Failed to delete records. Please try again.');
            $('#deleteCount').text('');
            $('#confirmDeleteBtn').hide();
          }
        }, 'json');

        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
      });
    }

    // Show modal for full row editing
    function showModal(data, rowIndex) {
        console.log("row index is:", rowIndex);
        $('#rowIndex').val(rowIndex);
        console.log("Hidden input value set to:", $('#rowIndex').val());

        settings.columns.forEach((col) => {
                if (col.data && col.editType && col.data !== 'id') {
                    $(`#${col.data}`).val(data[col.data] || '');
                }
            });

        $('#editModal .modal-title').text('Edit Record');
        $('#saveBtn').data('mode', 'edit');
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    // Save Modal
    $('#saveBtn').click(function () {
      const mode = $(this).data('mode') || 'edit';
      const id = $('#rowIndex').val();
      const rowData = {};

      console.log('[DEBUG] Save button clicked. Mode:', mode);

      $('#editForm input, #editForm select').each(function () {
        rowData[$(this).attr('id')] = $(this).val();
      });

      if (mode === 'edit') {
        if (!id) {
                console.error('Invalid ID:', id);
                alert('Error: Invalid ID. Please try again.');
                return;
            }
            rowData.id = id;
      }

      rowData.mode = mode;
      console.log("row data: ", rowData);
      $.post(settings.dataUrl, rowData, function (response) {
        console.log('[DEBUG] Server response:', response);
        if (response.success) {
            table.ajax.reload(null, false);
        }
        }, 'json')
        .fail(function(jqXHR, textStatus, errorThrown) {
        console.error('[DEBUG] AJAX request failed:', textStatus, errorThrown);
        console.error('[DEBUG] Response Text:', jqXHR.responseText);
        });

      bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
      
    });

    function createInputElement(columnDef, value, mode = 'inline') {
        const inputType = columnDef.editType || 'text';

        let input;
        if (inputType === 'select' && Array.isArray(columnDef.options)) {
            input = $('<select class="form-control"></select>');
            columnDef.options.forEach(opt => input.append(`<option value="${opt}">${opt}</option>`));
            input.val(value);
        } else if (inputType === 'date') {
            input = $('<input type="date" class="form-control">').val(value);
        } else if (inputType === 'number') {
            input = $('<input type="number" step="0.01" class="form-control">').val(value);
        }else {
            input = $('<input type="text" class="form-control">').val(value);
        }

        if (mode === 'bubble') input.attr('id', 'bubbleInput');

        return input;
        }

    return this;
  };
})(jQuery);
$(document).on('change', '#selectAll', function () {
  const checked = $(this).is(':checked');
  $('.row-select').prop('checked', checked);
});
$('#myTable').on('draw.dt', function () {
  $('#selectAll').prop('checked', false);
});
