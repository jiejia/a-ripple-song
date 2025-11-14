<aside class="sidebar sticky top-[70px] lg:block md:block">
    <div class="hidden md:block lg:block">
        @php(get_search_form())
        @if(is_active_sidebar('sidebar-primary'))
        @php(dynamic_sidebar('sidebar-primary'))
        @else
        <div class="rounded-lg bg-base-100 p-4 mt-4 text-center text-base-content/50">
            <p>请在后台的 外观 > 小工具 中添加小工具到"侧边栏"。</p>
        </div>
        @endif
    </div>

    @include('sections.player')

</aside>