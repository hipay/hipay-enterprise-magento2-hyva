<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Observer\Frontend;

use Hyva\Checkout\Observer\Frontend\HyvaCheckoutHyvaCheckoutInitAfter as BaseObserver;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;

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
class HyvaCheckoutHyvaCheckoutInitAfter extends BaseObserver
{
    /**
     * Tolerate the InputException raised when a method is selected too early.
     *
     * Mirrors the sibling processShippingAddress() tolerance.
     *
     * @param Quote $quote
     * @return Quote
     */
    public function processBillingAddress(Quote $quote): Quote
    {
        try {
            return parent::processBillingAddress($quote);
        } catch (InputException | NoSuchEntityException $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            return $quote;
        }
    }
}
