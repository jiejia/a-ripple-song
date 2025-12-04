{{-- Sidebar Drawer - Shows on screens where sidebar is hidden (below md) --}}
<div class="drawer drawer-end z-[100]" id="sidebar-drawer-container">
  <input type="checkbox" id="sidebar-drawer" class="drawer-toggle" />
  <div class="drawer-side">
    <label for="sidebar-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
    <div class="bg-base-100 min-h-full w-80 max-w-[90vw]">
      {{-- Header --}}
      <div class="sticky top-0 bg-base-100 p-4 border-b border-base-300 flex items-center justify-between z-10">
        <h3 class="font-bold text-lg">{!! __('Right Sidebar', 'sage') !!}</h3>
        <label for="sidebar-drawer" class="btn btn-sm btn-circle btn-ghost">
          <i data-lucide="x" class="w-4 h-4"></i>
        </label>
      </div>
      
      {{-- Sidebar Content --}}
      <div class="p-4">
        @php(get_search_form())
        @if(is_active_sidebar('sidebar-primary'))
          @php(dynamic_sidebar('sidebar-primary'))
        @else
          <div class="rounded-lg bg-base-200 p-4 text-center text-base-content/50">
            <p>{!! __('Please add widgets to "Sidebar" area in Appearance > Widgets in the admin panel.', 'sage') !!}</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

