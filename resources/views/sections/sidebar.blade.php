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

    @include('sections.player')
    
</aside>