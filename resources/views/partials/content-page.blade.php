<article class="mt-4 rounded-lg bg-base-100 p-4">
  <div class="grid grid-flow-row gap-2">
    <div class="grid grid-flow-row gap-1">
      <h4 class="text-md font-bold"><a href="{!! get_permalink() !!}">{!! $title !!}</a></h4>
      @include('partials.entry-meta')
    </div>
    <div class="prose max-w-none text-sm text-base-content/80 [&_p]:py-2 [&_img]:mx-auto [&_img]:cursor-pointer [&_img]:rounded-lg [&_img]:shadow-md" id="content">
      @php(the_excerpt())
    </div>
  </div>
  <div class="mt-4 rounded-lg bg-base-100 p-4">
  </div>
</article>