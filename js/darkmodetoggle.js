// js/darkmodetoggle.js
// Simple dark / light toggle for Bootstrap 5.3 using data-bs-theme

(() => {
  'use strict';

  const THEME_KEY = 'theme';           // reuse same key name
  const root = document.documentElement; // <html>
  const TOGGLE_ID = 'themeToggle';     // button id in header

  function setTheme(theme) {
    root.setAttribute('data-bs-theme', theme);
  }

  function getPreferredTheme() {
    const stored = localStorage.getItem(THEME_KEY);
    if (stored === 'light' || stored === 'dark') {
      return stored;
    }
    // fallback: match OS
    return window.matchMedia('(prefers-color-scheme: dark)').matches
      ? 'dark'
      : 'light';
  }

  function applyTheme(theme) {
    setTheme(theme);
    const btn = document.getElementById(TOGGLE_ID);
    if (btn) {
      btn.textContent = theme === 'dark' ? 'Light mode' : 'Dark mode';
    }
  }

  window.addEventListener('DOMContentLoaded', () => {
    // Initial theme
    let currentTheme = getPreferredTheme();
    applyTheme(currentTheme);

    const btn = document.getElementById(TOGGLE_ID);
    if (!btn) return;

    btn.addEventListener('click', () => {
      const current = root.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
      const next = current === 'dark' ? 'light' : 'dark';
      localStorage.setItem(THEME_KEY, next);
      applyTheme(next);
    });
  });
})();
