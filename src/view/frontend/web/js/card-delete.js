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

"use strict";

/**
 * Alpine component handling customer card deletion confirmation flow.
 */
function deleteCardComponent() {
    return {
        deleteUrlPrefix: "",
        selectedCardId: null,
        modal: null,

        /**
         * Read delete URL prefix from server-rendered data attribute.
         */
        init() {
            this.deleteUrlPrefix = this.$el.dataset.deleteUrlPrefix || "";
        },

        /**
         * Open confirmation modal and store the selected card id.
         */
        openModal(cardId) {
            this.selectedCardId = cardId;
            this.modal = document.getElementById("delete-confirmation-modal");
            if (this.modal) this.modal.showModal();
        },

        /**
         * Redirect to delete endpoint after user confirmation.
         */
        confirmDelete() {
            if (this.selectedCardId) {
                window.location.href = this.deleteUrlPrefix + this.selectedCardId;
            }
        }
    };
}

window.addEventListener(
    "alpine:init",
    () => Alpine.data("deleteCardComponent", deleteCardComponent),
    { once: true }
);
