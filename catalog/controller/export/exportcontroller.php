<?php
declare(strict_types=1);

namespace AureaVertex\Catalog\Controller\Export;

use AureaVertex\Catalog\Model\ResumeModel;
use AureaVertex\System\Engine\Controller;

class ExportController extends Controller
{
    public function pdf(string $id): string
    {
        $this->ensureAuth();

        $resumeId = (int) $id;
        $resume = $this->getResumeOrRedirect($resumeId);

        $html = $this->view->render('export/pdf', ['resume' => $resume]);

        if (!class_exists('\Dompdf\Dompdf')) {
            $this->response->addHeader('Content-Type: text/html; charset=utf-8');
            return '<p><strong>Dompdf não instalado.</strong> Execute <code>composer install</code> para habilitar PDF real.</p>' . $html;
        }

        try {
            $pdfBinary = $this->renderPdfBinary($html);
        } catch (\Throwable $exception) {
            $this->logger->error('Falha ao gerar PDF do currículo', [
                'resume_id' => $resumeId,
                'message' => $exception->getMessage(),
            ]);

            $this->response->addHeader('Content-Type: text/html; charset=utf-8');
            $browserUrl = base_url('catalog/index.php?route=resume/export/browser/' . $resumeId);
            $safeBrowserUrl = htmlspecialchars($browserUrl, ENT_QUOTES, 'UTF-8');

            return '<p><strong>Falha ao gerar o PDF.</strong> Tente novamente em instantes.</p>'
                . '<p>Enquanto isso, visualize no navegador: <a href="'
                . $safeBrowserUrl
                . '" target="_blank" rel="noopener noreferrer">'
                . $safeBrowserUrl
                . '</a></p>';
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
            return json_encode(['error' => 'Currículo não encontrado.'], JSON_UNESCAPED_UNICODE);
        }

        $payload = [
            'platform' => 'AureaVertex',
            'exported_at' => date(DATE_ATOM),
            'resume' => $resume,
            'integration_ready' => [
                'linkedin' => [
                    'status' => 'ready_for_manual_mapping',
                    'note' => 'Estrutura pronta para futura API oficial do LinkedIn.',
                ],
                'facebook' => [
                    'status' => 'ready_for_manual_mapping',
                    'note' => 'Estrutura pronta para compartilhamento e integrações futuras.',
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
            $this->flash('error', 'Faça login para exportar currículos.');
            $this->redirect('catalog/index.php?route=login');
        }
    }

    private function getResumeOrRedirect(int $resumeId): array
    {
        $resumeModel = new ResumeModel($this->registry);
        $resume = $resumeModel->getDetailedByIdForUser($resumeId, (int) $this->auth->id());

        if ($resume === null) {
            $this->flash('error', 'Currículo não encontrado para exportação.');
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
            throw new \RuntimeException('Dompdf retornou binário vazio.');
        }

        return $binary;
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
