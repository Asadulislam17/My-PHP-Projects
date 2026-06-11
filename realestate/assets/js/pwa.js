/**
 * ══════════════════════════════════════════════
 * PWA REGISTRATION — RealEstate BD
 * assets/js/pwa.js
 * ══════════════════════════════════════════════
 */

const PWA = (() => {

  const VAPID_PUBLIC_KEY = 'YOUR_VAPID_PUBLIC_KEY_HERE'; // Generate: web-push generate-vapid-keys

  /* ── Register Service Worker ── */
async function registerSW() {
  if (!('serviceWorker' in navigator)) {
    console.log('[PWA] Service Worker not supported');
    return;
  }

  try {
    // পরিবর্তন: scope: '/' থেকে './' করা হয়েছে
    // এটি আপনার realestate ফোল্ডারের জন্য সার্ভিস ওয়ার্কারকে অনুমতি দেবে
    const registration = await navigator.serviceWorker.register('./sw.js', {
      scope: './', 
      updateViaCache: 'none',
    });

    console.log('[PWA] SW registered, scope:', registration.scope);

    // Check for updates every 60 seconds
    setInterval(() => registration.update(), 60_000);

    // Handle SW update found
    registration.addEventListener('updatefound', () => {
      const newWorker = registration.installing;
      console.log('[PWA] New SW installing...');

      newWorker.addEventListener('statechange', () => {
        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
          // নিশ্চিত করুন showUpdateToast() ফাংশনটি আপনার ফাইলে আছে
          if (typeof showUpdateToast === 'function') {
            showUpdateToast();
          }
        }
      });
    });

    // Handle controller change (new SW activated)
    navigator.serviceWorker.addEventListener('controllerchange', () => {
      window.location.reload();
    });

    return registration;

  } catch (error) {
    console.error('[PWA] SW registration failed:', error);
  }
}


  /* ── Install Prompt (Add to Home Screen) ── */
  let deferredInstallPrompt = null;

  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredInstallPrompt = e;

    // Show custom install button after 30 seconds
    setTimeout(() => {
      if (deferredInstallPrompt && !isInstalled()) {
        showInstallBanner();
      }
    }, 30_000);

    // Also show on first visit
    if (!localStorage.getItem('pwa_install_shown')) {
      setTimeout(showInstallBanner, 5000);
    }
  });

  window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    hideInstallBanner();
    console.log('[PWA] App installed!');
    trackEvent('pwa_installed');
  });

  function isInstalled() {
    return window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
  }

  async function promptInstall() {
    if (!deferredInstallPrompt) return false;
    deferredInstallPrompt.prompt();
    const { outcome } = await deferredInstallPrompt.userChoice;
    deferredInstallPrompt = null;
    return outcome === 'accepted';
  }

  /* ── Push Notifications ── */
  async function requestPushPermission() {
    if (!('Notification' in window)) return 'unsupported';
    if (!('PushManager' in window)) return 'unsupported';

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') return permission;

    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.subscribe({
      userVisibleOnly:      true,
      applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
    });

    // Save subscription to server
    await fetch('/api/v1/push/subscribe', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(subscription),
    });

    console.log('[PWA] Push subscription active');
    return 'granted';
  }

  async function unsubscribePush() {
    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();
    if (!subscription) return;
    await subscription.unsubscribe();
    await fetch('/api/v1/push/unsubscribe', { method: 'POST' });
  }

  /* ── Offline Detection ── */
  function initOfflineDetection() {
    const indicator = document.createElement('div');
    indicator.id    = 'offlineIndicator';
    indicator.innerHTML = `
      <div class="offline-bar">
        <i class="bi bi-wifi-off me-2"></i>
        আপনি অফলাইনে আছেন। কিছু ফিচার কাজ নাও করতে পারে।
      </div>
    `;
    document.body.appendChild(indicator);

    const style = document.createElement('style');
    style.textContent = `
      #offlineIndicator {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 9999;
        display: none; transform: translateY(100%);
        transition: transform 0.3s ease;
      }
      #offlineIndicator.show { display: block; transform: translateY(0); }
      .offline-bar {
        background: #1E293B; color: rgba(255,255,255,0.9);
        padding: 10px 20px; text-align: center; font-size: 0.875rem;
        border-top: 2px solid #C5A059;
      }
    `;
    document.head.appendChild(style);

    function updateOnlineStatus() {
      indicator.classList.toggle('show', !navigator.onLine);
      if (navigator.onLine) {
        // showToast('ইন্টারনেট সংযোগ ফিরে এসেছে!', 'success');
      }
    }

    window.addEventListener('online',  updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    updateOnlineStatus();
  }

  /* ── Background Sync ── */
  async function queueOfflineAction(type, data) {
    if ('serviceWorker' in navigator && 'SyncManager' in window) {
      // Store in localStorage (use IndexedDB in production)
      const pending = JSON.parse(localStorage.getItem(`pending_${type}`) || '[]');
      pending.push({ ...data, id: Date.now(), queued_at: new Date().toISOString() });
      localStorage.setItem(`pending_${type}`, JSON.stringify(pending));

      const registration = await navigator.serviceWorker.ready;
      await registration.sync.register(`sync-${type}`);
      console.log('[PWA] Action queued for sync:', type);
      showToast('অফলাইনে সংরক্ষিত। সংযোগ হলে পাঠানো হবে।', 'info');
    }
  }

  /* ── UI Helpers ── */
  function showInstallBanner() {
    localStorage.setItem('pwa_install_shown', '1');

    const banner = document.createElement('div');
    banner.id    = 'pwaInstallBanner';
    banner.innerHTML = `
      <div class="pwa-banner">
        <div class="pwa-banner-icon">
          <img src="/assets/icons/icon-72.png" alt="App Icon" width="48" height="48">
        </div>
        <div class="pwa-banner-text">
          <strong>RealEstate BD App</strong>
          <small>হোম স্ক্রিনে যোগ করুন — দ্রুত অ্যাক্সেস পান</small>
        </div>
        <div class="pwa-banner-actions">
          <button onclick="PWA.promptInstall()" class="pwa-install-btn">
            ইনস্টল করুন
          </button>
          <button onclick="document.getElementById('pwaInstallBanner').remove()"
                  class="pwa-dismiss-btn">✕</button>
        </div>
      </div>
    `;

    const style = document.createElement('style');
    style.textContent = `
      #pwaInstallBanner {
        position: fixed; bottom: 20px; left: 16px; right: 16px;
        z-index: 9998; animation: slideUp 0.4s ease;
      }
      .pwa-banner {
        background: #1E293B; border: 1px solid rgba(197,160,89,0.3);
        border-radius: 16px; padding: 16px 18px;
        display: flex; align-items: center; gap: 14px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.4);
      }
      .pwa-banner-text { flex: 1; }
      .pwa-banner-text strong { display: block; color: #fff; font-size: 0.95rem; }
      .pwa-banner-text small  { color: rgba(255,255,255,0.5); font-size: 0.78rem; }
      .pwa-banner-actions { display: flex; gap: 8px; align-items: center; }
      .pwa-install-btn {
        background: #C5A059; color: #0F172A; border: none;
        padding: 8px 16px; border-radius: 8px; font-weight: 700;
        font-size: 0.82rem; cursor: pointer; white-space: nowrap;
      }
      .pwa-dismiss-btn {
        background: none; border: none; color: rgba(255,255,255,0.4);
        cursor: pointer; font-size: 1rem; padding: 4px 8px;
      }
    `;
    document.head.appendChild(style);
    document.body.appendChild(banner);
  }

  function hideInstallBanner() {
    document.getElementById('pwaInstallBanner')?.remove();
  }

  function showUpdateToast() {
    const toast = document.createElement('div');
    toast.style.cssText = `
      position:fixed;top:20px;right:20px;z-index:9999;
      background:#1E293B;border:1px solid rgba(197,160,89,0.4);
      border-radius:12px;padding:14px 18px;color:#fff;
      font-size:0.875rem;box-shadow:0 8px 24px rgba(0,0,0,0.3);
      display:flex;align-items:center;gap:12px;
    `;
    toast.innerHTML = `
      <span>🔄 নতুন আপডেট আছে!</span>
      <button onclick="location.reload()"
              style="background:#C5A059;color:#0F172A;border:none;padding:6px 14px;
                     border-radius:6px;font-weight:700;cursor:pointer;">
        Update করুন
      </button>
      <button onclick="this.parentElement.remove()"
              style="background:none;border:none;color:rgba(255,255,255,0.4);cursor:pointer;">✕</button>
    `;
    document.body.appendChild(toast);
  }

  /* ── Utility ── */
  function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const output  = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) output[i] = rawData.charCodeAt(i);
    return output;
  }

  function trackEvent(name, data = {}) {
    // Analytics tracking (connect to your analytics)
    console.log('[PWA Event]', name, data);
  }

  /* ── Init ── */
  async function init() {
    if (isInstalled()) {
      console.log('[PWA] Running as installed app');
      document.documentElement.classList.add('pwa-standalone');
    }

    await registerSW();
    initOfflineDetection();

    // Show push permission prompt after 2 minutes
    setTimeout(async () => {
      if ('Notification' in window && Notification.permission === 'default') {
        // Only show if user has been active
        if (document.hasFocus()) {
          showPushPrompt();
        }
      }
    }, 120_000);
  }

  function showPushPrompt() {
    if (localStorage.getItem('push_prompt_shown')) return;
    localStorage.setItem('push_prompt_shown', '1');

    const prompt = document.createElement('div');
    prompt.style.cssText = `
      position:fixed;top:80px;right:20px;z-index:9997;
      background:#1E293B;border:1px solid rgba(255,255,255,0.08);
      border-radius:14px;padding:20px;max-width:300px;
      box-shadow:0 12px 40px rgba(0,0,0,0.4);color:#fff;
    `;
    prompt.innerHTML = `
      <div style="font-size:1.5rem;margin-bottom:10px">🔔</div>
      <strong style="display:block;margin-bottom:6px">Notification চালু করুন</strong>
      <p style="font-size:0.82rem;color:rgba(255,255,255,0.55);margin-bottom:14px">
        নতুন property, inquiry reply ও bookings এর notification পান
      </p>
      <div style="display:flex;gap:8px">
        <button onclick="PWA.requestPushPermission().then(()=>this.closest('div[style]').remove())"
                style="flex:1;background:#C5A059;color:#0F172A;border:none;padding:8px;
                       border-radius:8px;font-weight:700;cursor:pointer;font-size:0.82rem">
          চালু করুন
        </button>
        <button onclick="this.closest('div[style]').remove()"
                style="background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.5);
                       border:none;padding:8px 12px;border-radius:8px;cursor:pointer">
          না
        </button>
      </div>
    `;
    document.body.appendChild(prompt);
    setTimeout(() => prompt?.remove(), 15000);
  }

  /* ── Public API ── */
  return {
    init,
    promptInstall,
    requestPushPermission,
    unsubscribePush,
    queueOfflineAction,
    isInstalled,
  };

})();

/* ── Auto-initialize ── */
document.addEventListener('DOMContentLoaded', () => PWA.init());