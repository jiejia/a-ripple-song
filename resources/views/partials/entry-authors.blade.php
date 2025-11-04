@php
    $author_ids = get_post_all_authors(get_the_ID());
@endphp

@if (!empty($author_ids))
<div class="avatar-group -space-x-2 mt-2 justify-center">
    @foreach($author_ids as $author_id)
        @php
            $author = get_userdata($author_id);
        @endphp
        @if ($author)
            <div class="avatar">
                <a href="{{ get_author_posts_url($author->ID) }}" class="w-6 block" title="{{ esc_attr($author->display_name) }}">
                    <img src="{{ get_avatar_url($author->ID, ['size' => 96]) }}" alt="{{ esc_attr($author->display_name) }}" />
                </a>
            </div>
        @endif
    @endforeach
</div>
@endif