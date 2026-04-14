<?php
declare(strict_types=1);

namespace AureaVertex\Catalog\Controller\Common;

use AureaVertex\Catalog\Model\SettingModel;
use AureaVertex\System\Engine\Controller;

class DonationController extends Controller
{
    public function index(): string
    {
        $settingModel = new SettingModel($this->registry);

        $donationEnabled = trim($settingModel->get('donation_enabled', '1')) !== '0';

        return $this->page('common/donate', [
            'donation_enabled' => $donationEnabled,
            'donation_title' => $settingModel->get('donation_title', 'Apoie o AureaVertex'),
            'donation_message' => $settingModel->get(
                'donation_message',
                'Se o AureaVertex te ajudou, você pode contribuir de forma voluntária para manter o projeto online.'
            ),
            'donation_goal_text' => $settingModel->get(
                'donation_goal_text',
                'As doações ajudam com hospedagem, manutenção, melhorias de UX e novos recursos para todos os usuários.'
            ),
            'donation_pix_key' => $settingModel->get('donation_pix_key', ''),
            'donation_pix_beneficiary' => $settingModel->get('donation_pix_beneficiary', 'AureaVertex'),
            'donation_paypal_url' => $settingModel->get('donation_paypal_url', ''),
            'donation_bank_transfer' => $settingModel->get('donation_bank_transfer', ''),
            'donation_qr_image' => $settingModel->get('donation_qr_image', ''),
            'donation_thanks_message' => $settingModel->get(
                'donation_thanks_message',
                'Obrigado pelo apoio. Toda contribuição faz diferença para manter a plataforma gratuita.'
            ),
        ]);
    }
}
