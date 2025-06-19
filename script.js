/**
 * CONSOLIDATED SCRIPT - All JavaScript functionality in one file
 */

// Initialize form and UI elements when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Check if device is low-end and reduce animations if needed
  const isLowEndDevice = () => {
    const ua = navigator.userAgent;
    return /Android/.test(ua) || 
           /iPhone/.test(ua) || 
           window.innerWidth < 768 ||
           window.navigator.hardwareConcurrency <= 2;
  };
  
  if (isLowEndDevice()) {
    console.log("Low-end device detected. Reducing animations...");
    document.documentElement.classList.add('reduce-animations');
    
    // Disable floating animations on low-end devices
    const floatingElements = document.querySelectorAll('.math-symbol, .letter-symbol');
    floatingElements.forEach(el => {
      el.style.animation = 'none';
      el.style.opacity = '0.3';
      el.style.transform = 'none';
    });
  }
  
  // Fix glitching animations by staggering them
  let delay = 0;
  document.querySelectorAll('.math-symbol, .letter-symbol').forEach((symbol, index) => {
    delay = index * 0.3; // 300ms between each animation start
    symbol.style.animationDelay = delay + 's';
  });

  // Initialize menus
  const menuToggle = document.querySelector('.menu-toggle');
  const dropdownMenu = document.querySelector('.dropdown-menu');
  const menuLabel = document.getElementById('menuLabel');
  if (menuToggle && dropdownMenu && menuLabel) {
    menuToggle.addEventListener('click', function() {
      var isOpen = dropdownMenu.classList.toggle('open');
      document.body.classList.toggle('menu-open', isOpen);
      dropdownMenu.style.display = isOpen ? 'flex' : 'none';
      menuLabel.textContent = isOpen ? 'Tutup' : 'Menu';
    });
    // Optional: close menu when clicking outside
    document.addEventListener('click', function(e) {
      if(isOpen && !dropdownMenu.contains(e.target) && !menuToggle.contains(e.target)) {
        dropdownMenu.classList.remove('open');
        document.body.classList.remove('menu-open');
        dropdownMenu.style.display = 'none';
        menuLabel.textContent = 'Menu';
      }
    });
  }

  // Initialize contact toggle
  const contactToggle = document.querySelector('.contact-toggle');
  const contactDropdown = document.querySelector('.contact-dropdown');
  if (contactToggle && contactDropdown) {
    contactToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      this.classList.toggle('active');
      contactDropdown.classList.toggle('show');
    });
  }

  // Close dropdowns on outside click
  document.addEventListener('click', function() {
    if (menuToggle && dropdownMenu) {
      menuToggle.classList.remove('active');
      dropdownMenu.classList.remove('show');
    }
    if (contactToggle && contactDropdown) {
      contactToggle.classList.remove('active');
      contactDropdown.classList.remove('show');
    }
  });

  // Enhanced smooth scrolling function
  function smoothScrollTo(targetY, duration = 800) {
    const startY = window.pageYOffset;
    const difference = targetY - startY;
    const startTime = performance.now();
    
    function step() {
      const currentTime = performance.now();
      const elapsed = currentTime - startTime;
      
      if (elapsed < duration) {
        // Easing function - easeInOutQuad
        let progress = elapsed / duration;
        progress = progress < 0.5
          ? 2 * progress * progress
          : 1 - Math.pow(-2 * progress + 2, 2) / 2;
          
        window.scrollTo(0, startY + difference * progress);
        requestAnimationFrame(step);
      } else {
        window.scrollTo(0, targetY);
      }
    }
    
    requestAnimationFrame(step);
  }
  
  // Expose smoothScrollTo globally for other scripts
  window.smoothScrollTo = smoothScrollTo;
  
  // Helper function to get element position
  function getOffsetTop(element) {
    let top = 0;
    while (element) {
      top += element.offsetTop;
      element = element.offsetParent;
    }
    return top;
  }

  // Back-to-top button with smooth scrolling
  const backToTop = document.querySelector('.back-to-top');
  if (backToTop) {
    backToTop.addEventListener('click', function(e) {
      e.preventDefault();
      smoothScrollTo(0);
    });
    
    window.addEventListener('scroll', function() {
      if (window.pageYOffset > 300) {
        backToTop.classList.add('show');
      } else {
        backToTop.classList.remove('show');
      }
    }, { passive: true });
  }
  
  // "Daftar Sekarang" button with smooth scrolling
  const heroCta = document.querySelector('.hero-cta');
  if (heroCta) {
    heroCta.addEventListener('click', function(e) {
      e.preventDefault();
      const targetId = this.getAttribute('data-target') || 'daftar';
      const targetElement = document.getElementById(targetId);
      if (targetElement) {
        const targetPosition = getOffsetTop(targetElement);
        smoothScrollTo(targetPosition);
      }
    });
  }

  // Handle all elements with data-smooth="true" attribute
  document.querySelectorAll('[data-smooth="true"]').forEach(element => {
    if (!element.classList.contains('hero-cta')) {
      element.addEventListener('click', function(e) {
        e.preventDefault();
        let targetId;
        
        if (this.hasAttribute('href') && this.getAttribute('href').startsWith('#')) {
          targetId = this.getAttribute('href').substring(1);
        } else if (this.hasAttribute('data-target')) {
          targetId = this.getAttribute('data-target');
        }
        
        if (targetId) {
          const targetElement = document.getElementById(targetId);
          if (targetElement) {
            const targetPosition = getOffsetTop(targetElement);
            smoothScrollTo(targetPosition);
          }
        } else if (this.classList.contains('back-to-top')) {
          smoothScrollTo(0);
        }
      });
    }
  });

  // All anchor links with smooth scrolling
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    // Skip if already handled by data-smooth
    if (anchor.hasAttribute('data-smooth')) return;
    
    anchor.addEventListener('click', function(e) {
      const targetId = this.getAttribute('href').substring(1);
      if (!targetId) return;
      
      const targetElement = document.getElementById(targetId);
      if (targetElement) {
        e.preventDefault();
        const targetPosition = getOffsetTop(targetElement);
        smoothScrollTo(targetPosition);
      }
    });
  });

  // Logo click handler - return to homepage
  const logoContainer = document.querySelector('.logo-container');
  const logoImage = document.querySelector('.logo-container img');
  
  if (logoContainer) {
    logoContainer.style.cursor = 'pointer';
    logoContainer.addEventListener('click', function() {
      window.location.href = 'index.html';
    });
  }
  
  if (logoImage) {
    logoImage.style.cursor = 'pointer';
    logoImage.addEventListener('click', function(e) {
      e.stopPropagation();
      window.location.href = 'index.html';
    });
  }

  // Initialize form submission
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    // Skip forms that have their own handlers
    if (
      form.id === 'jariAljabarForm' ||
      form.id === 'bacaTulisForm' ||
      form.id === 'popupRegistrationForm' ||
      form.id === 'indexRegistrationForm'
    ) {
      if (form.id === 'indexRegistrationForm') {
        console.log('DEBUG: Handler global SKIP indexRegistrationForm');
      }
      return;
    }

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const submitBtn = form.querySelector('.submit-btn');
      if (!submitBtn) return;

      submitBtn.disabled = true;
      submitBtn.textContent = 'Mengirim...';

      const formData = new FormData(form);
      const data = new URLSearchParams(formData);

      fetch('submit_registration.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: data
      })
      .then(response => response.text())
      .then(text => {
        const feedbackDiv = form.querySelector('.form-feedback');
        if (!feedbackDiv) return;

        feedbackDiv.style.display = 'block';

        // Jika response mengandung kata berhasil/success
        if (text.toLowerCase().includes('berhasil') || text.toLowerCase().includes('success')) {
          feedbackDiv.textContent = 'Pendaftaran berhasil! Kami akan segera menghubungi Anda.';
          feedbackDiv.style.backgroundColor = '#d4edda';
          feedbackDiv.style.color = '#155724';
          feedbackDiv.style.border = '1px solid #c3e6cb';
          form.reset();
        } else {
          // Jika response JSON error
          let msg = text;
          try {
            const obj = JSON.parse(text);
            if (obj && obj.message) msg = obj.message;
          } catch {}
          feedbackDiv.textContent = msg || 'Terjadi kesalahan. Silakan coba lagi.';
          feedbackDiv.style.backgroundColor = '#f8d7da';
          feedbackDiv.style.color = '#721c24';
          feedbackDiv.style.border = '1px solid #f5c6cb';
        }
      })
      .catch(() => {
        const feedbackDiv = form.querySelector('.form-feedback');
        if (!feedbackDiv) return;
        feedbackDiv.style.display = 'block';
        feedbackDiv.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
        feedbackDiv.style.backgroundColor = '#f8d7da';
        feedbackDiv.style.color = '#721c24';
        feedbackDiv.style.border = '1px solid #f5c6cb';
      })
      .finally(() => {
        submitBtn.textContent = 'Daftar Sekarang';
        submitBtn.disabled = false;
      });
    });
  });
  
  // Initialize popup if it exists
  const openBtn = document.getElementById('openPopup');
  const closeBtn = document.getElementById('closePopup');
  const popup = document.getElementById('popupForm');
  
  if (openBtn && popup) {
    openBtn.addEventListener('click', function() {
      popup.style.display = 'block';
    });
  }
  
  if (closeBtn && popup) {
    closeBtn.addEventListener('click', function() {
      popup.style.display = 'none';
    });
  }
  
  // Initialize any special handling for the Baca Tulis page
  if (document.body.classList.contains('baca-tulis-page')) {
    initBacaTulis();
  }
  
  // Initialize interaction for letters in Baca Tulis
  function initBacaTulis() {
    const alphabetLetters = document.querySelectorAll('.alphabet-letter');
    
    if (alphabetLetters.length > 0) {
      alphabetLetters.forEach(letter => {
        letter.addEventListener('click', function() {
          // Create a fun pop-up effect
          this.classList.add('letter-animate');
          setTimeout(() => {
            this.classList.remove('letter-animate');
          }, 500);
        });
      });
    }
  }

  // Initialize the jadwal dropdowns if they exist
  const programSelect = document.getElementById('programSelect');
  const popupProgramSelect = document.getElementById('popupProgramSelect');
  
  if (programSelect) {
    programSelect.addEventListener('change', updateJadwal);
    // Initialize on page load if there's a default value
    if (programSelect.value) {
      updateJadwal();
    }
  }
  
  if (popupProgramSelect) {
    popupProgramSelect.addEventListener('change', updatePopupJadwal);
    // Initialize on page load if there's a default value
    if (popupProgramSelect.value) {
      updatePopupJadwal();
    }
  }
  
  // Fix method-card images
  // Find all method-card images
  const methodImages = document.querySelectorAll('.method-card .method-image img');
  
  // Process each image
  methodImages.forEach(function(img) {
    // Check if image is already loaded but failed
    if (img.complete) {
      if (img.naturalHeight === 0) {
        // Apply fallback
        applyFallbackImage(img);
      }
    }
    
    // Add load and error event handlers
    img.addEventListener('load', function() {
      img.classList.add('loaded');
    });
    
    img.addEventListener('error', function() {
      applyFallbackImage(this);
    });
  });
  
  // Function to apply fallback image
  function applyFallbackImage(img) {
    // Get the alt text
    const altText = img.getAttribute('alt') || 'Method Image';
    const encodedAltText = encodeURIComponent(altText);
    
    // Try a placeholder service
    img.src = `https://via.placeholder.com/800x600?text=${encodedAltText}`;
    img.setAttribute('data-fallback', 'true');
    
    // Add fallback class
    img.classList.add('fallback-image');
  }

  // Simple fix for method card images
  const methodImagesSimple = document.querySelectorAll('.simple-method-image img');
  methodImagesSimple.forEach(img => {
    // Add error handler to each image
    img.onerror = function() {
      // Set a placeholder if the image fails to load
      this.src = 'https://via.placeholder.com/800x500?text=' + encodeURIComponent(this.alt || 'Method Image');
      this.alt = this.alt || 'Method Image';
      
      // Add class to apply special styling if needed
      this.classList.add('image-fallback');
      
      // Log the error for debugging
      console.log('Image failed to load: ' + this.src);
    };
    
    // If image is already in error state when script runs
    if (img.complete && img.naturalHeight === 0) {
      img.onerror();
    }
  });
});

