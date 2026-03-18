<?php

namespace App\Controllers\Api;

use App\Models\TodoItemModel;
use App\Libraries\Markdown;
use Ramsey\Uuid\Uuid;

class TodoItems extends BaseController
{
    /**
     * GET /api/todo/items
     * Query params: status (todo|complete|deleted|all), page, search, category
     */
    public function index()
    {
        $userUuid = $this->getUserUuid();
        if (empty($userUuid)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Could not determine user identity.']);
        }

        $status   = $this->request->getGet('status') ?? 'todo';
        $page     = max(1, (int) ($this->request->getGet('page') ?? 1));
        $search   = trim($this->request->getGet('search') ?? '');
        $category = trim($this->request->getGet('category') ?? '');
        $perPage  = 20;

        // Build count query
        $countModel = new TodoItemModel();
        $countModel->where('user_uuid', $userUuid);
        $this->applyStatusFilter($countModel, $status);
        if ($search !== '') {
            $countModel->groupStart()->like('markdown', $search)->orLike('category', $search)->groupEnd();
        }
        if ($category !== '') {
            $countModel->where('category', $category);
        }
        $total = $countModel->countAllResults();

        // Build items query
        $model = new TodoItemModel();
        $model->where('user_uuid', $userUuid);
        $this->applyStatusFilter($model, $status);
        if ($search !== '') {
            $model->groupStart()->like('markdown', $search)->orLike('category', $search)->groupEnd();
        }
        if ($category !== '') {
            $model->where('category', $category);
        }

        if ($status === 'todo' || $status === 'all') {
            $model->orderBy('is_pinned', 'DESC')->orderBy('created_at', 'DESC');
        } else {
            $model->orderBy('updated_at', 'DESC');
        }

        $items = $model->findAll($perPage, ($page - 1) * $perPage);

        return $this->response->setJSON([
            'status'     => 'success',
            'items'      => $items,
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
        ]);
    }

    /**
     * GET /api/todo/counts
     * Query params: search, category
     */
    public function counts()
    {
        $userUuid = $this->getUserUuid();
        if (empty($userUuid)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Could not determine user identity.']);
        }

        $search   = trim($this->request->getGet('search') ?? '');
        $category = trim($this->request->getGet('category') ?? '');

        $countFor = function (string $status) use ($userUuid, $search, $category): int {
            $model = new TodoItemModel();
            $model->where('user_uuid', $userUuid);
            $this->applyStatusFilter($model, $status);
            if ($search !== '') {
                $model->groupStart()->like('markdown', $search)->orLike('category', $search)->groupEnd();
            }
            if ($category !== '') {
                $model->where('category', $category);
            }
            return $model->countAllResults();
        };

        return $this->response->setJSON([
            'status'   => 'success',
            'todo'     => $countFor('todo'),
            'complete' => $countFor('complete'),
            'deleted'  => $countFor('deleted'),
        ]);
    }

    /**
     * GET /api/todo/categories
     */
    public function categories()
    {
        $userUuid = $this->getUserUuid();
        if (empty($userUuid)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Could not determine user identity.']);
        }

        $db   = \Config\Database::connect();
        $rows = $db->table('todo_items')
            ->distinct()
            ->select('category')
            ->where('user_uuid', $userUuid)
            ->where('deleted_at IS NULL', null, false)
            ->where("category != ''")
            ->where('category IS NOT NULL', null, false)
            ->orderBy('category', 'ASC')
            ->get()
            ->getResultArray();

        $categories = array_values(array_filter(array_column($rows, 'category')));

        return $this->response->setJSON([
            'status'     => 'success',
            'categories' => $categories,
        ]);
    }

    /**
     * POST /api/todo/items
     */
    public function create()
    {
        $userUuid = $this->getUserUuid();
        if (empty($userUuid)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Could not determine user identity.']);
        }

        $body     = $this->request->getJSON(true) ?? [];
        $markdown = trim($body['markdown'] ?? '');
        $category = strtolower(trim($body['category'] ?? ''));

        if (empty($markdown)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Markdown content is required.']);
        }

        if ($category === '') {
            $category = 'uncategorised';
        }

        $html = $this->convertMarkdown($markdown);
        $uuid = Uuid::uuid4()->toString();

        $model = new TodoItemModel();
        $model->insert([
            'uuid'      => $uuid,
            'user_uuid' => $userUuid,
            'status'    => 'todo',
            'markdown'  => $markdown,
            'html'      => $html,
            'category'  => $category,
            'is_pinned' => 0,
        ]);

        $item = (new TodoItemModel())->where('uuid', $uuid)->first();

        return $this->response->setStatusCode(201)->setJSON([
            'status' => 'success',
            'item'   => $item,
        ]);
    }

    /**
     * POST /api/todo/items/(:uuid)
     * Body: { markdown?, category? }
     */
    public function update(string $uuid)
    {
        $userUuid = $this->getUserUuid();
        if (empty($userUuid)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Could not determine user identity.']);
        }

        $model = new TodoItemModel();
        $item  = $model->where('uuid', $uuid)->where('user_uuid', $userUuid)->first();
        if (!$item) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Item not found.']);
        }

        $body = $this->request->getJSON(true) ?? [];
        $data = [];

        if (array_key_exists('markdown', $body)) {
            $markdown = trim($body['markdown']);
            if ($markdown === '') {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Markdown cannot be empty.']);
            }
            $data['markdown'] = $markdown;
            $data['html']     = $this->convertMarkdown($markdown);
        }

        if (array_key_exists('category', $body)) {
            $category         = strtolower(trim($body['category']));
            $data['category'] = $category === '' ? 'uncategorised' : $category;
        }

