<!doctype html>
<html @php(language_attributes()) class="bg-base-200" x-data x-init="$store.theme.init()" :data-theme="$store.theme.current">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @php(do_action('get_header'))
  @php(wp_head())
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Vite assets are now loaded via wp_enqueue_scripts in setup.php --}}
  {{-- This ensures they work in all contexts including customizer preview --}}
  <style>
    html {
      margin-top: 0px !important;
      scrollbar-gutter: stable;
    }

    #wpadminbar {
      display: none;
    }
  </style>
</head>

<body @php(body_class('bg-base-200'))>
  <div class="max-w-screen-xl mx-auto mb-[230px] md:mb-0">
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
      @include('sections.footer')
      @php(do_action('get_footer'))
      @php(wp_footer())
    </div>
  </div>
  @include('sections.mobile-menu')
  @include('sections.search-modal')
  @include('sections.playlist-drawer')
</body>

</html>