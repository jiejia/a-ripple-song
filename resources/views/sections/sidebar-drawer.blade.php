{{-- Sidebar Drawer - Shows on screens where sidebar is hidden (below md) --}}
<div class="drawer drawer-end z-[100]" id="sidebar-drawer-container">
  <input type="checkbox" id="sidebar-drawer" class="drawer-toggle" />
  <div class="drawer-side">
    <label for="sidebar-drawer" aria-label="{{ __('Close', 'a-ripple-song') }}" class="drawer-overlay"></label>
    <div class="bg-base-100 min-h-full w-80 max-w-[90vw]">
      {{-- Header --}}
      <div class="sticky top-0 bg-base-100 p-4 border-b border-base-300 flex items-center justify-between z-10">
        <h3 class="font-bold text-lg">{!! __('Right Sidebar', 'a-ripple-song') !!}</h3>
        <button type="button" class="btn btn-sm btn-circle btn-ghost" aria-label="{{ __('Close', 'a-ripple-song') }}"
          aria-controls="sidebar-drawer" onclick="document.getElementById('sidebar-drawer').checked = false;">
          <i data-lucide="x" class="w-4 h-4" aria-hidden="true"></i>
        </button>
      </div>
      
      {{-- Sidebar Content --}}
      <div class="p-4">
        @php(get_search_form())
        @if(is_active_sidebar(\App\Theme::SIDEBAR_PRIMARY))
          @php(dynamic_sidebar(\App\Theme::SIDEBAR_PRIMARY))
        @else
          <div class="rounded-lg bg-base-200 p-4 text-center text-base-content/50">
            <p>{!! __('Please add widgets to "Sidebar" area in Appearance > Widgets in the admin panel.', 'a-ripple-song') !!}</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
