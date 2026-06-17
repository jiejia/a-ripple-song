{{-- Leftbar Drawer - Shows on screens where leftbar is hidden (below lg) --}}
<div class="drawer drawer-start z-[100]" id="leftbar-drawer-container">
  <input type="checkbox" id="leftbar-drawer" class="drawer-toggle" />
  <div class="drawer-side">
    <label for="leftbar-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
    <div class="bg-base-100 min-h-full w-72 max-w-[85vw]">
      {{-- Header --}}
      <div class="sticky top-0 bg-base-100 p-4 border-b border-base-300 flex items-center justify-between z-10">
        <h3 class="font-bold text-lg">{!! __('Left Sidebar', 'sage') !!}</h3>
        <label for="leftbar-drawer" class="btn btn-sm btn-circle btn-ghost">
          <i data-lucide="x" class="w-4 h-4"></i>
        </label>
      </div>
      
      {{-- Leftbar Content --}}
      <div class="p-4">
        @if(is_active_sidebar(\App\Theme::SIDEBAR_LEFTBAR))
          @php(dynamic_sidebar(\App\Theme::SIDEBAR_LEFTBAR))
        @else
          <div class="rounded-lg bg-base-200 p-4 text-center text-base-content/50">
            <p>{!! __('Please add widgets to "Leftbar" area in Appearance > Widgets in the admin panel.', 'sage') !!}</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

