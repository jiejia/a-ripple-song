<header class="fixed top-0 h-[55px] left-0 right-0 z-100 bg-base-100/75 transition-fade" id="swup-header">
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
          <!-- 主题切换下拉菜单 -->
          <div class="dropdown dropdown-end" x-data>
            <button 
              type="button" 
              tabindex="0" 
              class="btn btn-ghost btn-sm btn-circle"
              :title="$store.theme.mode === 'light' ? '{{ __('Light Mode', 'sage') }}' : ($store.theme.mode === 'dark' ? '{{ __('Dark Mode', 'sage') }}' : '{{ __('Follow System', 'sage') }}')"
            >
            <!-- 明亮模式图标 -->
            <i data-lucide="sun" class="w-5 h-5" x-show="$store.theme.isLight"></i>
            <!-- 黑暗模式图标 -->
            <i data-lucide="moon" class="w-5 h-5" x-show="$store.theme.isDark && !$store.theme.isAuto"></i>
            <!-- 跟随系统图标 -->
            <i data-lucide="monitor" class="w-5 h-5" x-show="$store.theme.isAuto"></i>
            </button>
            <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-auto p-2 shadow-lg">
              <li>
                <a 
                  @click.prevent="$store.theme.setMode('light')" 
                  :class="{ 'active': $store.theme.mode === 'light' }"
                  class="flex items-center justify-center"
                  title="{{ __('Light Mode', 'sage') }}"
                >
                  <i data-lucide="sun" class="w-4 h-4"></i>
                </a>
              </li>
              <li>
                <a 
                  @click.prevent="$store.theme.setMode('dark')" 
                  :class="{ 'active': $store.theme.mode === 'dark' }"
                  class="flex items-center justify-center"
                  title="{{ __('Dark Mode', 'sage') }}"
                >
                  <i data-lucide="moon" class="w-4 h-4"></i>
                </a>
              </li>
              <li>
                <a 
                  @click.prevent="$store.theme.setMode('auto')" 
                  :class="{ 'active': $store.theme.mode === 'auto' }"
                  class="flex items-center justify-center"
                  title="{{ __('Follow System', 'sage') }}"
                >
                  <i data-lucide="monitor" class="w-4 h-4"></i>
                </a>
              </li>
            </ul>
          </div>
          
          <label for="mobile-menu" class="xl:hidden block"><i data-lucide="menu" class="w-5 h-5 cursor-pointer"></i></label>

        </div>
      </div>
    </div>
  </div>
</header>