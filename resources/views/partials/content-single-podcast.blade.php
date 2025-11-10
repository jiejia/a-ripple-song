@php
    $post_id = get_the_ID();
    $audio_file = get_post_meta($post_id, 'audio_file', true);
    $featured_image = get_the_post_thumbnail_url($post_id, 'medium') ?: 'https://cdn.pixabay.com/photo/2025/10/03/09/14/asters-9870320_960_720.jpg';
    $episode_data = [
        'id' => $post_id,
        'audioUrl' => esc_js($audio_file),
        'title' => esc_js(get_the_title()),
        'description' => esc_js(wp_strip_all_tags(get_the_excerpt())),
        'publishDate' => esc_js(get_the_date()),
        'featuredImage' => esc_url($featured_image),
        'link' => esc_url(get_permalink())
    ];
@endphp

<div class="rounded-lg bg-base-100 p-4">
    <div class="grid grid-flow-row gap-2">
        <div class="bg-base-200/50 rounded-lg hover:bg-base-200">
            <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center">
                <div>
                    <a href="{{ get_permalink() }}" class="block w-10 h-10 rounded-lg overflow-hidden">
                        @if(has_post_thumbnail())
                            <img src="{{ get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') }}" alt="{{ get_the_title() }}" class="w-10 h-10 rounded-md object-cover" />
                        @else
                            <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="科技前沿对话" class="w-10 h-10 rounded-md" />
                        @endif
                    </a>
                </div>
                <div class="grid grid-flow-row gap-1">
                    <h4 class="text-md font-bold">{!! $title !!}</h4>
                    @include('partials.entry-meta')
                </div>
                <div class="flex gap-2">
                    @if($audio_file)
                        <i data-lucide="plus-circle" 
                           class="text-xs h-4 cursor-pointer hover:text-primary transition-colors" 
                           data-episode='@json($episode_data)'
                           onclick="window.playlistManager.addEpisode(JSON.parse(this.dataset.episode));"
                           title="加入播放列表"></i>
                    @endif
                    <i data-lucide="ellipsis-vertical" class="text-xs h-4 cursor-pointer"></i>
                </div>
            </div>
        </div>
        <div class="text-base text-base-content/80 leading-relaxed text-sm" id="content">
            @php(the_content())
        </div>
        @include('partials.entry-tags')
        @include('partials.entry-authors')
    </div>
</div>