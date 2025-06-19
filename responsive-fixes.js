/**
 * Additional Responsive Fixes for Better Mobile Experience
 */

document.addEventListener('DOMContentLoaded', function() {
  // Detect device type
  const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth < 768;
  const isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
  const isDesktop = window.innerWidth >= 1024;
  
  if (isMobile) {
    applyMobileFixes();
  } else if (isTablet) {
    applyTabletFixes();
  } else {
    applyDesktopFixes();
  }
  
  // Handle orientation change
  window.addEventListener('orientationchange', function() {
    setTimeout(function() {
      location.reload();
    }, 500);
  });
  
  // Handle window resize
  let resizeTimer;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
      applyResponsiveFixes();
    }, 250);
  });
  
  function applyResponsiveFixes() {
    const currentWidth = window.innerWidth;
    
    if (currentWidth < 768) {
      applyMobileFixes();
    } else if (currentWidth >= 768 && currentWidth < 1024) {
      applyTabletFixes();
    } else {
      applyDesktopFixes();
    }
  }
  
  function applyMobileFixes() {
    // Fix viewport issues
    const viewportMeta = document.querySelector('meta[name="viewport"]');
    if (viewportMeta) {
      viewportMeta.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover';
    }
    
    // Ensure all images are responsive
    const images = document.querySelectorAll('img');
    images.forEach(img => {
      img.style.maxWidth = '100%';
      img.style.height = 'auto';
    });
    
    // Fix table responsiveness
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
      if (!table.parentElement.classList.contains('table-responsive')) {
        const wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        wrapper.style.overflowX = 'auto';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
      }
    });
    
    // Fix form inputs
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      input.style.fontSize = '16px'; // Prevent zoom on iOS
      input.style.maxWidth = '100%';
      input.style.boxSizing = 'border-box';
    });
    
    // Fix buttons
    const buttons = document.querySelectorAll('button, .btn, .submit-btn');
    buttons.forEach(button => {
      button.style.minHeight = '44px'; // Ensure touch target size
      button.style.padding = '12px 20px';
    });
    
    // Fix navigation
    const nav = document.querySelector('header');
    if (nav) {
      nav.style.position = 'fixed';
      nav.style.top = '0';
      nav.style.left = '0';
      nav.style.width = '100%';
      nav.style.zIndex = '1000';
    }
    
    // Add mobile menu toggle if not exists
    if (!document.querySelector('.mobile-menu-toggle')) {
      const mobileToggle = document.createElement('button');
      mobileToggle.className = 'mobile-menu-toggle';
      mobileToggle.innerHTML = '<span></span><span></span><span></span>';
      mobileToggle.style.display = 'block';
      mobileToggle.style.position = 'fixed';
      mobileToggle.style.top = '10px';
      mobileToggle.style.left = '10px';
      mobileToggle.style.zIndex = '1001';
      mobileToggle.style.background = 'none';
      mobileToggle.style.border = 'none';
      mobileToggle.style.cursor = 'pointer';
      mobileToggle.style.padding = '10px';
      
      document.body.appendChild(mobileToggle);
      
      mobileToggle.addEventListener('click', function() {
        const menu = document.querySelector('.dropdown-menu, .menu-container ul');
        if (menu) {
          menu.classList.toggle('show');
        }
      });
    }
    
    // Fix sticky footer
    const stickyFooter = document.querySelector('.sticky-footer');
    if (stickyFooter) {
      stickyFooter.style.position = 'fixed';
      stickyFooter.style.bottom = '0';
      stickyFooter.style.left = '0';
      stickyFooter.style.width = '100%';
      stickyFooter.style.zIndex = '999';
      stickyFooter.style.padding = '15px 20px';
      stickyFooter.style.background = '#fff';
      stickyFooter.style.boxShadow = '0 -2px 10px rgba(0,0,0,0.1)';
    }
    
    // Add padding to body to account for fixed header and footer
    const header = document.querySelector('header');
    const footer = document.querySelector('.sticky-footer');
    let paddingTop = 0;
    let paddingBottom = 0;
    
    if (header) {
      paddingTop = header.offsetHeight + 20;
    }
    
    if (footer) {
      paddingBottom = footer.offsetHeight + 20;
    }
    
    document.body.style.paddingTop = paddingTop + 'px';
    document.body.style.paddingBottom = paddingBottom + 'px';
  }
  
  function applyTabletFixes() {
    // Tablet-specific fixes
    const containers = document.querySelectorAll('.container');
    containers.forEach(container => {
      container.style.padding = '0 30px';
    });
    
    // Adjust grid layouts for tablet
    const grids = document.querySelectorAll('.stages-grid, .methods-grid, .benefit-cards');
    grids.forEach(grid => {
      if (grid.classList.contains('stages-grid')) {
        grid.style.gridTemplateColumns = 'repeat(2, 1fr)';
      } else if (grid.classList.contains('methods-grid')) {
        grid.style.gridTemplateColumns = 'repeat(2, 1fr)';
      } else if (grid.classList.contains('benefit-cards')) {
        grid.style.flexDirection = 'row';
        grid.style.flexWrap = 'wrap';
        grid.style.gap = '20px';
      }
    });
  }
  
  function applyDesktopFixes() {
    // Desktop-specific fixes
    const containers = document.querySelectorAll('.container');
    containers.forEach(container => {
      container.style.maxWidth = '1200px';
      container.style.margin = '0 auto';
      container.style.padding = '0 40px';
    });
    
    // Show desktop navigation
    const desktopNav = document.querySelector('.menu-container ul');
    if (desktopNav) {
      desktopNav.style.display = 'flex';
    }
    
    // Hide mobile menu toggle
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    if (mobileToggle) {
      mobileToggle.style.display = 'none';
    }
  }
  
  // Fix iOS Safari issues
  if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
    // Fix viewport height issue on iOS
    const setVH = () => {
      const vh = window.innerHeight * 0.01;
      document.documentElement.style.setProperty('--vh', `${vh}px`);
    };
    
    setVH();
    window.addEventListener('resize', setVH);
    
    // Fix input zoom issue
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      input.addEventListener('focus', function() {
        this.style.fontSize = '16px';
      });
    });
  }
  
  // Fix Android Chrome issues
  if (/Android/.test(navigator.userAgent)) {
    // Fix viewport issues on Android
    const viewportMeta = document.querySelector('meta[name="viewport"]');
    if (viewportMeta) {
      viewportMeta.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover';
    }
  }
  
  // Performance optimizations
  if (isMobile) {
    // Reduce animations on mobile
    const animatedElements = document.querySelectorAll('.floating-letters, .floating-math, .animated-title');
    animatedElements.forEach(el => {
      el.style.animation = 'none';
    });
    
    // Lazy load images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          img.classList.remove('lazy');
          observer.unobserve(img);
        }
      });
    });
    
    images.forEach(img => imageObserver.observe(img));
  }
  
  // Accessibility improvements
  // Add focus indicators
  const focusableElements = document.querySelectorAll('a, button, input, select, textarea, [tabindex]');
  focusableElements.forEach(el => {
    el.addEventListener('focus', function() {
      this.style.outline = '2px solid #007bff';
      this.style.outlineOffset = '2px';
    });
    
    el.addEventListener('blur', function() {
      this.style.outline = '';
      this.style.outlineOffset = '';
    });
  });
  
  // Add skip links for accessibility
  if (!document.querySelector('.skip-link')) {
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.textContent = 'Skip to main content';
    skipLink.className = 'skip-link';
    skipLink.style.position = 'absolute';
    skipLink.style.top = '-40px';
    skipLink.style.left = '6px';
    skipLink.style.background = '#000';
    skipLink.style.color = '#fff';
    skipLink.style.padding = '8px';
    skipLink.style.textDecoration = 'none';
    skipLink.style.zIndex = '10000';
    
    skipLink.addEventListener('focus', function() {
      this.style.top = '6px';
    });
    
    skipLink.addEventListener('blur', function() {
      this.style.top = '-40px';
    });
    
    document.body.insertBefore(skipLink, document.body.firstChild);
  }
  
  // Add main content landmark
  const mainContent = document.querySelector('main, .hero-section, .learning-stages');
  if (mainContent && !mainContent.id) {
    mainContent.id = 'main-content';
  }
});

// Export for use in other scripts
window.ResponsiveFixes = {
  isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth < 768,
  isTablet: window.innerWidth >= 768 && window.innerWidth < 1024,
  isDesktop: window.innerWidth >= 1024
}; 