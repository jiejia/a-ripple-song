{{-- Leftbar Drawer - Shows on screens where leftbar is hidden (below lg) --}}
<div class="drawer drawer-start z-[100]" id="leftbar-drawer-container">
  <input type="checkbox" id="leftbar-drawer" class="drawer-toggle" />
  <div class="drawer-side">
    <label for="leftbar-drawer" aria-label="{{ __('Close', 'a-ripple-song') }}" class="drawer-overlay"></label>
    <div class="bg-base-100 min-h-full w-72 max-w-[85vw]">
      {{-- Header --}}
      <div class="sticky top-0 bg-base-100 p-4 border-b border-base-300 flex items-center justify-between z-10">
        <h3 class="font-bold text-lg">{!! __('Left Sidebar', 'a-ripple-song') !!}</h3>
        <button type="button" class="btn btn-sm btn-circle btn-ghost" aria-label="{{ __('Close', 'a-ripple-song') }}"
          aria-controls="leftbar-drawer" onclick="document.getElementById('leftbar-drawer').checked = false;">
          <i data-lucide="x" class="w-4 h-4" aria-hidden="true"></i>
        </button>
      </div>
      
      {{-- Leftbar Content --}}
      <div class="p-4">
        @if(is_active_sidebar(\App\Theme::SIDEBAR_LEFTBAR))
          @php(dynamic_sidebar(\App\Theme::SIDEBAR_LEFTBAR))
        @else
          <div class="rounded-lg bg-base-200 p-4 text-center text-base-content/50">
            <p>{!! __('Please add widgets to "Leftbar" area in Appearance > Widgets in the admin panel.', 'a-ripple-song') !!}</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
