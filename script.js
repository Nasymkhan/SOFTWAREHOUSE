// ============================================
// Z9 INTERNATIONAL SOFTWARE HOUSE - JAVASCRIPT
// ============================================

// Console log for debugging
console.log('Z9 International Software House - Initialized');

// ============================================
// THEME TOGGLE - Dark/Light Mode
// ============================================

const themeToggle = document.getElementById('theme-toggle');
const html = document.documentElement;

// Check for saved theme preference or default to light mode
const currentTheme = localStorage.getItem('theme') || 'light';
if (currentTheme === 'dark') {
  document.body.classList.add('dark-mode');
  themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
}

themeToggle.addEventListener('click', () => {
  console.log('Theme toggled');
  document.body.classList.toggle('dark-mode');
  
  if (document.body.classList.contains('dark-mode')) {
    localStorage.setItem('theme', 'dark');
    themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
  } else {
    localStorage.setItem('theme', 'light');
    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
  }
});

// ============================================
// MOBILE NAVIGATION - Hamburger Menu
// ============================================

const hamburger = document.querySelector('.hamburger');
const navLinks = document.querySelector('.nav-links');

if (hamburger) {
  hamburger.addEventListener('click', () => {
    console.log('Hamburger menu clicked');
    navLinks.classList.toggle('active');
  });

  // Close menu when a link is clicked
  document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
      navLinks.classList.remove('active');
    });
  });
}

// ============================================
// JOB APPLICATION FORM - Toggle Functionality
// ============================================

const applyBtn = document.getElementById('apply-btn');
const jobForm = document.getElementById('job-form');
const cancelBtn = document.getElementById('cancel-btn');

if (applyBtn) {
  applyBtn.addEventListener('click', () => {
    console.log('Apply button clicked');
    jobForm.classList.remove('hidden');
    applyBtn.style.display = 'none';
    jobForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
}

if (cancelBtn) {
  cancelBtn.addEventListener('click', () => {
    console.log('Cancel button clicked');
    jobForm.classList.add('hidden');
    applyBtn.style.display = 'block';
  });
}

// ============================================
// JOB APPLICATION FORM - Submit Handler
// ============================================

if (jobForm) {
  jobForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    console.log('Job form submitted');

    // Collect form data
    const formData = new FormData(jobForm);
    const data = Object.fromEntries(formData);
    
    console.log('Form data:', data);

    // Validate CNIC format (optional - basic validation)
    if (!data.cnic.match(/^\d{5}-\d{7}-\d{1}$/)) {
      console.error('Invalid CNIC format');
      showErrorModal('Invalid CNIC Format', 'Please use format: XXXXX-XXXXXXX-X');
      return;
    }

    try {
      // Send data to server
      const response = await fetch('submit_job.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
      });

      const result = await response.json();
      console.log('Server response:', result);

      if (result.success) {
        console.log('Job application submitted successfully');
        showSuccessModal('Application Submitted!', 'Thank you for applying. We will review your application and contact you soon.');
        jobForm.reset();
        jobForm.classList.add('hidden');
        applyBtn.style.display = 'block';
      } else {
        console.error('Server error:', result.message);
        showErrorModal('Submission Error', result.message || 'Failed to submit application. Please try again.');
      }
    } catch (error) {
      console.error('Error submitting job application:', error);
      showErrorModal('Error', 'An error occurred while submitting your application. Please try again later.');
    }
  });
}

// ============================================
// CONTACT FORM - Submit Handler
// ============================================

const contactForm = document.getElementById('contact-form');

if (contactForm) {
  contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    console.log('Contact form submitted');

    // Collect form data
    const formData = new FormData(contactForm);
    const data = Object.fromEntries(formData);
    
    console.log('Contact form data:', data);

    try {
      // Send data to server
      const response = await fetch('submit_contact.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
      });

      const result = await response.json();
      console.log('Server response:', result);

      if (result.success) {
        console.log('Contact message submitted successfully');
        showSuccessModal('Message Sent!', 'Thank you for your message. We will get back to you as soon as possible.');
        contactForm.reset();
      } else {
        console.error('Server error:', result.message);
        showErrorModal('Submission Error', result.message || 'Failed to send message. Please try again.');
      }
    } catch (error) {
      console.error('Error submitting contact form:', error);
      showErrorModal('Error', 'An error occurred while sending your message. Please try again later.');
    }
  });
}

// ============================================
// MODAL FUNCTIONS
// ============================================

function showSuccessModal(title, message) {
  const modal = document.getElementById('success-modal');
  document.getElementById('modal-title').textContent = title;
  document.getElementById('modal-message').textContent = message;
  modal.classList.remove('hidden');
  console.log('Success modal shown:', title);
}

