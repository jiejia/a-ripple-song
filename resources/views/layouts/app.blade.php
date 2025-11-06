<!doctype html>
<html @php(language_attributes()) class="bg-base-200" data-theme="retro">

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

<body @php(body_class('bg-base-200'))>
  <div class="max-w-screen-xl mx-auto">
    @php(wp_body_open())
    <div id="app" class="p-4 gap-4">
      @include('sections.header')
      <div class="grid grid-cols-[220px_1fr_300px] gap-4 mt-[55px] items-start">
        @include('sections.leftbar')
        <div class="">
          <main id="main" class="main">
            @yield('content')
            @include('sections.footer')
          </main>
          @php(do_action('get_footer'))
          @php(wp_footer())
        </div>
        @hasSection('sidebar')
        @yield('sidebar')
        @endif
      </div>
    </div>

  </div>
</body>

</html>