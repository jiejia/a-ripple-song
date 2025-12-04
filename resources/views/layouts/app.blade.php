<!doctype html>
<html @php(language_attributes()) class="bg-base-200" x-data x-init="$store.theme.init()" :data-theme="$store.theme.current">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @php(do_action('get_header'))
  @php(wp_head())

  {{-- Vite assets are now loaded via wp_enqueue_scripts in setup.php --}}
  {{-- This ensures they work in all contexts including customizer preview --}}
  <style>
    /* html {
      margin-top: 0px !important;
      scrollbar-gutter: stable;
    }

    #wpadminbar {
      display: none;
    } */
    html {
      scrollbar-gutter: stable;
    }

    /* 当显示 WordPress 管理工具栏时的调整 */
    body.admin-bar #swup-header {
      top: 32px;
    }

    body.admin-bar .sticky.top-\[70px\] {
      top: 102px;
    }

    @media screen and (max-width: 782px) {
      body.admin-bar #swup-header {
        top: 46px;
      }

      body.admin-bar .sticky.top-\[70px\] {
        top: 116px;
      }
    }
  </style>
</head>

<body @php(body_class('bg-base-200'))>
  <div class="mb-[190px] md:mb-0">
    <div class="max-w-screen-xl mx-auto">
      @php(wp_body_open())
      <div id="app" class="p-4 gap-4">
        @include('sections.header')
        <div class="grid lg:grid-cols-[220px_1fr_300px] md:grid-cols-[1fr_300px] grid-cols-[1fr] gap-4 mt-[55px] items-start relative">
          @include('sections.leftbar')
          <div class="">
            <main id="swup-main" class="main transition-fade">
              @yield('content')
            </main>

          </div>
          @hasSection('sidebar')
          @yield('sidebar')
          @endif
        </div>
      </div>
    </div>
    @include('sections.footer')
    @php(do_action('get_footer'))
    @php(wp_footer())
  </div>
  @include('sections.mobile-menu')
  @include('sections.search-modal')
  @include('sections.playlist-drawer')
  @include('partials.image-lightbox')
  @include('sections.autoplay-confirm')
  @include('sections.leftbar-drawer')
  @include('sections.sidebar-drawer')

  {{-- Leftbar Drawer Toggle Button - Shows when leftbar is hidden (below lg) --}}
  <label 
    for="leftbar-drawer" 
    class="fixed left-0 top-1/2 -translate-y-1/2 z-[99] lg:hidden cursor-pointer
           bg-base-300/80 text-base-content/70 
           rounded-r-md shadow-sm
           py-3 px-1
           hover:bg-base-300 hover:text-base-content hover:px-2
           transition-all duration-200"
    aria-label="{{ __('Open Left Sidebar', 'sage') }}"
  >
    <i data-lucide="chevron-right" class="w-3 h-3"></i>
  </label>

  {{-- Sidebar Drawer Toggle Button - Shows when sidebar is hidden (below md) --}}
  <label 
    for="sidebar-drawer" 
    class="fixed right-0 top-1/2 -translate-y-1/2 z-[99] md:hidden cursor-pointer
           bg-base-300/80 text-base-content/70 
           rounded-l-md shadow-sm
           py-3 px-1
           hover:bg-base-300 hover:text-base-content hover:px-2
           transition-all duration-200"
    aria-label="{{ __('Open Right Sidebar', 'sage') }}"
  >
    <i data-lucide="chevron-left" class="w-3 h-3"></i>
  </label>

  {{-- Back to Top Button --}}
  <button
    x-data="{ show: false }"
    x-init="window.addEventListener('scroll', () => { show = window.scrollY > 300 })"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
    class="fixed bottom-52 md:bottom-6 right-4 z-50 btn btn-circle btn-primary shadow-lg"
    aria-label="{{ __('Back to top', 'flavor') }}"
  >
    <i data-lucide="arrow-up" class="w-5 h-5"></i>
  </button>
</body>

</html>