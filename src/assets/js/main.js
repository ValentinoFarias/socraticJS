// main.js — Shared JavaScript
// Loaded on all pages. Add global helpers and shared behaviour here.
// Page-specific JS can go inline in each .php file or in separate files.

console.log('SocraticJS loaded');

// ── Nav logo — back button behaviour ────────────────────────────
// Instead of always going home, the logo navigates back to the
// previous page in the browser history (like the browser back button).
// If there is no history to go back to (e.g. the user landed directly
// on this page), it falls back to the href="/index.php" on the <a> tag.
document.addEventListener('DOMContentLoaded', function () {
  var logoLink = document.querySelector('.nav__logo');
  if (!logoLink) return;

  logoLink.parentElement.addEventListener('click', function (e) {
    // history.length === 1 means this tab was opened fresh with no prior page
    if (history.length > 1) {
      e.preventDefault();   // stop the <a href> from firing
      history.back();       // go to the previous page
    }
    // if history.length is 1, the default href="/index.php" fires normally
  });
});
