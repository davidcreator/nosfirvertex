<?php
declare(strict_types=1);

namespace NosfirVertex\Catalog\Controller\Export;

use NosfirVertex\Catalog\Model\ResumeModel;
use NosfirVertex\System\Engine\Controller;

class ExportController extends Controller
{
    public function pdf(string $id): string
    {
        $this->ensureAuth();

        $resumeId = (int) $id;
        $resume = $this->getResumeOrRedirect($resumeId);

        if (!class_exists('\Dompdf\Dompdf')) {
            $this->logger->warning('Exportacao PDF indisponivel: Dompdf ausente', [
                'resume_id' => $resumeId,
            ]);

            $this->flash('error', 'Exportacao PDF indisponivel. Execute composer install no diretorio system.');
            $this->redirect('catalog/index.php?route=resume/view/' . $resumeId);
        }

        $html = $this->view->render('export/pdf', ['resume' => $resume]);

        try {
            $pdfBinary = $this->renderPdfBinary($html);
        } catch (\Throwable $exception) {
            $this->logger->error('Falha ao gerar PDF do curriculo', [
                'resume_id' => $resumeId,
                'message' => $exception->getMessage(),
            ]);

            $this->flash('error', 'Falha ao gerar PDF. Tente novamente.');
            $this->redirect('catalog/index.php?route=resume/view/' . $resumeId);
        }

        $this->prepareBinaryStream();

        $this->response->addHeader('Content-Type: application/pdf');
        $this->response->addHeader('Content-Disposition: attachment; filename="curriculo-' . $resumeId . '.pdf"');
        $this->response->addHeader('Content-Transfer-Encoding: binary');
        $this->response->addHeader('Cache-Control: private, max-age=0, must-revalidate');
        $this->response->addHeader('Pragma: public');
        $this->response->addHeader('Content-Length: ' . (string) strlen($pdfBinary));

        return $pdfBinary;
    }

    public function docx(string $id): string
    {
        $this->ensureAuth();

        $resumeId = (int) $id;
        $resume = $this->getResumeOrRedirect($resumeId);

        if (!class_exists('\ZipArchive')) {
            $this->logger->warning('Exportacao DOCX indisponivel: ZipArchive ausente', [
                'resume_id' => $resumeId,
            ]);

            $this->flash('error', 'Exportacao DOCX indisponivel. Habilite a extensao ZIP do PHP.');
            $this->redirect('catalog/index.php?route=resume/view/' . $resumeId);
        }

        try {
            $docxBinary = $this->renderDocxBinary($resume);
        } catch (\Throwable $exception) {
            $this->logger->error('Falha ao gerar DOCX do curriculo', [
                'resume_id' => $resumeId,
                'message' => $exception->getMessage(),
            ]);

            $this->flash('error', 'Falha ao gerar DOCX. Tente novamente.');
            $this->redirect('catalog/index.php?route=resume/view/' . $resumeId);
        }

        $this->prepareBinaryStream();

        $this->response->addHeader('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->response->addHeader('Content-Disposition: attachment; filename="curriculo-' . $resumeId . '.docx"');
        $this->response->addHeader('Content-Transfer-Encoding: binary');
        $this->response->addHeader('Cache-Control: private, max-age=0, must-revalidate');
        $this->response->addHeader('Pragma: public');
        $this->response->addHeader('Content-Length: ' . (string) strlen($docxBinary));

        return $docxBinary;
    }

    public function browser(string $id): string
    {
        $this->ensureAuth();

        $resume = $this->getResumeOrRedirect((int) $id);

        $this->response->addHeader('Content-Type: text/html; charset=utf-8');
        return $this->view->render('export/pdf', ['resume' => $resume]);
    }

    public function json(string $id): string
    {
        $this->ensureAuth();

        $resumeId = (int) $id;
        $resumeModel = new ResumeModel($this->registry);
        $resume = $resumeModel->getDetailedByIdForUser($resumeId, (int) $this->auth->id());

        if ($resume === null) {
            $this->response->addHeader('Content-Type: application/json; charset=utf-8');
            return json_encode(['error' => 'Curriculo nao encontrado.'], JSON_UNESCAPED_UNICODE);
        }

        $payload = [
            'platform' => 'Vertex',
            'exported_at' => date(DATE_ATOM),
            'resume' => $resume,
            'integration_ready' => [
                'linkedin' => [
                    'status' => 'ready_for_manual_mapping',
                    'note' => 'Estrutura pronta para futura API oficial do LinkedIn.',
                ],
                'facebook' => [
                    'status' => 'ready_for_manual_mapping',
                    'note' => 'Estrutura pronta para compartilhamento e integracoes futuras.',
                ],
            ],
        ];

        $this->response->addHeader('Content-Type: application/json; charset=utf-8');
        $this->response->addHeader('Content-Disposition: attachment; filename="curriculo-' . $resumeId . '.json"');

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}';
    }

