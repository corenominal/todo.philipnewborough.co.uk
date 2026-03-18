<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- Page header -->
    <div class="row">
        <div class="col-12">
            <div class="border-bottom border-1 mb-4 pb-4 d-flex align-items-center justify-content-between gap-3">
                <h2 class="mb-0">Admin Dashboard</h2>
            </div>
        </div>
    </div>

    <!-- Stats row -->
    <div class="row row-cols-2 row-cols-sm-3 row-cols-xl-6 g-3 mb-4">
        <div class="col">
            <div class="card h-100 border-info text-center">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-info"><?= $stats['users'] ?></div>
                    <div class="text-secondary small mt-1"><i class="bi bi-people-fill me-1"></i>Users</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 border-secondary text-center">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-white"><?= $stats['total'] ?></div>
                    <div class="text-secondary small mt-1"><i class="bi bi-list-check me-1"></i>Total Items</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 border-warning text-center">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-warning"><?= $stats['todo'] ?></div>
                    <div class="text-secondary small mt-1"><i class="bi bi-circle me-1"></i>Pending</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 border-success text-center">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-success"><?= $stats['complete'] ?></div>
                    <div class="text-secondary small mt-1"><i class="bi bi-check-circle me-1"></i>Complete</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 border-secondary text-center">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-white"><?= $stats['pinned'] ?></div>
                    <div class="text-secondary small mt-1"><i class="bi bi-pin-fill me-1"></i>Pinned</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 border-danger text-center">
                <div class="card-body py-3">
                    <div class="fs-2 fw-bold text-danger"><?= $stats['deleted'] ?></div>
                    <div class="text-secondary small mt-1"><i class="bi bi-trash me-1"></i>Soft-deleted</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Todo items table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap py-2">
                    <h5 class="mb-0">Todo Items</h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">

                        <!-- Status filter dropdown -->
                        <div class="dropdown">
                            <button id="btn-status-filter" class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-funnel-fill"></i><span class="d-none d-lg-inline"> Status: All</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a href="#" class="dropdown-item status-filter-item active" data-value="">All Status</a></li>
                                <li><a href="#" class="dropdown-item status-filter-item" data-value="todo">Todo</a></li>
                                <li><a href="#" class="dropdown-item status-filter-item" data-value="complete">Complete</a></li>
                            </ul>
                        </div>

                        <!-- Deleted filter toggle -->
                        <button id="btn-show-deleted" class="btn btn-sm btn-outline-secondary" type="button" title="Show soft-deleted items">
                            <i class="bi bi-trash"></i><span class="d-none d-lg-inline"> Deleted</span>
                        </button>

                        <!-- Refresh -->
                        <button id="btn-datatable-refresh" class="btn btn-sm btn-outline-secondary" type="button" title="Refresh table">
                            <i class="bi bi-arrow-clockwise"></i><span class="d-none d-lg-inline"> Refresh</span>
                        </button>

                        <!-- Delete selected -->
                        <button id="btn-delete" class="btn btn-sm btn-outline-danger" type="button" disabled title="Soft-delete selected items">
                            <i class="bi bi-trash3-fill"></i><span class="d-none d-lg-inline"> Delete</span>
                        </button>

                    </div>
                </div>
                <div class="card-body p-0 pb-3">
                    <div class="table-responsive">
                        <table id="todo-table" class="table table-hover mb-3 w-100">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Delete confirmation modal -->
<div class="modal fade" id="modal-delete-confirm" tabindex="-1" aria-labelledby="modal-delete-confirm-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header border-danger">
                <h5 class="modal-title" id="modal-delete-confirm-label">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-secondary">
                You are about to soft-delete <strong id="delete-modal-count" class="text-white">0</strong> item(s).
                They will remain in the database and can be viewed using the <em>Deleted</em> filter.
            </div>
            <div class="modal-footer border-danger">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btn-delete-confirm">Delete</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
