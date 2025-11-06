<aside class="sidebar sticky top-[70px] lg:block md:block">
    <div class="hidden md:block lg:block">
        @php(get_search_form())
        @php(dynamic_sidebar('sidebar-primary'))
        <div class="card bg-base-100 w-full mt-4">
            <div class="card-body p-4">
                <h2 class="text-lg font-bold">SUBSCRIBE</h2>
                <button class="btn bg-gradient-to-r from-gray-600 via-gray-800 to-black btn-sm text-white border-black transition-all duration-500 ease-in-out hover:from-black hover:via-gray-800 hover:to-gray-600">
                    <i data-lucide="podcast" class="w-4 h-4"></i>
                    Apple Podcast
                </button>
                <button class="btn bg-gradient-to-r from-green-400 via-green-500 to-[#03C755] btn-sm text-white border-[#00b544] transition-all duration-500 ease-in-out hover:from-[#03C755] hover:via-green-500 hover:to-green-400">
                    <i data-lucide="music" class="w-4 h-4"></i>
                    Spotify
                </button>
                <button class="btn bg-gradient-to-r from-yellow-300 via-yellow-400 to-[#FEE502] btn-sm text-[#181600] border-[#f1d800] transition-all duration-500 ease-in-out hover:from-[#FEE502] hover:via-yellow-400 hover:to-yellow-300">
                    <i data-lucide="youtube" class="w-4 h-4"></i>
                    Youtube Music
                </button>

            </div>
        </div>
    </div>



    <div class="card md:bg-base-100 bg-base-300/90 mt-4 md:static fixed bottom-0 left-0 right-0 z-100">
        <div class="card-body p-4">
            <h2 class="text-lg font-bold">NOW PLAYING</h2>
            <div class="grid grid-cols-[60px_1fr] gap-4 items-center md:bg-base-300/50 bg-base-100/75 p-4 rounded-lg">
                <div>
                    <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="音乐漫谈" class="w-15 h-15 rounded-md" />
                </div>
                <div>
                    <h4 class="text-md font-bold">音乐漫谈：从古典到流行的跨界之旅</h4>
                    <p class="text-xs text-base-content/80">
                        <span>October 18, 2025</span>
                    </p>
                    <p class="text-xs text-base-content/50">
                        <span>142k views</span>
                    </p>
                </div>
            </div>
            <div>
                <div class="h-[40px]" id="wave">
                </div>
                <div class="mt-0 w-full">
                    <div class="grid grid-cols-[30px_1fr_30px] gap-2 items-center text-xs">
                        <span id="sound-current-time">00:00</span>
                        <input type="range" min="0" max="100" value="0" id="sound-progress" class="range range-xs w-full text-base-content/20 [--range-bg:orange] [--range-thumb:blue] [--range-fill:0.5] " oninput="seek(this.value)" />
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
                            <input type="range" min="0" max="300" value="300" id="volume-slider" class="w-28 absolute left-[-35px] bottom-[55px] range range-xs range-success transform -rotate-90" oninput="changeVolume(this.value)" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</aside>