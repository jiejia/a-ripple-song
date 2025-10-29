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
      <div class="grid grid-cols-[1fr_290px] gap-4 h-full">
        <main id="main" class="main grid grid-rows-[1fr_100px] gap-4 h-full relative">
          @yield('content')
          <div class="w-full h-[100px] absolute bottom-0 left-0 bg-base-300/75 z-100 rounded-lg p-2 grid grid-cols-[1fr_1fr] gap-4">
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