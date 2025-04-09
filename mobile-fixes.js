/**
 * Mobile-specific optimizations and performance improvements
 */

document.addEventListener('DOMContentLoaded', function() {
  // Detect if user is on mobile
  const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth < 768;
  
  if (isMobile) {
    // Add mobile class to body for additional CSS hooks
    document.body.classList.add('is-mobile');
    
    // Fix viewport issues that can cause horizontal scrolling
    fixViewportIssues();
    
    // Optimize performance by reducing animations
    reduceAnimations();
    
    // Fix form focus issues on iOS
    fixIOSInputs();
    
    // Make any fixed elements work better on mobile
    fixFixedElements();
    
    // Fix any specific mobile navigation issues
    initMobileNavigation();
    
    // Fix double-tap issues on iOS
    fixDoubleTapIssues();
    
    // Adjust sticky footer for mobile
    adjustStickyFooter();
    
    // Ensure proper tap target sizes
    enforceTapTargetSizes();
    
    // Fix any specific issues with the popup
    fixMobilePopup();
  }
  
  // Viewport issues fix
  function fixViewportIssues() {
    // Force update of viewport when page loads and on orientation change
    const viewportMeta = document.querySelector('meta[name="viewport"]');
    if (viewportMeta) {
      viewportMeta.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover';
    }
    
    // Fix any elements that might cause horizontal overflow
    const wideElements = document.querySelectorAll('img, table, pre, video');
    wideElements.forEach(el => {
      el.style.maxWidth = '100%';
    });
    
    // Prevent body from scrolling horizontally
    document.body.style.maxWidth = '100vw';
    document.body.style.overflowX = 'hidden';
  }
  
  // Reduce animations for performance
  function reduceAnimations() {
    // Reduce or turn off non-essential animations
    const animatedElements = document.querySelectorAll('.floating-letters, .floating-math');
    animatedElements.forEach(el => {
      el.style.opacity = '0.3';
    });
    
    // Reduce the number of floating elements
    const floatingItems = document.querySelectorAll('.letter-symbol, .math-symbol');
    for (let i = 0; i < floatingItems.length; i++) {
      if (i % 2 !== 0) { // Remove every second item
        floatingItems[i].style.display = 'none';
      }
    } // Fixed: Removed extra closing bracket here
  }
  
  // Fix form inputs on iOS
  function fixIOSInputs() {
    // Prevent auto-zoom on input focus by ensuring proper font size
    const inputs = document.querySelectorAll('input, select, textarea');
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    
    inputs.forEach(input => {
      // Only set font size if it's smaller than 16px (avoid overriding larger fonts)
      const currentSize = window.getComputedStyle(input).fontSize;
      const sizeInPx = parseFloat(currentSize);
      
      if (isIOS && sizeInPx < 16) {
        input.style.fontSize = '16px';
      }
      
      // Add blur handler to fix position after keyboard closes
      input.addEventListener('blur', function() {
        // Small timeout to let the keyboard close first
        setTimeout(() => {
          // Prevent scrolling issues when keyboard closes
          window.scrollTo(0, window.scrollY);
        }, 300); // Increased timeout for slower devices
      });
    });
    
    // Fix body scrolling when inputs are focused
    if (isIOS) {
      const originalHeight = window.innerHeight;
      
      window.addEventListener('resize', function() {
        if (window.innerHeight < originalHeight) {
          // Keyboard is likely showing
          document.body.classList.add('keyboard-open');
        } else {
          document.body.classList.remove('keyboard-open');
        }
      });
    }
  }
  
  // Fix fixed elements on mobile
  function fixFixedElements() {
    // Handle keyboard showing/hiding which affects fixed elements
    const originalHeight = window.innerHeight;
    
    window.addEventListener('resize', function() {
      // If height changes significantly, keyboard is probably showing
      if (originalHeight - window.innerHeight > 150) {
        document.body.classList.add('keyboard-visible');
        
        // Hide fixed bottom elements when keyboard is showing
        const fixedElements = document.querySelectorAll('.back-to-top, .sticky-footer');
        fixedElements.forEach(el => {
          el.style.opacity = '0';
          el.style.visibility = 'hidden';
        });
      } else {
        document.body.classList.remove('keyboard-visible');
        
        // Show fixed elements again
        const fixedElements = document.querySelectorAll('.back-to-top, .sticky-footer');
        fixedElements.forEach(el => {
          el.style.opacity = '';
          el.style.visibility = '';
        });
      }
    });
    
    // Fix any position: fixed elements with proper safe area insets
    const fixedElements = document.querySelectorAll('.back-to-top, .sticky-footer');
    if ('CSS' in window && CSS.supports('padding-bottom: env(safe-area-inset-bottom)')) {
      fixedElements.forEach(el => {
        // Only add if not already set
        if (!el.style.paddingBottom.includes('env(safe-area-inset-bottom)')) {
          const currentPadding = window.getComputedStyle(el).paddingBottom;
          const paddingValue = parseInt(currentPadding) || 0;
          el.style.paddingBottom = `calc(${paddingValue}px + env(safe-area-inset-bottom))`;
        }
      });
    }
  }
  
  // Mobile navigation improvements
  function initMobileNavigation() {
    // Make dropdowns more touch-friendly
    const dropdownTriggers = document.querySelectorAll('.menu-toggle, .contact-toggle');
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
      let clickedToggle = false;
      dropdownTriggers.forEach(trigger => {
        if (trigger.contains(e.target)) {
          clickedToggle = true;
        }
      });
      
      if (!clickedToggle) {
        dropdownTriggers.forEach(trigger => {
          trigger.setAttribute('data-open', 'false');
          trigger.classList.remove('active');
          
          // Find and hide dropdown
          const dropdown = trigger.nextElementSibling;
          if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
          }
        });
      }
    });
    
    dropdownTriggers.forEach(trigger => {
      // Use touchend instead of touchstart for better behavior
      trigger.addEventListener('touchend', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Get data-open attribute or default to false
        const isOpen = this.getAttribute('data-open') === 'true';
        
        // Close all dropdowns first
        dropdownTriggers.forEach(t => {
          t.setAttribute('data-open', 'false');
          t.classList.remove('active');
          
          // Find and hide dropdown
          const dropdown = t.nextElementSibling;
          if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
          }
        });
        
        // Toggle current dropdown
        if (!isOpen) {
          this.setAttribute('data-open', 'true');
          this.classList.add('active');
          
          // Find and show dropdown
          const dropdown = this.nextElementSibling;
          if (dropdown) {
            dropdown.classList.add('show');
          }
        }
      });
    });
    
    // Ensure back-to-top button works well on mobile
    const backToTop = document.querySelector('.back-to-top');
    if (backToTop) {
      backToTop.style.opacity = '0.8'; // Make more visible
      
      // Improve tap target size
      backToTop.style.width = '50px';
      backToTop.style.height = '50px';
      
      // Fix touch event handling
      backToTop.addEventListener('touchend', function(e) {
        e.preventDefault();
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    }
  }
  
  // Fix iOS double-tap issues on clickable elements
  function fixDoubleTapIssues() {
    // Add explicit touch handling to interactive elements
    const clickableElements = document.querySelectorAll('a, button, .method-card, .stage-card, .alphabet-letter');
    
    clickableElements.forEach(el => {
      // Add CSS to prevent double-tap zoom
      el.style.touchAction = 'manipulation';
      
      el.addEventListener('touchend', function(e) {
        const href = this.getAttribute('href');
        // Only for elements that don't navigate or have special handling
        if (!href || href === '#' || href.startsWith('javascript')) {
          e.preventDefault();
          // Trigger click after touchend
          setTimeout(() => {
            this.click();
          }, 100);
        }
      });
    });
  }
  
  // Adjust sticky footer for mobile
  function adjustStickyFooter() {
    const stickyFooter = document.querySelector('.sticky-footer');
    if (stickyFooter) {
      // Improve safe area handling
      if ('CSS' in window && CSS.supports('padding-bottom: env(safe-area-inset-bottom)')) {
        stickyFooter.style.paddingBottom = 'calc(8px + env(safe-area-inset-bottom))';
        
        // Make sure body has enough padding at bottom
        document.body.style.paddingBottom = `calc(${stickyFooter.offsetHeight}px + env(safe-area-inset-bottom))`;
      } else {
        // Fallback for browsers without safe area support
        document.body.style.paddingBottom = `${stickyFooter.offsetHeight + 8}px`;
      }
      
      // Make sure footer is above other elements
      stickyFooter.style.zIndex = '999';
    }
  }
  
  // Enforce proper tap target sizes
  function enforceTapTargetSizes() {
    // Find all small interactive elements
    const smallTargets = document.querySelectorAll('a, button, input[type="button"], input[type="submit"], .alphabet-letter');
    
    smallTargets.forEach(el => {
      const rect = el.getBoundingClientRect();
      // If tap target is too small (below 44px in either dimension)
      if (rect.width < 44 || rect.height < 44) {
        const computedStyle = window.getComputedStyle(el);
        const currentPadding = {
          top: parseInt(computedStyle.paddingTop) || 0,
          right: parseInt(computedStyle.paddingRight) || 0,
          bottom: parseInt(computedStyle.paddingBottom) || 0,
          left: parseInt(computedStyle.paddingLeft) || 0
        };
        
        // Calculate how much padding we need to add to reach 44px
        let extraPadding = {
          vertical: Math.max(0, (44 - rect.height) / 2),
          horizontal: Math.max(0, (44 - rect.width) / 2)
        };
        
        // Only apply extra padding if needed
        if (extraPadding.vertical > 0 || extraPadding.horizontal > 0) {
          el.style.paddingTop = `${currentPadding.top + extraPadding.vertical}px`;
          el.style.paddingBottom = `${currentPadding.bottom + extraPadding.vertical}px`;
          el.style.paddingLeft = `${currentPadding.left + extraPadding.horizontal}px`;
          el.style.paddingRight = `${currentPadding.right + extraPadding.horizontal}px`;
        }
        
        // For inline elements, make them block or inline-block
        const displayStyle = computedStyle.display;
        if (displayStyle === 'inline') {
          el.style.display = 'inline-block';
        }
      }
    });
  }
  
  // Fix popup issues on mobile
  function fixMobilePopup() {
    // Try different possible popup selectors
    const popupSelectors = ['#popupForm', '.popup-overlay', '.popup-form', '.popup'];
    let popup = null;
    
    for (const selector of popupSelectors) {
      const foundPopup = document.querySelector(selector);
      if (foundPopup) {
        popup = foundPopup;
        break;
      }
    }
    
    if (popup) {
      // Try to find the popup content
      const contentSelectors = ['.popup-content', '.popup-body', '.modal-content'];
      let content = null;
      
      for (const selector of contentSelectors) {
        const foundContent = popup.querySelector(selector);
        if (foundContent) {
          content = foundContent;
          break;
        }
      }
      
      if (content) {
        // Make popup scrollable if needed
        content.style.maxHeight = '85vh';
        content.style.overflow = 'auto';
        content.style.WebkitOverflowScrolling = 'touch'; // Smooth scrolling on iOS
        
        // Simplify layout on very small screens
        if (window.innerWidth < 480) {
          // Try to find popup image and body
          const popupBody = content.querySelector('.popup-body, .modal-body');
          if (popupBody) {
            popupBody.style.flexDirection = 'column';
          }
          
          const popupImage = content.querySelector('.popup-image, .modal-image, img');
          if (popupImage) {
            popupImage.style.width = '100px';
            popupImage.style.marginBottom = '15px';
          }
        }
        
        // Fix popup positioning
        content.style.position = 'relative';
        content.style.margin = '10vh auto';
        content.style.width = '90%';
        content.style.maxWidth = '450px';
      }
    }
  }
});
