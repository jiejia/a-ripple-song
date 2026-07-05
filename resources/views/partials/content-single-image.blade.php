<article class="rounded-lg bg-base-100 p-4">
  @php
    $attachmentMetadata = wp_get_attachment_metadata(get_the_ID()) ?: [];
    $attachmentWidth = (int) ($attachmentMetadata['width'] ?? 0);
    $attachmentHeight = (int) ($attachmentMetadata['height'] ?? 0);
  @endphp
  <div class="grid grid-flow-row gap-2">
    <div class="grid grid-flow-row gap-1">
      <h4 class="text-md font-bold">{{ $title }}</h4>
      @include('partials.entry-meta')
    </div>
    <div class=" max-w-none text-sm text-base-content/80 [&_p]:py-2 [&_img]:mx-auto [&_img]:cursor-pointer [&_img]:rounded-lg [&_img]:shadow-md" id="content">
      @if(has_excerpt())
      <div class="prose max-w-none">{!! wp_kses_post(wpautop(get_the_excerpt())) !!}</div>
      @endif
      <img src="{{ esc_url(wp_get_attachment_url()) }}" alt="{{ get_the_title() }}"
        @if($attachmentWidth > 0) width="{{ $attachmentWidth }}" @endif
        @if($attachmentHeight > 0) height="{{ $attachmentHeight }}" @endif
        class="w-full h-auto rounded-lg shadow-md">
    </div>
    @include('partials.entry-authors')
  </div>
</article>
@php(comments_template())
