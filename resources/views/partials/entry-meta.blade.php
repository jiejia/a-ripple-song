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

<div
  x-data="{ metricsReady: false }"
  x-init="metricsReady = window.aripplesongMetricsReady === true; window.addEventListener('aripplesong:metrics:ready', () => { metricsReady = true; })"
>
  <div x-show="!metricsReady" class="flex items-center gap-2" aria-hidden="true">
    <span class="skeleton h-3 w-24"></span>
    <span class="skeleton h-3 w-16"></span>
    <span class="skeleton h-3 w-20"></span>
  </div>

  <p x-cloak x-show="metricsReady" class="text-xs text-base-content/50">
    <time class="dt-published" datetime="{{ get_post_time('c', true, $meta_post_id) }}">
      {{ get_localized_date($meta_post_id) }}
    </time>
    @php $meta_post_type = get_post_type($meta_post_id); @endphp
    @php $podcast_post_type = function_exists('aripplesong_get_podcast_post_type') ? aripplesong_get_podcast_post_type() : null; @endphp
    <span class="ml-2">
      · <span class="js-views-count" data-post-id="{{ $meta_post_id }}" data-post-type="{{ $meta_post_type }}">--</span> {{ __('views', 'sage') }}
      @if ($podcast_post_type && $meta_post_type === $podcast_post_type)
        · <span class="js-play-count" data-post-id="{{ $meta_post_id }}">--</span> {{ __('plays', 'sage') }}
      @endif
    </span>
  </p>
</div>
