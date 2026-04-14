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
        $settings = $model->getSettings();

        if ($this->request->isPost() && $this->validateCsrfToken()) {
            foreach ($this->request->allPost() as $key => $value) {
                if ($key === 'csrf_token') {
                    continue;
                }

                $model->save((string) $key, (string) $value);
            }

            $this->flash('success', 'Configurações atualizadas.');
            $this->redirect('admin/index.php?route=settings');
        }

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
                'donation_title' => 'Apoie o NosfirVertex',
                'donation_message' => 'Se a plataforma te ajudou, você pode contribuir de forma voluntária para manter o projeto online.',
                'donation_goal_text' => 'As doações ajudam com hospedagem, manutenção, melhorias de UX e novos recursos para todos os usuários.',
                'donation_pix_key' => '',
                'donation_pix_beneficiary' => 'NosfirVertex',
                'donation_paypal_url' => '',
                'donation_bank_transfer' => '',
                'donation_qr_image' => '',
                'donation_thanks_message' => 'Obrigado pelo apoio. Toda contribuição faz diferença para manter a plataforma gratuita.',
            ],
        ]);
    }

    private function ensureAuth(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('admin/index.php?route=login');
        }
    }
}
