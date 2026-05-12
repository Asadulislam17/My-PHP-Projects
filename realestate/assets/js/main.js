// ============================================
// MAIN JS
// ============================================

document.addEventListener('DOMContentLoaded', () => {

    // Search Tab Toggle
    const tabs = document.querySelectorAll('.search-tab');
    const priceTypeInput = document.getElementById('searchPriceType');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            if (priceTypeInput) {
                priceTypeInput.value = tab.dataset.type;
            }
        });
    });

    // AJAX Location Suggestions
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
                        if (!data.length) { suggestions.style.display = 'none'; return; }
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

    // Wishlist Toggle (AJAX)
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

    // Select location from suggestion
    window.selectLocation = function(name) {
        if (locationInput) locationInput.value = name;
        if (suggestions)   suggestions.style.display = 'none';
    };

    // Toast Notification
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

    // Counter Animation
    const counters = document.querySelectorAll('.stat-item strong');
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('counted');
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(c => observer.observe(c));

});

// CSS for toast animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }
    .suggestion-item {
        padding: 10px 14px; cursor: pointer; font-size: 0.875rem;
        border-bottom: 1px solid #f1f5f9; color: #0F172A;
    }
    .suggestion-item:hover { background: #f8fafc; color: #C5A059; }
`;
document.head.appendChild(style);

document.querySelectorAll(".wl-remove-form").forEach(form => {
  form.addEventListener("submit", async function(e) {
    e.preventDefault();

    const btn = this.querySelector("button");
    const card = this.closest(".col-md-6");

    btn.disabled = true;
    btn.innerHTML = "⏳";

    const formData = new FormData(this);

    const res = await fetch(window.location.href, {
      method: "POST",
      body: formData
    });

    if (res.ok) {
      card.style.transform = "scale(0.8)";
      card.style.opacity = "0";

      setTimeout(() => {
        card.remove();
      }, 300);
    } else {
      alert("Error removing item!");
      btn.disabled = false;
      btn.innerHTML = "❤️";
    }
  });
});

function updateWishlistCount() {
  const count = document.querySelectorAll(".wishlist-card").length;
  const headerCount = document.querySelector(".iph-content p");
  if (headerCount) {
    headerCount.textContent = `${count} টি property সংরক্ষিত`;
  }
}

// call after remove


