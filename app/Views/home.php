<?= $this->extend('templates/default') ?>

<?= $this->section('content') ?>
<div class="container-xl">
    <div class="row">
        <div class="col-12">

            <div class="border-bottom border-1 mb-4 pb-2 mt-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h1 class="h4 mb-0"><i class="bi bi-check-circle me-2"></i>TODO List</h1>
                <!-- Search bar -->
                <div class="d-flex gap-2" style="min-width: 280px; max-width: 420px; flex: 1 1 280px;">
                    <div class="input-group input-group-sm">
                        <input type="text" id="search-input" class="form-control" placeholder="Search todos…" autocomplete="off">
                        <button class="btn btn-outline-primary" id="search-btn" type="button" title="Search">
                            <i class="bi bi-search"></i>
                        </button>
                        <button class="btn btn-outline-primary d-none" id="clear-search-btn" type="button" title="Clear search">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Active filters bar -->
            <div id="filter-bar" class="d-none alert alert-dark border-primary text-primary py-2 d-flex flex-wrap align-items-center gap-2 mb-3">
                <i class="bi bi-funnel-fill"></i>
                <span id="filter-bar-text"></span>
                <button class="btn btn-sm btn-outline-primary ms-auto" id="clear-filters-btn">
                    <i class="bi bi-x-lg me-1"></i>Clear filters
                </button>
            </div>

            <!-- Create form -->
            <div class="card mb-4" id="create-form-card">
                <div class="card-header py-2">
                    <span class="fw-semibold"><i class="bi bi-plus-circle me-1"></i>New TODO item</span>
                </div>
                <div class="card-body">
                    <form id="create-todo-form" novalidate>
                        <div class="mb-2">
                            <textarea
                                class="form-control font-monospace"
                                id="todo-markdown"
                                rows="4"
                                placeholder="Write your todo in Markdown…"
                                required></textarea>
                        </div>
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <input
                                    type="text"
                                    class="form-control"
                                    id="todo-category"
                                    placeholder="Category (optional — defaults to uncategorised)"
                                    list="category-datalist"
                                    autocomplete="off">
                                <datalist id="category-datalist"></datalist>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary" id="create-btn">
                                    <i class="bi bi-plus-lg me-1"></i>Create
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="todo-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-todo-btn" data-bs-toggle="tab"
                        data-bs-target="#pane-todo" type="button" role="tab"
                        aria-controls="pane-todo" aria-selected="true" data-tab="todo">
                        <i class="bi bi-list-check me-1"></i><span class="d-none d-md-inline">TODO</span>
                        <span class="badge bg-primary ms-1" id="tab-todo-count">…</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-complete-btn" data-bs-toggle="tab"
                        data-bs-target="#pane-complete" type="button" role="tab"
                        aria-controls="pane-complete" aria-selected="false" data-tab="complete">
                        <i class="bi bi-check-circle me-1"></i><span class="d-none d-md-inline">Completed</span>
                        <span class="badge bg-success ms-1" id="tab-complete-count">…</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-deleted-btn" data-bs-toggle="tab"
                        data-bs-target="#pane-deleted" type="button" role="tab"
                        aria-controls="pane-deleted" aria-selected="false" data-tab="deleted">
                        <i class="bi bi-trash me-1"></i><span class="d-none d-md-inline">Deleted</span>
                        <span class="badge bg-danger ms-1" id="tab-deleted-count">…</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content border border-top-0 rounded-bottom p-3 mb-5" id="todo-tabs-content">
                <div class="tab-pane fade show active" id="pane-todo" role="tabpanel" aria-labelledby="tab-todo-btn">
                    <div id="items-todo"><div class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div></div>
                    <div id="pagination-todo" class="mt-3"></div>
                </div>
                <div class="tab-pane fade" id="pane-complete" role="tabpanel" aria-labelledby="tab-complete-btn">
                    <div id="items-complete"><div class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div></div>
                    <div id="pagination-complete" class="mt-3"></div>
                </div>
                <div class="tab-pane fade" id="pane-deleted" role="tabpanel" aria-labelledby="tab-deleted-btn">
                    <div id="items-deleted"><div class="text-center py-4"><div class="spinner-border spinner-border-sm text-secondary" role="status"></div></div></div>
                    <div id="pagination-deleted" class="mt-3"></div>
                </div>
            </div>

        </div><!-- /.col-12 -->
    </div><!-- /.row -->
</div><!-- /.container-xl -->

<!-- Change Category Modal -->
<div class="modal fade" id="changeCategoryModal" tabindex="-1"
    aria-labelledby="changeCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeCategoryModalLabel">
                    <i class="bi bi-tag me-1"></i>Change Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" id="modal-category-input"
                    placeholder="Category" list="category-datalist" autocomplete="off">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="save-category-modal-btn">
                    <i class="bi bi-check-lg me-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1200;">
    <div id="app-toast" class="toast align-items-center border-0" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="app-toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
