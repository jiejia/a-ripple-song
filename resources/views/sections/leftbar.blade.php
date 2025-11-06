<aside class="sticky top-[70px]">
  @php
  $members = get_users([
  'role__in' => ['administrator', 'editor', 'author'],
  'orderby' => 'display_name',
  'order' => 'ASC',
  ]);

  $contributors = get_users([
  'role' => 'contributor',
  'orderby' => 'display_name',
  'order' => 'ASC',
  ]);
  @endphp
  <div class="bg-base-100 rounded-lg p-4">
    <h3 class="text-sm font-bold text-base-content/50">Members</h3>
    <div class="grid grid-flow-row gap-2 mt-4">
      @foreach($members as $user)
      @php
      $avatar_url = get_avatar_url($user->ID, ['size' => 192]);
      $post_count = calculate_user_post_count($user->ID);
      @endphp
      <a href="{{ get_author_posts_url($user->ID) }}" class="grid grid-cols-[40px_1fr_40px] items-center gap-2 bg-base-200/50 hover:bg-base-200 rounded-lg p-2">
        <div class="avatar">
          <div class="ring-base-content/50 ring-offset-base-100 w-6 rounded-full ring-1 ring-offset-1">
            <img src="{{ $avatar_url }}" alt="{{ $user->display_name }}" />
          </div>
        </div>
        <span class="text-xs">{{ $user->display_name }}</span>
        <span class="text-xs text-base-content/50">{{ $post_count }}</span>
      </a>
      @endforeach
    </div>
    <h3 class="text-sm font-bold text-base-content/50 mt-4">Guests</h3>
    <div class="grid grid-flow-row gap-2 mt-4">
      @foreach($contributors as $user)
      @php
      $avatar_url = get_avatar_url($user->ID, ['size' => 192]);
      $post_count = calculate_user_post_count($user->ID);
      @endphp
      <a href="{{ get_author_posts_url($user->ID) }}" class="grid grid-cols-[40px_1fr_40px] items-center gap-2 bg-base-200/50 hover:bg-base-200 rounded-lg p-2">
        <div class="avatar">
          <div class="ring-base-content/50 ring-offset-base-100 w-6 rounded-full ring-1 ring-offset-1">
            <img src="{{ $avatar_url }}" alt="{{ $user->display_name }}" />
          </div>
        </div>
        <span class="text-xs">{{ $user->display_name }}</span>
        <span class="text-xs text-base-content/50">{{ $post_count }}</span>
      </a>
      @endforeach
    </div>
  </div>

</aside>