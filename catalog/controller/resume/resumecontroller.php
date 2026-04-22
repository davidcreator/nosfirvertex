<?php
declare(strict_types=1);

namespace NosfirVertex\Catalog\Controller\Resume;

use NosfirVertex\Catalog\Model\ResumeModel;
use NosfirVertex\Catalog\Model\TemplateModel;
use NosfirVertex\System\Engine\Controller;

class ResumeController extends Controller
{
    public function create(): string
    {
        $this->ensureAuth();

        $resumeModel = new ResumeModel($this->registry);
        $templateModel = new TemplateModel($this->registry);
        $templates = $templateModel->getActiveTemplates();

        $form = $this->defaultForm();
        $requestedTemplateId = (int) $this->request->get('template_id', 0);

        if ($requestedTemplateId > 0 && $this->templateExists($templates, $requestedTemplateId)) {
            $form['template_id'] = (string) $requestedTemplateId;
        }

        if ($this->request->isPost()) {
            if (!$this->validateCsrfToken()) {
                $this->flash('error', 'Token de seguranca invalido.');
                $this->redirect('catalog/index.php?route=resume/create');
            }

            $form = $this->mapForm($this->request->allPost());

            if (trim($form['title']) === '') {
                return $this->page('resume/form', [
                    'mode' => 'create',
                    'error' => 'Informe um titulo para o curriculo.',
                    'form' => $form,
                    'templates' => $templates,
                ]);
            }

            $formatError = $this->validateAtsCriticalRules($form);
            if ($formatError !== '') {
                return $this->page('resume/form', [
                    'mode' => 'create',
                    'error' => $formatError,
                    'form' => $form,
                    'templates' => $templates,
                ]);
            }

            $resumeId = $resumeModel->save((int) $this->auth->id(), $form, null);
            $this->flash('success', 'Curriculo criado com sucesso.');
            $this->redirect('catalog/index.php?route=resume/view/' . $resumeId);
        }

        return $this->page('resume/form', [
            'mode' => 'create',
            'error' => '',
            'form' => $form,
            'templates' => $templates,
        ]);
    }

    public function edit(string $id): string
    {
        $this->ensureAuth();

        $resumeId = (int) $id;
        $resumeModel = new ResumeModel($this->registry);
        $templateModel = new TemplateModel($this->registry);

        $resume = $resumeModel->getDetailedByIdForUser($resumeId, (int) $this->auth->id());

        if ($resume === null) {
            $this->flash('error', 'Curriculo nao encontrado.');
            $this->redirect('catalog/index.php?route=dashboard');
        }

        $form = $this->mapResumeToForm($resume);

        if ($this->request->isPost()) {
            if (!$this->validateCsrfToken()) {
                $this->flash('error', 'Token de seguranca invalido.');
                $this->redirect('catalog/index.php?route=resume/edit/' . $resumeId);
            }

            $form = $this->mapForm($this->request->allPost());

            if (trim($form['title']) === '') {
                return $this->page('resume/form', [
                    'mode' => 'edit',
                    'resume_id' => $resumeId,
                    'error' => 'Informe um titulo para o curriculo.',
                    'form' => $form,
                    'templates' => $templateModel->getActiveTemplates(),
                ]);
            }

            $formatError = $this->validateAtsCriticalRules($form);
            if ($formatError !== '') {
                return $this->page('resume/form', [
                    'mode' => 'edit',
                    'resume_id' => $resumeId,
                    'error' => $formatError,
                    'form' => $form,
                    'templates' => $templateModel->getActiveTemplates(),
                ]);
            }

            $resumeModel->save((int) $this->auth->id(), $form, $resumeId);
            $this->flash('success', 'Curriculo atualizado.');
            $this->redirect('catalog/index.php?route=resume/view/' . $resumeId);
        }

        return $this->page('resume/form', [
            'mode' => 'edit',
            'resume_id' => $resumeId,
            'error' => '',
            'form' => $form,
            'templates' => $templateModel->getActiveTemplates(),
        ]);
    }

