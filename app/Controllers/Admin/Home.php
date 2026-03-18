<?php

namespace App\Controllers\Admin;

use Config\Database;

class Home extends BaseController
{
    public function index()
    {
        $db = Database::connect();

        $usersResult = $db->query('SELECT COUNT(DISTINCT user_uuid) AS cnt FROM todo_items WHERE deleted_at IS NULL')->getRow();

        $stats = [
            'users'    => (int) ($usersResult->cnt ?? 0),
            'total'    => $db->table('todo_items')->where('deleted_at', null)->countAllResults(),
            'todo'     => $db->table('todo_items')->where('deleted_at', null)->where('status', 'todo')->countAllResults(),
            'complete' => $db->table('todo_items')->where('deleted_at', null)->where('status', 'complete')->countAllResults(),
            'pinned'   => $db->table('todo_items')->where('deleted_at', null)->where('is_pinned', 1)->countAllResults(),
            'deleted'  => $db->table('todo_items')->where('deleted_at IS NOT NULL', null, false)->countAllResults(),
        ];

        $data['js']         = ['admin/home'];
        $data['css']        = ['admin/home'];
        $data['datatables'] = true;
        $data['title']      = 'Admin Dashboard';
        $data['stats']      = $stats;

        return view('admin/home', $data);
    }

    public function datatable()
    {
        $db = Database::connect();

        $draw      = (int) $this->request->getGet('draw');
        $start     = max(0, (int) $this->request->getGet('start'));
        $length    = (int) $this->request->getGet('length');
        $length    = max(1, min($length ?: 25, 500));
        $search    = $this->request->getGet('search')['value'] ?? '';
        $orderArr  = $this->request->getGet('order') ?? [];
        $orderBy   = $orderArr[0] ?? ['column' => 1, 'dir' => 'desc'];

        $statusFilter  = $this->request->getGet('status_filter') ?? '';
        $statusFilter  = in_array($statusFilter, ['', 'todo', 'complete']) ? $statusFilter : '';
        $deletedFilter = $this->request->getGet('deleted_filter') ?? '';
        $deletedFilter = in_array($deletedFilter, ['', 'deleted']) ? $deletedFilter : '';

        $columnMap = [
            0 => 'id',
            1 => 'id',
            2 => 'user_uuid',
            3 => 'markdown',
            4 => 'category',
            5 => 'status',
            6 => 'is_pinned',
            7 => 'created_at',
            8 => 'deleted_at',
        ];

        $orderCol = $columnMap[(int)($orderBy['column'] ?? 1)] ?? 'id';
        $orderDir = strtolower($orderBy['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        // Total active records (unfiltered)
        $totalRecords = $db->table('todo_items')->where('deleted_at', null)->countAllResults();

        // Filtered query builder
        $builder = $db->table('todo_items');

        if ($deletedFilter === 'deleted') {
            $builder->where('deleted_at IS NOT NULL', null, false);
        } else {
            $builder->where('deleted_at', null);
        }

        if ($statusFilter !== '') {
            $builder->where('status', $statusFilter);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('markdown', $search)
                ->orLike('category', $search)
                ->orLike('user_uuid', $search)
                ->groupEnd();
        }

        $filteredRecords = $builder->countAllResults(false);

        $rows = $builder
            ->orderBy($orderCol, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $tableData = [];

        foreach ($rows as $row) {
            $statusBadge = $row['status'] === 'complete'
                ? '<span class="badge bg-success">Complete</span>'
                : '<span class="badge bg-warning text-dark">Todo</span>';

            $pinnedIcon = (int)$row['is_pinned'] === 1
                ? '<i class="bi bi-pin-fill text-warning"></i>'
                : '<i class="bi bi-pin text-secondary"></i>';

            $content = trim($row['markdown'] ?? '');
            if (mb_strlen($content) > 80) {
                $content = mb_substr($content, 0, 80) . '…';
            }

            $tableData[] = [
                'id'         => (int)$row['id'],
                'user_uuid'  => esc(substr($row['user_uuid'] ?? '', 0, 8)),
                'content'    => $content !== '' ? esc($content) : '<span class="text-secondary fst-italic">—</span>',
                'category'   => ($row['category'] ?? '') !== '' ? esc($row['category']) : '<span class="text-secondary">—</span>',
                'status'     => $statusBadge,
                'is_pinned'  => $pinnedIcon,
                'created_at' => date('d M Y', strtotime($row['created_at'])),
                'deleted_at' => $row['deleted_at'] ? date('d M Y', strtotime($row['deleted_at'])) : '<span class="text-secondary">—</span>',
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $tableData,
        ]);
    }

    public function delete()
    {
        $body = $this->request->getJSON(true);
        $ids  = $body['ids'] ?? [];

        if (empty($ids) || ! is_array($ids)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'No IDs provided']);
        }

        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (empty($ids)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid IDs']);
        }

        $model = model('App\Models\TodoItemModel');
        $model->delete($ids);

        return $this->response->setJSON(['deleted' => count($ids)]);
    }
}
