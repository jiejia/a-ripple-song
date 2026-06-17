{{-- 自动播放确认提示栏 --}}
{{-- 当用户刷新页面且之前状态为正在播放时显示 --}}
<div 
    x-data
    x-cloak
    x-show="$store.player.showAutoplayConfirm" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="transform translate-y-full opacity-0"
    x-transition:enter-end="transform translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="transform translate-y-0 opacity-100"
    x-transition:leave-end="transform translate-y-full opacity-0"
    class="fixed bottom-0 left-0 right-0 z-[200] flex justify-center pointer-events-none"
>
    <div class="bg-base-100 shadow-lg rounded-t-xl p-4 max-w-md w-full mx-4 mb-0 pointer-events-auto border border-base-300 border-b-0">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                <i data-lucide="play-circle" class="w-5 h-5 text-primary"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <h4 class="font-semibold text-sm text-base-content">{{ __('Continue playing?', 'sage') }}</h4>
                    <span class="text-xs text-base-content/40" x-text="$store.player.autoplayCountdown + 's'"></span>
                </div>
                <p class="text-xs text-base-content/60 truncate" x-text="$store.player.currentEpisode?.title || '{{ __('Unknown episode', 'sage') }}'"></p>
            </div>
        </div>
        <div class="flex gap-2">
            <button 
                @click="$store.player.confirmAutoplay()" 
                class="btn btn-primary btn-sm flex-1"
            >
                <i data-lucide="play" class="w-4 h-4"></i>
                {{ __('Play', 'sage') }}
            </button>
            <button 
                @click="$store.player.cancelAutoplay()" 
                class="btn btn-sm flex-1"
            >
                <i data-lucide="x" class="w-4 h-4"></i>
                {{ __('Cancel', 'sage') }}
            </button>
        </div>
    </div>
</div>

