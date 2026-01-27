// public/assets/app.js
// Small UI helpers used across public pages

// Toggle active role UI on login page (keeps existing setRole inlined functional)
function setRoleUI(role) {
  var roleInput = document.getElementById('roleInput');
  if (roleInput) roleInput.value = role;
  document.querySelectorAll('.role-option').forEach(function(el){ el.classList.remove('active'); });
  var active = document.getElementById('tab-' + role);
  if (active) active.classList.add('active');
}

// Simple confirm wrapper (can be used in onClick)
function confirmAction(message) {
  return confirm(message || 'Are you sure?');
}

// Small helper to hide an element
function hide(id) {
  var el = document.getElementById(id);
  if (el) el.style.display = 'none';
}

// Small helper to show an element
function show(id) {
  var el = document.getElementById(id);
  if (el) el.style.display = '';
}