<?php
declare(strict_types=1);

namespace AureaVertex\Install\Model;

use AureaVertex\System\Engine\Model;
use PDO;

class InstallerModel extends Model
{
    public function getRequirements(): array
    {
        return [
            [
                'name' => 'PHP >= 8.1',
                'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
                'current' => PHP_VERSION,
                'required' => '8.1+',
            ],
            [
                'name' => 'PDO',
                'status' => extension_loaded('pdo'),
                'current' => extension_loaded('pdo') ? 'Ativo' : 'Inativo',
                'required' => 'Ativo',
            ],
            [
                'name' => 'PDO MySQL',
                'status' => extension_loaded('pdo_mysql'),
                'current' => extension_loaded('pdo_mysql') ? 'Ativo' : 'Inativo',
                'required' => 'Ativo',
            ],
            [
                'name' => 'JSON',
                'status' => extension_loaded('json'),
                'current' => extension_loaded('json') ? 'Ativo' : 'Inativo',
                'required' => 'Ativo',
            ],
            [
                'name' => 'MBString',
                'status' => extension_loaded('mbstring'),
                'current' => extension_loaded('mbstring') ? 'Ativo' : 'Inativo',
                'required' => 'Ativo',
            ],
        ];
    }

    public function getPermissionChecks(): array
    {
        $paths = [
            DIR_SYSTEM . '/storage',
            DIR_SYSTEM . '/storage/logs',
            DIR_SYSTEM . '/storage/sessions',
            DIR_SYSTEM . '/config',
            DIR_SYSTEM . '/vendor',
            DIR_ROOT . '/image/templates',
        ];

        $result = [];

        foreach ($paths as $path) {
            $result[] = [
                'path' => $path,
                'status' => is_writable($path),
            ];
        }

        return $result;
    }

