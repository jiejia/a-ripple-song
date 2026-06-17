{{--
Blog List Widget Template
@param string $title
@param WP_Query $posts
@param bool $show_see_all
@param int $columns
--}}

<div class="rounded-lg bg-base-100 p-4">
  <div class="grid grid-cols-[1fr_auto] items-center">
    <h2 class="text-lg font-bold">
      {{ esc_html($title) }}
    </h2>
    @if($show_see_all)
      <span class="text-xs text-base-content/70">
        <a href="{{ esc_url(get_permalink(get_page_by_path('blog'))) }}">{{ __('See all', 'sage') }}</a>
      </span>
    @endif
  </div>

  <ul class="grid grid-cols-{{ esc_attr($columns) }} gap-4 gap-y-8 mt-4">
    @if($posts->have_posts())
      @while($posts->have_posts())
        @php($posts->the_post())
        <li class="bg-base-200/50 rounded-lg p-4 hover:bg-base-200">
          <h3 class="text-md font-bold">
            <a href="{{ esc_url(get_permalink()) }}">{{ esc_html(get_the_title()) }}</a>
          </h3>
          <div class="grid grid-flow-row gap-1 mt-2">
            @php($categories = get_the_category())
            @if(!empty($categories))
              <span class="text-xs text-base-content">
                <span>
                  <a href="{{ esc_url(get_category_link($categories[0]->term_id)) }}">
                    {{ esc_html($categories[0]->name) }}
                  </a>
                </span>
              </span>
            @endif

            {!! \Roots\view('partials.entry-meta', ['post_id' => get_the_ID()])->render() !!}
          </div>
        </li>
      @endwhile
      @php(wp_reset_postdata())
    @else
      <li class="col-span-{{ esc_attr($columns) }} text-center text-base-content/50 py-8">
        {{ __('No blog posts yet', 'sage') }}
      </li>
    @endif
  </ul>
</div>
