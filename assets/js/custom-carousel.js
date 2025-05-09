/**
 * Custom Swiper Carousel Frontend JavaScript
 *
 * This file handles the initialization of Swiper carousels with the settings
 * specified in the WordPress admin, including touch control by device type and lightbox.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if a device is mobile
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);

    // Variables for touch handling
    let touchStartX = 0;
    let touchEndX = 0;

    // Touch handler functions for lightbox
    function handleTouchStart(evt) {
        touchStartX = evt.touches[0].clientX;
    }

    function handleTouchMove(evt) {
        touchEndX = evt.touches[0].clientX;

        // Calculate direction
        const diffX = touchStartX - touchEndX;

        // Prevent default to stop scrolling during swipe
        if (Math.abs(diffX) > 10) {
            evt.preventDefault();
        }
    }

    function handleTouchEnd() {
        const diffX = touchStartX - touchEndX;

        // Determine if it was a swipe (minimum 50px difference)
        if (Math.abs(diffX) > 50) {
            if (diffX > 0) {
                // Swipe left - go to next
                navigateLightbox('next');
            } else {
                // Swipe right - go to previous
                navigateLightbox('prev');
            }
        }

        // Reset
        touchStartX = 0;
        touchEndX = 0;
    }

    // Find all carousel elements
    const carouselContainers = document.querySelectorAll('.swiper.custom-carousel');

    // Initialize each carousel
    carouselContainers.forEach(function(container) {
        try {
            // Get the carousel ID
            const carouselId = container.id;

            // Convert ID to variable name format (replacing hyphens with underscores)
            const carouselDataVarName = 'carouselData_' + carouselId.replace(/-/g, '_');

            // Get the carousel data from the global variable
            const carouselData = window[carouselDataVarName] || {};

            // Handle touch control settings
            let touchEnabled = carouselData.touchEnabled !== undefined ? carouselData.touchEnabled : true;

            // Override touch settings based on device type and user preferences
            if (isMobile && carouselData.desktopTouchOnly) {
                touchEnabled = false;
            }

            if (!isMobile && carouselData.mobileTouchOnly) {
                touchEnabled = false;
            } else if (isMobile && carouselData.mobileTouchOnly) {
                touchEnabled = true; // Explicitly enable for mobile when mobileTouchOnly is true
            }

            // Default configuration options
            const defaultOptions = {
                // Core settings
                slidesPerView: 3,
                spaceBetween: 30,
                speed: 500,
                effect: 'slide',

                // Touch settings
                touchEventsTarget: 'container',
                touchRatio: 1,
                touchReleaseOnEdges: false,
                shortSwipes: true,
                longSwipes: true,

                // Optional features
                loop: true,
                grabCursor: true,
                preventClicks: true,
                preventClicksPropagation: true,

                // Navigation
                navigation: {
                    nextEl: '#' + carouselId + ' .swiper-button-next',
                    prevEl: '#' + carouselId + ' .swiper-button-prev',
                    hideOnClick: false,
                    disabledClass: 'swiper-button-disabled',
                    hiddenClass: 'swiper-button-hidden'
                },

                // Enable/disable touch based on settings
                touchStartPreventDefault: false,
                simulateTouch: touchEnabled,
                allowTouchMove: touchEnabled,

                // Responsive settings
                breakpoints: {
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 10
                    },
                    640: {
                        slidesPerView: 1,
                        spaceBetween: 20
                    },
                    992: {
                        slidesPerView: 3,
                        spaceBetween: 30
                    }
                },

                // Accessibility
                a11y: {
                    enabled: true,
                    prevSlideMessage: 'Previous slide',
                    nextSlideMessage: 'Next slide',
                    firstSlideMessage: 'This is the first slide',
                    lastSlideMessage: 'This is the last slide',
                    paginationBulletMessage: 'Go to slide {{index}}'
                },

                // Keyboard control
                keyboard: {
                    enabled: true,
                    onlyInViewport: true
                }
            };

            // Merge default options with carousel data
            const swiperOptions = {...defaultOptions, ...carouselData};

            // Make sure navigation is properly configured
            if (swiperOptions.navigation === true) {
                swiperOptions.navigation = {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                    hideOnClick: false,
                    disabledClass: 'swiper-button-disabled',
                    hiddenClass: 'swiper-button-hidden'
                };
            } else if (swiperOptions.navigation) {
                // Make sure navigation elements are properly targeted
                swiperOptions.navigation = {
                    nextEl: '.swiper-carousel-container .swiper-button-next',
                    prevEl: '.swiper-carousel-container .swiper-button-prev',
                    hideOnClick: false,
                    disabledClass: 'swiper-button-disabled',
                    hiddenClass: 'swiper-button-hidden'
                };
            }

            // Configure pagination if enabled
            if (swiperOptions.pagination) {
                swiperOptions.pagination = {
                    el: '#' + carouselId + ' .swiper-pagination',
                    clickable: true,
                    dynamicBullets: true,
                    renderBullet: function(index, className) {
                        return '<span class="' + className + '" aria-label="Go to slide ' + (index + 1) + '"></span>';
                    }
                };
            }

            // Autoplay
            if (swiperOptions.autoplay && swiperOptions.autoplay === true) {
                swiperOptions.autoplay = {
                    delay: swiperOptions.autoplayDelay || 3000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                };
            } else if (swiperOptions.autoplay === false) {
                // Make sure autoplay is completely disabled when set to false
                swiperOptions.autoplay = false;
            }

            // Initialize Swiper
            const swiper = new Swiper('#' + carouselId, swiperOptions);

            // Store the swiper instance for potential later use
            container.swiperInstance = swiper;

            // Add mobile-specific classes if needed
            if (isMobile && carouselData.hideArrowsMobile) {
                container.closest('.swiper-carousel-container').classList.add('mobile-arrows-hidden');
            }

            // Add keyboard listeners for enhanced accessibility
            container.addEventListener('mouseenter', function() {
                swiper.keyboard.enable();
            });

            container.addEventListener('mouseleave', function() {
                if (!container.contains(document.activeElement)) {
                    swiper.keyboard.disable();
                }
            });

            // Initialize lightbox if enabled
            if (swiperOptions.lightbox) {
                initLightbox(container, carouselData);
            }

        } catch (error) {
            console.error('Error initializing carousel:', container.id, error);
        }
    });

/**
 * Initialize lightbox functionality on a carousel container
 * @param {HTMLElement} container - The carousel container element
 * @param {Object} carouselData - The carousel data with settings
 */
