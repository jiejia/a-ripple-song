@if (! post_password_required())
<section id="comments" class="comments">
  @if ($responses())
  <h2 class="text-sm font-bold mb-4 flex items-center gap-2">
    <i data-lucide="message-circle" class="w-4 h-4"></i>
    {!! $title !!}
  </h2>

  <ol class="comment-list space-y-4">
    {!! $responses !!}
  </ol>

  @if ($paginated())
  <nav aria-label="Comment" class="mt-4">
    <ul class="flex gap-2 justify-center text-xs">
      @if ($previous())
      <li class="previous">
        <div class="btn btn-xs btn-outline gap-1">
          <i data-lucide="chevron-left" class="w-3 h-3"></i>
          {!! $previous !!}
        </div>
      </li>
      @endif

      @if ($next())
      <li class="next">
        <div class="btn btn-xs btn-outline gap-1">
          {!! $next !!}
          <i data-lucide="chevron-right" class="w-3 h-3"></i>
        </div>
      </li>
      @endif
    </ul>
  </nav>
  @endif
  @endif

  @if ($closed())
  <div class="alert alert-warning rounded-lg mb-6 text-xs">
    <i data-lucide="lock" class="w-4 h-4"></i>
    <span>{!! __('Comments are closed.', 'sage') !!}</span>
  </div>
  @endif

  <div class="bg-base-200/50 rounded-lg p-4">
    <h3 class="text-sm font-bold mb-4 flex items-center gap-2">
      <i data-lucide="pen-line" class="w-4 h-4"></i>
      发表评论
    </h3>
    @php(comment_form())
  </div>
</section>
@endif