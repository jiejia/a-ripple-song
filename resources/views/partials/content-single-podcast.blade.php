@php
    $post_id = get_the_ID();
    $audio_file = get_post_meta($post_id, 'audio_file', true);
    $episode_data = get_episode_data($post_id);
@endphp

<div class="rounded-lg bg-base-100 p-4" x-data="{ episode: @js($episode_data) }">
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
                        <button type="button" 
                                @click="$store.player.addEpisode(episode)"
                                class="cursor-pointer hover:text-primary transition-colors"
                                title="加入播放列表">
                            <i data-lucide="plus-circle" class="text-xs h-4"></i>
                        </button>
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
    <div class="mt-4 rounded-lg bg-base-100 p-4">
    @php(comments_template())
  </div>
</div>