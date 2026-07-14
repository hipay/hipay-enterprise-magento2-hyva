<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Model\Magewire\Payment;

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
class PlaceIframeOrderService extends PlaceOrderService
{
    /**
     * Keep the customer on the checkout so the payment page can be shown in an iframe.
     */
    public function canRedirect(): bool
    {
        return false;
    }
}
