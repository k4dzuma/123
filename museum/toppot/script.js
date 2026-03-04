const thumbnails = document.getElementById('thumbnails');
const mainImage = document.getElementById('mainImage');
const fullScreenCarousel = document.getElementById('fullScreenCarousel');
const fullscreenCarouselModal = new bootstrap.Modal(document.getElementById('fullscreenCarousel')); // Initialize Fullscreen Modal
const carousel = new bootstrap.Carousel(mainImage); // Initialize Bootstrap Carousel

thumbnails.addEventListener('click', (event) => {
    if (event.target.tagName === 'A') {
        const target = event.target;
        const slideIndex = target.dataset.bsSlideTo;
        const imageSrc = target.dataset.imageSrc;

        // Update the main carousel image
        const mainImageItem = mainImage.querySelector('.carousel-item.active');
        mainImageItem.querySelector('img').src = imageSrc;

        // Set the active slide in the main carousel
        carousel.to(slideIndex); 
    }
});

// Fullscreen Carousel Event Listener
mainImage.addEventListener('click', (event) => {
    if (event.target.tagName === 'IMG') {
        // Get the active slide's image
        const activeSlide = mainImage.querySelector('.carousel-item.active');
        const activeImageSrc = activeSlide.querySelector('img').src;

        // Update the full-screen carousel image
        const fullScreenCarouselInner = fullScreenCarousel.querySelector('.carousel-inner');
        const activeFullScreenItem = fullScreenCarouselInner.querySelector('.carousel-item.active');
        activeFullScreenItem.querySelector('img').src = activeImageSrc;

        // Open the modal with full-screen carousel
        fullscreenCarouselModal.show();
    }
});

// Fullscreen Carousel Controls
const fullScreenCarouselInstance = new bootstrap.Carousel(fullScreenCarousel);

// Update the fullscreen carousel when main carousel changes
carousel.on('slid.bs.carousel', function () {
    const activeSlide = mainImage.querySelector('.carousel-item.active');
    const activeImageSrc = activeSlide.querySelector('img').src;
    const activeFullScreenItem = fullScreenCarousel.querySelector('.carousel-item.active');
    activeFullScreenItem.querySelector('img').src = activeImageSrc;
});


