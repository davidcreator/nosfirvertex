<?php
declare(strict_types=1);

namespace NosfirVertex\Catalog\Model;

use NosfirVertex\System\Engine\Model;

class ResumeModel extends Model
{
    public function getByUser(int $userId): array
    {
        if ($this->db === null) {
            return [];
        }

        return $this->db->fetchAll(
            'SELECT r.resume_id, r.title, r.status, r.updated_at, t.name AS template_name
             FROM resumes r
             LEFT JOIN templates t ON t.template_id = r.template_id
             WHERE r.user_id = :user_id
             ORDER BY r.updated_at DESC, r.created_at DESC',
            [':user_id' => $userId]
        );
    }

    public function getByIdForUser(int $resumeId, int $userId): array|null
    {
        if ($this->db === null) {
            return null;
        }

        return $this->db->fetch(
            'SELECT r.resume_id,
                    r.user_id,
                    r.template_id,
                    r.title,
                    r.personal_data,
                    r.objective,
                    r.professional_summary,
                    r.status,
                    t.name AS template_name,
                    t.category AS template_category,
                    t.image_path AS template_image_path
             FROM resumes r
             LEFT JOIN templates t ON t.template_id = r.template_id
             WHERE r.resume_id = :resume_id AND r.user_id = :user_id
             LIMIT 1',
            [
                ':resume_id' => $resumeId,
                ':user_id' => $userId,
            ]
        );
    }

    public function getDetailedByIdForUser(int $resumeId, int $userId): array|null
    {
        $resume = $this->getByIdForUser($resumeId, $userId);

        if ($resume === null || $this->db === null) {
            return $resume;
        }

        $resume['experiences'] = $this->db->fetchAll('SELECT * FROM resume_experiences WHERE resume_id = :resume_id ORDER BY sort_order, experience_id', [':resume_id' => $resumeId]);
        $resume['educations'] = $this->db->fetchAll('SELECT * FROM resume_educations WHERE resume_id = :resume_id ORDER BY sort_order, education_id', [':resume_id' => $resumeId]);
        $resume['courses'] = $this->db->fetchAll('SELECT * FROM resume_courses WHERE resume_id = :resume_id ORDER BY sort_order, course_id', [':resume_id' => $resumeId]);
        $resume['skills'] = $this->db->fetchAll('SELECT * FROM resume_skills WHERE resume_id = :resume_id ORDER BY sort_order, skill_id', [':resume_id' => $resumeId]);
        $resume['languages'] = $this->db->fetchAll('SELECT * FROM resume_languages WHERE resume_id = :resume_id ORDER BY sort_order, language_id', [':resume_id' => $resumeId]);
        $resume['certifications'] = $this->db->fetchAll('SELECT * FROM resume_certifications WHERE resume_id = :resume_id ORDER BY sort_order, certification_id', [':resume_id' => $resumeId]);
        $resume['projects'] = $this->db->fetchAll('SELECT * FROM resume_projects WHERE resume_id = :resume_id ORDER BY sort_order, project_id', [':resume_id' => $resumeId]);
        $resume['links'] = $this->db->fetchAll('SELECT * FROM resume_links WHERE resume_id = :resume_id ORDER BY sort_order, link_id', [':resume_id' => $resumeId]);
        $resume['design_options'] = $this->getDesignOptionsForResume($resumeId);

        return $resume;
    }

