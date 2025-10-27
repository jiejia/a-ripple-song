<!doctype html>
<html @php(language_attributes()) class="h-full">

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
  <div class="container mx-auto p-4 h-full">
    @php(wp_body_open())
    <div id="app" class="h-full bg-base-300 rounded-2xl p-4 grid grid-cols-[200px_1fr_2fr] gap-4">
      <header class="bg-base-200 rounded-2xl p-4">
        <div>
          <h1 class="text-2xl font-bold">
            <a href="{{ home_url('/') }}">
              {{ __('A Ripple Song', 'sage') }}
            </a>
          </h1>
        </div>
        <nav>
          <ul>
            <li>
              <a href="#">Home</a>
            </li>
            <li>
              <a href="#">Podcasts</a>
            </li>
            <li>
              <a href="#">Blog</a>
            </li>
            <li>
              <a href="#">About</a>
            </li>
            <li>
              <a href="#">Contact</a>
            </li>
          </ul>
        </nav>
        @include('sections.header')
      </header>

      <main id="main" class="main">
        @yield('content')
      </main>

      @hasSection('sidebar')
      <aside class="sidebar">
        @yield('sidebar')
      </aside>
      @endif

      @include('sections.footer')
    </div>
    @php(do_action('get_footer'))
    @php(wp_footer())
  </div>
</body>

</html>