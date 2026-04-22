<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Controller\Resume;

use NosfirVertex\Admin\Model\ResumeModel;
use NosfirVertex\System\Engine\Controller;

class ResumeController extends Controller
{
    public function index(): string
    {
        $this->ensureAuth();

        $model = new ResumeModel($this->registry);
        $filters = [
            'q' => trim((string) $this->request->get('q', '')),
            'status' => strtolower(trim((string) $this->request->get('status', ''))),
            'updated_from' => trim((string) $this->request->get('updated_from', '')),
            'updated_to' => trim((string) $this->request->get('updated_to', '')),
        ];
        $page = max(1, (int) $this->request->get('page', 1));
        $perPage = $this->normalizePerPage((int) $this->request->get('per_page', 20));
        $total = $model->countResumes($filters);
        $totalPages = max(1, (int) ceil($total / $perPage));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        return $this->page('resume/index', [
            'resumes' => $model->getResumes($filters, $page, $perPage),
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