    public function view(string $id): string
    {
        $this->ensureAuth();

        $resumeId = (int) $id;
        $resumeModel = new ResumeModel($this->registry);
        $resume = $resumeModel->getDetailedByIdForUser($resumeId, (int) $this->auth->id());

        if ($resume === null) {
            $this->flash('error', 'Curriculo nao encontrado.');
            $this->redirect('catalog/index.php?route=dashboard');
        }

        return $this->page('resume/view', [
            'resume' => $resume,
        ]);
    }

    public function delete(string $id): never
    {
        $this->ensureAuth();

        if (!$this->request->isPost() || !$this->validateCsrfToken()) {
            $this->flash('error', 'Requisicao invalida para exclusao de curriculo.');
            $this->redirect('catalog/index.php?route=dashboard');
        }

        $resumeId = (int) $id;
        $resumeModel = new ResumeModel($this->registry);
        $resumeModel->delete($resumeId, (int) $this->auth->id());

        $this->flash('success', 'Curriculo removido.');
        $this->redirect('catalog/index.php?route=dashboard');
    }

    private function ensureAuth(): void
    {
        if (!$this->auth->check()) {
            $this->flash('error', 'Faca login para acessar seus curriculos.');
            $this->redirect('catalog/index.php?route=login');
        }
    }

    private function defaultForm(): array
    {
        return [
            'title' => '',
            'template_id' => '',
            'status' => 'draft',
            'font_size' => '11',
            'accent_color' => '#0a66c2',
            'header_bg_color' => '#f3f8fd',
            'text_color' => '#1f2937',
            'personal_data' => '',
            'objective' => '',
            'professional_summary' => '',
            'experiences_raw' => '',
            'educations_raw' => '',
            'courses_raw' => '',
            'skills_raw' => '',
            'languages_raw' => '',
            'certifications_raw' => '',
            'projects_raw' => '',
            'links_raw' => '',
        ];
    }

    private function mapForm(array $input): array
    {
        return [
            'title' => (string) ($input['title'] ?? ''),
            'template_id' => (string) ($input['template_id'] ?? ''),
            'status' => (string) ($input['status'] ?? 'draft'),
            'font_size' => (string) ($input['font_size'] ?? '11'),
            'accent_color' => (string) ($input['accent_color'] ?? '#0a66c2'),
            'header_bg_color' => (string) ($input['header_bg_color'] ?? '#f3f8fd'),
            'text_color' => (string) ($input['text_color'] ?? '#1f2937'),
            'personal_data' => (string) ($input['personal_data'] ?? ''),
            'objective' => (string) ($input['objective'] ?? ''),
            'professional_summary' => (string) ($input['professional_summary'] ?? ''),
            'experiences_raw' => (string) ($input['experiences_raw'] ?? ''),
            'educations_raw' => (string) ($input['educations_raw'] ?? ''),
            'courses_raw' => (string) ($input['courses_raw'] ?? ''),
            'skills_raw' => (string) ($input['skills_raw'] ?? ''),
            'languages_raw' => (string) ($input['languages_raw'] ?? ''),
            'certifications_raw' => (string) ($input['certifications_raw'] ?? ''),
            'projects_raw' => (string) ($input['projects_raw'] ?? ''),
            'links_raw' => (string) ($input['links_raw'] ?? ''),
        ];
    }

    private function mapResumeToForm(array $resume): array
    {
        $designOptions = is_array($resume['design_options'] ?? null) ? $resume['design_options'] : [];

        return [
            'title' => (string) ($resume['title'] ?? ''),
            'template_id' => (string) ($resume['template_id'] ?? ''),
            'status' => (string) ($resume['status'] ?? 'draft'),
            'font_size' => (string) ($designOptions['font_size'] ?? '11'),
            'accent_color' => (string) ($designOptions['accent_color'] ?? '#0a66c2'),
            'header_bg_color' => (string) ($designOptions['header_bg_color'] ?? '#f3f8fd'),
            'text_color' => (string) ($designOptions['text_color'] ?? '#1f2937'),
            'personal_data' => (string) ($resume['personal_data'] ?? ''),
            'objective' => (string) ($resume['objective'] ?? ''),
            'professional_summary' => (string) ($resume['professional_summary'] ?? ''),
            'experiences_raw' => $this->rowsToRaw($resume['experiences'] ?? [], ['company', 'role', 'start_period', 'end_period', 'description']),
            'educations_raw' => $this->rowsToRaw($resume['educations'] ?? [], ['institution', 'degree', 'start_period', 'end_period', 'description']),
            'courses_raw' => $this->rowsToRaw($resume['courses'] ?? [], ['name', 'institution', 'completion_year']),
            'skills_raw' => $this->rowsToRaw($resume['skills'] ?? [], ['skill', 'level']),
            'languages_raw' => $this->rowsToRaw($resume['languages'] ?? [], ['language', 'level']),
            'certifications_raw' => $this->rowsToRaw($resume['certifications'] ?? [], ['title', 'issuer', 'issue_year']),
            'projects_raw' => $this->rowsToRaw($resume['projects'] ?? [], ['name', 'role', 'project_link', 'description']),
            'links_raw' => $this->rowsToRaw($resume['links'] ?? [], ['label', 'url']),
        ];
    }

