// =====================================================
// RESPONSIVE FUNCTIONALITY
// =====================================================
// This file adds responsive functionality without modifying existing code

document.addEventListener('DOMContentLoaded', function() {
    
    // =====================================================
    // MOBILE MENU FUNCTIONALITY
    // =====================================================
    
    // Create mobile menu toggle button
    function createMobileMenuToggle() {
        const toggle = document.createElement('button');
        toggle.className = 'mobile-menu-toggle';
        toggle.innerHTML = '<i class="fas fa-bars"></i>';
        toggle.setAttribute('aria-label', 'Toggle navigation menu');
        toggle.setAttribute('aria-expanded', 'false');
        
        // Add click event
        toggle.addEventListener('click', toggleMobileMenu);
        
        return toggle;
    }
    
    // Toggle mobile menu
    function toggleMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.querySelector('.mobile-menu-toggle');
        
        if (!sidebar || !toggle) return;
        
        const isOpen = sidebar.classList.contains('mobile-open') || sidebar.classList.contains('tablet-open');
        
        if (isOpen) {
            // Close menu
            sidebar.classList.remove('mobile-open', 'tablet-open');
            toggle.innerHTML = '<i class="fas fa-bars"></i>';
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        } else {
            // Open menu
            sidebar.classList.add('mobile-open');
            toggle.innerHTML = '<i class="fas fa-times"></i>';
            toggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden'; // Prevent background scroll
        }
    }
    
    // Close mobile menu when clicking outside
    function closeMobileMenuOnOutsideClick(event) {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.querySelector('.mobile-menu-toggle');
        
        if (!sidebar || !toggle) return;
        
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnToggle = toggle.contains(event.target);
        
        if (!isClickInsideSidebar && !isClickOnToggle) {
            const isOpen = sidebar.classList.contains('mobile-open') || sidebar.classList.contains('tablet-open');
            if (isOpen) {
                toggleMobileMenu();
            }
        }
    }
    
    // Handle escape key to close menu
    function handleEscapeKey(event) {
        if (event.key === 'Escape') {
            const sidebar = document.querySelector('.sidebar');
            const isOpen = sidebar && (sidebar.classList.contains('mobile-open') || sidebar.classList.contains('tablet-open'));
            if (isOpen) {
                toggleMobileMenu();
            }
        }
    }
    
    // =====================================================
    // RESPONSIVE TABLE HANDLING
    // =====================================================
    
    // Make tables responsive
    function makeTablesResponsive() {
        const tables = document.querySelectorAll('table');
        
        tables.forEach(table => {
            // Check if table needs responsive wrapper
            if (!table.parentElement.classList.contains('table-responsive')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                wrapper.style.overflowX = 'auto';
                wrapper.style.webkitOverflowScrolling = 'touch';
                
                // Wrap the table
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
    }
    
    // =====================================================
    // RESPONSIVE NAVIGATION
    // =====================================================
    
    // Handle navigation item clicks on mobile
    function handleMobileNavigation() {
        const navItems = document.querySelectorAll('.nav-item');
        
        navItems.forEach(item => {
            item.addEventListener('click', function(e) {
                const isMobile = window.innerWidth <= 768;
                
                if (isMobile) {
                    // Close mobile menu after navigation
                    setTimeout(() => {
                        const sidebar = document.querySelector('.sidebar');
                        if (sidebar && sidebar.classList.contains('mobile-open')) {
                            toggleMobileMenu();
                        }
                    }, 300);
                }
            });
        });
    }
    
    // =====================================================
    // RESPONSIVE FONT SCALING
    // =====================================================
    
    // Adjust font sizes based on screen width
    function adjustFontSizes() {
        const width = window.innerWidth;
        const root = document.documentElement;
        
        if (width <= 480) {
            root.style.fontSize = '14px';
        } else if (width <= 768) {
            root.style.fontSize = '15px';
        } else if (width <= 1024) {
            root.style.fontSize = '15px';
        } else {
            root.style.fontSize = '16px';
        }
    }
    
    // =====================================================
    // ORIENTATION CHANGE HANDLING
    // =====================================================
    
    // Handle orientation changes
    function handleOrientationChange() {
        const isLandscape = window.innerWidth > window.innerHeight;
        const isMobile = window.innerWidth <= 768;
        
        if (isMobile && isLandscape) {
            // Add landscape class for special styling
            document.body.classList.add('mobile-landscape');
        } else {
            document.body.classList.remove('mobile-landscape');
        }
        
        // Re-adjust layouts after orientation change
        setTimeout(() => {
            adjustLayouts();
        }, 100);
    }
    
    // =====================================================
    // LAYOUT ADJUSTMENTS
    // =====================================================
    
    // Adjust layouts based on screen size
    function adjustLayouts() {
        const width = window.innerWidth;
        
        // Adjust grid layouts
        adjustGrids(width);
        
        // Adjust card layouts
        adjustCards(width);
        
        // Adjust action buttons
        adjustActionButtons(width);
    }
    
    // Adjust grid systems
    function adjustGrids(width) {
        const statsGrids = document.querySelectorAll('.stats-grid');
        const actionGrids = document.querySelectorAll('.actions-grid');
        const grid4s = document.querySelectorAll('.grid-4');
        const grid3s = document.querySelectorAll('.grid-3');
        const grid2s = document.querySelectorAll('.grid-2');
        
        // These are handled by CSS media queries, but we can add JavaScript enhancements
        if (width <= 480) {
            // Mobile grid enhancements
            statsGrids.forEach(grid => {
                grid.style.gap = '12px';
            });
            
            actionGrids.forEach(grid => {
                grid.style.gap = '12px';
            });
        }
    }
    
    // Adjust card layouts
    function adjustCards(width) {
        const cards = document.querySelectorAll('.card');
        
        cards.forEach(card => {
            if (width <= 480) {
                card.style.marginBottom = '12px';
            } else if (width <= 768) {
                card.style.marginBottom = '16px';
            }
        });
    }
    
    // Adjust action buttons
    function adjustActionButtons(width) {
        const actionButtons = document.querySelectorAll('.action-btn');
        
        actionButtons.forEach(btn => {
            if (width <= 480) {
                btn.style.padding = '16px';
                btn.style.fontSize = '0.85rem';
            } else if (width <= 768) {
                btn.style.padding = '18px';
                btn.style.fontSize = '0.9rem';
            }
        });
    }
    
    // =====================================================
    // ACCESSIBILITY ENHANCEMENTS
    // =====================================================
    
    // Add ARIA labels and improve accessibility
    function enhanceAccessibility() {
        // Add proper ARIA labels to navigation
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach((item, index) => {
            if (!item.getAttribute('aria-label')) {
                const text = item.textContent.trim();
                item.setAttribute('aria-label', `Navigation item ${index + 1}: ${text}`);
            }
        });
        
        // Add skip to content link
        if (!document.querySelector('.skip-to-content')) {
            const skipLink = document.createElement('a');
            skipLink.href = '#main-content';
            skipLink.className = 'skip-to-content';
            skipLink.textContent = 'Skip to main content';
            skipLink.style.cssText = `
                position: absolute;
                top: -40px;
                left: 6px;
                background: var(--bg-700);
                color: var(--text-100);
                padding: 8px;
                text-decoration: none;
                border-radius: 4px;
                z-index: 10000;
            `;
            
            skipLink.addEventListener('focus', function() {
                this.style.top = '6px';
            });
            
            skipLink.addEventListener('blur', function() {
                this.style.top = '-40px';
            });
            
            document.body.insertBefore(skipLink, document.body.firstChild);
        }
        
        // Add main content id if not present
        const main = document.querySelector('main');
        if (main && !main.id) {
            main.id = 'main-content';
        }
    }
    
    // =====================================================
    // PERFORMANCE OPTIMIZATIONS
    // =====================================================
    
    // Debounce function for performance
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Throttle function for performance
    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
    
    // Optimized resize handler
    const optimizedResize = debounce(function() {
        adjustFontSizes();
        adjustLayouts();
        handleOrientationChange();
    }, 250);
    
    // Optimized scroll handler
    const optimizedScroll = throttle(function() {
        // Add scroll-based animations if needed
        handleScrollEffects();
    }, 16);
    
    // Handle scroll effects
    function handleScrollEffects() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const header = document.querySelector('.header');
        
        if (header) {
            if (scrollTop > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }
    }
    
    // =====================================================
    // INITIALIZATION
    // =====================================================
    
    // Initialize responsive functionality
    function initResponsive() {
        // Add mobile menu toggle
        const header = document.querySelector('.header');
        const sidebar = document.querySelector('.sidebar');
        
        if (header && sidebar && !document.querySelector('.mobile-menu-toggle')) {
            const toggle = createMobileMenuToggle();
            header.appendChild(toggle);
        }
        
        // Make tables responsive
        makeTablesResponsive();
        
        // Handle mobile navigation
        handleMobileNavigation();
        
        // Adjust initial layouts
        adjustFontSizes();
        adjustLayouts();
        handleOrientationChange();
        
        // Enhance accessibility
        enhanceAccessibility();
        
        // Add event listeners
        window.addEventListener('resize', optimizedResize);
        window.addEventListener('orientationchange', handleOrientationChange);
        window.addEventListener('scroll', optimizedScroll);
        document.addEventListener('click', closeMobileMenuOnOutsideClick);
        document.addEventListener('keydown', handleEscapeKey);
        
        console.log('Responsive functionality initialized');
    }
    
    // Initialize when DOM is ready
    initResponsive();
    
    // =====================================================
    // UTILITY FUNCTIONS
    // =====================================================
    
    // Check if device is mobile
    window.isMobile = function() {
        return window.innerWidth <= 768;
    };
    
    // Check if device is tablet
    window.isTablet = function() {
        return window.innerWidth > 768 && window.innerWidth <= 1024;
    };
    
    // Check if device is desktop
    window.isDesktop = function() {
        return window.innerWidth > 1024;
    };
    
    // Get current breakpoint
    window.getCurrentBreakpoint = function() {
        const width = window.innerWidth;
        if (width <= 480) return 'mobile';
        if (width <= 768) return 'tablet';
        if (width <= 1024) return 'laptop';
        return 'desktop';
    };
    
    // Re-initialize on dynamic content changes
    window.reinitResponsive = function() {
        setTimeout(() => {
            makeTablesResponsive();
            adjustLayouts();
            enhanceAccessibility();
        }, 100);
    };
    
});