function initLightbox(container, carouselData) {
    // Create lightbox elements if they don't exist
    let lightboxOverlay = document.getElementById('carousel-lightbox-overlay');

    if (!lightboxOverlay) {
        // Create and add lightbox elements to the page
        lightboxOverlay = document.createElement('div');
        lightboxOverlay.id = 'carousel-lightbox-overlay';
        lightboxOverlay.className = 'carousel-lightbox-overlay';

        const lightboxContent = document.createElement('div');
        lightboxContent.className = 'carousel-lightbox-content';

        const lightboxImage = document.createElement('img');
        lightboxImage.className = 'carousel-lightbox-image';
        lightboxImage.setAttribute('alt', '');  // Will be updated dynamically

        const lightboxClose = document.createElement('button');
        lightboxClose.className = 'carousel-lightbox-close';
        lightboxClose.innerHTML = '&times;';
        lightboxClose.setAttribute('aria-label', 'Close lightbox');
        lightboxClose.setAttribute('type', 'button');
        lightboxClose.style.touchAction = 'manipulation'; // Improve touch handling

        const lightboxCaption = document.createElement('div');
        lightboxCaption.className = 'carousel-lightbox-caption';

        // Add navigation buttons
        const lightboxPrev = document.createElement('button');
        lightboxPrev.className = 'carousel-lightbox-prev';
        lightboxPrev.innerHTML = '&#10094;';
        lightboxPrev.setAttribute('aria-label', 'Previous image');

        const lightboxNext = document.createElement('button');
        lightboxNext.className = 'carousel-lightbox-next';
        lightboxNext.innerHTML = '&#10095;';
        lightboxNext.setAttribute('aria-label', 'Next image');

        // Add pagination container if enabled
        if (carouselData.lightboxPagination) {
            const lightboxPagination = document.createElement('div');
            lightboxPagination.className = 'carousel-lightbox-pagination';
            lightboxContent.appendChild(lightboxPagination);
        }

        // Assemble the lightbox elements
        lightboxContent.appendChild(lightboxImage);
        lightboxContent.appendChild(lightboxClose);
        lightboxContent.appendChild(lightboxCaption);
        lightboxContent.appendChild(lightboxPrev);
        lightboxContent.appendChild(lightboxNext);
        lightboxOverlay.appendChild(lightboxContent);

        // Add to document
        document.body.appendChild(lightboxOverlay);

        // Add mobile-specific classes if needed
        if (isMobile && carouselData.hideLightboxArrowsMobile) {
            lightboxOverlay.classList.add('mobile-lightbox-arrows-hidden');
        }

        // Close button handlers
        function handleCloseTouch(e) {
            e.preventDefault();
            e.stopPropagation();
            closeLightboxIOS();
        }

        function handleClose(e) {
            e.preventDefault();
            e.stopPropagation();
            closeLightboxIOS();
        }

        function handleOverlayTouch(e) {
            if (e.target === lightboxOverlay) {
                e.preventDefault();
                e.stopPropagation();
                closeLightboxIOS();
            }
        }

        function handleOverlayClick(e) {
            if (e.target === lightboxOverlay) {
                e.preventDefault();
                e.stopPropagation();
                closeLightboxIOS();
            }
        }

        // Close lightbox on overlay click - iOS compatible version
        lightboxOverlay.addEventListener('touchstart', handleOverlayTouch, { passive: false });
        lightboxOverlay.addEventListener('click', handleOverlayClick);

        // Close button click - iOS compatible version
        lightboxClose.addEventListener('touchstart', handleCloseTouch, { passive: false });
        lightboxClose.addEventListener('click', handleClose);

        // Navigation button clicks
        lightboxPrev.addEventListener('click', function(e) {
            e.stopPropagation();
            navigateLightbox('prev');
        });

        lightboxNext.addEventListener('click', function(e) {
            e.stopPropagation();
            navigateLightbox('next');
        });

        // Handle keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (!lightboxOverlay.classList.contains('active')) return;

            if (e.key === 'Escape') {
                closeLightboxIOS();
            } else if (e.key === 'ArrowLeft') {
                navigateLightbox('prev');
            } else if (e.key === 'ArrowRight') {
                navigateLightbox('next');
            }
        });

        // Enable touch swipe for lightbox navigation if enabled
        if (carouselData.lightboxTouch !== false) {
            lightboxOverlay.addEventListener('touchstart', handleTouchStart, false);
            lightboxOverlay.addEventListener('touchmove', handleTouchMove, false);
            lightboxOverlay.addEventListener('touchend', handleTouchEnd, false);
        }

        // Add a simple double-tap to close for iOS
        let lastTapTime = 0;
        lightboxOverlay.addEventListener('touchend', function(e) {
            const currentTime = new Date().getTime();
            const tapLength = currentTime - lastTapTime;

            if (tapLength < 500 && tapLength > 0) {
                // Double tap detected
                closeLightboxIOS();
                e.preventDefault();
            }

            lastTapTime = currentTime;
        });
    }

    // Find all images in the carousel
    const slides = container.querySelectorAll('.swiper-slide');

    // Add click event to each slide
    slides.forEach(function(slide, index) {
        const image = slide.querySelector('img');

        if (image) {
            // Make the slide indicate it's clickable
            slide.classList.add('has-lightbox');

            // Store index for navigation
            slide.setAttribute('data-lightbox-index', index);

            // Add click event
            slide.addEventListener('click', function(e) {
                // Don't open lightbox if clicking on navigation elements
                if (e.target.closest('.swiper-button-next') || e.target.closest('.swiper-button-prev')) {
                    return;
                }

                // Prevent default behavior
                e.preventDefault();
                e.stopPropagation();

                // Get the full size image URL
                // If there's a data-full-size attribute, use that, otherwise use the src
                const fullSizeUrl = image.getAttribute('data-full-size') || image.src;

                // Get alt text for caption and accessibility
                const caption = image.alt || '';

                // Show in lightbox
                openLightbox(fullSizeUrl, caption, container, index);

                // Pause autoplay when lightbox is open
                if (container.swiperInstance && container.swiperInstance.autoplay) {
                    container.swiperInstance.autoplay.stop();
                }
            });
        }
    });
}

