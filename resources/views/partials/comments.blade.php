@if (! post_password_required())
  <section id="comments" class="comments mt-8">
    @if ($responses())
      <div class="bg-base-200 rounded-lg p-6 mb-6">
        <h2 class="text-md font-bold mb-6 flex items-center gap-2">
          <i data-lucide="message-circle" class="w-4 h-4"></i>
          {!! $title !!}
        </h2>

        <ol class="comment-list space-y-4">
          {!! $responses !!}
        </ol>

        @if ($paginated())
          <nav aria-label="Comment" class="mt-6">
            <ul class="flex gap-3 justify-center text-xs">
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
      </div>
    @endif

    @if ($closed())
      <div class="alert alert-warning rounded-lg mb-6 text-xs">
        <i data-lucide="lock" class="w-4 h-4"></i>
        <span>{!! __('Comments are closed.', 'sage') !!}</span>
      </div>
    @endif

    <div class="bg-base-100 rounded-lg p-6 shadow-sm">
      <h3 class="text-md font-bold mb-4 flex items-center gap-2">
        <i data-lucide="pen-line" class="w-4 h-4"></i>
        发表评论
      </h3>
      @php(comment_form())
    </div>
  </section>
@endif
