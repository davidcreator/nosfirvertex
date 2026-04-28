<?php
declare(strict_types=1);

namespace NosfirVertex\Admin\Controller\Setting;

use NosfirVertex\Admin\Model\SettingModel;
use NosfirVertex\System\Engine\Controller;

class SettingController extends Controller
{
    public function index(): string
    {
        $this->ensureAuth();

        $model = new SettingModel($this->registry);
        $rules = $this->allowedSettingRules();

        if ($this->request->isPost()) {
            if (!$this->validateCsrfToken()) {
                $this->flash('error', $this->lang('Token de segurança inválido para salvar configurações.'));
                $this->redirect('admin/index.php?route=settings');
            }

            $normalizedSettings = $this->normalizeAllowedSettings($this->request->allPost(), $rules);
            foreach ($normalizedSettings as $key => $value) {
                $model->save($key, $value);
            }

            $this->flash('success', $this->lang('Configurações atualizadas.'));
            $this->redirect('admin/index.php?route=settings');
        }

        $settings = $model->getSettings();
        $settings = array_values(array_filter(
            $settings,
            fn (array $setting): bool => isset($rules[(string) ($setting['key'] ?? '')])
        ));

        $settingsMap = [];
        foreach ($settings as $setting) {
            $key = (string) ($setting['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $settingsMap[$key] = (string) ($setting['value'] ?? '');
        }

        return $this->page('setting/index', [
            'settings' => $settings,
            'settings_map' => $settingsMap,
            'donation_defaults' => [
                'donation_enabled' => '1',
                'donation_title' => $this->lang('Apoie o NosfirVertex'),
                'donation_message' => $this->lang('Se a plataforma te ajudou, você pode contribuir de forma voluntária para manter o projeto online.'),
                'donation_goal_text' => $this->lang('As doações ajudam com hospedagem, manutenção, melhorias de UX e novos recursos para todos os usuários.'),
                'donation_pix_key' => '',
                'donation_pix_beneficiary' => 'NosfirVertex',
                'donation_paypal_url' => '',
                'donation_bank_transfer' => '',
                'donation_qr_image' => '',
                'donation_thanks_message' => $this->lang('Obrigado pelo apoio. Toda contribuição faz diferença para manter a plataforma gratuita.'),
            ],
        ]);
    }

    private function allowedSettingRules(): array
    {
        return [
            'site_name' => ['type' => 'text', 'max' => 120],
            'default_theme' => ['type' => 'enum', 'values' => ['light', 'dark']],
            'allow_registration' => ['type' => 'bool'],
            'ads_enabled' => ['type' => 'bool'],
            'donation_enabled' => ['type' => 'bool'],
            'donation_title' => ['type' => 'text', 'max' => 120],
            'donation_message' => ['type' => 'multiline', 'max' => 1200],
            'donation_goal_text' => ['type' => 'multiline', 'max' => 1200],
            'donation_pix_key' => ['type' => 'text', 'max' => 255],
            'donation_pix_beneficiary' => ['type' => 'text', 'max' => 120],
            'donation_paypal_url' => ['type' => 'https_url', 'max' => 255],
            'donation_bank_transfer' => ['type' => 'multiline', 'max' => 1500],
            'donation_qr_image' => ['type' => 'https_url_or_local_path', 'max' => 255],
            'donation_thanks_message' => ['type' => 'multiline', 'max' => 500],
        ];
    }

    private function normalizeAllowedSettings(array $input, array $rules): array
    {
        $normalized = [];

        foreach ($rules as $key => $rule) {
            $value = (string) ($input[$key] ?? '');
            $normalized[$key] = $this->normalizeSettingValue($value, $rule);
        }

        return $normalized;
    }

    private function normalizeSettingValue(string $value, array $rule): string
    {
        $type = (string) ($rule['type'] ?? 'text');
        $max = (int) ($rule['max'] ?? 255);

        if ($type === 'bool') {
            return in_array(strtolower(trim($value)), ['1', 'true', 'on', 'yes'], true) ? '1' : '0';
        }

        if ($type === 'enum') {
            $values = is_array($rule['values'] ?? null) ? $rule['values'] : [];
            $candidate = strtolower(trim($value));

            return in_array($candidate, $values, true) ? $candidate : (string) ($values[0] ?? '');
        }

        if ($type === 'https_url') {
            $candidate = trim($value);
            if ($candidate === '' || !$this->isHttpsUrl($candidate)) {
                return '';
            }

            return mb_substr($candidate, 0, $max);
        }

        if ($type === 'https_url_or_local_path') {
            $candidate = trim($value);
            if ($candidate === '') {
                return '';
            }

            if ($this->isHttpsUrl($candidate) || $this->isSafeLocalPath($candidate)) {
                return mb_substr($candidate, 0, $max);
            }

            return '';
        }

        if ($type === 'multiline') {
            $normalized = preg_replace('/\r\n|\r/', "\n", trim($value)) ?? '';

            return mb_substr($normalized, 0, $max);
        }

        return mb_substr(trim($value), 0, $max);
    }

    private function isHttpsUrl(string $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) (parse_url($value, PHP_URL_SCHEME) ?? ''));

        return in_array($scheme, ['http', 'https'], true);
    }

    private function isSafeLocalPath(string $value): bool
    {
        if (str_contains($value, '..')) {
            return false;
        }

        return preg_match('#^[a-zA-Z0-9/_\-.]+$#', ltrim($value, '/')) === 1;
    }

    private function ensureAuth(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('admin/index.php?route=login');
        }
    }
}
