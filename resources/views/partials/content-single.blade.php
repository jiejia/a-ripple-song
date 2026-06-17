<article class="rounded-lg bg-base-100 p-4">
  <div class="grid grid-flow-row gap-2">
    <div class="grid grid-flow-row gap-1">
      <h4 class="text-md font-bold">{{ $title }}</h4>
      @include('partials.entry-meta')
    </div>
    <div class="max-w-none text-sm text-base-content/80 [&_p]:py-2 [&_img]:mx-auto [&_img]:cursor-pointer [&_img]:rounded-lg [&_img]:shadow-md" id="content">
      @php(the_content())
    </div>
    @include('partials.entry-tags')
    @include('partials.entry-authors')
  </div>
</article>
<div class="mt-4 rounded-lg bg-base-100 p-4">
  @php(comments_template())
</div>
