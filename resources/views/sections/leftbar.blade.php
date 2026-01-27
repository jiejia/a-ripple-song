<aside class="sticky top-[70px] hidden lg:block md:hidden">
  @if(is_active_sidebar(\App\Theme::SIDEBAR_LEFTBAR))
    @php(dynamic_sidebar(\App\Theme::SIDEBAR_LEFTBAR))
  @else
    <div class="rounded-lg bg-base-100 p-4 text-center text-base-content/50">
      <p>{!! __('Please add widgets to "Leftbar" area in Appearance > Widgets in the admin panel.', 'a-ripple-song') !!}</p>
    </div>
  @endif
</aside>