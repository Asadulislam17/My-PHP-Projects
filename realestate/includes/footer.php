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
            <h4><i class="bi bi-buildings-fill me-2"></i>Real<span class="text-accent">Estate</span> BD</h4>
            <p>বাংলাদেশের সেরা ও বিশ্বস্ত রিয়েল এস্টেট প্ল্যাটফর্ম। আপনার স্বপ্নের বাড়ি খুঁজে নিন।</p>
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
          <h6 class="footer-heading">দ্রুত লিঙ্ক</h6>
          <ul class="footer-links">
            <li><a href="?page=home">হোম</a></li>
            <li><a href="?page=listing">সব প্রপার্টি</a></li>
            <li><a href="?page=listing&price_type=sale">বিক্রয়</a></li>
            <li><a href="?page=listing&price_type=rent">ভাড়া</a></li>
            <li><a href="?page=blog">ব্লগ</a></li>
            <li><a href="?page=contact">যোগাযোগ</a></li>
          </ul>
        </div>

        <!-- Property Types -->
        <div class="col-lg-2 col-md-6">
          <h6 class="footer-heading">প্রপার্টি টাইপ</h6>
          <ul class="footer-links">
            <li><a href="?page=listing&type=apartment">অ্যাপার্টমেন্ট</a></li>
            <li><a href="?page=listing&type=villa">ভিলা</a></li>
            <li><a href="?page=listing&type=commercial">কমার্শিয়াল</a></li>
            <li><a href="?page=listing&type=land">জমি</a></li>
            <li><a href="?page=listing&type=office">অফিস স্পেস</a></li>
          </ul>
        </div>

        <!-- Tools & Services -->
        <div class="col-lg-2 col-md-6">
          <h6 class="footer-heading">টুলস</h6>
          <ul class="footer-links">
            <li><a href="?page=tools&tool=emi">EMI Calculator</a></li>
            <li><a href="?page=tools&tool=estimator">নির্মাণ খরচ</a></li>
            <li><a href="?page=compare">প্রপার্টি তুলনা</a></li>
            <li><a href="?page=subscription">Agent Plan</a></li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="col-lg-2 col-md-6">
          <h6 class="footer-heading">যোগাযোগ</h6>
          <ul class="footer-contact">
            <li>
              <i class="bi bi-geo-alt"></i>
              <span>গুলশান-১, ঢাকা-১২১২</span>
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
              <span>শনি–বৃহ: ৯টা–৬টা</span>
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
          <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. সর্বস্বত্ব সংরক্ষিত।</p>
        </div>
        <div class="col-md-6 text-center text-md-end">
          <a href="?page=privacy">গোপনীয়তা নীতি</a>
          <span class="mx-2">|</span>
          <a href="?page=terms">শর্তাবলী</a>
          <span class="mx-2">|</span>
          <a href="?page=sitemap">সাইটম্যাপ</a>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>