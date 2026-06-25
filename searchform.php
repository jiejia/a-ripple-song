<?php

/**
 * Search form template loaded by get_search_form().
 *
 * @link https://developer.wordpress.org/reference/functions/get_search_form/
 */
?>
<form role="search" method="get" class="search-form rounded-lg bg-base-100 p-4 mb-4" action="<?php echo esc_url(home_url('/')); ?>" data-swup-form data-swup-animation="overlay">
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
      <input type="search" placeholder="<?php echo esc_attr_x('Search &hellip;', 'placeholder', 'a-ripple-song'); ?>"
        value="<?php echo esc_attr(get_search_query()); ?>" name="s">
    </label>
    <a class="btn btn-square bg-base-100" href="<?php echo esc_url(get_feed_link()); ?>" target="_blank">
      <i data-lucide="Rss" class="w-4 h-4"></i>
    </a>
  </div>
</form>
