<aside class="sticky top-[70px] hidden lg:block md:hidden">
  @if(is_active_sidebar('leftbar-primary'))
    @php(dynamic_sidebar('leftbar-primary'))
  @else
    <div class="rounded-lg bg-base-100 p-4 text-center text-base-content/50">
      <p>请在后台的 外观 > 小工具 中添加小工具到"左侧栏"。</p>
    </div>
  @endif
</aside>