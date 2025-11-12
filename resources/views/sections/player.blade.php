<div class="card md:bg-base-100 bg-base-300/90 mt-4 md:static fixed bottom-0 left-0 right-0 z-100" x-data>
    <div class="card-body p-4">
        <h2 class="text-lg font-bold">NOW PLAYING</h2>
        <div class="grid grid-cols-[60px_1fr] gap-4 items-center md:bg-base-300/50 bg-base-100/75 p-4 rounded-lg">
            <div>
                <img :src="$store.player.currentEpisode?.featuredImage || 'https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80'" :alt="$store.player.currentEpisode?.title || '音乐漫谈'" class="w-15 h-15 rounded-md" />
            </div>
            <div>
                <h4 class="text-md font-bold" x-text="$store.player.currentEpisode?.title || '暂无播放'">音乐漫谈：从古典到流行的跨界之旅</h4>
                <p class="text-xs text-base-content/80">
                    <span x-text="$store.player.currentEpisode?.publishDate || '-'">October 18, 2025</span>
                </p>
                <p class="text-xs text-base-content/50" x-show="$store.player.currentEpisode?.description">
                    <span x-text="$store.player.currentEpisode?.description" class="line-clamp-1">142k views</span>
                </p>
            </div>
        </div>
        <div>
            <div class="h-[40px]" id="wave">
            </div>
            <div class="mt-0 w-full">
                <div class="grid grid-cols-[30px_1fr_30px] gap-2 items-center text-xs">
                    <span x-text="$store.player.currentTimeText">00:00</span>
                    <input type="range" min="0" :max="$store.player.duration" :value="$store.player.currentTime" x-on:input="$store.player.seek($event.target.value)" class="range range-xs w-full text-base-content/20 [--range-bg:orange] [--range-thumb:blue] [--range-fill:0.5] " />
                    <span class="justify-self-end" x-text="$store.player.durationText">00:00</span>
                </div>
            </div>
            <div class="mt-2 grid grid-cols-[1fr_1fr_1fr] gap-4 items-center w-full">
                <div class="flex items-center gap-2">
                    <label for="playlist-drawer" class="cursor-pointer">
                        <i data-lucide="list-music" class="w-4 h-4"></i>
                    </label>
                    <div class="relative">
                        <label class="cursor-pointer text-xs font-semibold px-2 py-1 rounded transition-colors flex items-center gap-1 hover:opacity-70" x-text="$store.player.playbackRateText" x-on:click="$store.player.togglePlaybackRatePanel()">1x</label>
                        
                        <div x-show="$store.player.playbackRatePanelOpen" @click.outside="$store.player.playbackRatePanelOpen = false" class="absolute bottom-full left-0 mb-2 bg-base-100 rounded-lg shadow-lg p-2 min-w-[80px]">
                            <template x-for="rate in $store.player.availableRates" :key="rate">
                                <button x-on:click="$store.player.setPlaybackRate(rate)" class="w-full text-left px-3 py-2 text-xs rounded transition-colors" :class="{ 'bg-primary text-primary-content': $store.player.playbackRate === rate }">
                                    <span x-text="rate === 1 ? '1x' : rate + 'x'"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center gap-4 items-center">
                    <i data-lucide="skip-back" class="cursor-pointer w-4 h-4" x-on:click="$store.player.playPrevious()"></i>
                    <i x-show="!$store.player.isPlaying" data-lucide="play" class="cursor-pointer w-4 h-4 bg-success-500 rounded-full" x-on:click="$store.player.togglePlay()"></i>
                    <i x-show="$store.player.isPlaying" data-lucide="pause" class="cursor-pointer w-4 h-4 bg-success-500 rounded-full" x-on:click="$store.player.togglePlay()"></i>
                    <i data-lucide="skip-forward" class="cursor-pointer w-4 h-4" x-on:click="$store.player.playNext()"></i>
                </div>
                <div class="justify-self-end relative">
                    <i x-show="!$store.player.isMuted" data-lucide="volume" class="cursor-pointer w-4 h-4" x-on:click="$store.player.toggleVolumePanel()"></i>
                    <i x-show="$store.player.isMuted" data-lucide="volume-x" class="cursor-pointer w-4 h-4" x-on:click="$store.player.toggleVolumePanel()"></i>

                    <div x-show="$store.player.volumePanelOpen" @click.outside="$store.player.volumePanelOpen = false"  class="absolute bottom-full right-[-8px] mb-2 bg-base-100 rounded-full shadow-lg p-2 w-10 h-32">
                        <input type="range" min="0" max="1" step="0.01" :value="$store.player.volume" x-on:input="$store.player.setVolume($event.target.value)" class="w-22 absolute left-[-23px] bottom-[70px] range range-xs range-success transform -rotate-90" />
                        <label class="swap absolute bottom-3 left-3 cursor-pointer">
                            <i x-show="!$store.player.isMuted" data-lucide="volume-2" class="w-4 h-4 " x-on:click="$store.player.toggleMute()"></i>
                            <i x-show="$store.player.isMuted" data-lucide="volume-x" class="w-4 h-4 " x-on:click="$store.player.toggleMute()"></i>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>


</script>