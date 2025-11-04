<article class="mt-4 rounded-lg bg-base-100 p-4">
  <div class="grid grid-flow-row gap-2">
    <div class="grid grid-flow-row gap-1">
      <h4 class="text-md font-bold"><a href="{{ get_permalink() }}">{!! $title !!}</a></h4>
      @include('partials.entry-meta')
    </div>
    <div class="text-base text-base-content/80 leading-relaxed text-sm" id="content">
      @php(the_excerpt())
    </div>
    <div class="grid grid-flow-row gap-2 mt-2">
      <ul class="flex gap-2 justify-center">
        <li>
          <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Company</a>
        </li>
        <li>
          <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Technology</a>
        </li>
        <li>
          <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Product</a>
        </li>
      </ul>
    </div>
    @include('partials.entry-authors')
  </div>
  <div class="mt-4 rounded-lg bg-base-100 p-4">
  </div>
</article>