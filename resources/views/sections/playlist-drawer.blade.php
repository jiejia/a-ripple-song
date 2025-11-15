<div class="drawer drawer-end z-[200]" x-data>
  <input id="playlist-drawer" type="checkbox" class="drawer-toggle" />
  <div class="drawer-side">
    <label for="playlist-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
    <div class="bg-base-100 text-base-content min-h-full w-96 max-w-[90vw]">
      <!-- Header -->
      <div class="sticky top-0 bg-base-100 p-4 border-b border-base-300 flex items-center justify-between z-10">
        <h3 class="font-bold text-lg">播放列表</h3>
        <label for="playlist-drawer" class="btn btn-sm btn-circle btn-ghost">✕</label>
      </div>
      
      <!-- Playlist Content -->
      <div class="p-4">
        <div class="text-sm text-base-content/60 mb-4 flex items-center justify-between">
          <span x-text="'共 ' + $store.player.playlist.length + ' 首'"></span>
          <button 
            @click="if(confirm('确定要清空播放列表吗？')) $store.player.clearPlaylist();"
            class="btn btn-ghost btn-xs"
            title="清空播放列表">
            <i data-lucide="trash-2" class="w-3 h-3"></i>
            清空
          </button>
        </div>
        
        <!-- 播放列表容器 -->
        <ul class="space-y-2">
          <!-- 空状态 -->
          <template x-if="$store.player.playlist.length === 0">
            <div class="p-8 text-center text-base-content/60">
              <i data-lucide="list-music" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
              <p>播放列表为空</p>
              <p class="text-sm mt-2">添加一些节目开始收听吧</p>
            </div>
          </template>

          <!-- 播放列表项 -->
          <template x-for="(episode, index) in $store.player.playlist" :key="episode.id">
            <li 
              @click="$store.player.playByIndex(index)"
              :class="index === $store.player.currentIndex ? 'bg-primary/10 border-l-4 border-primary' : 'hover:bg-base-200'"
              class="p-3 rounded-lg cursor-pointer transition-colors group">
              <div class="flex gap-3 items-center">
                <!-- 封面图 -->
                <div class="relative flex-shrink-0 w-14 h-14">
                  <template x-if="episode.featuredImage">
                    <div class="relative w-full h-full">
                      <img 
                        :src="episode.featuredImage" 
                        :alt="episode.title" 
                        class="w-14 h-14 rounded object-cover" />
                      <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-base-900/30 rounded">
                        <i 
                          data-lucide="podcast" 
                          class="w-5 h-5 text-base-100" 
                          :class="{ 'text-primary': index === $store.player.currentIndex }"></i>
                      </div>
                    </div>
                  </template>
                  <template x-if="!episode.featuredImage">
                    <div class="w-full h-full rounded bg-base-300/60 flex items-center justify-center">
                      <i data-lucide="podcast" class="w-5 h-5 text-base-content/70" x-show="index !== $store.player.currentIndex"></i>
                      <i data-lucide="podcast" class="w-5 h-5 text-primary" x-show="index === $store.player.currentIndex"></i>
                    </div>
                  </template>
                </div>

                <!-- 节目信息 -->
                <div class="flex-1 min-w-0">
                  <p 
                    x-text="episode.title"
                    :class="index === $store.player.currentIndex ? 'text-primary' : ''"
                    class="font-semibold text-sm truncate"></p>
                  <p x-text="episode.publishDate" class="text-xs text-base-content/60"></p>
                  <template x-if="episode.description">
                    <p x-text="episode.description" class="text-xs text-base-content/50 truncate"></p>
                  </template>
                </div>

                <!-- 删除按钮 -->
                <button 
                  @click.stop="$store.player.removeEpisode(episode.id)"
                  class="btn btn-ghost btn-sm btn-circle opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0"
                  title="删除">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
              </div>
            </li>
          </template>
        </ul>
      </div>
    </div>
  </div>
</div>