        if (!empty($data)) {
            $model->where('uuid', $uuid)->where('user_uuid', $userUuid)->set($data)->update();
        }

        $updated = (new TodoItemModel())->where('uuid', $uuid)->first();

        return $this->response->setJSON([
            'status' => 'success',
            'item'   => $updated,
        ]);
    }

    /**
     * POST /api/todo/items/(:uuid)/status
     * Body: { status: 'todo'|'complete' }
     */
    public function updateStatus(string $uuid)
    {
        $userUuid = $this->getUserUuid();
        if (empty($userUuid)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Could not determine user identity.']);
        }

        $body   = $this->request->getJSON(true) ?? [];
        $status = $body['status'] ?? '';

        if (!in_array($status, ['todo', 'complete'], true)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid status. Must be "todo" or "complete".']);
        }

        $model = new TodoItemModel();
        $item  = $model->where('uuid', $uuid)->where('user_uuid', $userUuid)->first();
        if (!$item) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Item not found.']);
        }

        $updateData = ['status' => $status];
        if ($status === 'complete') {
            $updateData['is_pinned'] = 0;
        }
        $model->where('uuid', $uuid)->where('user_uuid', $userUuid)->set($updateData)->update();
        $updated = (new TodoItemModel())->where('uuid', $uuid)->first();

        return $this->response->setJSON([
            'status' => 'success',
            'item'   => $updated,
        ]);
    }

    /**
     * POST /api/todo/items/(:uuid)/pin
     */
    public function togglePin(string $uuid)
    {
        $userUuid = $this->getUserUuid();
        if (empty($userUuid)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Could not determine user identity.']);
        }

        $model = new TodoItemModel();
        $item  = $model->where('uuid', $uuid)->where('user_uuid', $userUuid)->first();
        if (!$item) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Item not found.']);
        }

        $newPinned = $item['is_pinned'] ? 0 : 1;
        $model->where('uuid', $uuid)->where('user_uuid', $userUuid)->set(['is_pinned' => $newPinned])->update();
        $updated = (new TodoItemModel())->where('uuid', $uuid)->first();

        return $this->response->setJSON([
            'status' => 'success',
            'item'   => $updated,
        ]);
    }

    /**
     * POST /api/todo/items/(:uuid)/delete  (soft delete)
     */
    public function delete(string $uuid)
    {
        $userUuid = $this->getUserUuid();
        if (empty($userUuid)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Could not determine user identity.']);
        }

        $model = new TodoItemModel();
        $item  = $model->where('uuid', $uuid)->where('user_uuid', $userUuid)->first();
        if (!$item) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Item not found.']);
        }

        $model->delete($item['id']);

        return $this->response->setJSON(['status' => 'success']);
    }

    /**
     * POST /api/todo/items/(:uuid)/restore
     */
    public function restore(string $uuid)
    {
        $userUuid = $this->getUserUuid();
        if (empty($userUuid)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Could not determine user identity.']);
        }

        $model = new TodoItemModel();
        $item  = $model->withDeleted()->where('uuid', $uuid)->where('user_uuid', $userUuid)->first();
        if (!$item || $item['deleted_at'] === null) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Deleted item not found.']);
        }

        \Config\Database::connect()
            ->table('todo_items')
            ->where('uuid', $uuid)
            ->where('user_uuid', $userUuid)
            ->update(['deleted_at' => null]);

        $restored = (new TodoItemModel())->where('uuid', $uuid)->first();

        return $this->response->setJSON([
            'status' => 'success',
            'item'   => $restored,
        ]);
    }

    /**
     * POST /api/todo/items/(:uuid)/destroy  (permanent delete)
     */
    public function destroy(string $uuid)
    {
        $userUuid = $this->getUserUuid();
        if (empty($userUuid)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Could not determine user identity.']);
        }

        $model = new TodoItemModel();
        $item  = $model->withDeleted()->where('uuid', $uuid)->where('user_uuid', $userUuid)->first();
        if (!$item) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Item not found.']);
        }

        \Config\Database::connect()
            ->table('todo_items')
            ->where('uuid', $uuid)
            ->where('user_uuid', $userUuid)
            ->delete();

        return $this->response->setJSON(['status' => 'success']);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve the authenticated user UUID.
     * Prefers the request header (for API/programmatic calls);
     * falls back to the session (for browser requests where the cookie
     * may be HttpOnly and therefore unreadable by JavaScript).
     */
    private function getUserUuid(): string
    {
        $fromHeader  = $this->request->getHeaderLine('user-uuid');
        $fromSession = session()->get('user_uuid') ?? '';
        return $fromHeader ?: $fromSession;
    }

    /**
     * Apply status filter to a model builder.
     */
    private function applyStatusFilter(TodoItemModel $model, string $status): void
    {
        if ($status === 'deleted') {
            $model->onlyDeleted();
        } elseif ($status === 'all') {
            $model->withDeleted();
        } else {
            // Default: 'todo' or 'complete' – useSoftDeletes adds deleted_at IS NULL automatically
            $model->where('status', $status);
        }
    }

    /**
     * Convert markdown to HTML using the Markdown library.
     * Falls back to a basic nl2br conversion if the service is unavailable.
     */
    private function convertMarkdown(string $markdown): string
    {
        try {
            $lib = new Markdown();
            $lib->setMarkdown($markdown);
            $result = $lib->convert();
            return $result['html'] ?? esc($markdown);
        } catch (\Exception $e) {
            return '<p>' . nl2br(esc($markdown)) . '</p>';
        }
    }
}