    private function ensureAuth(): void
    {
        if (!$this->auth->check()) {
            $this->flash('error', 'Faca login para exportar curriculos.');
            $this->redirect('catalog/index.php?route=login');
        }
    }

    private function getResumeOrRedirect(int $resumeId): array
    {
        $resumeModel = new ResumeModel($this->registry);
        $resume = $resumeModel->getDetailedByIdForUser($resumeId, (int) $this->auth->id());

        if ($resume === null) {
            $this->flash('error', 'Curriculo nao encontrado para exportacao.');
            $this->redirect('catalog/index.php?route=dashboard');
        }

        return $resume;
    }

    private function renderPdfBinary(string $html): string
    {
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        $binary = $dompdf->output();
        if ($binary === '') {
            throw new \RuntimeException('Dompdf retornou binario vazio.');
        }

        return $binary;
    }

    private function renderDocxBinary(array $resume): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'resume_docx_');
        if ($zipPath === false) {
            throw new \RuntimeException('Nao foi possivel preparar arquivo temporario DOCX.');
        }

        $zip = new \ZipArchive();
        $openResult = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($openResult !== true) {
            @unlink($zipPath);
            throw new \RuntimeException('Nao foi possivel abrir ZIP para DOCX.');
        }

        $title = trim((string) ($resume['title'] ?? 'Curriculo'));
        $nowIso = gmdate('Y-m-d\TH:i:s\Z');

        $zip->addFromString('[Content_Types].xml', $this->buildDocxContentTypesXml());
        $zip->addFromString('_rels/.rels', $this->buildDocxPackageRelsXml());
        $zip->addFromString('docProps/core.xml', $this->buildDocxCorePropsXml($title, $nowIso));
        $zip->addFromString('docProps/app.xml', $this->buildDocxAppPropsXml());
        $zip->addFromString('word/_rels/document.xml.rels', $this->buildDocxDocumentRelsXml());
        $zip->addFromString('word/document.xml', $this->buildDocxDocumentXml($resume));
        $zip->addFromString('word/styles.xml', $this->buildDocxStylesXml());

        $zip->close();

        $binary = file_get_contents($zipPath);
        @unlink($zipPath);

        if ($binary === false || $binary === '') {
            throw new \RuntimeException('Nao foi possivel obter binario DOCX.');
        }

        return $binary;
    }

    private function buildDocxContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            . '</Types>';
    }

    private function buildDocxPackageRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function buildDocxCorePropsXml(string $title, string $nowIso): string
    {
        $safeTitle = $this->xmlText($title !== '' ? $title : 'Curriculo');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"'
            . ' xmlns:dc="http://purl.org/dc/elements/1.1/"'
            . ' xmlns:dcterms="http://purl.org/dc/terms/"'
            . ' xmlns:dcmitype="http://purl.org/dc/dcmitype/"'
            . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>' . $safeTitle . '</dc:title>'
            . '<dc:creator>Vertex</dc:creator>'
            . '<cp:lastModifiedBy>Vertex</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $nowIso . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $nowIso . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function buildDocxAppPropsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"'
            . ' xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>Vertex</Application>'
            . '</Properties>';
    }

    private function buildDocxDocumentRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function buildDocxStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:style w:type="paragraph" w:default="1" w:styleId="Normal">'
            . '<w:name w:val="Normal"/>'
            . '<w:qFormat/>'
            . '<w:rPr><w:sz w:val="22"/></w:rPr>'
            . '</w:style>'
            . '<w:style w:type="paragraph" w:styleId="HeadingNV">'
            . '<w:name w:val="HeadingNV"/>'
            . '<w:basedOn w:val="Normal"/>'
            . '<w:qFormat/>'
            . '<w:rPr><w:b/><w:sz w:val="24"/></w:rPr>'
            . '</w:style>'
            . '</w:styles>';
    }

    private function buildDocxDocumentXml(array $resume): string
    {
        $entries = $this->buildDocxEntries($resume);
        $paragraphsXml = '';

        foreach ($entries as $entry) {
            $text = (string) ($entry['text'] ?? '');
            $isHeading = (bool) ($entry['heading'] ?? false);

            if ($text === '') {
                $paragraphsXml .= '<w:p/>';
                continue;
            }

            $styleXml = $isHeading ? '<w:pPr><w:pStyle w:val="HeadingNV"/></w:pPr>' : '';
            $runPropXml = $isHeading ? '<w:rPr><w:b/></w:rPr>' : '';

            $paragraphsXml .= '<w:p>'
                . $styleXml
                . '<w:r>'
                . $runPropXml
                . '<w:t xml:space="preserve">' . $this->xmlText($text) . '</w:t>'
                . '</w:r>'
                . '</w:p>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>'
            . $paragraphsXml
            . '<w:sectPr>'
            . '<w:pgSz w:w="11906" w:h="16838"/>'
            . '<w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134" w:header="708" w:footer="708" w:gutter="0"/>'
            . '</w:sectPr>'
            . '</w:body>'
            . '</w:document>';
    }

    private function buildDocxEntries(array $resume): array
    {
        [$displayName, $contactItems] = $this->extractIdentity($resume);
        $entries = [];

        $title = trim((string) ($resume['title'] ?? 'Curriculo profissional'));
        if ($displayName !== '') {
            $entries[] = ['text' => $displayName, 'heading' => true];
        }
        if ($title !== '') {
            $entries[] = ['text' => $title, 'heading' => false];
        }
        if ($contactItems !== []) {
            $entries[] = ['text' => implode(' | ', $contactItems), 'heading' => false];
        }

        $entries[] = ['text' => '', 'heading' => false];

        $summary = trim((string) ($resume['professional_summary'] ?? ''));
        if ($summary !== '') {
            $entries[] = ['text' => 'Resumo profissional', 'heading' => true];
            foreach ($this->splitLines($summary) as $line) {
                $entries[] = ['text' => $line, 'heading' => false];
            }
            $entries[] = ['text' => '', 'heading' => false];
        }

        $objective = trim((string) ($resume['objective'] ?? ''));
        if ($objective !== '') {
            $entries[] = ['text' => 'Objetivo profissional', 'heading' => true];
            foreach ($this->splitLines($objective) as $line) {
                $entries[] = ['text' => $line, 'heading' => false];
            }
            $entries[] = ['text' => '', 'heading' => false];
        }

        $experiences = is_array($resume['experiences'] ?? null) ? $resume['experiences'] : [];
        if ($experiences !== []) {
            $addedHeader = false;
            foreach ($experiences as $item) {
                $company = trim((string) ($item['company'] ?? ''));
                $role = trim((string) ($item['role'] ?? ''));
                $start = trim((string) ($item['start_period'] ?? ''));
                $end = trim((string) ($item['end_period'] ?? ''));
                $description = trim((string) ($item['description'] ?? ''));
                $period = $this->formatPeriod($start, $end, true);

                if ($company === '' && $role === '' && $period === '' && $description === '') {
                    continue;
                }

                if (!$addedHeader) {
                    $entries[] = ['text' => 'Experiencia profissional', 'heading' => true];
                    $addedHeader = true;
                }

                $headline = $role !== '' && $company !== '' ? ($role . ' - ' . $company) : ($role !== '' ? $role : $company);
                if ($headline !== '') {
                    $entries[] = ['text' => $headline, 'heading' => false];
                }
                if ($period !== '') {
                    $entries[] = ['text' => 'Periodo: ' . $period, 'heading' => false];
                }

                foreach ($this->toBullets($description) as $bullet) {
                    $entries[] = ['text' => '- ' . $bullet, 'heading' => false];
                }

                $entries[] = ['text' => '', 'heading' => false];
            }
        }

        $educations = is_array($resume['educations'] ?? null) ? $resume['educations'] : [];
        if ($educations !== []) {
            $addedHeader = false;
            foreach ($educations as $item) {
                $institution = trim((string) ($item['institution'] ?? ''));
                $degree = trim((string) ($item['degree'] ?? ''));
                $start = trim((string) ($item['start_period'] ?? ''));
                $end = trim((string) ($item['end_period'] ?? ''));
                $description = trim((string) ($item['description'] ?? ''));
                $period = $this->formatPeriod($start, $end, false);

                if ($institution === '' && $degree === '' && $period === '' && $description === '') {
                    continue;
                }

                if (!$addedHeader) {
                    $entries[] = ['text' => 'Formacao academica', 'heading' => true];
                    $addedHeader = true;
                }

                $headline = $degree !== '' && $institution !== '' ? ($degree . ' - ' . $institution) : ($degree !== '' ? $degree : $institution);
                if ($headline !== '') {
                    $entries[] = ['text' => $headline, 'heading' => false];
                }
                if ($period !== '') {
                    $entries[] = ['text' => 'Periodo: ' . $period, 'heading' => false];
                }
                if ($description !== '') {
                    foreach ($this->splitLines($description) as $line) {
                        $entries[] = ['text' => $line, 'heading' => false];
                    }
                }

                $entries[] = ['text' => '', 'heading' => false];
            }
        }

        $this->appendSimplePairsSection(
            $entries,
            'Habilidades',
            is_array($resume['skills'] ?? null) ? $resume['skills'] : [],
            'skill',
            'level'
        );
        $this->appendSimplePairsSection(
            $entries,
            'Idiomas',
            is_array($resume['languages'] ?? null) ? $resume['languages'] : [],
            'language',
            'level'
        );

        $certifications = is_array($resume['certifications'] ?? null) ? $resume['certifications'] : [];
        if ($certifications !== []) {
            $addedHeader = false;
            foreach ($certifications as $item) {
                $titleValue = trim((string) ($item['title'] ?? ''));
                $issuer = trim((string) ($item['issuer'] ?? ''));
                if ($titleValue === '') {
                    continue;
                }

                if (!$addedHeader) {
                    $entries[] = ['text' => 'Certificacoes', 'heading' => true];
                    $addedHeader = true;
                }

                $line = $titleValue . ($issuer !== '' ? ' - ' . $issuer : '');
                $entries[] = ['text' => $line, 'heading' => false];
            }

            if ($addedHeader) {
                $entries[] = ['text' => '', 'heading' => false];
            }
        }

        $courses = is_array($resume['courses'] ?? null) ? $resume['courses'] : [];
        if ($courses !== []) {
            $addedHeader = false;
            foreach ($courses as $item) {
                $name = trim((string) ($item['name'] ?? ''));
                $institution = trim((string) ($item['institution'] ?? ''));
                $year = trim((string) ($item['completion_year'] ?? ''));
                if ($name === '') {
                    continue;
                }

                if (!$addedHeader) {
                    $entries[] = ['text' => 'Cursos', 'heading' => true];
                    $addedHeader = true;
                }

                $line = $name;
                if ($institution !== '') {
                    $line .= ' - ' . $institution;
                }
                if ($year !== '') {
                    $line .= ' (' . $year . ')';
                }
                $entries[] = ['text' => $line, 'heading' => false];
            }

            if ($addedHeader) {
                $entries[] = ['text' => '', 'heading' => false];
            }
        }

        $projects = is_array($resume['projects'] ?? null) ? $resume['projects'] : [];
        if ($projects !== []) {
            $addedHeader = false;
            foreach ($projects as $item) {
                $name = trim((string) ($item['name'] ?? ''));
                $role = trim((string) ($item['role'] ?? ''));
                $description = trim((string) ($item['description'] ?? ''));
                $link = trim((string) ($item['project_link'] ?? ''));

                if ($name === '' && $role === '' && $description === '' && $link === '') {
                    continue;
                }

                if (!$addedHeader) {
                    $entries[] = ['text' => 'Projetos', 'heading' => true];
                    $addedHeader = true;
                }

                if ($name !== '') {
                    $entries[] = ['text' => $name, 'heading' => false];
                }
                if ($role !== '') {
                    $entries[] = ['text' => 'Funcao: ' . $role, 'heading' => false];
                }
                if ($description !== '') {
                    foreach ($this->splitLines($description) as $line) {
                        $entries[] = ['text' => $line, 'heading' => false];
                    }
                }
                if ($link !== '') {
                    $entries[] = ['text' => 'Link: ' . $link, 'heading' => false];
                }

                $entries[] = ['text' => '', 'heading' => false];
            }
        }

        $links = is_array($resume['links'] ?? null) ? $resume['links'] : [];
        if ($links !== []) {
            $addedHeader = false;
            foreach ($links as $item) {
                $url = trim((string) ($item['url'] ?? ''));
                $label = trim((string) ($item['label'] ?? ''));
                if ($url === '') {
                    continue;
                }

                if (!$addedHeader) {
                    $entries[] = ['text' => 'Links profissionais', 'heading' => true];
                    $addedHeader = true;
                }

                $line = $label !== '' ? ($label . ': ' . $url) : $url;
                $entries[] = ['text' => $line, 'heading' => false];
            }
        }

        while (!empty($entries) && (string) ($entries[count($entries) - 1]['text'] ?? '') === '') {
            array_pop($entries);
        }

        return $entries;
    }

    private function appendSimplePairsSection(
        array &$entries,
        string $title,
        array $rows,
        string $leftKey,
        string $rightKey
    ): void {
        if ($rows === []) {
            return;
        }

        $addedHeader = false;
        foreach ($rows as $item) {
            $left = trim((string) ($item[$leftKey] ?? ''));
            $right = trim((string) ($item[$rightKey] ?? ''));
            if ($left === '') {
                continue;
            }

            if (!$addedHeader) {
                $entries[] = ['text' => $title, 'heading' => true];
                $addedHeader = true;
            }

            $line = $left . ($right !== '' ? ' (' . $right . ')' : '');
            $entries[] = ['text' => $line, 'heading' => false];
        }

        if ($addedHeader) {
            $entries[] = ['text' => '', 'heading' => false];
        }
    }

    private function extractIdentity(array $resume): array
    {
        $personalData = trim((string) ($resume['personal_data'] ?? ''));
        $lines = $this->splitLines($personalData);
        $headlineParts = [];

        if (!empty($lines)) {
            $headlineParts = array_values(array_filter(
                array_map('trim', explode('|', $lines[0])),
                static fn (string $value): bool => $value !== ''
            ));
        }

        $displayName = $headlineParts[0] ?? trim((string) ($resume['title'] ?? 'Curriculo profissional'));
        if ($displayName === '') {
            $displayName = 'Curriculo profissional';
        }

        $contactItems = [];
        if (count($headlineParts) > 1) {
            $contactItems = array_slice($headlineParts, 1);
        }
        if (count($lines) > 1) {
            foreach (array_slice($lines, 1) as $line) {
                $contactItems[] = $line;
            }
        }

        return [$displayName, $contactItems];
    }

    private function splitLines(string $value): array
    {
        $parts = preg_split('/\r\n|\r|\n/', trim($value)) ?: [];

        return array_values(array_filter(
            array_map('trim', $parts),
            static fn (string $line): bool => $line !== ''
        ));
    }

    private function toBullets(string $value): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", trim($value));
        $segments = preg_split('/\n|\s*;\s*/', $normalized) ?: [];

        return array_values(array_filter(
            array_map('trim', $segments),
            static fn (string $segment): bool => $segment !== ''
        ));
    }

    private function formatPeriod(string $start, string $end, bool $allowCurrentEnd): string
    {
        $start = trim($start);
        $end = trim($end);

        if ($start === '' && $end === '') {
            return '';
        }

        if ($allowCurrentEnd && $start !== '' && $end === '') {
            return $start . ' - Atual';
        }

        if ($start !== '' && $end !== '') {
            return $start . ' - ' . $end;
        }

        return $start !== '' ? $start : $end;
    }

    private function xmlText(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function prepareBinaryStream(): void
    {
        @ini_set('display_errors', '0');
        @ini_set('display_startup_errors', '0');
        @ini_set('html_errors', '0');
        @ini_set('log_errors', '1');

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
}
