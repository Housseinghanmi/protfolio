/**
 * Visitor Tracker
 * Tracks visits with location and time data.
 * Sends data to Netlify Function → Supabase
 * Sends email alert via EmailJS → Gmail
 */

(function () {
  'use strict';

  const VisitorTracker = {

    // ── Config ────────────────────────────────────────────────────────────────

    apiEndpoint: '/.netlify/functions/track-visitor',

    emailjs: {
  publicKey:  'eYDWq8ZhkQ-pdwDxO',
  serviceId:  'service_cgpp6gk',
  templateId: 'template_la538wo'
},

    enableGeolocation: true,

    // ── Init ──────────────────────────────────────────────────────────────────

    init: function () {
      this.loadEmailJS().then(() => this.trackVisit());
    },

    // ── Load EmailJS SDK dynamically ──────────────────────────────────────────

    loadEmailJS: function () {
      return new Promise((resolve) => {
        if (window.emailjs) { resolve(); return; }

        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js';
        script.onload = () => {
          window.emailjs.init(this.emailjs.publicKey);
          resolve();
        };
        script.onerror = () => {
          console.warn('EmailJS failed to load — email alerts disabled.');
          resolve(); // don't block tracking
        };
        document.head.appendChild(script);
      });
    },

    // ── Build visitor data object ─────────────────────────────────────────────

    buildVisitorData: function (coords = null) {
      const data = {
        timestamp:  new Date().toISOString(),
        page:       window.location.pathname || '/',
        pageTitle:  document.title,
        referrer:   document.referrer || 'direct',
        userAgent:  navigator.userAgent,
        language:   navigator.language,
        timezone:   Intl.DateTimeFormat().resolvedOptions().timeZone,
        sessionId:  this.getOrCreateSessionId(),
        ipAddress:  'server-side' // resolved by Netlify function
      };

      if (coords) {
        data.latitude  = coords.latitude;
        data.longitude = coords.longitude;
        data.accuracy  = coords.accuracy;
      }

      return data;
    },

    // ── Main tracking flow ────────────────────────────────────────────────────

    trackVisit: function () {
      if (this.enableGeolocation && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          (position) => {
            const data = this.buildVisitorData(position.coords);
            this.sendToBackend(data);
            this.sendEmailAlert(data);
          },
          (error) => {
            console.log('Geolocation unavailable:', error.message);
            const data = this.buildVisitorData();
            this.sendToBackend(data);
            this.sendEmailAlert(data);
          },
          { enableHighAccuracy: false, timeout: 5000, maximumAge: 0 }
        );
      } else {
        const data = this.buildVisitorData();
        this.sendToBackend(data);
        this.sendEmailAlert(data);
      }
    },

    // ── Send to Netlify Function → Supabase ───────────────────────────────────

    sendToBackend: function (data) {
      const payload = JSON.stringify(data);

      if (navigator.sendBeacon) {
        navigator.sendBeacon(this.apiEndpoint, payload);
      } else {
        fetch(this.apiEndpoint, {
          method:    'POST',
          headers:   { 'Content-Type': 'application/json' },
          body:      payload,
          keepalive: true
        }).catch((err) => console.warn('Tracker backend error:', err));
      }
    },

    // ── Send Gmail alert via EmailJS ──────────────────────────────────────────

    sendEmailAlert: function (data) {
      if (!window.emailjs) return;

      const templateParams = {
        timestamp:  data.timestamp,
        page:       data.page,
        page_title: data.pageTitle,
        referrer:   data.referrer,
        language:   data.language,
        timezone:   data.timezone,
        session_id: data.sessionId,
        ip_address: data.ipAddress,
        user_agent: data.userAgent,
        latitude:   data.latitude  ?? 'N/A',
        longitude:  data.longitude ?? 'N/A'
      };

      window.emailjs
        .send(this.emailjs.serviceId, this.emailjs.templateId, templateParams)
        .then(() => console.log('Visitor alert sent.'))
        .catch((err) => console.warn('EmailJS error:', err));
    },

    // ── Session ID ────────────────────────────────────────────────────────────

    getOrCreateSessionId: function () {
      const key = 'visitor_session_id';
      let id = sessionStorage.getItem(key);
      if (!id) {
        id = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        sessionStorage.setItem(key, id);
      }
      return id;
    }
  };

  // ── Boot ───────────────────────────────────────────────────────────────────

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => VisitorTracker.init());
  } else {
    VisitorTracker.init();
  }

  window.VisitorTracker = VisitorTracker;

})();