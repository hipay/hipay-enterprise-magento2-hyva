<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Model\Method;

use HiPay\FullserviceMagento\Model\Method\BancomatPayHostedFields as BaseBancomatPayHostedFields;

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
class BancomatPayHostedFields extends BaseBancomatPayHostedFields
{
    /**
     * Skip phone validation while the method is only being selected in Hyvä Checkout.
     * The phone is not submitted yet at that point; the full parent validation
     * runs again at place order, once the phone has been set on the payment.
     *
     * @return $this
     */
    public function validate()
    {
        if (!$this->getInfoInstance()->getAdditionalInformation('phone')) {
            return $this;
        }

        return parent::validate();
    }
}
