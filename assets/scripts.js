// Seat selection is handled in book_ticket.php (toggleSeat) with animations

// Form validation
function showMessage(msg, type = 'error') {
  const el = document.createElement('div');
  el.className = 'msg ' + type;
  el.textContent = msg;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 3000);
}

function validateForm(form) {
  let valid = true;
  form.querySelectorAll('input[required], select[required]').forEach(input => {
    if (!input.value.trim()) {
      valid = false;
      input.style.borderColor = 'red';
    } else {
      input.style.borderColor = '#ccc';
    }
  });
  if (!valid) showMessage('Please fill all required fields.');
  return valid;
}

document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function(e) {
    if (!validateForm(form)) e.preventDefault();
  });
});

// Loading overlay logic
function showLoadingOverlay() {
  if (!document.getElementById('loading-overlay')) {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.id = 'loading-overlay';
    overlay.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(overlay);
  }
}
function hideLoadingOverlay() {
  const overlay = document.getElementById('loading-overlay');
  if (overlay) overlay.remove();
}

window.addEventListener('DOMContentLoaded', function() {
  // For all forms
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
      showLoadingOverlay();
      // Remove artificial delay, submit instantly
      // e.preventDefault();
      // setTimeout(() => form.submit(), 700); // REMOVE THIS
    });
  });

  // For all links
  document.querySelectorAll('a').forEach(link => {
    if (link.target !== '_blank' && link.href && !link.href.startsWith('javascript:')) {
      link.addEventListener('click', function(e) {
        // Only show if not anchor link
        if (link.hostname === window.location.hostname && link.pathname !== window.location.pathname) {
          // Remove artificial delay, navigate instantly
          // e.preventDefault();
          // showLoadingOverlay();
          // setTimeout(() => { window.location.href = link.href; }, 700); // REMOVE THIS
        }
      });
    }
  });

  // Dashboard animation for modern look
  const animatedEls = document.querySelectorAll('[data-animate]');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.animationDelay = (Math.random() * 0.3 + 0.1) + 's';
          entry.target.classList.add('animated-in');
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.2 });
    animatedEls.forEach(el => observer.observe(el));
  } else {
    animatedEls.forEach(el => el.classList.add('animated-in'));
  }
}); 