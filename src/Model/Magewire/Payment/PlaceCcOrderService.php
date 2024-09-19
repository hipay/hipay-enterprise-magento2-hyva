<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Model\Magewire\Payment;

use HiPay\FullserviceMagento\Model\Method\HostedMethod;
use HiPay\FullserviceMagento\Model\Method\Providers\GenericConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

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
class PlaceCcOrderService extends PlaceOrderService
{
    public function __construct(
        CartManagementInterface $cartManagement,
        OrderRepositoryInterface $orderRepository,
        private GenericConfigProvider $genericConfigProvider,
        private Session $checkoutSession
    ) {
        parent::__construct($cartManagement, $orderRepository);
    }

    public function canRedirect(): bool
    {
        $quote = $this->checkoutSession->getQuote();

        // In the method hosted case with oneclick payment and iframe mode activated,
        // We must check this additional data is present in order to be able to redirect immediately to success page
        if ($quote->getPayment()->getAdditionalInformation('can_redirect')) {
            return true;
        }

        // If can_redirect parameter doesn't and iframe mode is enabled,
        // Redirection is disabled in order to let user fill the iframe
        // Otherwise, redirection is enabled and credit card are filled in next step
        return !$this->genericConfigProvider->getConfig()['payment']['hiPayFullservice']['isIframeMode'][HostedMethod::HIPAY_METHOD_CODE];
    }
}
