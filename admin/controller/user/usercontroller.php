<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Controller\User;

use NosfirVertex\Admin\Model\UserModel;
use NosfirVertex\System\Engine\Controller;

class UserController extends Controller
{
    public function index(): string
    {
        $this->ensureAuth();

        $model = new UserModel($this->registry);
        $filters = [
            'q' => trim((string) $this->request->get('q', '')),
            'role' => strtolower(trim((string) $this->request->get('role', ''))),
            'status' => strtolower(trim((string) $this->request->get('status', ''))),
            'created_from' => trim((string) $this->request->get('created_from', '')),
            'created_to' => trim((string) $this->request->get('created_to', '')),
        ];
        $page = max(1, (int) $this->request->get('page', 1));
        $perPage = $this->normalizePerPage((int) $this->request->get('per_page', 20));
        $total = $model->countUsers($filters);
        $totalPages = max(1, (int) ceil($total / $perPage));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        return $this->page('user/index', [
            'users' => $model->getUsers($filters, $page, $perPage),
            'filters' => $filters,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ]);
    }

    private function ensureAuth(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('admin/index.php?route=login');
        }
    }

    private function normalizePerPage(int $value): int
    {
        $allowed = [20, 50, 100];

        return in_array($value, $allowed, true) ? $value : 20;
    }
}
