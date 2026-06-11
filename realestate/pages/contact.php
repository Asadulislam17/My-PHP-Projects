<?php
// Contact page logic (Optional: message handling)
$message_sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // এখানে আপনার মেইল পাঠানোর লজিক বা ডাটাবেসে সেভ করার কোড লিখবেন
    $message_sent = true;
}
?>

<section class="contact-3d-section">
  <!-- ব্যাকগ্রাউন্ডে ৩ডি ডাইনামিক লাইটিং গ্লো -->
  <div class="glow-3d-orb-contact orb-contact-1"></div>
  <div class="glow-3d-orb-contact orb-contact-2"></div>
  <div class="contact-mesh-overlay"></div>

  <div class="container position-relative py-5">
    <div class="row g-5 align-items-center">
        
        <!-- বাম পাশের ৩ডি ইনফরমেশন কার্ডস -->
        <div class="col-lg-5" data-aos="fade-right">
            <span class="contact-badge-3d mb-3">
              <i class="bi bi-headset text-accent me-1"></i> ২৪/৭ কাস্টমার সাপোর্ট
            </span>
            <h2 class="mb-4 contact-title-3d">আমাদের সাথে <br><span class="text-gradient-3d">যোগাযোগ করুন</span></h2>
            <p class="text-subtitle-3d mb-5">আপনার যেকোনো প্রশ্ন বা প্রপার্টি সংক্রান্ত সাহায্যের জন্য আমাদের মেটা-টিম সবসময় প্রস্তুত।</p>
            
            <div class="contact-info-wrapper-3d">
              <!-- ঠিকানা -->
              <div class="info-card-3d">
                  <div class="icon-box-3d">
                      <i class="bi bi-geo-alt-fill"></i>
                  </div>
                  <div class="info-content-3d">
                      <h5>ঠিকানা</h5>
                      <p>বাড়ি ১২, রোড ০৫, ধানমন্ডি, ঢাকা - ১২০৯</p>
                  </div>
              </div>

              <!-- ফোন -->
              <div class="info-card-3d">
                  <div class="icon-box-3d">
                      <i class="bi bi-telephone-fill"></i>
                  </div>
                  <div class="info-content-3d">
                      <h5>ফোন করুন</h5>
                      <p>+৮৮০ ১৭০০-০০০০০০</p>
                  </div>
              </div>

              <!-- ইমেলে -->
              <div class="info-card-3d">
                  <div class="icon-box-3d">
                      <i class="bi bi-envelope-fill"></i>
                  </div>
                  <div class="info-content-3d">
                      <h5>ইমেইল</h5>
                      <p>info@realestate.com</p>
                  </div>
              </div>
            </div>
        </div>

        <!-- ডান পাশের ফ্লোটিং ৩ডি কন্টাক্ট ফর্ম -->
        <div class="col-lg-7" data-aos="fade-left" data-aos-delay="100">
            <div class="contact-card-3d">
                <?php if ($message_sent): ?>
                    <div class="alert alert-success-3d mb-4">
                        <i class="bi bi-check-circle-fill me-2"></i>আপনার বার্তাটি সফলভাবে পাঠানো হয়েছে! আমরা শীঘ্রই যোগাযোগ করব।
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="form-3d">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="input-block-3d-contact">
                                <label class="form-label"><i class="bi bi-person-fill text-accent"></i> আপনার নাম</label>
                                <input type="text" name="name" class="field-input-3d-contact" required placeholder="নাম লিখুন">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-block-3d-contact">
                                <label class="form-label"><i class="bi bi-envelope-at-fill text-accent"></i> ইমেইল</label>
                                <input type="email" name="email" class="field-input-3d-contact" required placeholder="email@example.com">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="input-block-3d-contact">
                                <label class="form-label"><i class="bi bi-bookmark-star-fill text-accent"></i> বিষয়</label>
                                <input type="text" name="subject" class="field-input-3d-contact" placeholder="কি বিষয়ে জানতে চান?">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="input-block-3d-contact">
                                <label class="form-label"><i class="bi bi-chat-left-text-fill text-accent"></i> বার্তা</label>
                                <textarea name="message" class="field-input-3d-contact" rows="4" placeholder="আপনার বার্তাটি বিস্তারিত লিখুন..."></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="contact-submit-btn-3d">
                                <span>বার্তা পাঠান</span>
                                <i class="bi bi-cursor-fill ms-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
  </div>
</section>

<!-- ============================================
     CONTACT 3D STYLES (Inline CSS for Safety)
