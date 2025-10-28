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

      <div class="grid grid-cols-[1fr_290px] gap-4">
        <main id="main" class="main relative">
          @yield('content')
          <div class="absolute bottom-0 left-0 w-full h-[100px] bg-base-300/80 z-100 rounded-2xl p-2 grid grid-cols-[1fr_1fr] gap-4">
            <div class="grid grid-cols-[60px_1fr] gap-4 items-center bg-base-100/80 p-2 rounded-lg">
              <div>
                <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="Podcast 1" class="w-15 h-15 rounded-md" />
              </div>
              <div>
                <h4 class="text-md font-bold">Podcast 1</h4>
                <p class="text-xs text-base-content/80">
                  <span>October 28, 2025</span>
                </p>
                <p class="text-xs text-base-content/50">
                  <span>100k views</span>
                </p>
              </div>
            </div>
          </div>

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