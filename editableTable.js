(function($) {
  $.fn.makeEditableTable = function(options) {
    const settings = $.extend({
      dataUrl: 'data.php',
      editableModes: ['row', 'bubble', 'inline'],
      columns: [],
    }, options);

    const table = this.DataTable({
      ajax: {
        url: settings.dataUrl,
        type: 'GET',
        dataSrc: 'data'
      },
      columns: settings.columns
    });

    let selectedRow = null;
    let currentEditMode = '';

    const $bubbleEditor = $('<div id="bubbleEditor" style="display:none; position:absolute; background:white; padding:10px; border:1px solid black; z-index:1000;"></div>');
    $('body').append($bubbleEditor);

    // Button Event Binding
    $(document).on('click', '.edit-mode-btn', function() {
      currentEditMode = $(this).data('mode');
    });

    // Row Select
    this.on('click', 'tbody tr', function() {
      if (currentEditMode === 'row') {
        const rowData = table.row(this).data();
        const sortingValue = $(this).find('td.sorting_1').text();
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
    this.on('click', 'tbody td', function(e) {
      if (currentEditMode !== 'bubble') return;

      const cell = table.cell(this);
      const columnIdx = cell.index().column;
    //   const columnName = table.settings().init().columns[columnIdx].data;
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

    // Inline Editing
    this.on('click', 'tbody td', function () {
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

    // External API: Delete
    $(document).on('click', '#deleteBtn', function () {
      if (!selectedRow) {
        alert('Please select a row to delete.');
        return;
      }

      const rowData = selectedRow.data();
      if (confirm('Are you sure you want to delete this record?')) {
        $.post(settings.dataUrl, { id: rowData.id, mode: 'delete' }, function (response) {
          if (response.success) {
            table.ajax.reload(null, false);
            selectedRow = null;
          } else {
            alert('Failed to delete the record.');
          }
        }, 'json');
      }
    });

    // Show modal for full row editing
    function showModal(data, rowIndex) {
        console.log("row index is:", rowIndex);
        $('#rowIndex').val(rowIndex);
        console.log("Hidden input value set to:", $('#rowIndex').val());
        // Exclude rowIndex from the loop to prevent overwriting
        $('#editForm input:not(#rowIndex), #editForm select').each(function() {
            const id = $(this).attr('id');
            if (id && data[id] !== undefined) {
                $(this).val(data[id]);
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
        } else {
            input = $('<input type="text" class="form-control">').val(value);
        }

        if (mode === 'bubble') input.attr('id', 'bubbleInput');

        return input;
        }

    return this;
  };
})(jQuery);
