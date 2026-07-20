<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Model\Method;

use HiPay\FullserviceMagento\Model\Method\Mbway as BaseMbway;

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
class Mbway extends BaseMbway
{
    /**
     * Skip phone validation while the billing address has no telephone yet
     * (method selection in Hyvä Checkout). The full parent validation runs
     * as soon as a telephone is available, including at place order.
     *
     * @return $this
     */
    public function validate()
    {
        $info = $this->getInfoInstance();

        $order = $info->getQuote();
        if ($info->getOrder()) {
            $order = $info->getOrder();
        }

        $billingAddress = $order ? $order->getBillingAddress() : null;
        if (!$billingAddress || !$billingAddress->getTelephone()) {
            return $this;
        }

        return parent::validate();
    }
}
