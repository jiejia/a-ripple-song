@if (! post_password_required())
<div class="mt-4 rounded-lg bg-base-100 p-4 {{ comments_open() ? '' : 'hidden' }}">
  <section id="comments" class="comments text-sm">
    @if ($responses())
    <h2 class="mb-4 flex items-center gap-2 text-base font-bold">
      <i data-lucide="message-circle" class="h-4 w-4"></i>
      {{ $title() }}
    </h2>

    <ol class="comment-list space-y-4">
      {!! $responses() !!}
    </ol>

    @if ($paginated())
    <nav aria-label="{{ esc_attr__('Comment', 'sage') }}" class="mt-4">
      <ul class="flex justify-center gap-2 text-sm">
        @if ($previous())
        <li class="previous">
          <div class="btn btn-xs btn-outline gap-1">
            <i data-lucide="chevron-left" class="h-3 w-3"></i>
            {!! $previous() !!}
          </div>
        </li>
        @endif

        @if ($next())
        <li class="next">
          <div class="btn btn-xs btn-outline gap-1">
            {!! $next() !!}
            <i data-lucide="chevron-right" class="h-3 w-3"></i>
          </div>
        </li>
        @endif
      </ul>
    </nav>
    @endif
    @endif

    @if ($closed())
    <div class="alert alert-warning mb-6 rounded-lg text-sm">
      <i data-lucide="lock" class="h-4 w-4"></i>
      <span>{!! __('Comments are closed.', 'sage') !!}</span>
    </div>
    @endif

    <div class="mt-4 rounded-lg bg-base-200/50 p-4">
      <h3 class="mb-4 flex items-center gap-2 text-base font-bold">
        <i data-lucide="pen-line" class="h-4 w-4"></i>
        {!! __('Leave a Comment', 'sage') !!}
      </h3>
      @php(comment_form())
    </div>
  </section>
</div>
@endif
