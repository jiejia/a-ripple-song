<div class="drawer drawer-end z-[200]">
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
          <span id="playlist-stats">共 0 首</span>
          <button 
            onclick="if(confirm('确定要清空播放列表吗？')) window.playlistManager.clearPlaylist();"
            class="btn btn-ghost btn-xs"
            title="清空播放列表">
            <i data-lucide="trash-2" class="w-3 h-3"></i>
            清空
          </button>
        </div>
        
        <!-- 播放列表容器 - 由 JavaScript 动态渲染 -->
        <ul id="playlist-container" class="space-y-2">
          <!-- 播放列表项将由 JavaScript 动态生成 -->
        </ul>
      </div>
    </div>
  </div>
</div>

