<article class="rounded-lg bg-base-100 p-4">
  <div class="grid grid-flow-row gap-2">
    <div class="grid grid-flow-row gap-1">
      <h4 class="text-md font-bold">{!! $title !!}</h4>
      @include('partials.entry-meta')
    </div>
    <div class="prose prose-sm max-w-none text-xs text-base-content/80 [&_p]:py-2 [&_img]:mx-auto [&_img]:cursor-pointer [&_img]:rounded-lg [&_img]:shadow-md" id="content">
      @if(has_excerpt())
      <div class="prose max-w-none">{!! wpautop(get_the_excerpt()) !!}</div>
      @endif
      <a href="{{ wp_get_attachment_url() }}" download class="btn btn-primary btn-sm" target="_blank">
        <i data-lucide="download" class="w-4 h-4"></i>
        {{ __('Download', 'sage') }}
      </a>
    </div>
    @include('partials.entry-authors')
  </div>
</article>
<div class="mt-4 rounded-lg bg-base-100 p-4">
  @php(comments_template())
</div>