<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\ViewModel;

use HiPay\FullserviceMagento\Block\Adminhtml\HipayConfig;
use HiPay\FullserviceMagento\Model\Method\Providers\ApplepayConfigProvider;
use HiPay\FullserviceMagento\Model\Method\Providers\CcConfigProvider;
use HiPay\FullserviceMagento\Model\Method\Providers\PaypalConfigProvider as GeneralPaypalConfigProvider;
use HiPay\FullserviceMagento\Model\PaypalConfigProvider;
use HiPay\FullserviceMagento\Model\Method\Providers\GenericConfigProvider;
use HiPay\FullserviceMagento\Model\Method\Providers\LocalConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * HiPay Fullservice Magento - Hyvä Checkout
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright Copyright (c) 2024 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */
class Config implements ArgumentInterface
{
    /**
     * @param CcConfigProvider $creditCardConfigProvider
     * @param SerializerInterface $serializer
     * @param GenericConfigProvider $genericConfigProvider
     * @param ApplepayConfigProvider $applepayConfigProvider
     * @param GeneralPaypalConfigProvider $generalPaypalConfigProvider
     * @param PaypalConfigProvider $payPalConfigProvider
     * @param HipayConfig $hipayConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param LocalConfigProvider $localConfigProvider
     */
    public function __construct(
        private CcConfigProvider $creditCardConfigProvider,
        private SerializerInterface $serializer,
        private GenericConfigProvider $genericConfigProvider,
        private ApplepayConfigProvider $applepayConfigProvider,
        private GeneralPaypalConfigProvider $generalPaypalConfigProvider,
        private PaypalConfigProvider $payPalConfigProvider,
        private HipayConfig $hipayConfig,
        private ScopeConfigInterface $scopeConfig,
        private LocalConfigProvider $localConfigProvider,
    ) {
    }

    /**
     * Return HiPay API username used by frontend SDK initialization.
     */
    public function getApiUsernameTokenJs(): string
    {
        return $this->hipayConfig->getApiUsernameTokenJs();
    }

    /**
     * Return HiPay API password used by frontend SDK initialization.
     */
    public function getApiPasswordTokenJs(): string
    {
        return $this->hipayConfig->getApiPasswordTokenJs();
    }

    /**
     * Return the configured HiPay environment identifier for frontend SDK usage.
     */
    public function getApiEnv(): string
    {
        return $this->hipayConfig->getEnv();
    }

    /**
     * Build the serialized credit card configuration consumed by hosted and hosted-fields frontend components.
     *
     * @param string $methodCode
     */
    public function getSerializedCreditCardConfig(string $methodCode): string
    {
        $genericPaymentConfig = $this->getGenericPaymentConfig();
        $useOneClick = $genericPaymentConfig['hiPayFullservice']['useOneclick'][$methodCode] ?? false;
        $selectedCard = $genericPaymentConfig['hiPayFullservice']['selectedCard'] ?? [];

        $maxSavedCards = (int) $this->scopeConfig->getValue(
            "payment/{$methodCode}/one_click/max_saved_cards",
            ScopeInterface::SCOPE_STORE
        );
        $oneClickMaxCards = $maxSavedCards >= 1 ? $maxSavedCards : 9999999;

        $highlightColor = (string) $this->scopeConfig->getValue(
            "payment/{$methodCode}/one_click/saved_card_highlight_color",
            ScopeInterface::SCOPE_STORE
        );
        $toggleColor = (string) $this->scopeConfig->getValue(
            "payment/{$methodCode}/one_click/save_card_toggle_color",
            ScopeInterface::SCOPE_STORE
        );

        $ccConfig = $this->creditCardConfigProvider->getConfig();
        $ccConfig['payment']['hipay_hosted_fields']['selectedCard'] = $selectedCard;
        $ccConfig['payment']['hipay_hosted_fields']['useOneClick'] = $useOneClick;
        $ccConfig['payment']['hipay_hosted_fields']['oneClickMaxCards'] = $oneClickMaxCards;
        $ccConfig['payment']['hipay_hosted_fields']['oneClickHighlightColor'] = $highlightColor;
        $ccConfig['payment']['hipay_hosted_fields']['oneClickToggleColor'] = $toggleColor;

        return $this->serializer->serialize($ccConfig);
    }

    /**
     * Return serialized Apple Pay configuration for the Hyva frontend component.
     */
    public function getSerializedApplePayConfig(): string
    {
        return $this->serializer->serialize($this->applepayConfigProvider->getConfig());
    }

    /**
     * Return serialized PayPal configuration for the Hyva frontend component.
     */
    public function getSerializedPayPalConfig(): string
    {
        return $this->serializer->serialize($this->generalPaypalConfigProvider->getConfig());
    }

    /**
     * Return the serialized generic HiPay payment configuration shared by frontend components.
     */
    public function getSerializedConfig(): string
    {
        return $this->serializer->serialize($this->genericConfigProvider->getConfig());
    }

    /**
     * Return serialized local hosted-fields configuration (iDEAL, bancontact, mbway...).
     */
    public function getSerializedLocalConfig(): string
    {
        return $this->serializer->serialize($this->localConfigProvider->getConfig());
    }

    /**
     * Indicate whether one-click is enabled for the given HiPay payment method code.
     *
     * @param string $methodCode
     */
    public function getOneClickEnabled(string $methodCode): bool
    {
        $genericPaymentConfig = $this->getGenericPaymentConfig();

        return (bool) ($genericPaymentConfig['hiPayFullservice']['useOneclick'][$methodCode] ?? false);
    }

    /**
     * Return saved customer cards from the generic HiPay payment config.
     *
     * @return array<int|string, array<string, mixed>>
     */
    public function getCustomerCards(): array
    {
        $genericPaymentConfig = $this->getGenericPaymentConfig();

        return $genericPaymentConfig['hiPayFullservice']['customerCards'] ?? [];
    }

    /**
     * Indicate whether the PayPal V2 integration path is enabled.
     */
    public function isPayPalV2(): bool
    {
        $config = $this->payPalConfigProvider->getConfig();

        return (bool) ($config['payment']['hipay_paypalapi']['isPayPalV2'] ?? false);
    }

    /**
     * Indicate whether Magento checkout agreements are enabled for the current store.
     */
    public function isTOCEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Return the payment node from the generic HiPay checkout config.
     *
     * @return array<string, mixed>
     */
    private function getGenericPaymentConfig(): array
    {
        return $this->genericConfigProvider->getConfig()['payment'] ?? [];
    }
}
