<!doctype html>
<html @php(language_attributes()) class="h-full bg-base-200" data-theme="retro">

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

<body @php(body_class('h-full bg-base-200 h-full'))>
  <div class="max-w-screen-xl mx-auto h-full">
    @php(wp_body_open())
    <div id="app" class="h-full p-4 gap-4">
      <header class="fixed top-0 h-[55px] left-0 right-0 z-100 bg-base-100/75">
        <div class="max-w-screen-xl mx-auto h-full">
          <div class="px-6 py-3">
            <div class="grid grid-cols-[220px_1fr_300px] gap-4">
              <h1 class="text-2xl font-bold text-center">
                <a href="{{ home_url('/') }}" class="flex items-center justify-center gap-2">
                  <i data-lucide="podcast" class="w-6 h-6"></i>
                  <span class="text-2xl bg-gradient-to-r from-base-content/40 via-base-content/70 to-base-content bg-clip-text text-transparent transition-all duration-500 ease-in-out hover:from-base-content hover:via-base-content/70 hover:to-base-content/40">{{ $siteName }}</span>
                </a>
              </h1>
              <ul class="grid grid-flow-col gap-2 text-md justify-center">
                <li>
                  <a href="{{ home_url('/') }}" class="grid place-items-center h-full w-full text-center px-4 rounded-lg text-base-content/80 hover:text-base-content">
                    Home
                  </a>
                </li>
                <li>
                  <a href="{{ get_post_type_archive_link('podcast') }}" class="grid place-items-center h-full w-full text-center px-4 rounded-lg text-base-content/80 hover:text-base-content">
                    Podcasts
                  </a>
                </li>
                <li class=""">
                  <div class=" dropdown dropdown-hover dropdown-start h-full w-full">
                  <a class="grid place-items-center h-full w-full text-center px-4 rounded-lg text-base-content/80 hover:text-base-content" href="{{ get_permalink( get_page_by_path( 'blog' ) ) }}">Blog</a>
                  <ul tabindex="-1" class="dropdown-content menu bg-base-200/75 rounded-box z-1 w-52 p-2 shadow-sm">
                    <li><a href="#">Blog 1</a></li>
                    <li><a href="#">Blog 2</a></li>
                    <li><a href="#">Blog 3</a></li>
                  </ul>
            </div>
            </li>
            <li>
              <a href="{{ home_url('/about') }}" class="grid place-items-center h-full w-full text-center px-4 rounded-lg text-base-content/80 hover:text-base-content">
                About
              </a>
            </li>
            <li>
              <a href="{{ home_url('/contact') }}" class="grid place-items-center h-full w-full text-center px-4 rounded-lg text-base-content/80 hover:text-base-content">
                Contact
              </a>
            </li>
            </ul>
            <div class="grid grid-flow-col justify-end gap-2 place-items-center">
              <label class="swap swap-rotate">
                <!-- this hidden checkbox controls the state -->
                <input type="checkbox" class="theme-controller" value="synthwave" />

                <!-- sun icon -->
                <svg
                  class="swap-off w-5 h-5 fill-current"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24">
                  <path
                    d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" />
                </svg>

                <!-- moon icon -->
                <svg
                  class="swap-on w-5 h-5 fill-current"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24">
                  <path
                    d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z" />
                </svg>
              </label>
            </div>
          </div>
        </div>
    </div>
    </header>
    @include('sections.header')
    <div class="h-full ml-[240px] mr-[320px] mt-[55px]">
      <main id="main" class="main h-full">
        @yield('content')
        @include('sections.footer')
      </main>
      @php(do_action('get_footer'))
      @php(wp_footer())
    </div>
    <!-- <div class="h-[100px] fixed bottom-0 left-0 right-0 z-100">
        <div class="max-w-screen-xl mx-auto p-1">
          <div class="px-4">
            <div class="ml-[240px] mr-[280px] bg-base-300/75 rounded-lg p-2 grid grid-cols-[1fr_1fr] gap-4">
            <div class="grid grid-cols-[60px_1fr] gap-4 items-center bg-base-100/75 p-2 rounded-lg">
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
            <div>
              <div class="h-[40px]" id="wave">
              </div>
              <div class="mt-0 w-full">
                <div class="grid grid-cols-[30px_1fr_30px] gap-2 items-center text-xs">
                  <span id="sound-current-time">00:00</span>
                  <input type="range" min="0" max="100" value="0" id="sound-progress" class="range range-xs w-full text-transparent [--range-bg:orange] [--range-thumb:blue] [--range-fill:0] " oninput="seek(this.value)" />
                  <span class="justify-self-end" id="sound-duration">00:00</span>
                </div>
              </div>
              <div class="mt-2 grid grid-cols-[1fr_1fr_1fr] gap-4 items-center w-full">
                <div>
                  <i data-lucide="list-music" class="cursor-pointer w-4 h-4"></i>
                </div>
                <div class="flex justify-center gap-4 items-center">
                  <i data-lucide="skip-back" class="cursor-pointer w-4 h-4"></i>
                  <i data-lucide="play" class="cursor-pointer w-4 h-4 bg-success-500 rounded-full" data-type="play" id="play-pause-button" onclick="playOrPause()"></i>
                  <i data-lucide="skip-forward" class="cursor-pointer w-4 h-4"></i>
                </div>
                <div class="justify-self-end relative">
                  <i data-lucide="volume" class="cursor-pointer w-4 h-4" id="volume-button" onclick="toggleVolumePanel()"></i>
                  <div id="volume-panel" class="hidden absolute bottom-full right-[-8px] mb-2 bg-base-100 rounded-full shadow-lg p-2 w-10 h-32">
                    <input type="range" min="0" max="300" value="300" id="volume-slider" class="w-28 absolute left-[-35px] bottom-[55px] range range-xs transform -rotate-90" oninput="changeVolume(this.value)" />
                  </div>
                </div>
              </div>
            </div>
          </div>
          </div>
        </div>
      </div> -->
    <aside class="sidebar fixed top-[70px] right-0 h-[calc(100vh-2rem)] w-[300px]" style="right: max(1rem, calc((100vw - 1280px) / 2 + 1rem));">
      @hasSection('sidebar')
      @yield('sidebar')
      @endif
    </aside>
  </div>

  </div>
</body>

</html>