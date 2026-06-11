<!-- ============================================
     FOOTER
============================================ -->
<footer class="site-footer">
  <div class="footer-top">
    <div class="container">
      <div class="row g-4">

        <!-- Brand Column -->
        <div class="col-lg-4 col-md-6">
          <div class="footer-brand">
            <h4><i class="bi bi-buildings-fill me-2"></i>Omni<span class="text-accent">Estate</span> BD</h4>
            <p><?= __('বাংলাদেশের সেরা ও বিশ্বস্ত রিয়েল এস্টেট প্ল্যাটফর্ম। আপনার স্বপ্নের বাড়ি খুঁজে নিন।', 'The best and most trusted real estate platform in Bangladesh. Find your dream home.') ?></p>
            <div class="social-links">
              <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
              <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
              <a href="#" class="social-link"><i class="bi bi-youtube"></i></a>
              <a href="#" class="social-link"><i class="bi bi-linkedin"></i></a>
            </div>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="col-lg-2 col-md-6">
          <h6 class="footer-heading"><?= __('দ্রুত লিঙ্ক', 'Quick Links') ?></h6>
          <ul class="footer-links">
            <li><a href="?page=home"><?= __('হোম', 'Home') ?></a></li>
            <li><a href="?page=listing"><?= __('সব প্রপার্টি', 'All Properties') ?></a></li>
            <li><a href="?page=listing&price_type=sale"><?= __('বিক্রয়', 'Buy') ?></a></li>
            <li><a href="?page=listing&price_type=rent"><?= __('ভাড়া', 'Rent') ?></a></li>
            <li><a href="?page=blog"><?= __('ব্লগ', 'Blog') ?></a></li>
            <li><a href="?page=contact"><?= __('যোগাযোগ', 'Contact') ?></a></li>
          </ul>
        </div>

        <!-- Property Types -->
        <div class="col-lg-2 col-md-6">
          <h6 class="footer-heading"><?= __('প্রপার্টি টাইপ', 'Property Types') ?></h6>
          <ul class="footer-links">
            <li><a href="?page=listing&type=apartment"><?= __('অ্যাপার্টমেন্ট', 'Apartment') ?></a></li>
            <li><a href="?page=listing&type=villa"><?= __('ভিলা', 'Villa') ?></a></li>
            <li><a href="?page=listing&type=commercial"><?= __('কমার্শিয়াল', 'Commercial') ?></a></li>
            <li><a href="?page=listing&type=land"><?= __('জমি', 'Land') ?></a></li>
            <li><a href="?page=listing&type=office"><?= __('অফিস স্পেস', 'Office Space') ?></a></li>
          </ul>
        </div>

        <!-- Tools & Services -->
        <div class="col-lg-2 col-md-6">
          <h6 class="footer-heading"><?= __('টুলস', 'Tools') ?></h6>
          <ul class="footer-links">
            <li><a href="?page=tools&tool=emi"><?= __('EMI Calculator', 'EMI Calculator') ?></a></li>
            <li><a href="?page=tools&tool=estimator"><?= __('নির্মাণ খরচ', 'Construction Cost') ?></a></li>
            <li><a href="?page=compare"><?= __('প্রপার্টি তুলনা', 'Compare Properties') ?></a></li>
            <li><a href="?page=subscription"><?= __('Agent Plan', 'Agent Plan') ?></a></li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="col-lg-2 col-md-6">
          <h6 class="footer-heading"><?= __('যোগাযোগ', 'Contact Info') ?></h6>
          <ul class="footer-contact">
            <li>
              <i class="bi bi-geo-alt"></i>
              <span><?= __('গুলশান-১, ঢাকা-১২১২', 'Gulshan-1, Dhaka-1212') ?></span>
            </li>
            <li>
              <i class="bi bi-telephone"></i>
              <span>+880 1700-000000</span>
            </li>
            <li>
              <i class="bi bi-envelope"></i>
              <span>info@realestate.com</span>
            </li>
            <li>
              <i class="bi bi-clock"></i>
              <span><?= __('শনি–বৃহ: ৯টা–৬টা', 'Sat–Thu: 9am–6pm') ?></span>
            </li>
          </ul>
        </div>

      </div>
    </div>
  </div>

  <!-- Footer Bottom -->
  <div class="footer-bottom">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6 text-center text-md-start">
          <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. <?= __('সর্বস্বত্ব সংরক্ষিত।', 'All Rights Reserved.') ?></p>
        </div>
        <div class="col-md-6 text-center text-md-end">
          <a href="?page=privacy"><?= __('গোপনীয়তা নীতি', 'Privacy Policy') ?></a>
          <span class="mx-2">|</span>
          <a href="?page=terms"><?= __('শর্তাবলী', 'Terms & Conditions') ?></a>
          <span class="mx-2">|</span>
          <a href="?page=sitemap"><?= __('সাইটম্যাপ', 'Sitemap') ?></a>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script src="<?= APP_URL ?>/assets/js/pwa.js"></script>
</body>
</html>