    private function rowsToRaw(array $rows, array $keys): string
    {
        $lines = [];

        foreach ($rows as $row) {
            $parts = [];
            foreach ($keys as $key) {
                $parts[] = trim((string) ($row[$key] ?? ''));
            }
            $lines[] = rtrim(implode(' | ', $parts), ' |');
        }

        return implode("\n", $lines);
    }

    private function templateExists(array $templates, int $templateId): bool
    {
        foreach ($templates as $template) {
            if ((int) ($template['template_id'] ?? 0) === $templateId) {
                return true;
            }
        }

        return false;
    }

    private function validateAtsCriticalRules(array $form): string
    {
        $experienceError = $this->validateStructuredPeriodLines(
            (string) ($form['experiences_raw'] ?? ''),
            'Experiencias',
            true
        );
        if ($experienceError !== '') {
            return $experienceError;
        }

        return $this->validateStructuredPeriodLines(
            (string) ($form['educations_raw'] ?? ''),
            'Formacao academica',
            true
        );
    }

    private function validateStructuredPeriodLines(string $raw, string $sectionLabel, bool $allowCurrentEnd): string
    {
        $lines = $this->splitNonEmptyLines($raw);

        foreach ($lines as $index => $line) {
            [$first, $second, $start, $end, $description] = $this->splitLineParts($line, 5);
            if ($first === '' && $second === '' && $start === '' && $end === '' && $description === '') {
                continue;
            }

            $lineNumber = $index + 1;
            if ($start !== '' && !$this->isMonthYear($start)) {
                return $sectionLabel . ': linha ' . $lineNumber . ' com data de inicio invalida. Use MM/AAAA.';
            }

            if ($end !== '') {
                $isCurrentLabel = $allowCurrentEnd && $this->isCurrentPeriodLabel($end);
                if (!$isCurrentLabel && !$this->isMonthYear($end)) {
                    return $sectionLabel . ': linha ' . $lineNumber . ' com data de fim invalida. Use MM/AAAA ou Atual.';
                }
            }

            if ($description !== '' && $this->containsDatePattern($description)) {
                return $sectionLabel . ': linha ' . $lineNumber . ' possui data na descricao. Coloque datas apenas nos campos de inicio/fim.';
            }
        }

        return '';
    }

    private function splitNonEmptyLines(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($raw)) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $line): bool => $line !== ''));
    }

    private function splitLineParts(string $line, int $count): array
    {
        $parts = array_map('trim', explode('|', $line));
        while (count($parts) < $count) {
            $parts[] = '';
        }

        return array_slice($parts, 0, $count);
    }

    private function isMonthYear(string $value): bool
    {
        return preg_match('/^(0[1-9]|1[0-2])\/(19|20)\d{2}$/', trim($value)) === 1;
    }

    private function isCurrentPeriodLabel(string $value): bool
    {
        $normalized = mb_strtolower(trim($value));

        return in_array($normalized, ['atual', 'presente', 'em andamento', 'cursando'], true);
    }

    private function containsDatePattern(string $value): bool
    {
        return preg_match(
            '/\b(0[1-9]|1[0-2])\/(19|20)\d{2}\b|\b(19|20)\d{2}\s*[-–]\s*(19|20)\d{2}\b/u',
            $value
        ) === 1;
    }
}
