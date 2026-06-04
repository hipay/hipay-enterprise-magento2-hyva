<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Model\Magewire\Payment;

use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
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
class PlaceOrderService extends AbstractPlaceOrderService
{
    /**
     * Initialize the default place-order service.
     *
     * @param CartManagementInterface $cartManagement
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        private OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($cartManagement);
    }

    /**
     * Return the frontend redirect URL resolved from the placed order payment.
     *
     * @param Quote $quote
     * @param int|null $orderId
     * @return string
     */
    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        $order = $this->orderRepository->get($orderId);

        $paymentInfos = $order->getPayment()?->getAdditionalInformation();

        return $paymentInfos['redirectUrl'] ?? parent::REDIRECT_PATH;
    }

    /**
     * Place the current quote order using the active payment instance.
     *
     * @param Quote $quote
     * @throws CouldNotSaveException
     */
    public function placeOrder(Quote $quote): int
    {
        return (int) $this->cartManagement->placeOrder($quote->getId(), $quote->getPayment());
    }
}
