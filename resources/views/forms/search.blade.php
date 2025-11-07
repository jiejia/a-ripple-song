<form role="search" method="get" class="search-form" action="{{ home_url('/') }}">
  <div class="grid grid-cols-[1fr_auto] gap-2">
    <label class="input w-full">
      <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g
          stroke-linejoin="round"
          stroke-linecap="round"
          stroke-width="2.5"
          fill="none"
          stroke="currentColor">
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.3-4.3"></path>
        </g>
      </svg>
      <input type="search" placeholder="{!! esc_attr_x('Search &hellip;', 'placeholder', 'sage') !!}"
        value="{{ get_search_query() }}" name="s">
    </label>
    <a class="btn btn-square bg-base-100" href="{{ get_feed_link() }}" target="_blank">
      <i data-lucide="Rss" class="w-4 h-4"></i>
    </a>
  </div>
</form>