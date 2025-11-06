<header class="fixed top-0 h-[55px] left-0 right-0 z-100 bg-base-100/75">
  <div class="max-w-screen-xl mx-auto h-full">
    <div class="px-6 py-3">
      <div class="grid xl:grid-cols-[220px_1fr_300px] grid-cols-[220px_1fr] gap-4">
        <h1 class="text-2xl font-bold text-center">
          <a href="{{ home_url('/') }}" class="flex items-center justify-center gap-2">
            <i data-lucide="podcast" class="w-6 h-6"></i>
            <span class="text-2xl bg-gradient-to-r from-base-content/40 via-base-content/70 to-base-content bg-clip-text text-transparent transition-all duration-500 ease-in-out hover:from-base-content hover:via-base-content/70 hover:to-base-content/40">{{ $siteName }}</span>
          </a>
        </h1>
        @include('sections.primary-navigation')
        <div class="grid grid-flow-col justify-end gap-2 place-items-center">
          <label for="search-modal" class="md:hidden block"><i data-lucide="search" class="w-5 h-5 cursor-pointer"></i></label>
          <label class="swap swap-rotate">
            <!-- this hidden checkbox controls the state -->
            <input type="checkbox" class="theme-controller" value="synthwave" />

            <!-- sun icon -->
            <i data-lucide="sun" class="swap-off w-5 h-5"></i>
            <!-- moon icon -->
            <i data-lucide="moon" class="swap-on w-5 h-5"></i>
          </label>
          <label for="mobile-menu" class="xl:hidden block"><i data-lucide="menu" class="w-5 h-5 cursor-pointer"></i></label>

        </div>
      </div>
    </div>
  </div>
</header>