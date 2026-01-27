<aside class="sidebar sticky top-[70px] lg:block md:block">
    <div class="hidden md:block lg:block">
        @php(get_search_form())
        @if(is_active_sidebar(\App\Theme::SIDEBAR_PRIMARY))
        @php(dynamic_sidebar(\App\Theme::SIDEBAR_PRIMARY))
        @else
        <div class="rounded-lg bg-base-100 p-4 mt-4 text-center text-base-content/50 mb-4">
            <p>{!! __('Please add widgets to "Sidebar" area in Appearance > Widgets in the admin panel.', 'a-ripple-song') !!}</p>
        </div>
        @endif
    </div>

    @if(function_exists('aripplesong_podcast_features_enabled') && aripplesong_podcast_features_enabled())
        @include('sections.player')
    @endif

</aside>