/**
 * Open the lightbox with the specified image
 * @param {string} imageUrl - URL of the image to show
 * @param {string} caption - Optional caption for the image
 * @param {HTMLElement} container - The carousel container element
 * @param {number} index - Index of the current slide
 */
function openLightbox(imageUrl, caption, container, index) {
    const lightboxOverlay = document.getElementById('carousel-lightbox-overlay');

    // Don't proceed if we're in the process of closing
    if (lightboxOverlay.getAttribute('data-closing') === 'true') {
        return;
    }

    const lightboxImage = lightboxOverlay.querySelector('.carousel-lightbox-image');
    const lightboxCaption = lightboxOverlay.querySelector('.carousel-lightbox-caption');
    const lightboxPagination = lightboxOverlay.querySelector('.carousel-lightbox-pagination');
    const lightboxContent = lightboxOverlay.querySelector('.carousel-lightbox-content');

    // Store current scroll position
    const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

    // Set image src
    lightboxImage.src = imageUrl;

    // Set image alt text and caption
    lightboxImage.alt = caption;
    lightboxCaption.textContent = caption;

    // Store reference to container and current index
    lightboxOverlay.setAttribute('data-container-id', container.id);
    lightboxOverlay.setAttribute('data-current-index', index);

    // Store the original index when opening the lightbox
    lightboxOverlay.setAttribute('data-original-index', index);

    // Store scroll position
    lightboxOverlay.setAttribute('data-scroll-position', scrollPosition);

    // Update pagination
    if (lightboxPagination) {
        updateLightboxPagination(container, index);
    }

    // Make the image larger and center the lightbox content
    lightboxContent.style.position = 'absolute';
    lightboxContent.style.top = '50%';
    lightboxContent.style.left = '50%';
    lightboxContent.style.transform = 'translate(-50%, -50%)';
    lightboxContent.style.maxHeight = '90vh';
    lightboxContent.style.maxWidth = '90vw';
    lightboxContent.style.width = 'auto';
    lightboxContent.style.height = 'auto';

    // Make the image larger
    lightboxImage.style.maxHeight = '80vh'; // 80% of viewport height
    lightboxImage.style.maxWidth = '80vw';  // 80% of viewport width
    lightboxImage.style.width = 'auto';
    lightboxImage.style.height = 'auto';
    lightboxImage.style.objectFit = 'contain';

    // Show the lightbox
    lightboxOverlay.classList.add('active');
    lightboxOverlay.style.position = 'fixed';
    lightboxOverlay.style.top = '0';
    lightboxOverlay.style.left = '0';
    lightboxOverlay.style.width = '100%';
    lightboxOverlay.style.height = '100%';
    lightboxOverlay.style.display = 'block';

    // Prevent page scrolling while lightbox is open
    document.body.style.overflow = 'hidden';

    // If on mobile, use different approach to prevent scroll issues
    if (isMobile) {
        document.body.style.position = 'fixed';
        document.body.style.top = `-${scrollPosition}px`;
        document.body.style.width = '100%';
    }
}

