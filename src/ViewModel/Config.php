<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\ViewModel;

use HiPay\FullserviceMagento\Block\Adminhtml\HipayConfig;
use HiPay\FullserviceMagento\Model\Method\Providers\ApplepayConfigProvider;
use HiPay\FullserviceMagento\Model\Method\Providers\CcConfigProvider;
use HiPay\FullserviceMagento\Model\Method\Providers\PaypalConfigProvider as GeneralPaypalConfigProvider;
use HiPay\FullserviceMagento\Model\PaypalConfigProvider;
use HiPay\FullserviceMagento\Model\Method\Providers\GenericConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
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
    public function __construct(
        private CcConfigProvider $creditCardConfigProvider,
        private SerializerInterface $serializer,
        private GenericConfigProvider $genericConfigProvider,
        private ApplepayConfigProvider $applepayConfigProvider,
        private GeneralPaypalConfigProvider $generalPaypalConfigProvider,
        private PaypalConfigProvider $payPalConfigProvider,
        private HipayConfig $hipayConfig,
    ) {
    }

    public function getApiUsernameTokenJs(): string
    {
        return $this->hipayConfig->getApiUsernameTokenJs();
    }

    public function getApiPasswordTokenJs(): string
    {
        return $this->hipayConfig->getApiPasswordTokenJs();
    }

    public function getApiEnv(): string
    {
        return $this->hipayConfig->getEnv();
    }

    public function getSerializedCreditCardConfig($methodCode): string
    {
        $genericConfig = $this->genericConfigProvider->getConfig();
        $useOneClick =  $genericConfig['payment']['hiPayFullservice']['useOneclick'][$methodCode] ?? false;
        $selectedCard =  $genericConfig['payment']['hiPayFullservice']['selectedCard'] ?? [];
        $oneClickMaxCards = (int) $genericConfig['payment']['hiPayFullservice']['maxSavedCard'][$methodCode] ?? 0;
        $ccConfig = $this->creditCardConfigProvider->getConfig();
        $ccConfig['payment']['hipay_hosted_fields']['selectedCard'] = $selectedCard;
        $ccConfig['payment']['hipay_hosted_fields']['useOneClick'] = $useOneClick;
        $ccConfig['payment']['hipay_hosted_fields']['oneClickMaxCards'] = $oneClickMaxCards;

        return $this->serializer->serialize($ccConfig);
    }

    public function getSerializedApplePayConfig(): string
    {
        return $this->serializer->serialize($this->applepayConfigProvider->getConfig());
    }

    public function getSerializedPayPalConfig(): string
    {
        return $this->serializer->serialize($this->generalPaypalConfigProvider->getConfig());
    }

    public function getSerializedConfig(): string
    {
        return $this->serializer->serialize($this->genericConfigProvider->getConfig());
    }

    public function getOneClickEnabled(string $methodCode): bool
    {
        return (bool) ($this->genericConfigProvider->getConfig()['payment']['hiPayFullservice']['useOneclick'][$methodCode] ?? false);
    }

    public function getCustomerCards(): array
    {
        return $this->genericConfigProvider->getConfig()['payment']['hiPayFullservice']['customerCards'] ?? [];
    }

    public function isPayPalV2(): bool
    {
        return (bool) $this->payPalConfigProvider->getConfig()['payment']['hipay_paypalapi']['isPayPalV2'] ?? false;
    }
}
