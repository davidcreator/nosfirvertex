<?php
declare(strict_types=1);

namespace NosfirVertex\Install\Controller;

use NosfirVertex\Install\Model\InstallerModel;
use NosfirVertex\System\Engine\Controller;

class InstallerController extends Controller
{
    private const SESSION_PREFIX = 'install_wizard_';

    public function index(): string
    {
        if ($this->isInstalled()) {
            return $this->alreadyInstalledPage();
        }

        $this->redirect('install/index.php?route=step/1');
    }

    public function step1(): string
    {
        if ($this->isInstalled()) {
            return $this->alreadyInstalledPage();
        }

        $installer = new InstallerModel($this->registry);
        $requirements = $installer->getRequirements();
        $permissions = $installer->getPermissionChecks();

        $requirementsOk = $this->allOk($requirements);
        $permissionsOk = $this->allOk($permissions);

        return $this->page('install/step1', [
            'step' => 1,
            'requirements' => $requirements,
            'permissions' => $permissions,
            'requirements_ok' => $requirementsOk,
            'permissions_ok' => $permissionsOk,
            'can_continue' => $requirementsOk && $permissionsOk,
        ]);
    }

    public function step1Next(): never
    {
        if ($this->request->method() !== 'POST') {
            $this->redirect('install/index.php?route=step/1');
        }

        if (!$this->validateCsrfToken()) {
            $this->flash('error', 'Token CSRF inválido.');
            $this->redirect('install/index.php?route=step/1');
        }

        $installer = new InstallerModel($this->registry);
        $requirementsOk = $this->allOk($installer->getRequirements());
        $permissionsOk = $this->allOk($installer->getPermissionChecks());

        if (!$requirementsOk || !$permissionsOk) {
            $this->flash('error', 'Corrija os requisitos e permissões antes de continuar.');
            $this->redirect('install/index.php?route=step/1');
        }

        $this->setWizardData('step1_ok', true);
        $this->clearStepDataFrom(2);

        $this->redirect('install/index.php?route=step/2');
    }

    public function step2(): string
    {
        if ($this->isInstalled()) {
            return $this->alreadyInstalledPage();
        }

        if (!$this->step1Ready()) {
            $this->flash('error', 'Finalize o passo 1 antes de acessar o passo 2.');
            $this->redirect('install/index.php?route=step/1');
        }

        $form = array_replace($this->defaultStep2Form(), $this->getWizardData('step2_form', []));
        $dbStatus = $this->getWizardData('step2_db_status', [
            'success' => false,
            'message' => 'Preencha os dados e teste a conexão com o banco.',
            'details' => [],
        ]);

        return $this->page('install/step2', [
            'step' => 2,
            'form' => $form,
            'db_status' => $dbStatus,
            'can_continue' => (bool) ($dbStatus['success'] ?? false),
        ]);
    }

    public function testDb(): never
    {
        if ($this->request->method() !== 'POST') {
            $this->redirect('install/index.php?route=step/2');
        }

        if (!$this->validateCsrfToken()) {
            $this->flash('error', 'Token CSRF inválido.');
            $this->redirect('install/index.php?route=step/2');
        }

        if (!$this->step1Ready()) {
            $this->flash('error', 'Finalize o passo 1 antes de testar o banco.');
            $this->redirect('install/index.php?route=step/1');
        }

        $installer = new InstallerModel($this->registry);
        $payload = $this->sanitizeStep2Payload($this->request->allPost());
        $result = $installer->testDatabaseConnection($payload);

        $this->setWizardData('step2_form', $payload);
        $this->setWizardData('step2_db_status', $result);

        if (($result['success'] ?? false) === true) {
            $this->flash('success', 'Conexão com banco validada no passo 2.');
        } else {
            $this->flash('error', (string) ($result['message'] ?? 'Falha ao conectar com o banco.'));
        }

        $this->redirect('install/index.php?route=step/2');
    }

