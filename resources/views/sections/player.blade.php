<div class="card md:bg-base-100 bg-base-300/90 md:static fixed bottom-0 left-0 right-0 z-100" x-data>
    <div class="card-body md:p-4 py-2 px-4">
        <h2 class="md:text-lg text-md font-bold">{!! __('NOW PLAYING', 'sage') !!}</h2>
        <div class="grid grid-cols-[60px_1fr] gap-4 items-center md:bg-base-300/50 bg-base-100/75 md:p-4 py-2 px-4 rounded-lg">
            <div class="md:w-15 md:h-15 w-10 h-10">
                <template x-if="$store.player.currentEpisode?.featuredImage">
                    <div class="relative md:w-15 md:h-15 w-10 h-10">
                        <img
                            :src="$store.player.currentEpisode?.featuredImage"
                            :alt="$store.player.currentEpisode?.title || '{{ __('Podcast', 'sage') }}'"
                            class="md:w-15 md:h-15 w-10 h-10 rounded-md object-cover" />
                        <div class="pointer-events-none absolute inset-0 bg-base-900/30 flex items-center justify-center rounded-md">
                            <i data-lucide="podcast" class="w-6 h-6 text-base-100"></i>
                        </div>
                    </div>
                </template>
                <template x-if="!$store.player.currentEpisode?.featuredImage">
                    <div class="md:w-15 md:h-15 w-10 h-10 rounded-md bg-base-300/60 flex items-center justify-center">
                        <i data-lucide="podcast" class="w-6 h-6 text-base-content/70"></i>
                    </div>
                </template>
            </div>
            <div>
                <h4 class="text-md font-bold line-clamp-2" x-text="$store.player.currentEpisode?.title || '{{ __('No Episode Playing', 'sage') }}'">{{ __('Podcast Episode', 'sage') }}</h4>
                <p class="text-xs text-base-content/80">
                    <span x-text="$store.player.currentEpisodePublishDate">October 18, 2025</span>
                </p>
                <!-- <p class="text-xs text-base-content/50" x-show="$store.player.currentEpisode?.description">
                    <span x-text="$store.player.currentEpisode?.description" class="line-clamp-1">142k views</span>
                </p> -->
            </div>
        </div>
        <div>
            <div class="h-[40px] relative" id="wave">
                {{-- 加载状态提示 --}}
                <div x-show="$store.player.isLoading" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 flex items-center justify-center gap-2 text-base-content/60">
                    <span class="loading loading-ring loading-lg text-base-content"></span>
                    <span class="text-xs text-base-content/75">{{ __('Loading audio', 'sage') }}</span>
                </div>
            </div>
            <div class="mt-0 w-full">
                <div class="grid grid-cols-[30px_1fr_30px] gap-2 items-center text-xs">
                    <span x-text="$store.player.currentTimeText">00:00</span>
                    <input type="range" min="0" :max="$store.player.duration" :value="$store.player.currentTime" :style="$store.player.progressRangeStyle" x-on:input="$store.player.seek($event.target.value)" class="range range-xs w-full aripplesong-progress-range text-base-content/20 [--range-bg:orange] [--range-thumb:blue] [--range-fill:0]" />
                    <span class="justify-self-end" x-text="$store.player.durationText">00:00</span>
                </div>
            </div>
            <div class="mt-1 md:mt-2 grid grid-cols-[1fr_1fr_1fr] gap-4 items-center w-full">
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

                    <div x-show="$store.player.volumePanelOpen" @click.outside="$store.player.volumePanelOpen = false" class="absolute bottom-full right-[-8px] mb-2 bg-base-100 rounded-full shadow-lg p-2 w-10 h-32">
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
