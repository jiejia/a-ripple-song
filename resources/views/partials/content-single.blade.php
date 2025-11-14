<article class="rounded-lg bg-base-100 p-4">
  <div class="grid grid-flow-row gap-2">
    <div class="grid grid-flow-row gap-1">
      <h4 class="text-md font-bold">{!! $title !!}</h4>
      @include('partials.entry-meta')
    </div>
    <div class="text-base text-base-content/80 leading-relaxed text-sm" id="content">
      @php(the_content())
    </div>
    @include('partials.entry-tags')
    @include('partials.entry-authors')
  </div>
</article>
<div class="mt-4 rounded-lg bg-base-100 p-4">
  @php(comments_template())
</div>