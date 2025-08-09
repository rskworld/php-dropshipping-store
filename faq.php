<?php
$pageTitle = 'FAQ | RSK Dropshipping Template';
$currentPage = 'faq';
require 'header.php';
?>

<div class="container page-content" style="margin-top: 100px;">
    <h1 class="section-title">Frequently Asked Questions</h1>
    <div class="accordion" id="faqAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="faqHeadingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseOne" aria-expanded="true" aria-controls="faqCollapseOne">
                    What is dropshipping?
                </button>
            </h2>
            <div id="faqCollapseOne" class="accordion-collapse collapse show" aria-labelledby="faqHeadingOne" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Dropshipping is a retail fulfillment method where a store doesn't keep the products it sells in stock. Instead, the store purchases items from a third party and ships them directly to the customer.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="faqHeadingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseTwo" aria-expanded="false" aria-controls="faqCollapseTwo">
                    Do I need to purchase inventory upfront?
                </button>
            </h2>
            <div id="faqCollapseTwo" class="accordion-collapse collapse" aria-labelledby="faqHeadingTwo" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    No. With dropshipping, you only purchase the product once a customer places an order, eliminating the need for upfront inventory costs.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="faqHeadingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseThree" aria-expanded="false" aria-controls="faqCollapseThree">
                    How long does shipping take?
                </button>
            </h2>
            <div id="faqCollapseThree" class="accordion-collapse collapse" aria-labelledby="faqHeadingThree" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Shipping times vary depending on the supplier and destination country, but typical delivery times range from 7-21 business days.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="faqHeadingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseFour" aria-expanded="false" aria-controls="faqCollapseFour">
                    Can I customize product prices and descriptions?
                </button>
            </h2>
            <div id="faqCollapseFour" class="accordion-collapse collapse" aria-labelledby="faqHeadingFour" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Absolutely! You have full control over product pricing, descriptions, and images in your store.
                </div>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
