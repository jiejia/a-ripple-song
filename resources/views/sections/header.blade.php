<header class="fixed top-0 h-[55px] left-0 right-0 z-100 bg-base-100/75 transition-fade" id="swup-header">
  <div class="max-w-screen-xl mx-auto h-full">
    <div class="px-6 py-3">
      <div class="grid xl:grid-cols-[220px_1fr_300px] grid-cols-[220px_1fr] gap-4">
        @php
          $siteLogo = carbon_get_theme_option('crb_site_logo');
        @endphp
        <h1 class="text-2xl font-bold text-center">
          <a href="{{ home_url('/') }}" class="flex items-center gap-2">
            @if($siteLogo)
              <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="object-contain" width="220" height="32">
            @else
              <i data-lucide="podcast" class="w-6 h-6"></i>
              <span
                class="text-2xl bg-gradient-to-r from-base-content/40 via-base-content/70 to-base-content bg-clip-text text-transparent transition-all duration-500 ease-in-out hover:from-base-content hover:via-base-content/70 hover:to-base-content/40">{{ $siteName }}</span>
            @endif
          </a>
        </h1>
        @include('sections.primary-navigation')
        <div class="grid grid-flow-col justify-end gap-2 place-items-center">
          <label for="search-modal" class="md:hidden block"><i data-lucide="search"
              class="w-5 h-5 cursor-pointer"></i></label>
          <!-- 主题循环切换按钮 -->
          <button type="button" class="btn btn-ghost btn-sm btn-circle"
            @click="$store.theme.toggle()"
            :title="$store.theme.mode === 'light' ? '{{ __('Light Mode', 'a-ripple-song') }}' : ($store.theme.mode === 'dark' ? '{{ __('Dark Mode', 'a-ripple-song') }}' : '{{ __('Follow System', 'a-ripple-song') }}')">
            <i data-lucide="sun" class="w-5 h-5" x-show="$store.theme.isLight"></i>
            <i data-lucide="moon" class="w-5 h-5" x-show="$store.theme.isDark && !$store.theme.isAuto"></i>
            <i data-lucide="sun-moon" class="w-5 h-5" x-show="$store.theme.isAuto"></i>
            <span class="sr-only">{{ __('Toggle Theme', 'a-ripple-song') }}</span>
          </button>

          <label for="mobile-menu" class="xl:hidden block"><i data-lucide="menu"
              class="w-5 h-5 cursor-pointer"></i></label>

        </div>
      </div>
    </div>
  </div>
</header>