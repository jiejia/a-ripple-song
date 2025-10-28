<!doctype html>
<html @php(language_attributes()) class="h-full" data-theme="retro">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @php(do_action('get_header'))
  @php(wp_head())

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    html {
      margin-top: 0px !important;
    }

    #wpadminbar {
      display: none;
    }
  </style>
</head>

<body @php(body_class('h-full bg-base-100'))>
  <div class="max-w-screen-xl mx-auto p-4 h-full">
    @php(wp_body_open())
    <div id="app" class="h-full bg-base-200 rounded-2xl p-4 grid grid-cols-[220px_1fr] gap-4">
      @include('sections.header')

      <div class="grid grid-cols-[1fr_290px] gap-4 relative">
        <main id="main" class="main">
          @yield('content')
        </main>
        <aside class="sidebar">
          @hasSection('sidebar')
          @yield('sidebar')
          @endif
        </aside>

        <!-- @include('sections.footer') -->
      </div>
    </div>
    @php(do_action('get_footer'))
    @php(wp_footer())
  </div>
</body>

</html>