/**
 * Update the lightbox pagination
 * @param {HTMLElement} container - The carousel container element
 * @param {number} currentIndex - Current slide index
 */
function updateLightboxPagination(container, currentIndex) {
    const lightboxOverlay = document.getElementById('carousel-lightbox-overlay');
    const lightboxPagination = lightboxOverlay.querySelector('.carousel-lightbox-pagination');

    if (!lightboxPagination) return;

    // Get all slides in this container
    const slides = container.querySelectorAll('.swiper-slide');
    const totalSlides = slides.length;

    // Clear pagination
    lightboxPagination.innerHTML = '';

    // Create pagination bullets
    for (let i = 0; i < totalSlides; i++) {
        const bullet = document.createElement('span');
        bullet.className = 'carousel-lightbox-pagination-bullet';
        if (i === currentIndex) {
            bullet.classList.add('active');
        }

        // Add click event to navigate to this slide
        bullet.addEventListener('click', function(e) {
            e.stopPropagation();

            // Don't do anything if this is already the active bullet
            if (i === parseInt(lightboxOverlay.getAttribute('data-current-index'))) {
                return;
            }

            // Get current container ID from lightbox
            const containerId = lightboxOverlay.getAttribute('data-container-id');
            const container = document.getElementById(containerId);
            if (container) {
                // Open lightbox with this slide
                const slide = slides[i];
                const image = slide.querySelector('img');
                if (image) {
                    const fullSizeUrl = image.getAttribute('data-full-size') || image.src;
                    const caption = image.alt || '';

                    // Directly set the index and update the display
                    lightboxOverlay.setAttribute('data-current-index', i);

                    // Use a simplified version of the image loading process
                    const lightboxImage = lightboxOverlay.querySelector('.carousel-lightbox-image');
                    const lightboxCaption = lightboxOverlay.querySelector('.carousel-lightbox-caption');

                    // Fade out
                    lightboxImage.style.opacity = '0';

                    // Update after short delay
                    setTimeout(function() {
                        lightboxImage.src = fullSizeUrl;
                        lightboxImage.alt = caption;
                        lightboxCaption.textContent = caption;

                        // Update pagination again to ensure it's correct
                        updateLightboxPagination(container, i);

                        // Fade in
                        lightboxImage.style.opacity = '1';

                        // Update carousel
                        if (container.swiperInstance) {
                            container.swiperInstance.slideTo(i);
                        }
                    }, 200);
                }
            }
        });

        lightboxPagination.appendChild(bullet);
    }
}

