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
          <div id="{{ $slide_id }}" class="carousel-item relative w-full rounded-lg snap-center"
            style="scroll-snap-stop: always">
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
            // Get original slides before cloning
            const originalSlides = Array.from(carousel.querySelectorAll('.carousel-item'));
            const totalSlides = originalSlides.length;

            if (totalSlides < 2) return; // No need for loop/dots if single slide

            // --- 1. Setup Infinite Loop (Clones) ---
            const firstClone = originalSlides[0].cloneNode(true);
            const lastClone = originalSlides[totalSlides - 1].cloneNode(true);

            // Add marker classes or ids to clones if needed (optional, logic relies on index)
            firstClone.removeAttribute('id'); // Avoid duplicate IDs
            lastClone.removeAttribute('id');
            firstClone.setAttribute('aria-hidden', 'true');
            lastClone.setAttribute('aria-hidden', 'true');

            carousel.appendChild(firstClone);
            carousel.insertBefore(lastClone, originalSlides[0]);

            // --- 2. Initial Positioning ---
            // Need to wait for layout to ensure widths are correct
            let slideWidth = carousel.offsetWidth;

            // Start at index 1 (the first real slide)
            // Using 'auto' behavior to prevent initialization animation
            carousel.scrollTo({ left: slideWidth, behavior: 'auto' });

            let currentIndex = 0; // Represents real index (0 to totalSlides - 1)
            let autoplayTimer = null;
            let isScrolling = false;
            let scrollTimeout = null;

            function updateDots(realIndex) {
              dots.forEach((dot, i) => {
                if (i === realIndex) {
                  dot.classList.remove('bg-white/50', 'hover:bg-white/80');
                  dot.classList.add('bg-white', 'scale-125');
                } else {
                  dot.classList.remove('bg-white', 'scale-125');
                  dot.classList.add('bg-white/50', 'hover:bg-white/80');
                }
              });
            }

            function getRealIndexFromScroll() {
              const currentScroll = carousel.scrollLeft;
              const width = carousel.offsetWidth;
              // Dom Index: 0 (Clone Last), 1 (Real 1), ... , Total (Real Last), Total+1 (Clone First)
              const domIndex = Math.round(currentScroll / width);

              let realIndex = 0;
              if (domIndex === 0) {
                realIndex = totalSlides - 1;
              } else if (domIndex === totalSlides + 1) {
                realIndex = 0;
              } else {
                realIndex = domIndex - 1;
              }

              // Safety clamp
              if (realIndex < 0) realIndex = totalSlides - 1;
              if (realIndex >= totalSlides) realIndex = 0;

              return realIndex;
            }

            // --- 3. Scroll Handler (Loop Logic) ---
            carousel.addEventListener('scroll', () => {
              if (scrollTimeout) clearTimeout(scrollTimeout);
              isScrolling = true;
              stopAutoplay();

              const width = carousel.offsetWidth;
              const scrollLeft = carousel.scrollLeft;

              // Check for loop jump conditions (Snap points)
              // If at Clone Last (Index 0) -> Jump to Real Last
              if (scrollLeft <= 5) { // Use small threshold for float inaccuracies
                carousel.classList.remove('scroll-smooth');
                carousel.style.scrollBehavior = 'auto';
                carousel.scrollLeft = width * totalSlides;
                // Force reflow/flush could be done here implicitly
                carousel.style.scrollBehavior = '';
                carousel.classList.add('scroll-smooth');
              }
              // If at Clone First (Index Total + 1) -> Jump to Real First
              else if (scrollLeft >= width * (totalSlides + 1) - 5) {
                carousel.classList.remove('scroll-smooth');
                carousel.style.scrollBehavior = 'auto';
                carousel.scrollLeft = width;
                carousel.style.scrollBehavior = '';
                carousel.classList.add('scroll-smooth');
              }

              // Update Dots continuously for responsiveness, or debounced
              const realIndex = getRealIndexFromScroll();
              if (realIndex !== currentIndex) {
                currentIndex = realIndex;
                updateDots(currentIndex);
              }

              // Restart autoplay after interaction stops
              scrollTimeout = setTimeout(() => {
                isScrolling = false;
                startAutoplay();
              }, 1500); // Wait a bit longer before auto-resuming
            });

            // --- 4. Navigation Logic ---
            function goToRealSlide(realIndex) {
              const width = carousel.offsetWidth;
              const targetDomIndex = realIndex + 1; // +1 because of prev clone

              carousel.scrollTo({
                left: targetDomIndex * width,
                behavior: 'smooth'
              });
            }

            function nextSlide() {
              // If currently not changing slides, move forward
              // We can just increment existing scroll based index
              const width = carousel.offsetWidth;
              const currentDomIndex = Math.round(carousel.scrollLeft / width);

              carousel.scrollTo({
                left: (currentDomIndex + 1) * width,
                behavior: 'smooth'
              });
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

            // --- 5. Event Listeners ---
            dots.forEach((dot, index) => {
              dot.addEventListener('click', (e) => {
                e.stopPropagation();
                stopAutoplay();
                goToRealSlide(index);
              });
            });

            // Pause on hover
            carousel.addEventListener('mouseenter', stopAutoplay);
            carousel.addEventListener('mouseleave', startAutoplay);
            carousel.addEventListener('touchstart', stopAutoplay, { passive: true });

            // Handle Resize
            let resizeTimer;
            window.addEventListener('resize', () => {
              // Re-adjust scroll position to maintain current slide
              clearTimeout(resizeTimer);
              resizeTimer = setTimeout(() => {
                slideWidth = carousel.offsetWidth;
                carousel.classList.remove('scroll-smooth');
                carousel.style.scrollBehavior = 'auto';
                carousel.scrollLeft = (currentIndex + 1) * slideWidth;
                carousel.classList.add('scroll-smooth');
                carousel.style.scrollBehavior = '';
              }, 100);
            });

            // Start
            startAutoplay();
          })();
        </script>
      @endif
    </div>
  </div>
@endif