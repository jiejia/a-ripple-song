<!-- <time class="dt-published" datetime="{{ get_post_time('c', true) }}">
  {{ get_the_date() }}
</time>

<p>
  <span>{{ __('By', 'sage') }}</span>
  <a href="{{ get_author_posts_url(get_the_author_meta('ID')) }}" class="p-author h-card">
    {{ get_the_author() }}
  </a>
</p> -->


@php
  $meta_post_id = $post_id ?? get_the_ID();
@endphp

<p class="text-xs text-base-content/50">
  <time class="dt-published" datetime="{{ get_post_time('c', true, $meta_post_id) }}">
    {{ get_localized_date($meta_post_id) }}
  </time>
</p>