    public function step2Next(): never
    {
        if ($this->request->method() !== 'POST') {
            $this->redirect('install/index.php?route=step/2');
        }

        if (!$this->validateCsrfToken()) {
            $this->flash('error', 'Token CSRF inválido.');
            $this->redirect('install/index.php?route=step/2');
        }

        if (!$this->step1Ready()) {
            $this->flash('error', 'Finalize o passo 1 antes de continuar.');
            $this->redirect('install/index.php?route=step/1');
        }

        $installer = new InstallerModel($this->registry);
        $payload = $this->sanitizeStep2Payload($this->request->allPost());
        $result = $installer->testDatabaseConnection($payload);

        $this->setWizardData('step2_form', $payload);
        $this->setWizardData('step2_db_status', $result);

        if (($result['success'] ?? false) !== true) {
            $this->flash('error', 'Não foi possível validar o banco. Revise os dados do passo 2.');
            $this->redirect('install/index.php?route=step/2');
        }

        $this->setWizardData('step2_ok', true);
        $this->clearStepDataFrom(3);
        $this->flash('success', 'Passo 2 validado. Configure o administrador no passo 3.');

        $this->redirect('install/index.php?route=step/3');
    }

    public function step3(): string
    {
        if ($this->isInstalled()) {
            return $this->alreadyInstalledPage();
        }

        if (!$this->step1Ready()) {
            $this->flash('error', 'Finalize o passo 1 antes de acessar o passo 3.');
            $this->redirect('install/index.php?route=step/1');
        }

        if (!$this->step2Ready()) {
            $this->flash('error', 'Finalize o passo 2 antes de acessar o passo 3.');
            $this->redirect('install/index.php?route=step/2');
        }

        $form = array_replace([
            'admin_name' => 'Administrador',
            'admin_email' => 'admin@nosfirvertex.local',
            'admin_password' => '',
            'admin_password_confirm' => '',
        ], $this->getWizardData('step3_form', []));

        $dbForm = $this->getWizardData('step2_form', []);
        $dbStatus = $this->getWizardData('step2_db_status', []);

        return $this->page('install/step3', [
            'step' => 3,
            'form' => $form,
            'db_form' => $dbForm,
            'db_status' => $dbStatus,
            'errors' => [],
        ]);
    }

    public function run(): string
    {
        if ($this->request->method() !== 'POST') {
            $this->redirect('install/index.php?route=step/3');
        }

        if (!$this->validateCsrfToken()) {
            return $this->page('install/result', [
                'success' => false,
                'messages' => ['Token CSRF inválido. Recarregue a página e tente novamente.'],
            ]);
        }

        if (!$this->step1Ready() || !$this->step2Ready()) {
            return $this->page('install/result', [
                'success' => false,
                'messages' => ['Fluxo incompleto. Execute os passos 1 e 2 antes de finalizar.'],
            ]);
        }

        $adminPayload = $this->sanitizeStep3Payload($this->request->allPost());
        $validationErrors = $this->validateStep3Payload($adminPayload);

        if ($validationErrors !== []) {
            $dbForm = $this->getWizardData('step2_form', []);
            $dbStatus = $this->getWizardData('step2_db_status', []);

            return $this->page('install/step3', [
                'step' => 3,
                'form' => $adminPayload,
                'db_form' => $dbForm,
                'db_status' => $dbStatus,
                'errors' => $validationErrors,
            ]);
        }

        $this->setWizardData('step3_form', [
            'admin_name' => $adminPayload['admin_name'],
            'admin_email' => $adminPayload['admin_email'],
        ]);

        $payload = array_merge($this->getWizardData('step2_form', []), $adminPayload);
        $installer = new InstallerModel($this->registry);
        $result = $installer->install($payload);

        if (($result['success'] ?? false) === true) {
            $this->clearWizardData();

            return $this->page('install/result', [
                'success' => true,
                'messages' => [
                    'Instalação concluída com sucesso.',
                    'Catálogo e admin prontos para uso.',
                ],
                'catalog_url' => base_url('catalog/index.php'),
                'admin_url' => base_url('admin/index.php'),
            ]);
        }

        return $this->page('install/result', [
            'success' => false,
            'messages' => $result['errors'] ?? ['Falha inesperada na instalação.'],
        ]);
    }

    public function restart(): never
    {
        $this->clearWizardData();
        $this->flash('success', 'Assistente reiniciado.');

        $this->redirect('install/index.php?route=step/1');
    }

