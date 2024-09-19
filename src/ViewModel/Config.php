<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\ViewModel;

use HiPay\FullserviceMagento\Model\Method\Providers\ApplepayConfigProvider;
use HiPay\FullserviceMagento\Model\Method\Providers\CcConfigProvider;
use HiPay\FullserviceMagento\Model\Method\Providers\GenericConfigProvider;
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
        private ApplepayConfigProvider $applepayConfigProvider
    ) {
    }

    public function getSerializedCreditCardConfig(): string
    {
        return $this->serializer->serialize($this->creditCardConfigProvider->getConfig());
    }

    public function getSerializedApplePayConfig(): string
    {
        return $this->serializer->serialize($this->applepayConfigProvider->getConfig());
    }

    public function getSerializedConfig(): string
    {
        return $this->serializer->serialize($this->genericConfigProvider->getConfig());
    }

    public function getOneClickEnabled(string $methodCode): bool
    {
        return $this->genericConfigProvider->getConfig()['payment']['hiPayFullservice']['useOneclick'][$methodCode];
    }

    public function getCustomerCards(): array
    {
        return $this->genericConfigProvider->getConfig()['payment']['hiPayFullservice']['customerCards'];
    }
}