    public function save(int $userId, array $data, int|null $resumeId = null): int
    {
        if ($this->db === null) {
            throw new \RuntimeException('Banco de dados não configurado.');
        }

        $title = trim((string) ($data['title'] ?? 'Meu Currículo'));
        $templateId = (int) ($data['template_id'] ?? 0);
        $templateId = $templateId > 0 ? $templateId : null;
        $status = trim((string) ($data['status'] ?? 'draft')) === 'published' ? 'published' : 'draft';

        return $this->db->transaction(function () use ($userId, $data, $resumeId, $title, $templateId, $status): int {
            if ($resumeId === null) {
                $this->db->execute(
                    'INSERT INTO resumes (user_id, template_id, title, personal_data, objective, professional_summary, status, created_at)
                     VALUES (:user_id, :template_id, :title, :personal_data, :objective, :professional_summary, :status, NOW())',
                    [
                        ':user_id' => $userId,
                        ':template_id' => $templateId,
                        ':title' => $title,
                        ':personal_data' => trim((string) ($data['personal_data'] ?? '')),
                        ':objective' => trim((string) ($data['objective'] ?? '')),
                        ':professional_summary' => trim((string) ($data['professional_summary'] ?? '')),
                        ':status' => $status,
                    ]
                );

                $resumeId = $this->db->lastInsertId();
            } else {
                $this->db->execute(
                    'UPDATE resumes
                     SET template_id = :template_id,
                         title = :title,
                         personal_data = :personal_data,
                         objective = :objective,
                         professional_summary = :professional_summary,
                         status = :status,
                         updated_at = NOW()
                     WHERE resume_id = :resume_id AND user_id = :user_id',
                    [
                        ':template_id' => $templateId,
                        ':title' => $title,
                        ':personal_data' => trim((string) ($data['personal_data'] ?? '')),
                        ':objective' => trim((string) ($data['objective'] ?? '')),
                        ':professional_summary' => trim((string) ($data['professional_summary'] ?? '')),
                        ':status' => $status,
                        ':resume_id' => $resumeId,
                        ':user_id' => $userId,
                    ]
                );
            }

            $this->persistSections($resumeId, $data);

            $snapshot = json_encode($this->getDetailedByIdForUser($resumeId, $userId), JSON_UNESCAPED_UNICODE);
            $this->db->execute(
                'INSERT INTO resume_versions (resume_id, version_label, payload, created_at) VALUES (:resume_id, :version_label, :payload, NOW())',
                [
                    ':resume_id' => $resumeId,
                    ':version_label' => 'Atualização ' . date('d/m/Y H:i'),
                    ':payload' => $snapshot ?: '{}',
                ]
            );

            return $resumeId;
        });
    }

    public function delete(int $resumeId, int $userId): void
    {
        if ($this->db === null) {
            return;
        }

        $this->db->execute('DELETE FROM resumes WHERE resume_id = :resume_id AND user_id = :user_id', [
            ':resume_id' => $resumeId,
            ':user_id' => $userId,
        ]);
    }

    private function persistSections(int $resumeId, array $data): void
    {
        if ($this->db === null) {
            return;
        }

        $designOptions = $this->normalizeDesignOptions($data);
        $designOptionsJson = json_encode($designOptions, JSON_UNESCAPED_UNICODE);

        $this->db->execute('DELETE FROM resume_sections WHERE resume_id = :resume_id', [':resume_id' => $resumeId]);
        $this->db->execute('DELETE FROM resume_experiences WHERE resume_id = :resume_id', [':resume_id' => $resumeId]);
        $this->db->execute('DELETE FROM resume_educations WHERE resume_id = :resume_id', [':resume_id' => $resumeId]);
        $this->db->execute('DELETE FROM resume_courses WHERE resume_id = :resume_id', [':resume_id' => $resumeId]);
        $this->db->execute('DELETE FROM resume_skills WHERE resume_id = :resume_id', [':resume_id' => $resumeId]);
        $this->db->execute('DELETE FROM resume_languages WHERE resume_id = :resume_id', [':resume_id' => $resumeId]);
        $this->db->execute('DELETE FROM resume_certifications WHERE resume_id = :resume_id', [':resume_id' => $resumeId]);
        $this->db->execute('DELETE FROM resume_projects WHERE resume_id = :resume_id', [':resume_id' => $resumeId]);
        $this->db->execute('DELETE FROM resume_links WHERE resume_id = :resume_id', [':resume_id' => $resumeId]);

        $sections = [
            ['objective', 'Objetivo Profissional', (string) ($data['objective'] ?? '')],
            ['professional_summary', 'Resumo Profissional', (string) ($data['professional_summary'] ?? '')],
            ['personal_data', 'Dados Pessoais', (string) ($data['personal_data'] ?? '')],
            ['experiences', 'ExperiÃªncias', (string) ($data['experiences_raw'] ?? '')],
            ['educations', 'FormaÃ§Ã£o AcadÃªmica', (string) ($data['educations_raw'] ?? '')],
            ['courses', 'Cursos', (string) ($data['courses_raw'] ?? '')],
            ['skills', 'Habilidades', (string) ($data['skills_raw'] ?? '')],
            ['languages', 'Idiomas', (string) ($data['languages_raw'] ?? '')],
            ['certifications', 'CertificaÃ§Ãµes', (string) ($data['certifications_raw'] ?? '')],
            ['projects', 'Projetos', (string) ($data['projects_raw'] ?? '')],
            ['professional_links', 'Links Profissionais', (string) ($data['links_raw'] ?? '')],
            ['design_options', 'ConfiguraÃ§Ã£o Visual', $designOptionsJson ?: '{}'],
        ];

        $order = 0;
        foreach ($sections as $section) {
            $this->db->execute(
                'INSERT INTO resume_sections (resume_id, section_key, section_title, content, sort_order, created_at)
                 VALUES (:resume_id, :section_key, :section_title, :content, :sort_order, NOW())',
                [
                    ':resume_id' => $resumeId,
                    ':section_key' => $section[0],
                    ':section_title' => $section[1],
                    ':content' => trim($section[2]),
                    ':sort_order' => $order,
                ]
            );
            $order++;
        }

        $this->saveExperienceRows($resumeId, (string) ($data['experiences_raw'] ?? ''));
        $this->saveEducationRows($resumeId, (string) ($data['educations_raw'] ?? ''));
        $this->saveCourseRows($resumeId, (string) ($data['courses_raw'] ?? ''));
        $this->saveSkillRows($resumeId, (string) ($data['skills_raw'] ?? ''));
        $this->saveLanguageRows($resumeId, (string) ($data['languages_raw'] ?? ''));
        $this->saveCertificationRows($resumeId, (string) ($data['certifications_raw'] ?? ''));
        $this->saveProjectRows($resumeId, (string) ($data['projects_raw'] ?? ''));
        $this->saveLinkRows($resumeId, (string) ($data['links_raw'] ?? ''));
    }