    public function testDatabaseConnection(array $payload): array
    {
        $errors = $this->validateStep2Payload($payload);

        if ($errors !== []) {
            return [
                'success' => false,
                'message' => implode(' ', $errors),
                'details' => [],
            ];
        }

        try {
            $host = (string) $payload['db_host'];
            $port = (int) $payload['db_port'];
            $dbUser = (string) $payload['db_user'];
            $dbPass = (string) ($payload['db_password'] ?? '');
            $dbName = (string) $payload['db_name'];

            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port),
                $dbUser,
                $dbPass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            $versionRow = $pdo->query('SELECT VERSION() AS version')->fetch();
            $userRow = $pdo->query('SELECT CURRENT_USER() AS db_user')->fetch();

            $existsRow = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :schema LIMIT 1');
            $existsRow->execute([':schema' => $dbName]);
            $dbExists = $existsRow->fetch() !== false;

            $databases = $pdo->query('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA ORDER BY SCHEMA_NAME ASC LIMIT 10')->fetchAll();
            $preview = array_map(static fn (array $row): string => (string) ($row['SCHEMA_NAME'] ?? ''), $databases);

            return [
                'success' => true,
                'message' => $dbExists
                    ? 'Conexão validada. O banco informado já existe e pode ser usado.'
                    : 'Conexão validada. O banco informado não existe e pode ser criado na instalação.',
                'details' => [
                    'host' => $host,
                    'port' => $port,
                    'database' => $dbName,
                    'database_exists' => $dbExists,
                    'server_version' => (string) ($versionRow['version'] ?? 'desconhecida'),
                    'authenticated_as' => (string) ($userRow['db_user'] ?? 'desconhecido'),
                    'database_preview' => $preview,
                ],
            ];
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'message' => 'Falha ao conectar com o banco: ' . $exception->getMessage(),
                'details' => [
                    'host' => (string) ($payload['db_host'] ?? ''),
                    'port' => (int) ($payload['db_port'] ?? 0),
                    'database' => (string) ($payload['db_name'] ?? ''),
                ],
            ];
        }
    }

    public function install(array $payload): array
    {
        $errors = $this->validateInstallPayload($payload);

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $port = (int) ($payload['db_port'] ?? 3306);
            $host = (string) $payload['db_host'];
            $dbName = (string) $payload['db_name'];
            $dbUser = (string) $payload['db_user'];
            $dbPass = (string) ($payload['db_password'] ?? '');
            $createIfMissing = (string) ($payload['db_create_if_missing'] ?? '1') === '1';

            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port),
                $dbUser,
                $dbPass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            $existsStmt = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :schema LIMIT 1');
            $existsStmt->execute([':schema' => $dbName]);
            $dbExists = $existsStmt->fetch() !== false;

            if (!$dbExists && !$createIfMissing) {
                throw new \RuntimeException('O banco informado não existe e a criação automática está desativada.');
            }

            if (!$dbExists && $createIfMissing) {
                $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', $dbName) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            }

            $pdo->exec('USE `' . str_replace('`', '', $dbName) . '`');

            $schema = file_get_contents(DIR_ROOT . '/install/sql/schema.sql');
            if (!is_string($schema) || trim($schema) === '') {
                throw new \RuntimeException('Schema SQL não encontrado.');
            }

            $schema = preg_replace('/^\xEF\xBB\xBF/', '', $schema) ?? $schema;
            $statements = array_filter(array_map('trim', explode(';', $schema)));
            foreach ($statements as $statement) {
                $pdo->exec($statement);
            }

            $adminPassword = password_hash((string) $payload['admin_password'], PASSWORD_DEFAULT);

            $adminExists = $pdo->prepare('SELECT user_id FROM users WHERE email = :email LIMIT 1');
            $adminExists->execute([':email' => mb_strtolower((string) $payload['admin_email'])]);

            if ($adminExists->fetch() === false) {
                $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role, status, created_at) VALUES (:name, :email, :password, :role, :status, NOW())')
                    ->execute([
                        ':name' => (string) $payload['admin_name'],
                        ':email' => mb_strtolower((string) $payload['admin_email']),
                        ':password' => $adminPassword,
                        ':role' => 'admin',
                        ':status' => 'active',
                    ]);
            }

            $this->seedDefaults($pdo);
            $this->writeInstalledConfig($payload);

            return ['success' => true, 'errors' => []];
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'errors' => ['Falha na instalação: ' . $exception->getMessage()],
            ];
        }
    }

    private function validateStep2Payload(array $payload): array
    {
        $errors = [];

        $required = [
            'base_url' => 'URL base',
            'db_host' => 'Host do banco',
            'db_port' => 'Porta do banco',
            'db_name' => 'Nome do banco',
            'db_user' => 'Usuário do banco',
        ];

        foreach ($required as $field => $label) {
            if (!isset($payload[$field]) || trim((string) $payload[$field]) === '') {
                $errors[] = sprintf('%s é obrigatório.', $label);
            }
        }

        if (isset($payload['db_port']) && (!is_numeric((string) $payload['db_port']) || (int) $payload['db_port'] <= 0)) {
            $errors[] = 'A porta do banco deve ser numérica e positiva.';
        }

        return $errors;
    }

    private function validateInstallPayload(array $payload): array
    {
        $errors = $this->validateStep2Payload($payload);

        $required = [
            'admin_name' => 'Nome do administrador',
            'admin_email' => 'E-mail do administrador',
            'admin_password' => 'Senha do administrador',
        ];

        foreach ($required as $field => $label) {
            if (!isset($payload[$field]) || trim((string) $payload[$field]) === '') {
                $errors[] = sprintf('%s é obrigatório.', $label);
            }
        }

        if (isset($payload['admin_email']) && !filter_var($payload['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'O e-mail do administrador é inválido.';
        }

        if (isset($payload['admin_password']) && mb_strlen((string) $payload['admin_password']) < 8) {
            $errors[] = 'A senha do administrador deve ter pelo menos 8 caracteres.';
        }

        return $errors;
    }

    private function seedDefaults(PDO $pdo): void
    {
        $templateCount = (int) ($pdo->query('SELECT COUNT(*) AS total FROM templates')->fetch()['total'] ?? 0);

        if ($templateCount === 0) {
            $templates = [
                ['Básico Essencial', 'basico', 'image/templates/basico.svg', 'Template direto para primeiro emprego.'],
                ['Moderno Dinâmico', 'moderno', 'image/templates/moderno.svg', 'Visual equilibrado para perfis atualizados.'],
                ['Profissional Executivo', 'profissional', 'image/templates/profissional.svg', 'Foco em experiência e liderança.'],
                ['Criativo Portfólio', 'criativo', 'image/templates/criativo.svg', 'Ideal para áreas criativas e design.'],
                ['Minimalista Premium', 'minimalista', 'image/templates/minimalista.svg', 'Leitura limpa com hierarquia elegante.'],
            ];

            $stmtTemplate = $pdo->prepare('INSERT INTO templates (name, category, image_path, description, is_active, created_at) VALUES (:name, :category, :image_path, :description, 1, NOW())');

            foreach ($templates as $template) {
                $stmtTemplate->execute([
                    ':name' => $template[0],
                    ':category' => $template[1],
                    ':image_path' => $template[2],
                    ':description' => $template[3],
                ]);
            }
        }

        $adCount = (int) ($pdo->query('SELECT COUNT(*) AS total FROM ad_blocks')->fetch()['total'] ?? 0);

        if ($adCount === 0) {
            $ads = [
                ['Banner Home Discreto', 'home_top', '<div><strong>Patrocinado:</strong> Dica de curso gratuito para impulsionar sua carreira.</div>', 1, 1],
                ['Bloco Informativo', 'home_mid', '<div><strong>Parceiro:</strong> Ferramenta de revisão de currículo com IA.</div>', 1, 2],
                ['Rodapé Institucional', 'home_footer', '<div><strong>Apoio:</strong> Plataforma de vagas e mentorias.</div>', 1, 3],
            ];

            $stmtAd = $pdo->prepare('INSERT INTO ad_blocks (name, position_code, content_html, is_active, display_order, created_at) VALUES (:name, :position_code, :content_html, :is_active, :display_order, NOW())');

            foreach ($ads as $ad) {
                $stmtAd->execute([
                    ':name' => $ad[0],
                    ':position_code' => $ad[1],
                    ':content_html' => $ad[2],
                    ':is_active' => $ad[3],
                    ':display_order' => $ad[4],
                ]);
            }
        }

        $settingCount = (int) ($pdo->query('SELECT COUNT(*) AS total FROM settings')->fetch()['total'] ?? 0);

        if ($settingCount === 0) {
            $settings = [
                ['site_name', 'AureaVertex', 1, 1],
                ['default_theme', 'light', 1, 1],
                ['allow_registration', '1', 0, 1],
                ['ads_enabled', '1', 0, 1],
                ['donation_enabled', '1', 0, 1],
                ['donation_title', 'Apoie o AureaVertex', 0, 1],
                ['donation_message', 'Se a plataforma te ajudou, você pode contribuir de forma voluntária para manter o projeto online.', 0, 1],
                ['donation_goal_text', 'As doações ajudam com hospedagem, manutenção, melhorias de UX e novos recursos para todos os usuários.', 0, 1],
                ['donation_pix_key', '', 0, 1],
                ['donation_pix_beneficiary', 'AureaVertex', 0, 1],
                ['donation_paypal_url', '', 0, 1],
                ['donation_bank_transfer', '', 0, 1],
                ['donation_qr_image', '', 0, 1],
                ['donation_thanks_message', 'Obrigado pelo apoio. Toda contribuição faz diferença para manter a plataforma gratuita.', 0, 1],
            ];

            $stmtSetting = $pdo->prepare('INSERT INTO settings (`key`, `value`, is_public, autoload, created_at) VALUES (:key, :value, :is_public, :autoload, NOW())');

            foreach ($settings as $setting) {
                $stmtSetting->execute([
                    ':key' => $setting[0],
                    ':value' => $setting[1],
                    ':is_public' => $setting[2],
                    ':autoload' => $setting[3],
                ]);
            }
        }
    }

    private function writeInstalledConfig(array $payload): void
    {
        $content = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export([
            'installed' => true,
            'app' => [
                'base_url' => rtrim((string) $payload['base_url'], '/') . '/',
            ],
            'database' => [
                'host' => (string) $payload['db_host'],
                'port' => (int) $payload['db_port'],
                'name' => (string) $payload['db_name'],
                'user' => (string) $payload['db_user'],
                'password' => (string) ($payload['db_password'] ?? ''),
            ],
        ], true) . ";\n";

        file_put_contents(DIR_SYSTEM . '/config/installed.php', $content);
    }
}