// Function to update jadwal options based on selected program
function updateJadwal() {
  const programSelect = document.getElementById('programSelect');
  const jadwalSelect = document.getElementById('jadwalSelect');
  
  if (!programSelect || !jadwalSelect) return;
  
  // Clear existing options
  jadwalSelect.innerHTML = '<option value="">Pilih Jadwal Les</option>';
  
  if (programSelect.value === 'baca-tulis') {
    // Add schedules for Baca Tulis
    addOption(jadwalSelect, 'senin-rabu', 'Senin & Rabu (15:00 - 16:30)');
    addOption(jadwalSelect, 'selasa-kamis', 'Selasa & Kamis (15:00 - 16:30)');
    addOption(jadwalSelect, 'jumat-sabtu', 'Jumat & Sabtu (10:00 - 11:30)');
  } else if (programSelect.value === 'jari-aljabar') {
    // Add schedules for Jari Aljabar
    addOption(jadwalSelect, 'senin-rabu', 'Senin & Rabu (16:30 - 18:00)');
    addOption(jadwalSelect, 'selasa-kamis', 'Selasa & Kamis (16:30 - 18:00)');
    addOption(jadwalSelect, 'jumat-sabtu', 'Jumat & Sabtu (13:00 - 14:30)');
  }
}

// Function to update popup jadwal options based on selected program
function updatePopupJadwal() {
  const programSelect = document.getElementById('popupProgramSelect');
  const jadwalSelect = document.getElementById('popupJadwalSelect');
  
  if (!programSelect || !jadwalSelect) return;
  
  // Clear existing options
  jadwalSelect.innerHTML = '<option value="">Pilih Jadwal Les</option>';
  
  if (programSelect.value === 'baca-tulis') {
    // Add schedules for Baca Tulis
    addOption(jadwalSelect, 'senin-rabu', 'Senin & Rabu (15:00 - 16:30)');
    addOption(jadwalSelect, 'selasa-kamis', 'Selasa & Kamis (15:00 - 16:30)');
    addOption(jadwalSelect, 'jumat-sabtu', 'Jumat & Sabtu (10:00 - 11:30)');
  } else if (programSelect.value === 'jari-aljabar') {
    // Add schedules for Jari Aljabar
    addOption(jadwalSelect, 'senin-rabu', 'Senin & Rabu (16:30 - 18:00)');
    addOption(jadwalSelect, 'selasa-kamis', 'Selasa & Kamis (16:30 - 18:00)');
    addOption(jadwalSelect, 'jumat-sabtu', 'Jumat & Sabtu (13:00 - 14:30)');
  }
  
  console.log('Popup jadwal updated:', programSelect.value);
}

// Helper function to add options to select element
function addOption(selectElement, value, text) {
  const option = document.createElement('option');
  option.value = value;
  option.textContent = text;
  selectElement.appendChild(option);
}

// Helper function to show form submission messages
function showMessage(form, type, message) {
  const overlay = document.createElement('div');
  overlay.className = `${type}-overlay`;
  
  const content = `
    <div class="${type}-content">
      <div class="${type}-icon">
        <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
      </div>
      <h3>${type === 'success' ? 'Pendaftaran Berhasil!' : 'Gagal Mengirim'}</h3>
      <p>${message || (type === 'success' ? 
        'Terima kasih telah mendaftar. Kami akan segera menghubungi Anda.' : 
        'Maaf, terjadi kesalahan. Silakan coba lagi nanti.')}</p>
    </div>
  `;
  
  overlay.innerHTML = content;
  form.appendChild(overlay);
  
  setTimeout(function() {
    overlay.classList.add('fade-out');
    setTimeout(function() {
      overlay.remove();
    }, 500);
  }, 2000);
}