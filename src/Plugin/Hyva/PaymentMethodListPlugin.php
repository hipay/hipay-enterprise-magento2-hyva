<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Plugin\Hyva;

use Hyva\Checkout\Magewire\Checkout\Payment\MethodList;

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
class PaymentMethodListPlugin
{
    private const EXTRA_LISTENERS = [
        'billing_address_activated' => 'refresh',
        'billing_address_submitted' => 'refresh',
        'billing_as_shipping_address_updated' => 'refresh',
        'shipping_address_activated' => 'refresh',
        'shipping_address_submitted' => 'refresh',
        'guest_shipping_address_submitted' => 'refresh',
        'guest_shipping_address_saved' => 'refresh',
    ];

    /**
     * Append extra checkout refresh listeners required by HiPay payment methods.
     *
     * @param MethodList $subject
     * @param array $listeners
     * @return array
     */
    public function afterGetListeners(MethodList $subject, array $listeners): array
    {
        return array_replace($listeners, self::EXTRA_LISTENERS);
    }
}
