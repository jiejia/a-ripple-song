@php
  $action = esc_url(home_url('/'));
  $query = get_search_query();
  $placeholder = esc_attr_x('Search &hellip;', 'placeholder', 'a-ripple-song');
  $feed_url = esc_url(get_feed_link());
  $feed_title = esc_attr__('RSS Feed', 'a-ripple-song');
@endphp

<form method="get" class="search-form" action="{{ $action }}" data-swup-form data-swup-animation="overlay">
  <div class="grid grid-cols-[1fr_auto] gap-2">
    <label class="input w-full">
      <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.3-4.3"></path>
        </g>
      </svg>
      <input type="search" name="s" placeholder="{{ $placeholder }}" value="{{ esc_attr($query) }}">
    </label>

    <a class="btn btn-square bg-base-100" href="{{ $feed_url }}" target="_blank" rel="noopener" title="{{ $feed_title }}">
      <i data-lucide="Rss" class="w-4 h-4"></i>
    </a>
  </div>
</form>
