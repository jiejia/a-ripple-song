<div class="drawer drawer-end z-[200]" x-data>
  <input id="playlist-drawer" type="checkbox" class="drawer-toggle" />
  <div class="drawer-side">
    <label for="playlist-drawer" aria-label="{{ __('Close', 'a-ripple-song') }}" class="drawer-overlay"></label>
    <div class="bg-base-100 text-base-content min-h-full w-96 max-w-[90vw]">
      <!-- Header -->
      <div class="sticky top-0 bg-base-100 p-4 border-b border-base-300 flex items-center justify-between z-10">
        <h3 class="font-bold text-lg">{!! __('Playlist', 'a-ripple-song') !!}</h3>
        <button type="button" class="btn btn-sm btn-circle btn-ghost" aria-label="{{ __('Close', 'a-ripple-song') }}"
          aria-controls="playlist-drawer" onclick="document.getElementById('playlist-drawer').checked = false;">✕</button>
      </div>
      
      <!-- Playlist Content -->
      <div class="p-4">
        <div class="text-sm text-base-content/60 mb-4 flex items-center justify-between">
          <span x-text="'{{ __('Total', 'a-ripple-song') }} ' + $store.player.playlist.length + ' {{ __('episodes', 'a-ripple-song') }}'"></span>
          <button 
            @click="if(confirm('{{ __('Are you sure you want to clear the playlist?', 'a-ripple-song') }}')) $store.player.clearPlaylist();"
            class="btn btn-ghost btn-xs"
            title="{{ __('Clear Playlist', 'a-ripple-song') }}">
            <i data-lucide="trash-2" class="w-3 h-3" aria-hidden="true"></i>
            {{ __('Clear', 'a-ripple-song') }}
          </button>
        </div>
        
        <!-- 播放列表容器 -->
        <ul class="space-y-2">
          <!-- 空状态 -->
          <template x-if="$store.player.playlist.length === 0">
            <div class="p-8 text-center text-base-content/60">
              <i data-lucide="list-music" class="w-12 h-12 mx-auto mb-3 opacity-50" aria-hidden="true"></i>
              <p>{!! __('Playlist is empty', 'a-ripple-song') !!}</p>
              <p class="text-sm mt-2">{!! __('Add some episodes to start listening', 'a-ripple-song') !!}</p>
            </div>
          </template>

          <!-- 播放列表项 -->
          <template x-for="(episode, index) in $store.player.playlist" :key="episode.id">
            <li
              :class="index === $store.player.currentIndex ? 'bg-base-300/50' : 'hover:bg-base-200'"
              class="rounded-lg transition-colors group">
              <div class="flex gap-3 items-center">
                <button
                  type="button"
                  @click="$store.player.playByIndex(index)"
                  class="flex min-w-0 flex-1 items-center gap-3 rounded-lg p-3 text-left focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary"
                  :aria-current="index === $store.player.currentIndex ? 'true' : 'false'"
                  :title="episode.title"
                >
                  <!-- 封面图 -->
                  <div class="relative flex-shrink-0 w-14 h-14">
                    <template x-if="episode.featuredImage">
                      <div class="relative w-full h-full">
                        <img
                          :src="episode.featuredImage"
                          :alt="episode.title"
                          class="w-14 h-14 rounded object-cover"
                          width="56"
                          height="56" />
                        <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-base-900/30 rounded">
                          <i
                            data-lucide="podcast"
                            class="w-5 h-5 text-base-100"
                            aria-hidden="true"></i>
                        </div>
                      </div>
                    </template>
                    <template x-if="!episode.featuredImage">
                      <div class="w-full h-full rounded bg-base-300/60 flex items-center justify-center">
                        <i data-lucide="podcast" class="w-5 h-5 text-base-content/70" aria-hidden="true"></i>
                      </div>
                    </template>
                  </div>

                  <!-- 节目信息 -->
                  <div class="flex-1 min-w-0">
                    <p
                      x-text="episode.title"
                      :class="index === $store.player.currentIndex ? 'text-base-content' : 'text-base-content/80'"
                      class="font-semibold text-sm truncate"></p>
                    <p x-text="window.formatLocalizedDate(episode.publishDate)" class="text-xs text-base-content/60"></p>
                    <template x-if="episode.description">
                      <p x-text="episode.description" class="text-xs text-base-content/50 truncate"></p>
                    </template>
                  </div>
                </button>

                <!-- 删除按钮 -->
                <button 
                  @click.stop="$store.player.removeEpisode(episode.id)"
                  class="btn btn-ghost btn-sm btn-circle opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-opacity flex-shrink-0"
                  title="{{ __('Remove', 'a-ripple-song') }}"
                  aria-label="{{ __('Remove', 'a-ripple-song') }}">
                  <i data-lucide="trash-2" class="w-4 h-4" aria-hidden="true"></i>
                </button>
              </div>
            </li>
          </template>
        </ul>
      </div>
    </div>
  </div>
</div>
