<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Magewire;

use HiPay\FullserviceMagento\Model\Method\Providers\GenericConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magewirephp\Magewire\Component;

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
class CardMethod extends Component
{
    public array $customerInformation = [];

    public array $customerCards = [];

    public string $selectedCustomerCard = '';

    public bool $customerCardIsSelected = false;

    public function __construct(
        private CartRepositoryInterface $quoteRepository,
        private GenericConfigProvider $genericConfigProvider,
        private Session $checkoutSession
    ) { }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function mount(): void
    {
        $quote = $this->checkoutSession->getQuote();
        $billingAddress = $quote->getBillingAddress();
        $this->customerCards = $this->genericConfigProvider->getConfig()['payment']['hiPayFullservice']['customerCards'];

        if ($billingAddress) {
            $this->customerInformation = [
                'firstName' => $billingAddress->getFirstname(),
                'lastName' => $billingAddress->getLastname(),
            ];
        }

        if ($this->customerCards) {
            $this->selectedCustomerCard = '0';
            $this->customerCardIsSelected = true;
        }
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setPaymentData(array $value): void
    {
        $quote = $this->checkoutSession->getQuote();
        $payment = $quote->getPayment();
        $payment->setAdditionalInformation($value['additionalData'] ?? null);
        $payment->setCcType($value['ccType'] ?? null);
        $quote->setPayment($payment);
        $this->quoteRepository->save($quote);
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setAdditionalData(string $key, string $value): void
    {
        $quote = $this->checkoutSession->getQuote();
        $payment = $quote->getPayment();
        $payment->setAdditionalInformation($key, $value);
        $quote->setPayment($payment);
        $this->quoteRepository->save($quote);
    }

    public function updatingSelectedCustomerCard(?string $value): ?string
    {
        $this->customerCardIsSelected = strlen($value) > 0;

        return $value;
    }
}