============================================ -->
<style>
.contact-3d-section {
  position: relative;
  background: #090d16;
  padding: 80px 0 100px 0;
  overflow: hidden;
  font-family: 'Hind Siliguri', sans-serif;
}

/* ৩ডি লাইটিং গ্লো অর্বস */
.glow-3d-orb-contact {
  position: absolute;
  border-radius: 50%;
  filter: blur(140px);
  opacity: 0.12;
  z-index: 1;
}
.orb-contact-1 { width: 450px; height: 450px; background: #C5A059; top: -5%; left: -10%; }
.orb-contact-2 { width: 400px; height: 400px; background: #3b82f6; bottom: -5%; right: -5%; }

.contact-mesh-overlay {
  position: absolute;
  inset: 0;
  background-image: linear-gradient(rgba(255,255,255,0.01) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.01) 1px, transparent 1px);
  background-size: 30px 30px;
  z-index: 2;
}

.contact-badge-3d {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 8px 16px;
  border-radius: 50px;
  color: #94a3b8;
  font-size: 14px;
  display: inline-block;
  backdrop-filter: blur(10px);
}

.contact-title-3d {
  font-size: 3rem;
  font-weight: 800;
  line-height: 1.2;
  color: #ffffff;
}

.text-gradient-3d {
  background: linear-gradient(135deg, #fff 0%, #C5A059 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.text-subtitle-3d {
  color: #94a3b8;
  font-size: 1.1rem;
}

/* বাম পাশের ৩ডি কার্ড লেআউট */
.contact-info-wrapper-3d {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-card-3d {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  padding: 20px;
  border-radius: 20px;
  backdrop-filter: blur(5px);
  transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}

.info-card-3d:hover {
  transform: translateX(10px) translateY(-2px);
  box-shadow: 0 15px 30px rgba(0,0,0,0.3);
  border-color: rgba(197, 160, 89, 0.3);
}

.icon-box-3d {
  width: 52px;
  height: 52px;
  background: linear-gradient(135deg, #C5A059 0%, #a17f3f 100%);
  color: white;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  margin-right: 20px;
  box-shadow: 0 8px 16px rgba(197, 160, 89, 0.2);
  flex-shrink: 0;
}

.info-content-3d h5 {
  color: #ffffff;
  font-weight: 700;
  margin-bottom: 5px;
  font-size: 1.1rem;
}

.info-content-3d p {
  color: #94a3b8;
  margin-bottom: 0;
  font-size: 0.95rem;
}

/* ডান পাশের ফ্লোটিং ৩ডি ফর্ম কার্ড */
.contact-card-3d {
  background: rgba(15, 23, 42, 0.55);
  border: 1px solid rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(25px);
  border-radius: 28px;
  padding: 40px;
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255,255,255,0.1);
  transform: perspective(1000px) rotateY(-3deg);
  transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.contact-card-3d:hover {
  transform: perspective(1000px) rotateY(0deg) translateY(-5px);
}

.input-block-3d-contact label {
  color: #94a3b8;
  font-size: 14px;
  margin-bottom: 8px;
  display: block;
}

.field-input-3d-contact {
  width: 100%;
  background: rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.05);
  padding: 14px 18px;
  border-radius: 14px;
  color: #fff;
  outline: none;
  transition: all 0.3s ease;
}

.field-input-3d-contact:focus {
  border-color: #C5A059;
  background: rgba(0, 0, 0, 0.4);
  box-shadow: 0 0 0 4px rgba(197, 160, 89, 0.15);
}

/* ৩ডি সাবমিট বাটন */
.contact-submit-btn-3d {
  width: 100%;
  background: linear-gradient(135deg, #fff 0%, #e2e8f0 100%);
  border: none;
  padding: 16px;
  border-radius: 14px;
  font-weight: 700;
  color: #0f172a;
  box-shadow: 0 15px 30px rgba(255,255,255,0.05);
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  cursor: pointer;
}

.contact-submit-btn-3d:hover {
  background: linear-gradient(135deg, #C5A059 0%, #a17f3f 100%);
  color: #fff;
  transform: translateY(-3px);
  box-shadow: 0 15px 30px rgba(197, 160, 89, 0.4);
}

/* সাকসেস মেসেজ ৩ডি গ্লাস স্টাইল */
.alert-success-3d {
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.2);
  color: #22c55e;
  padding: 15px;
  border-radius: 14px;
  font-size: 0.95rem;
  backdrop-filter: blur(10px);
}
</style>