    private function saveExperienceRows(int $resumeId, string $raw): void
    {
        $rows = $this->lines($raw);
        $order = 0;

        foreach ($rows as $row) {
            [$company, $role, $start, $end, $description] = $this->splitParts($row, 5);
            if ($company === '' && $role === '') {
                continue;
            }

            $this->db->execute(
                'INSERT INTO resume_experiences (resume_id, company, role, start_period, end_period, description, sort_order)
                 VALUES (:resume_id, :company, :role, :start_period, :end_period, :description, :sort_order)',
                [
                    ':resume_id' => $resumeId,
                    ':company' => $company,
                    ':role' => $role,
                    ':start_period' => $start,
                    ':end_period' => $end,
                    ':description' => $description,
                    ':sort_order' => $order++,
                ]
            );
        }
    }

    private function saveEducationRows(int $resumeId, string $raw): void
    {
        $rows = $this->lines($raw);
        $order = 0;

        foreach ($rows as $row) {
            [$institution, $degree, $start, $end, $description] = $this->splitParts($row, 5);
            if ($institution === '' && $degree === '') {
                continue;
            }

            $this->db->execute(
                'INSERT INTO resume_educations (resume_id, institution, degree, start_period, end_period, description, sort_order)
                 VALUES (:resume_id, :institution, :degree, :start_period, :end_period, :description, :sort_order)',
                [
                    ':resume_id' => $resumeId,
                    ':institution' => $institution,
                    ':degree' => $degree,
                    ':start_period' => $start,
                    ':end_period' => $end,
                    ':description' => $description,
                    ':sort_order' => $order++,
                ]
            );
        }
    }

    private function saveCourseRows(int $resumeId, string $raw): void
    {
        $rows = $this->lines($raw);
        $order = 0;

        foreach ($rows as $row) {
            [$name, $institution, $year] = $this->splitParts($row, 3);
            if ($name === '') {
                continue;
            }

            $this->db->execute(
                'INSERT INTO resume_courses (resume_id, name, institution, completion_year, sort_order)
                 VALUES (:resume_id, :name, :institution, :completion_year, :sort_order)',
                [
                    ':resume_id' => $resumeId,
                    ':name' => $name,
                    ':institution' => $institution,
                    ':completion_year' => $year,
                    ':sort_order' => $order++,
                ]
            );
        }
    }

    private function saveSkillRows(int $resumeId, string $raw): void
    {
        $rows = $this->lines($raw);
        $order = 0;

        foreach ($rows as $row) {
            [$skill, $level] = $this->splitParts($row, 2);
            if ($skill === '') {
                continue;
            }

            $this->db->execute(
                'INSERT INTO resume_skills (resume_id, skill, level, sort_order)
                 VALUES (:resume_id, :skill, :level, :sort_order)',
                [
                    ':resume_id' => $resumeId,
                    ':skill' => $skill,
                    ':level' => $level,
                    ':sort_order' => $order++,
                ]
            );
        }
    }

    private function saveLanguageRows(int $resumeId, string $raw): void
    {
        $rows = $this->lines($raw);
        $order = 0;

        foreach ($rows as $row) {
            [$language, $level] = $this->splitParts($row, 2);
            if ($language === '') {
                continue;
            }

            $this->db->execute(
                'INSERT INTO resume_languages (resume_id, language, level, sort_order)
                 VALUES (:resume_id, :language, :level, :sort_order)',
                [
                    ':resume_id' => $resumeId,
                    ':language' => $language,
                    ':level' => $level,
                    ':sort_order' => $order++,
                ]
            );
        }
    }