    public function notFound(): string
    {
        return $this->page('install/result', [
            'success' => false,
            'messages' => ['Rota do instalador não encontrada.'],
        ]);
    }

    private function isInstalled(): bool
    {
        return is_file(DIR_SYSTEM . '/config/installed.php');
    }

    private function alreadyInstalledPage(): string
    {
        return $this->page('install/already_installed', [
            'catalog_url' => base_url('catalog/index.php'),
            'admin_url' => base_url('admin/index.php'),
        ]);
    }

    private function allOk(array $rows): bool
    {
        foreach ($rows as $row) {
            if (!isset($row['status']) || $row['status'] !== true) {
                return false;
            }
        }

        return true;
    }

    private function defaultStep2Form(): array
    {
        return [
            'base_url' => $this->detectBaseUrl(),
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_name' => 'nosfirvertex',
            'db_user' => 'root',
            'db_password' => '',
            'db_create_if_missing' => '1',
        ];
    }

    private function sanitizeStep2Payload(array $input): array
    {
        return [
            'base_url' => trim((string) ($input['base_url'] ?? $this->detectBaseUrl())),
            'db_host' => trim((string) ($input['db_host'] ?? '127.0.0.1')),
            'db_port' => trim((string) ($input['db_port'] ?? '3306')),
            'db_name' => trim((string) ($input['db_name'] ?? 'nosfirvertex')),
            'db_user' => trim((string) ($input['db_user'] ?? 'root')),
            'db_password' => (string) ($input['db_password'] ?? ''),
            'db_create_if_missing' => !empty($input['db_create_if_missing']) ? '1' : '0',
        ];
    }

    private function sanitizeStep3Payload(array $input): array
    {
        return [
            'admin_name' => trim((string) ($input['admin_name'] ?? '')),
            'admin_email' => trim((string) ($input['admin_email'] ?? '')),
            'admin_password' => (string) ($input['admin_password'] ?? ''),
            'admin_password_confirm' => (string) ($input['admin_password_confirm'] ?? ''),
        ];
    }

    private function validateStep3Payload(array $payload): array
    {
        $errors = [];

        if ($payload['admin_name'] === '') {
            $errors[] = 'Informe o nome do administrador.';
        }

        if (!filter_var($payload['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Informe um e-mail de administrador válido.';
        }

        if (mb_strlen($payload['admin_password']) < 8) {
            $errors[] = 'A senha deve ter pelo menos 8 caracteres.';
        }

        if ($payload['admin_password'] !== $payload['admin_password_confirm']) {
            $errors[] = 'A confirmação da senha não confere.';
        }

        return $errors;
    }

    private function detectBaseUrl(): string
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/install/index.php'));
        $base = preg_replace('#/install$#', '', $script) ?: '';

        return rtrim($scheme . '://' . $host . $base, '/') . '/';
    }

    private function step1Ready(): bool
    {
        return $this->getWizardData('step1_ok', false) === true;
    }

    private function step2Ready(): bool
    {
        $step2Ok = $this->getWizardData('step2_ok', false) === true;
        $dbStatus = $this->getWizardData('step2_db_status', []);

        return $step2Ok && (($dbStatus['success'] ?? false) === true);
    }

    private function setWizardData(string $key, mixed $value): void
    {
        $this->session->set(self::SESSION_PREFIX . $key, $value);
    }

    private function getWizardData(string $key, mixed $default = null): mixed
    {
        return $this->session->get(self::SESSION_PREFIX . $key, $default);
    }

    private function clearStepDataFrom(int $step): void
    {
        if ($step <= 2) {
            $this->session->remove(self::SESSION_PREFIX . 'step2_form');
            $this->session->remove(self::SESSION_PREFIX . 'step2_db_status');
            $this->session->remove(self::SESSION_PREFIX . 'step2_ok');
        }

        if ($step <= 3) {
            $this->session->remove(self::SESSION_PREFIX . 'step3_form');
        }
    }

    private function clearWizardData(): void
    {
        $keys = [
            'step1_ok',
            'step2_form',
            'step2_db_status',
            'step2_ok',
            'step3_form',
        ];

        foreach ($keys as $key) {
            $this->session->remove(self::SESSION_PREFIX . $key);
        }
    }
}
