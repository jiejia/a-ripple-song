{{--
Banner Carousel Widget Template
@param array $slides - Array of slide data
@param string $carousel_id - Unique carousel ID
--}}

@if(empty($slides))
  {{-- Empty state placeholder --}}
  <div class="w-full rounded-lg bg-base-100 p-4 pb-2">
    <div class="w-full h-48 rounded-lg bg-base-200 flex items-center justify-center">
      <div class="text-center text-base-content/50">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="text-sm font-medium">{{ __('No banner yet', 'sage') }}</p>
        <p class="text-xs mt-1">{{ __('Please add banner content in the admin panel', 'sage') }}</p>
      </div>
    </div>
  </div>
@else
  <div class="w-full rounded-lg bg-base-100 p-4 pb-2">
    <div class="relative">
      {{-- Carousel slides --}}
      <div id="{{ $carousel_id }}" class="carousel w-full rounded-lg snap-x snap-mandatory overflow-x-auto scroll-smooth">
        @foreach($slides as $index => $slide)
          @php
            $slide_id = $carousel_id . '-slide-' . $index;
            $image_url = $slide['image'] ?? '';
            $link_url = $slide['link'] ?? '';
            $description = $slide['description'] ?? '';
            $link_target = $slide['link_target'] ?? '_self';
          @endphp
          <div id="{{ $slide_id }}" class="carousel-item relative w-full rounded-lg snap-center">
            @if($link_url)
              <a href="{{ esc_url($link_url) }}" target="{{ esc_attr($link_target) }}" class="w-full">
                <img src="{{ esc_url($image_url) }}" class="w-full h-48 object-cover rounded-lg"
                  alt="{{ esc_attr($description) }}" />
              </a>
            @else
              <img src="{{ esc_url($image_url) }}" class="w-full h-48 object-cover rounded-lg"
                alt="{{ esc_attr($description) }}" />
            @endif
          </div>
        @endforeach
      </div>

      {{-- Dot indicators --}}
      @if(count($slides) > 1)
        <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-2 z-10">
          @foreach($slides as $index => $slide)
            <button type="button"
              class="banner-dot w-2.5 h-2.5 rounded-full transition-all duration-300 shadow-sm {{ $index === 0 ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/80' }}"
              data-carousel="{{ $carousel_id }}" data-index="{{ $index }}"
              aria-label="{{ sprintf(__('Go to slide %d', 'sage'), $index + 1) }}">
            </button>
          @endforeach
        </div>

        {{-- Autoplay script --}}
        <script>
          (function () {
            const carouselId = '{{ $carousel_id }}';
            const carousel = document.getElementById(carouselId);
            if (!carousel) return;

            const dots = document.querySelectorAll(`[data-carousel="${carouselId}"]`);
            const slides = carousel.querySelectorAll('.carousel-item');
            const totalSlides = slides.length;
            let currentIndex = 0;
            let autoplayTimer = null;

            // Use IntersectionObserver for performant slide detection
            const observer = new IntersectionObserver((entries) => {
              entries.forEach(entry => {
                if (entry.isIntersecting) {
                  const index = Array.from(slides).indexOf(entry.target);
                  if (index !== -1) {
                    currentIndex = index;
                    updateDots(currentIndex);
                  }
                }
              });
            }, {
              root: carousel,
              threshold: 0.5
            });

            slides.forEach(slide => observer.observe(slide));

            function updateDots(index) {
              dots.forEach((dot, i) => {
                if (i === index) {
                  dot.classList.remove('bg-white/50', 'hover:bg-white/80');
                  dot.classList.add('bg-white', 'scale-125');
                } else {
                  dot.classList.remove('bg-white', 'scale-125');
                  dot.classList.add('bg-white/50', 'hover:bg-white/80');
                }
              });
            }

            function goToSlide(index) {
              // Handle wrap-around and limits
              const targetIndex = (index + totalSlides) % totalSlides;
              const scrollPosition = carousel.clientWidth * targetIndex;

              carousel.scrollTo({
                left: scrollPosition,
                behavior: 'smooth'
              });
            }

            function nextSlide() {
              goToSlide(currentIndex + 1);
            }

            function startAutoplay() {
              stopAutoplay();
              autoplayTimer = setInterval(nextSlide, 5000);
            }

            function stopAutoplay() {
              if (autoplayTimer) {
                clearInterval(autoplayTimer);
                autoplayTimer = null;
              }
            }

            // Event Listeners
            dots.forEach((dot, index) => {
              dot.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent bubbling issues
                stopAutoplay();
                goToSlide(index);
              });
            });

            // Pause on interaction
            carousel.addEventListener('mouseenter', stopAutoplay);
            carousel.addEventListener('mouseleave', startAutoplay);
            carousel.addEventListener('touchstart', stopAutoplay, { passive: true });
            carousel.addEventListener('touchend', startAutoplay, { passive: true });

            // Start
            startAutoplay();
          })();
        </script>
      @endif
    </div>
  </div>
@endif