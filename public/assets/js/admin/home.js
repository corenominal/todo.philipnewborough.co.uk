document.addEventListener("DOMContentLoaded", function() {
    const sidebarLinks = document.querySelectorAll("#sidebar .nav-link");
    sidebarLinks.forEach(link => {
        if (link.getAttribute("href") === "/admin") {
            link.classList.remove("text-white-50");
            link.classList.add("active");
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {

    // ── Selection state ────────────────────────────────────────────────────────
    const selectedIds = new Set();

    function updateDeleteButton() {
        document.getElementById('btn-delete').disabled = selectedIds.size === 0;
    }

    // ── Filter state ───────────────────────────────────────────────────────────
    let statusFilter  = '';
    let deletedFilter = '';

    const todoTable = new DataTable('#todo-table', {

        // ── Layout & UI ────────────────────────────────────────────────────────
        autoWidth:      true,
        info:           true,
        lengthChange:   true,
        ordering:       true,
        paging:         true,
        searching:      true,
        orderMulti:     false,
        orderClasses:   true,
        pagingType:     'simple_numbers',
        pageLength:     25,
        lengthMenu:     [10, 25, 50, 100],

        // ── Default sort ───────────────────────────────────────────────────────
        order: [[1, 'desc']], // newest items first

        // ── Performance ────────────────────────────────────────────────────────
        deferRender:    false,
        processing:     true,
        serverSide:     true,
        stateSave:      false,

        // ── Data source ────────────────────────────────────────────────────────
        ajax: {
            url: '/admin/datatable',
            data: function(d) {
                d.status_filter  = statusFilter;
                d.deleted_filter = deletedFilter;
            },
        },

        // ── Column definitions ─────────────────────────────────────────────────
        columns: [
            {
                // Column 0 — Checkbox (row select)
                data:           null,
                title:          '<input type="checkbox" id="select-all-checkbox" class="form-check-input" aria-label="Select all rows on this page">',
                orderable:      false,
                searchable:     false,
                visible:        true,
                width:          '2rem',
                className:      'text-center',
                defaultContent: '<input type="checkbox" class="row-select form-check-input" aria-label="Select row">',
            },
            {
                // Column 1 — ID
                name:       'id',
                data:       'id',
                title:      '#',
                type:       'num',
                orderable:  true,
                searchable: false,
                visible:    true,
                width:      '3rem',
                className:  'text-end',
            },
            {
                // Column 2 — User UUID (truncated to first 8 chars)
                name:       'user_uuid',
                data:       'user_uuid',
                title:      'User',
                type:       'string',
                orderable:  true,
                searchable: true,
                visible:    true,
                width:      '6rem',
                className:  'font-monospace small',
            },
            {
                // Column 3 — Content preview (first 80 chars of markdown)
                name:       'markdown',
                data:       'content',
                title:      'Content',
                type:       'string',
                orderable:  false,
                searchable: true,
                visible:    true,
                width:      '',
                className:  'small',
            },
            {
                // Column 4 — Category
                name:       'category',
                data:       'category',
                title:      'Category',
                type:       'string',
                orderable:  true,
                searchable: true,
                visible:    true,
                width:      '8rem',
                className:  '',
            },
            {
                // Column 5 — Status (badge rendered server-side)
                name:       'status',
                data:       'status',
                title:      'Status',
                type:       'string',
                orderable:  true,
                searchable: true,
                visible:    true,
                width:      '6rem',
                className:  'text-center',
            },
            {
                // Column 6 — Pinned (icon rendered server-side)
                name:       'is_pinned',
                data:       'is_pinned',
                title:      '<i class="bi bi-pin" title="Pinned"></i>',
                type:       'string',
                orderable:  true,
                searchable: false,
                visible:    true,
                width:      '3rem',
                className:  'text-center',
            },
            {
                // Column 7 — Created date
                name:       'created_at',
                data:       'created_at',
                title:      'Created',
                type:       'date',
                orderable:  true,
                searchable: false,
                visible:    true,
                width:      '7rem',
                className:  '',
            },
            {
                // Column 8 — Deleted date (hidden by default; shown when deleted filter is active)
                name:       'deleted_at',
                data:       'deleted_at',
                title:      'Deleted',
                type:       'date',
                orderable:  true,
                searchable: false,
                visible:    false,
                width:      '7rem',
                className:  'text-danger',
            },
        ],

        // ── Language / localisation ────────────────────────────────────────────
        language: {
            emptyTable:     'No items found',
            info:           'Showing _START_ to _END_ of _TOTAL_ items',
            infoEmpty:      'Showing 0 to 0 of 0 items',
            infoFiltered:   '(filtered from _MAX_ total items)',
            lengthMenu:     'Show _MENU_ items',
            loadingRecords: 'Loading...',
            processing:     'Processing...',
            search:         'Search:',
            zeroRecords:    'No matching items found',
            paginate: {
                first:    'First',
                last:     'Last',
                next:     'Next',
                previous: 'Previous',
            },
        },

        // ── Draw callback ──────────────────────────────────────────────────────
        drawCallback: function() {
            // Restore checkbox state and row highlight after each draw
            todoTable.rows({ page: 'current' }).every(function() {
                const id       = this.data().id;
                const checkbox = this.node().querySelector('.row-select');
                const selected = selectedIds.has(id);
                if (checkbox) checkbox.checked = selected;
                this.node().classList.toggle('table-active', selected);
            });
            // Sync the select-all header checkbox
            const selectAll = document.getElementById('select-all-checkbox');
            if (selectAll) {
                const visibleIds = [];
                todoTable.rows({ page: 'current' }).every(function() { visibleIds.push(this.data().id); });
                const n = visibleIds.filter(id => selectedIds.has(id)).length;
                selectAll.checked       = n > 0 && n === visibleIds.length;
                selectAll.indeterminate = n > 0 && n <  visibleIds.length;
            }
            updateDeleteButton();
        },

    });

    // ── Row checkbox clicks ────────────────────────────────────────────────────
    document.querySelector('#todo-table tbody').addEventListener('change', function(e) {
        if (!e.target.classList.contains('row-select')) return;
        const row = todoTable.row(e.target.closest('tr'));
        const id  = row.data().id;
        const tr  = e.target.closest('tr');
        if (e.target.checked) {
            selectedIds.add(id);
            tr.classList.add('table-active');
        } else {
            selectedIds.delete(id);
            tr.classList.remove('table-active');
        }
        const selectAll = document.getElementById('select-all-checkbox');
        if (selectAll) {
            const visibleIds = [];
            todoTable.rows({ page: 'current' }).every(function() { visibleIds.push(this.data().id); });
            const n = visibleIds.filter(id => selectedIds.has(id)).length;
            selectAll.checked       = n > 0 && n === visibleIds.length;
            selectAll.indeterminate = n > 0 && n <  visibleIds.length;
        }
        updateDeleteButton();
    });

    // ── Select-all checkbox (current page) ────────────────────────────────────
    document.querySelector('#todo-table thead').addEventListener('change', function(e) {
        if (e.target.id !== 'select-all-checkbox') return;
        todoTable.rows({ page: 'current' }).every(function() {
            const id       = this.data().id;
            const checkbox = this.node().querySelector('.row-select');
            if (e.target.checked) {
                selectedIds.add(id);
                if (checkbox) checkbox.checked = true;
                this.node().classList.add('table-active');
            } else {
                selectedIds.delete(id);
                if (checkbox) checkbox.checked = false;
                this.node().classList.remove('table-active');
            }
        });
        updateDeleteButton();
    });

    // ── Status filter dropdown ─────────────────────────────────────────────────
    document.querySelectorAll('.status-filter-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.status-filter-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            const value = this.dataset.value;
            const label = value ? (value.charAt(0).toUpperCase() + value.slice(1)) : 'All';
            document.getElementById('btn-status-filter').innerHTML =
                '<i class="bi bi-funnel-fill"></i><span class="d-none d-lg-inline"> Status: ' + label + '</span>';
            statusFilter = value;
            selectedIds.clear();
            todoTable.ajax.reload(null, true);
        });
    });

    // ── Deleted filter toggle ──────────────────────────────────────────────────
    document.getElementById('btn-show-deleted').addEventListener('click', function() {
        if (deletedFilter === 'deleted') {
            deletedFilter = '';
            this.classList.remove('btn-danger');
            this.classList.add('btn-outline-secondary');
            todoTable.column(8).visible(false);
        } else {
            deletedFilter = 'deleted';
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-danger');
            todoTable.column(8).visible(true);
        }
        selectedIds.clear();
        todoTable.ajax.reload(null, true);
    });

    // ── Refresh button ─────────────────────────────────────────────────────────
    document.getElementById('btn-datatable-refresh').addEventListener('click', function() {
        todoTable.ajax.reload(null, false);
    });

    // ── Delete button → show confirmation modal ────────────────────────────────
    const deleteModalEl = document.getElementById('modal-delete-confirm');
    const deleteModal   = new bootstrap.Modal(deleteModalEl, { focus: false });

    deleteModalEl.addEventListener('shown.bs.modal', function() {
        const closeBtn = deleteModalEl.querySelector('.btn-close');
        if (closeBtn) closeBtn.focus();
    });

    deleteModalEl.addEventListener('hide.bs.modal', function() {
        const focused = deleteModalEl.querySelector(':focus');
        if (focused) focused.blur();
        const btn = document.getElementById('btn-delete');
        if (btn && !btn.disabled) btn.focus();
    });

    document.getElementById('btn-delete').addEventListener('click', function() {
        document.getElementById('delete-modal-count').textContent = selectedIds.size;
        deleteModal.show();
    });

    // ── Confirm delete ─────────────────────────────────────────────────────────
    document.getElementById('btn-delete-confirm').addEventListener('click', function() {
        const ids = Array.from(selectedIds);

        fetch('/admin/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids }),
        })
        .then(res => res.json())
        .then(() => {
            deleteModal.hide();
            selectedIds.clear();
            updateDeleteButton();
            todoTable.ajax.reload(null, false);
        })
        .catch(err => console.error('Delete failed:', err));
    });

});
