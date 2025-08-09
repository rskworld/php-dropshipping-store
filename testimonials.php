<?php
$pageTitle = 'Testimonials | RSK Dropshipping Template';
$currentPage = 'testimonials';
require 'header.php';
?>

<div class="container page-content" style="margin-top: 100px;">
    <h1 class="section-title text-center mb-5">What Our Customers Say</h1>

    <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm p-4 text-center">
                            <i class="fas fa-quote-left fa-2x text-primary mb-3"></i>
                            <p class="lead">"RSK's template helped me launch my store within a day. The design looks professional and my sales skyrocketed!"</p>
                            <h5 class="mt-3 mb-0">Sarah J.</h5>
                            <small class="text-muted">Owner, TrendyTech</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm p-4 text-center">
                            <i class="fas fa-quote-left fa-2x text-primary mb-3"></i>
                            <p class="lead">"The product import feature saved me hours of work. Highly recommend to anyone starting a dropshipping business."</p>
                            <h5 class="mt-3 mb-0">Michael B.</h5>
                            <small class="text-muted">Founder, HomeStyleHub</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm p-4 text-center">
                            <i class="fas fa-quote-left fa-2x text-primary mb-3"></i>
                            <p class="lead">"Amazing customer support and a feature-rich dashboard. I was able to scale my store with ease."</p>
                            <h5 class="mt-3 mb-0">Priya K.</h5>
                            <small class="text-muted">CEO, FashionFiesta</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>

<?php require 'footer.php'; ?>
