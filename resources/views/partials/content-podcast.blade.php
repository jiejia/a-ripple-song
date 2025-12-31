@php
$post_id = get_the_ID();
$audio_file = get_post_meta($post_id, 'audio_file', true);
$episode_data = get_episode_data($post_id);
@endphp

<div class="mb-4 rounded-lg bg-base-100 p-4" x-data="{ episode: @js($episode_data) }">
    <div class="grid grid-flow-row gap-2">
        @include('partials.podcast-episode-card', [
        'post_id' => $post_id,
        'audio_file' => $audio_file,
        'episode_data' => $episode_data,
        'title' => $title,
        'show_link' => true
        ])
        <div class="prose max-w-none text-sm text-base-content/80 [&_p]:py-2 [&_img]:mx-auto [&_img]:cursor-pointer [&_img]:rounded-lg [&_img]:shadow-md" id="content">
            @php(the_excerpt())
        </div>
        @include('partials.entry-tags')
        @include('partials.entry-authors')
    </div>
</div>