/**
 * Navigate to the next or previous image in the lightbox
 * @param {string} direction - Direction to navigate ('prev' or 'next')
 */
function navigateLightbox(direction) {
    const lightboxOverlay = document.getElementById('carousel-lightbox-overlay');
    const containerId = lightboxOverlay.getAttribute('data-container-id');
    const container = document.getElementById(containerId);

    if (!container) return;

    // Get current index
    let currentIndex = parseInt(lightboxOverlay.getAttribute('data-current-index'));

    // Get all slides in this container
    const slides = container.querySelectorAll('.swiper-slide');
    const totalSlides = slides.length;

    // Calculate new index
    let newIndex;
    if (direction === 'prev') {
        newIndex = (currentIndex - 1 + totalSlides) % totalSlides;
    } else {
        newIndex = (currentIndex + 1) % totalSlides;
    }

    // Get the new slide and image
    const newSlide = slides[newIndex];
    const newImage = newSlide.querySelector('img');

    if (newImage) {
        // Get image URL and caption
        const imageUrl = newImage.getAttribute('data-full-size') || newImage.src;
        const caption = newImage.alt || '';

        // Update lightbox
        const lightboxImage = lightboxOverlay.querySelector('.carousel-lightbox-image');
        const lightboxCaption = lightboxOverlay.querySelector('.carousel-lightbox-caption');
        const lightboxPagination = lightboxOverlay.querySelector('.carousel-lightbox-pagination');

        // Create a new image to preload
        const tempImage = new Image();

        tempImage.onload = function() {
            // Save the current opacity style to restore it later
            const currentOpacity = lightboxImage.style.opacity || '1';

            // Update stored index immediately to keep pagination in sync
            lightboxOverlay.setAttribute('data-current-index', newIndex);

            // Update pagination if enabled
            if (lightboxPagination) {
                updateLightboxPagination(container, newIndex);
            }

            // Fade out current image
            lightboxImage.style.transition = 'opacity 0.2s ease';
            lightboxImage.style.opacity = '0';

            setTimeout(function() {
                // Update the image source and caption
                lightboxImage.src = imageUrl;
                lightboxImage.alt = caption;
                lightboxCaption.textContent = caption;

                // Trigger browser reflow to ensure transition works
                void lightboxImage.offsetWidth;

                // Fade in new image
                lightboxImage.style.opacity = currentOpacity;

                // Move the main carousel to this slide as well
                if (container.swiperInstance) {
                    container.swiperInstance.slideTo(newIndex);
                }
            }, 200); // This timeout should match the transition duration
        };

        // Start loading the new image
        tempImage.src = imageUrl;

        // Handle loading errors
        tempImage.onerror = function() {
            console.error('Error loading image:', imageUrl);
            // Update index and pagination anyway to prevent getting stuck
            lightboxOverlay.setAttribute('data-current-index', newIndex);
            if (lightboxPagination) {
                updateLightboxPagination(container, newIndex);
            }
        };
    }
}

