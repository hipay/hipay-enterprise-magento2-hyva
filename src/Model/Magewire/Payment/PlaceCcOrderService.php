<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Model\Magewire\Payment;

use HiPay\FullserviceMagento\Model\Method\HostedMethod;
use HiPay\FullserviceMagento\Model\Method\Providers\GenericConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
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
    /**
     * Initialize the credit-card specific place-order service.
     *
     * @param CartManagementInterface $cartManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param GenericConfigProvider $genericConfigProvider
     * @param Session $checkoutSession
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        OrderRepositoryInterface $orderRepository,
        private GenericConfigProvider $genericConfigProvider,
        private Session $checkoutSession
    ) {
        parent::__construct($cartManagement, $orderRepository);
    }

    /**
     * Determine whether the hosted card flow should redirect after order placement.
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canRedirect(): bool
    {
        $quote = $this->checkoutSession->getQuote();
        $paymentConfig = $this->genericConfigProvider->getConfig()['payment'] ?? [];

        if ($quote->getPayment()->getAdditionalInformation('can_redirect')) {
            return true;
        }

        return !($paymentConfig['hiPayFullservice']['isIframeMode'][HostedMethod::HIPAY_METHOD_CODE] ?? false);
    }
}
