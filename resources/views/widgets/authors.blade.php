{{--
Authors Widget Template
@param string $members_title
@param string $guests_title
@param bool $show_members
@param bool $show_guests
@param array $members
@param array $contributors
@param array $post_counts_by_user
@param array $podcast_counts_by_user
--}}

<div>
  @if($show_members && !empty($members))
    <h3 class="text-sm font-bold text-base-content/50">{{ esc_html($members_title) }}</h3>
    <div class="grid grid-flow-row gap-2 mt-4">
      @foreach($members as $user)
        @php
          $avatar_url = get_avatar_url($user->ID, ['size' => 192]);
          $base_count = (int) ($post_counts_by_user[$user->ID] ?? 0) + (int) ($podcast_counts_by_user[$user->ID] ?? 0);
          $participated_count = function_exists('aripplesong_get_participated_podcast_ids') ? count(aripplesong_get_participated_podcast_ids($user->ID)) : 0;
          $post_count = $base_count + $participated_count;
        @endphp
        <a href="{{ esc_url(get_author_posts_url($user->ID)) }}" class="grid grid-cols-[40px_1fr_40px] items-center gap-2 bg-base-200/50 hover:bg-base-200 rounded-lg p-2">
          <div class="avatar">
            <div class="ring-base-content/50 ring-offset-base-100 w-6 rounded-full ring-1 ring-offset-1">
              <img src="{{ esc_url($avatar_url) }}" alt="{{ esc_attr($user->display_name) }}" />
            </div>
          </div>
          <span class="text-xs">{{ esc_html($user->display_name) }}</span>
          <span class="text-xs text-base-content/50">{{ esc_html($post_count) }}</span>
        </a>
      @endforeach
    </div>
  @endif

  @if($show_guests && !empty($contributors))
    <h3 class="text-sm font-bold text-base-content/50 {{ ($show_members && !empty($members)) ? 'mt-4' : '' }}">{{ esc_html($guests_title) }}</h3>
    <div class="grid grid-flow-row gap-2 mt-4">
      @foreach($contributors as $user)
        @php
          $avatar_url = get_avatar_url($user->ID, ['size' => 192]);
          $base_count = (int) ($post_counts_by_user[$user->ID] ?? 0) + (int) ($podcast_counts_by_user[$user->ID] ?? 0);
          $participated_count = function_exists('aripplesong_get_participated_podcast_ids') ? count(aripplesong_get_participated_podcast_ids($user->ID)) : 0;
          $post_count = $base_count + $participated_count;
        @endphp
        <a href="{{ esc_url(get_author_posts_url($user->ID)) }}" class="grid grid-cols-[40px_1fr_40px] items-center gap-2 bg-base-200/50 hover:bg-base-200 rounded-lg p-2">
          <div class="avatar">
            <div class="ring-base-content/50 ring-offset-base-100 w-6 rounded-full ring-1 ring-offset-1">
              <img src="{{ esc_url($avatar_url) }}" alt="{{ esc_attr($user->display_name) }}" />
            </div>
          </div>
          <span class="text-xs">{{ esc_html($user->display_name) }}</span>
          <span class="text-xs text-base-content/50">{{ esc_html($post_count) }}</span>
        </a>
      @endforeach
    </div>
  @endif

  @if((!$show_members || empty($members)) && (!$show_guests || empty($contributors)))
    <div class="text-center py-8">
      <div class="text-base-content/50">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <p class="text-sm font-medium">{{ __('No authors yet', 'sage') }}</p>
        <p class="text-xs mt-1">{{ __('Authors will appear here after adding users', 'sage') }}</p>
      </div>
    </div>
  @endif
</div>