/**
 * Close the lightbox - iOS optimized version
 */
function closeLightboxIOS() {
    console.log('Closing lightbox'); // Debug log

    const lightboxOverlay = document.getElementById('carousel-lightbox-overlay');
    if (!lightboxOverlay) return;

    // Mark as closing to prevent other events from interfering
    lightboxOverlay.setAttribute('data-closing', 'true');

    // Immediately hide the lightbox to prevent further interactions
    lightboxOverlay.style.display = 'none';
    lightboxOverlay.classList.remove('active');

    // Get the relevant data
    const containerId = lightboxOverlay.getAttribute('data-container-id');
    const container = document.getElementById(containerId);
    const originalIndex = parseInt(lightboxOverlay.getAttribute('data-original-index') || '0');
    const scrollPosition = parseInt(lightboxOverlay.getAttribute('data-scroll-position') || '0');

    // Restore body styles and scroll position
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    window.scrollTo(0, scrollPosition);

    // Handle the swiper instance
    if (container && container.swiperInstance) {
        // Get carousel data
        const carouselId = container.id;
        const carouselDataVarName = 'carouselData_' + carouselId.replace(/-/g, '_');
        const carouselData = window[carouselDataVarName] || {};

        // Stop any current transitions
        if (container.swiperInstance.detachEvents) {
            container.swiperInstance.detachEvents();
        }

        // Temporarily disable transitions
        const originalSpeed = container.swiperInstance.params.speed;
        container.swiperInstance.params.speed = 0;

        // Force slide position to original
        setTimeout(function() {
            // Reset to original slide
            container.swiperInstance.slideTo(originalIndex, 0, false);

            // After a delay, restore normal operation
            setTimeout(function() {
                // Restore original transition speed
                container.swiperInstance.params.speed = originalSpeed;

                if (container.swiperInstance.attachEvents) {
                    container.swiperInstance.attachEvents();
                }

                // Only restart autoplay if it was originally enabled
                if (carouselData.autoplay === true && container.swiperInstance.autoplay) {
                    container.swiperInstance.autoplay.start();
                }

                // Show the lightbox overlay again (but still not active)
                lightboxOverlay.style.display = '';

                // Clear the closing state
                lightboxOverlay.removeAttribute('data-closing');
            }, 300);
        }, 50);
    } else {
        // If no swiper instance, just restore the lightbox display after a delay
        setTimeout(function() {
            lightboxOverlay.style.display = '';
            lightboxOverlay.removeAttribute('data-closing');
        }, 300);
    }

    return false;
}

/**
 * Legacy close function (kept for compatibility)
 */
function closeLightbox() {
    return closeLightboxIOS();
}

});