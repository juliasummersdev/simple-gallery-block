/**
 * Simple Gallery Block - GLightbox Initialization and Gallery Expansion
 * Only initializes if GLightbox is available and not already instantiated
 */
(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGallery);
    } else {
        initGallery();
    }

    function initGallery() {
        // Check if GLightbox is available
        if (typeof GLightbox === 'undefined') {
            console.warn('Simple Gallery Block: GLightbox library not found');
            return;
        }

        // Check if GLightbox has already been instantiated for our galleries
        if (window.simpleGalleryGLightbox) {
            return;
        }

        // Initialize GLightbox for our gallery images
        try {
            window.simpleGalleryGLightbox = GLightbox({
                selector: '.pk-image-popup',
                touchNavigation: true,
                loop: true,
                autoplayVideos: false,
                moreLength: 0,
                skin: 'clean',
                closeButton: true,
                touchFollowAxis: true,
                keyboardNavigation: true,
                closeOnOutsideClick: true
            });

            // Force navigation arrows to show on mobile
            // GLightbox hides them by default on touch devices, but we want them visible
            window.simpleGalleryGLightbox.on('open', function() {
                forceShowNavigationArrows();
            });

        } catch (error) {
            console.error('Simple Gallery Block: Error initializing GLightbox', error);
        }

        // Handle gallery expansion when clicking on overlay
        initGalleryExpansion();
    }

    function initGalleryExpansion() {
        // Position sliding overlays after ALL Masonry instances have initialized
        function handleMasonryReady() {
            // Use requestAnimationFrame to ensure DOM is ready for measurements
            requestAnimationFrame(function() {
                positionSlidingOverlays();
            });
        }

        // Listen for custom event from masonry.js indicating all galleries are ready
        window.addEventListener('allMasonryInitialized', handleMasonryReady);

        // Listen for individual gallery lazy image loads
        window.addEventListener('galleryImagesLoaded', function(e) {
            // Re-position overlays for specific gallery after lazy images load
            requestAnimationFrame(function() {
                positionSlidingOverlays();
            });
        });

        // Fallback: Also run after a timeout in case event doesn't fire
        setTimeout(function() {
            positionSlidingOverlays();
        }, 3000);

        // Find all show more buttons
        const showMoreButtons = document.querySelectorAll('.gallery-show-more-button');

        showMoreButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                // Find the sliding overlay
                const slidingOverlay = button.closest('.gallery-sliding-overlay');
                if (!slidingOverlay) return;

                const container = slidingOverlay.closest('.mosaic-gallery-container');
                const gallery = container ? container.querySelector('.mosaic-gallery') : null;

                if (!container || !gallery) return;

                // Get the full gallery height before expanding
                const fullHeight = gallery.scrollHeight;

                // Expand container to full height
                container.style.transition = 'height 0.6s ease';
                container.style.height = fullHeight + 'px';

                // Animate overlay sliding down and fading out
                slidingOverlay.style.transition = 'transform 0.6s ease, opacity 0.6s ease';
                slidingOverlay.style.transform = 'translateY(100%)';
                slidingOverlay.style.opacity = '0';

                // Remove overlay after animation
                setTimeout(function() {
                    slidingOverlay.remove();
                }, 600);

                // Remove has-show-more class and reset height
                setTimeout(function() {
                    container.classList.remove('has-show-more');
                    container.style.height = '';
                }, 650);

                // Refresh GLightbox
                if (window.simpleGalleryGLightbox) {
                    setTimeout(function() {
                        window.simpleGalleryGLightbox.reload();
                    }, 100);
                }
            });
        });
    }

    function positionSlidingOverlays() {
        const overlays = document.querySelectorAll('.gallery-sliding-overlay');

        overlays.forEach(function(overlay) {
            const container = overlay.closest('.mosaic-gallery-container');
            const gallery = container.querySelector('.mosaic-gallery');

            // Get height settings
            const desktopHeight = parseInt(overlay.getAttribute('data-initial-height')) || 600;
            const mobileHeight = parseInt(overlay.getAttribute('data-initial-height-mobile')) || 400;
            const breakpoint = parseInt(overlay.getAttribute('data-mobile-breakpoint')) || 768;

            if (!gallery || !container) return;

            // Check if this is a lazy-loaded gallery
            const isLazyGallery = gallery.getAttribute('data-lazy-gallery') === 'true';

            // Check if lazy gallery has loaded images yet
            if (isLazyGallery) {
                const lazyImages = gallery.querySelectorAll('img[loading="lazy"]');
                let allLoaded = true;

                lazyImages.forEach(function(img) {
                    if (!img.complete || img.naturalHeight === 0) {
                        allLoaded = false;
                    }
                });

                // Don't remove overlay from lazy galleries until images are loaded
                if (!allLoaded) {
                    return;
                }
            }

            // Determine which height to use based on screen width
            const isMobile = window.innerWidth < breakpoint;
            const initialHeight = isMobile ? mobileHeight : desktopHeight;

            // Force a reflow to ensure Masonry layout is complete
            void gallery.offsetHeight;

            // Get the full gallery height
            const fullGalleryHeight = gallery.scrollHeight;

            // Add a minimum threshold to account for measurement errors
            const threshold = 50; // 50px buffer

            // Only show overlay if gallery is taller than initial height plus threshold
            if (fullGalleryHeight <= initialHeight + threshold) {
                // Gallery is short enough, remove overlay and has-show-more class
                overlay.remove();
                container.classList.remove('has-show-more');
                return;
            }

            // Set container height to initial height plus overlay height
            const overlayHeight = overlay.offsetHeight;
            const containerHeight = initialHeight + overlayHeight;
            container.style.height = containerHeight + 'px';
        });
    }

    // Reposition overlays on window resize with debouncing
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            positionSlidingOverlays();
        }, 250);
    });

    /**
     * Force navigation arrows to be visible on mobile
     * GLightbox hides these by default on touch devices
     */
    function forceShowNavigationArrows() {
        // Use MutationObserver to watch for when GLightbox adds hiding classes or styles
        var observer = new MutationObserver(function(mutations) {
            var prevBtn = document.querySelector('.glightbox-container .gprev');
            var nextBtn = document.querySelector('.glightbox-container .gnext');

            if (prevBtn && nextBtn) {
                // Remove the hidden class if GLightbox adds it
                prevBtn.classList.remove('glightbox-button-hidden');
                nextBtn.classList.remove('glightbox-button-hidden');

                // Force display styles
                prevBtn.style.display = 'flex';
                nextBtn.style.display = 'flex';
                prevBtn.style.opacity = '1';
                nextBtn.style.opacity = '1';
                prevBtn.style.visibility = 'visible';
                nextBtn.style.visibility = 'visible';
            }
        });

        // Start observing the document for changes
        var lightboxContainer = document.querySelector('.glightbox-container');
        if (lightboxContainer) {
            observer.observe(lightboxContainer, {
                attributes: true,
                childList: true,
                subtree: true,
                attributeFilter: ['class', 'style']
            });

            // Also immediately force-show the buttons
            var prevBtn = lightboxContainer.querySelector('.gprev');
            var nextBtn = lightboxContainer.querySelector('.gnext');

            if (prevBtn && nextBtn) {
                prevBtn.classList.remove('glightbox-button-hidden');
                nextBtn.classList.remove('glightbox-button-hidden');
                prevBtn.style.display = 'flex';
                nextBtn.style.display = 'flex';
                prevBtn.style.opacity = '1';
                nextBtn.style.opacity = '1';
                prevBtn.style.visibility = 'visible';
                nextBtn.style.visibility = 'visible';
            }
        }
    }
})();