    private function saveCertificationRows(int $resumeId, string $raw): void
    {
        $rows = $this->lines($raw);
        $order = 0;

        foreach ($rows as $row) {
            [$title, $issuer, $year] = $this->splitParts($row, 3);
            if ($title === '') {
                continue;
            }

            $this->db->execute(
                'INSERT INTO resume_certifications (resume_id, title, issuer, issue_year, sort_order)
                 VALUES (:resume_id, :title, :issuer, :issue_year, :sort_order)',
                [
                    ':resume_id' => $resumeId,
                    ':title' => $title,
                    ':issuer' => $issuer,
                    ':issue_year' => $year,
                    ':sort_order' => $order++,
                ]
            );
        }
    }

    private function saveProjectRows(int $resumeId, string $raw): void
    {
        $rows = $this->lines($raw);
        $order = 0;

        foreach ($rows as $row) {
            [$name, $role, $link, $description] = $this->splitParts($row, 4);
            $link = $this->sanitizeExternalUrl($link);
            if ($name === '') {
                continue;
            }

            $this->db->execute(
                'INSERT INTO resume_projects (resume_id, name, role, project_link, description, sort_order)
                 VALUES (:resume_id, :name, :role, :project_link, :description, :sort_order)',
                [
                    ':resume_id' => $resumeId,
                    ':name' => $name,
                    ':role' => $role,
                    ':project_link' => $link,
                    ':description' => $description,
                    ':sort_order' => $order++,
                ]
            );
        }
    }

    private function saveLinkRows(int $resumeId, string $raw): void
    {
        $rows = $this->lines($raw);
        $order = 0;

        foreach ($rows as $row) {
            [$label, $url] = $this->splitParts($row, 2);
            $url = $this->sanitizeExternalUrl($url);
            if ($label === '' && $url === '') {
                continue;
            }

            $this->db->execute(
                'INSERT INTO resume_links (resume_id, label, url, sort_order)
                 VALUES (:resume_id, :label, :url, :sort_order)',
                [
                    ':resume_id' => $resumeId,
                    ':label' => $label !== '' ? $label : 'Link',
                    ':url' => $url,
                    ':sort_order' => $order++,
                ]
            );
        }
    }

    private function getDesignOptionsForResume(int $resumeId): array
    {
        if ($this->db === null) {
            return $this->defaultDesignOptions();
        }

        $row = $this->db->fetch(
            'SELECT content FROM resume_sections WHERE resume_id = :resume_id AND section_key = :section_key LIMIT 1',
            [
                ':resume_id' => $resumeId,
                ':section_key' => 'design_options',
            ]
        );

        if ($row === null) {
            return $this->defaultDesignOptions();
        }

        $decoded = json_decode((string) ($row['content'] ?? ''), true);
        if (!is_array($decoded)) {
            return $this->defaultDesignOptions();
        }

        return $this->normalizeDesignOptions($decoded);
    }

    private function defaultDesignOptions(): array
    {
        return [
            'font_size' => 11,
            'accent_color' => '#0a66c2',
            'header_bg_color' => '#f3f8fd',
            'text_color' => '#1f2937',
        ];
    }

    private function normalizeDesignOptions(array $data): array
    {
        $defaults = $this->defaultDesignOptions();
        $fontSize = (int) ($data['font_size'] ?? $defaults['font_size']);

        if ($fontSize < 10 || $fontSize > 14) {
            $fontSize = (int) $defaults['font_size'];
        }

        return [
            'font_size' => $fontSize,
            'accent_color' => $this->sanitizeHexColor((string) ($data['accent_color'] ?? '')) ?? $defaults['accent_color'],
            'header_bg_color' => $this->sanitizeHexColor((string) ($data['header_bg_color'] ?? '')) ?? $defaults['header_bg_color'],
            'text_color' => $this->sanitizeHexColor((string) ($data['text_color'] ?? '')) ?? $defaults['text_color'],
        ];
    }

    private function sanitizeHexColor(string $value): string|null
    {
        $value = trim($value);
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            return null;
        }

        return strtolower($value);
    }

    private function lines(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($raw)) ?: [];

        return array_values(array_filter(array_map('trim', $lines), static fn (string $line): bool => $line !== ''));
    }

    private function splitParts(string $line, int $count): array
    {
        $parts = array_map('trim', explode('|', $line));
        while (count($parts) < $count) {
            $parts[] = '';
        }

        return array_slice($parts, 0, $count);
    }

    private function sanitizeExternalUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            return '';
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return '';
        }

        $scheme = strtolower((string) (parse_url($value, PHP_URL_SCHEME) ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return '';
        }

        return mb_substr($value, 0, 500);
    }
}

