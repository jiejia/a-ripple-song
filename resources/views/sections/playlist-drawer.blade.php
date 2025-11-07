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
        <div class="text-sm text-base-content/60 mb-4">
          共 5 首 · 总时长 23:45
        </div>
        
        <ul class="space-y-2">
          <!-- Playlist Item 1 - Currently Playing -->
          <li class="p-3 bg-primary/10 border-l-4 border-primary rounded-lg cursor-pointer hover:bg-primary/20 transition-colors">
            <div class="flex gap-3 items-center">
              <div class="relative">
                <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="音乐漫谈" class="w-14 h-14 rounded" />
                <div class="absolute inset-0 flex items-center justify-center bg-black/30 rounded">
                  <i data-lucide="volume-2" class="w-5 h-5 text-white"></i>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm truncate text-primary">音乐漫谈：从古典到流行的跨界之旅</p>
                <p class="text-xs text-base-content/60">October 18, 2025</p>
                <p class="text-xs text-base-content/50">142k views · 5:23</p>
              </div>
            </div>
          </li>

          <!-- Playlist Item 2 -->
          <li class="p-3 hover:bg-base-200 rounded-lg cursor-pointer transition-colors">
            <div class="flex gap-3 items-center">
              <img src="https://images.unsplash.com/photo-1511379938547-c1f69419868d?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="科技前沿" class="w-14 h-14 rounded" />
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm truncate">科技前沿：AI与人类的未来对话</p>
                <p class="text-xs text-base-content/60">October 15, 2025</p>
                <p class="text-xs text-base-content/50">89k views · 4:52</p>
              </div>
            </div>
          </li>

          <!-- Playlist Item 3 -->
          <li class="p-3 hover:bg-base-200 rounded-lg cursor-pointer transition-colors">
            <div class="flex gap-3 items-center">
              <img src="https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="文学沙龙" class="w-14 h-14 rounded" />
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm truncate">文学沙龙：现代诗歌的魅力与解读</p>
                <p class="text-xs text-base-content/60">October 12, 2025</p>
                <p class="text-xs text-base-content/50">56k views · 6:18</p>
              </div>
            </div>
          </li>

          <!-- Playlist Item 4 -->
          <li class="p-3 hover:bg-base-200 rounded-lg cursor-pointer transition-colors">
            <div class="flex gap-3 items-center">
              <img src="https://images.unsplash.com/photo-1484704849700-f032a568e944?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="电影评论" class="w-14 h-14 rounded" />
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm truncate">电影评论：年度十佳影片深度解析</p>
                <p class="text-xs text-base-content/60">October 10, 2025</p>
                <p class="text-xs text-base-content/50">123k views · 3:47</p>
              </div>
            </div>
          </li>

          <!-- Playlist Item 5 -->
          <li class="p-3 hover:bg-base-200 rounded-lg cursor-pointer transition-colors">
            <div class="flex gap-3 items-center">
              <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=100&q=80" alt="生活美学" class="w-14 h-14 rounded" />
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm truncate">生活美学：打造属于你的理想空间</p>
                <p class="text-xs text-base-content/60">October 8, 2025</p>
                <p class="text-xs text-base-content/50">78k views · 3:25</p>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

