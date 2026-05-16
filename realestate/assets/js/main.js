// ============================================
// MAIN JS (Next-Gen 3D Support Embedded)
// ============================================

document.addEventListener('DOMContentLoaded', () => {

    // 1. Search Tab Toggle (সংশোধিত: নতুন ৩ডি ক্লাসের নাম দেওয়া হয়েছে)
        // 1. Search Tab Toggle (সম্পূর্ণ সুরক্ষিত ও ৩ডি সাপোর্টেড)
    const tabs = document.querySelectorAll('.search-tab-3d');
    const priceTypeInput = document.getElementById('searchPriceType');

    if (tabs.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();  // বাটন ক্লিকের ডিফল্ট অ্যাকশন বন্ধ করবে
                e.stopPropagation();  // ফর্ম বা অন্য এলিমেন্টে ইভেন্ট ছড়ানো আটকাবে
                
                // আগের একটিভ ট্যাব থেকে active ক্লাস রিমুভ করা
                tabs.forEach(t => t.classList.remove('active'));
                
                // বর্তমান ক্লিক করা ৩ডি বাটনে active ক্লাস যোগ করা
                this.classList.add('active');
                
                // হিডেন ইনপুটে ভ্যালু অ্যাসাইন করা (sale অথবা rent)
                if (priceTypeInput) {
                    priceTypeInput.value = this.getAttribute('data-type');
                }
                
                console.log('[3D Search Tab] Active Price Type:', priceTypeInput.value);
            });
        });
    }


    // 2. AJAX Location Suggestions
    const locationInput = document.getElementById('locationSearch');
    const suggestions   = document.getElementById('searchSuggestions');

    if (locationInput && suggestions) {
        let timeout;
        locationInput.addEventListener('input', () => {
            clearTimeout(timeout);
            const query = locationInput.value.trim();
            if (query.length < 2) { suggestions.style.display = 'none'; return; }

            timeout = setTimeout(() => {
                fetch(`api/v1/search-suggestions.php?q=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(data => {
                        if (!data || !data.length) { suggestions.style.display = 'none'; return; }
                        suggestions.innerHTML = data.map(item =>
                            `<div class="suggestion-item" onclick="selectLocation('${item.name}')">
                                <i class="bi bi-geo-alt me-2"></i>${item.name}
                             </div>`
                        ).join('');
                        suggestions.style.display = 'block';
                    })
                    .catch(() => { suggestions.style.display = 'none'; });
            }, 300);
        });

        document.addEventListener('click', e => {
            if (!locationInput.contains(e.target)) {
                suggestions.style.display = 'none';
            }
        });
    }

    // 3. Wishlist Toggle (AJAX)
    window.toggleWishlist = function(propertyId, btn) {
        fetch('api/v1/wishlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ property_id: propertyId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const icon = btn.querySelector('i');
                if (data.wishlisted) {
                    icon.className = 'bi bi-heart-fill';
                    btn.classList.add('active');
                    showToast('Wishlist এ যোগ হয়েছে', 'success');
                } else {
                    icon.className = 'bi bi-heart';
                    btn.classList.remove('active');
                    showToast('Wishlist থেকে সরানো হয়েছে', 'info');
                }
            } else {
                window.location.href = '?page=login';
            }
        })
        .catch(() => showToast('কিছু একটা সমস্যা হয়েছে', 'error'));
    };

    // 4. Select location from suggestion
    window.selectLocation = function(name) {
        if (locationInput) locationInput.value = name;
        if (suggestions)   suggestions.style.display = 'none';
    };

    // 5. Toast Notification
    window.showToast = function(message, type = 'success') {
        const colors = {
            success: '#22c55e',
            error:   '#ef4444',
            info:    '#3b82f6'
        };
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            background: ${colors[type] || colors.success}; color: white;
            padding: 12px 20px; border-radius: 10px;
            font-family: 'Inter',sans-serif; font-size: 0.9rem; font-weight: 500;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            animation: slideUp 0.3s ease;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    // 6. Counter Animation (সংশোধিত: নতুন ৩ডি মিনি কার্ড ক্লাসের জন্য ট্র্যাকিং যোগ)
    const counters = document.querySelectorAll('.stat-item strong, .stat-box-3d .stat-val');
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('counted');
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(c => observer.observe(c));

    // 7. Wishlist Card Removal Handler
    document.querySelectorAll(".wl-remove-form").forEach(form => {
        form.addEventListener("submit", async function(e) {
            e.preventDefault();

            const btn = this.querySelector("button");
            const card = this.closest(".col-md-6, .col-lg-4, .col-lg-3"); // মাল্টিপল গ্রিড সেফটি চেক

            if (!btn || !card) return;

            btn.disabled = true;
            btn.innerHTML = "⏳";

            const formData = new FormData(this);

            try {
                const res = await fetch(window.location.href, {
                    method: "POST",
                    body: formData
                });

                if (res.ok) {
                    card.style.transform = "scale(0.8)";
                    card.style.opacity = "0";
                    card.style.transition = "all 0.3s ease";

                    setTimeout(() => {
                        card.remove();
                        updateWishlistCount(); // কার্ড ডিলিট হওয়ার পর কাউন্টার আপডেট কল
                    }, 300);
                } else {
                    throw new Error();
                }
            } catch (err) {
                alert("Error removing item!");
                btn.disabled = false;
                btn.innerHTML = "❤️";
            }
        });
    });

    // 8. Dynamic Wishlist Counter Updater
    function updateWishlistCount() {
        const count = document.querySelectorAll(".wishlist-card").length;
        const headerCount = document.querySelector(".iph-content p");
        if (headerCount) {
            headerCount.textContent = `${count} টি property সংরক্ষিত`;
        }
    }

});

// CSS Injection for dynamic elements
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }
    .suggestion-item {
        padding: 12px 16px; cursor: pointer; font-size: 0.9rem;
        border-bottom: 1px solid rgba(255,255,255,0.05); color: #fff;
        transition: background 0.2s ease;
    }
    .suggestion-item:hover { background: rgba(197, 160, 89, 0.15); color: #C5A059; }
`;
document.head.appendChild(style);