function showErrorModal(title, message) {
  const modal = document.getElementById('error-modal');
  document.getElementById('error-title').textContent = title;
  document.getElementById('error-message').textContent = message;
  modal.classList.remove('hidden');
  console.log('Error modal shown:', title);
}

function closeModal() {
  document.getElementById('success-modal').classList.add('hidden');
  document.getElementById('error-modal').classList.add('hidden');
  console.log('Modal closed');
}

// Close modal when clicking outside
document.addEventListener('click', (e) => {
  const successModal = document.getElementById('success-modal');
  const errorModal = document.getElementById('error-modal');
  
  if (e.target === successModal) {
    closeModal();
  }
  if (e.target === errorModal) {
    closeModal();
  }
});

// ============================================
// SCROLL ANIMATIONS - Fade in elements on scroll
// ============================================

const observerOptions = {
  threshold: 0.1,
  rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
      observer.unobserve(entry.target);
    }
  });
}, observerOptions);

// Observe all service cards and other elements
document.addEventListener('DOMContentLoaded', () => {
  const cards = document.querySelectorAll('.service-card, .position-card, .value-item');
  cards.forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
  });
  console.log('Scroll animations initialized');
});

// ============================================
// SMOOTH SCROLL OFFSET FOR FIXED NAV
// ============================================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const href = this.getAttribute('href');
    if (href !== '#') {
      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        const offset = 80; // Height of fixed nav
        const targetPosition = target.offsetTop - offset;
        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });
        console.log('Scrolled to section:', href);
      }
    }
  });
});

// ============================================
// FORM VALIDATION - Real-time validation
// ============================================

// Email validation
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

// Phone validation (basic)
function validatePhone(phone) {
  const re = /^[\d\s\-\+\(\)]+$/;
  return re.test(phone) && phone.replace(/\D/g, '').length >= 10;
}

// Add real-time validation to contact form
const contactNameInput = document.getElementById('contact-name');
const contactEmailInput = document.getElementById('contact-email');
const contactPhoneInput = document.getElementById('contact-phone');

if (contactEmailInput) {
  contactEmailInput.addEventListener('blur', function() {
    if (this.value && !validateEmail(this.value)) {
      console.warn('Invalid email format');
      this.style.borderColor = '#ff6b6b';
    } else {
      this.style.borderColor = '';
    }
  });
}

if (contactPhoneInput) {
  contactPhoneInput.addEventListener('blur', function() {
    if (this.value && !validatePhone(this.value)) {
      console.warn('Invalid phone format');
      this.style.borderColor = '#ff6b6b';
    } else {
      this.style.borderColor = '';
    }
  });
}

// ============================================
// PAGE LOAD ANIMATION
// ============================================

window.addEventListener('load', () => {
  console.log('Page fully loaded');
  document.body.style.opacity = '1';
});

// Initial page load setup
console.log('JavaScript loaded and ready');
// ============================================
// AUTHENTICATION - Check if user is logged in
// ============================================

function initializeAuth() {
  const token = localStorage.getItem('session_token');
  const userData = localStorage.getItem('user_data');
  
  const authLinks = document.getElementById('authLinks');
  const userLinks = document.getElementById('userLinks');
  
  if (token && userData) {
    // User is logged in
    const user = JSON.parse(userData);
    document.getElementById('navUsername').textContent = user.full_name || user.username;
    
    if (user.profile_pic_url) {
      document.getElementById('navUserPic').src = user.profile_pic_url;
    }
    
    // Show user menu, hide auth links
    if (authLinks) authLinks.style.display = 'none';
    if (userLinks) userLinks.style.display = 'flex';
  } else {
    // User is not logged in
    if (authLinks) authLinks.style.display = 'flex';
    if (userLinks) userLinks.style.display = 'none';
  }
}

// Initialize auth on page load
document.addEventListener('DOMContentLoaded', initializeAuth);

// Logout function
function logout() {
  const token = localStorage.getItem('session_token');
  
  if (token) {
    // Notify server of logout
    fetch('profile.php', {
      method: 'DELETE',
      headers: {
        'Authorization': 'Bearer ' + token
      }
    }).catch(err => console.log(err));
  }
  
  // Clear storage
  localStorage.removeItem('session_token');
  localStorage.removeItem('remember_token');
  localStorage.removeItem('user_data');
  
  // Reinitialize auth UI
  initializeAuth();
  
  // Show message
  alert('Logged out successfully!');
  
  // Scroll to top
  window.scrollTo(0, 0);
}