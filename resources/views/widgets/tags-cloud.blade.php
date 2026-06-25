{{--
Tags Cloud Widget Template
@param string $title
@param array $tags
--}}

<div>
    <h2 class="text-lg font-bold">{{ esc_html($title) }}</h2>

    @if(empty($tags))
      <div class="text-center py-8">
        <div class="text-base-content/50">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
          </svg>
          <p class="text-sm font-medium">{{ __('No tags yet', 'a-ripple-song') }}</p>
          <p class="text-xs mt-1">{{ __('Tags will appear here after publishing articles with tags', 'a-ripple-song') }}</p>
        </div>
      </div>
    @else
      <ul class="mt-0 flex flex-wrap gap-2 text-xs text-base-content/75">
        @foreach($tags as $tag)
          <li>
            <a href="{{ esc_url(get_tag_link($tag->term_id)) }}"
               class="bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2 transition-colors"
               title="{{ esc_attr(sprintf(_n('%d post', '%d posts', $tag->count, 'a-ripple-song'), $tag->count)) }}">
              # {{ esc_html($tag->name) }}
            </a>
          </li>
        @endforeach
      </ul>
    @endif
</div>
