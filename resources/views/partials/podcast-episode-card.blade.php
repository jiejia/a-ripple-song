{{--
  Podcast episode card component

  Params:
  - $post_id: Post ID
  - $audio_file: Audio file URL
  - $episode_data: Episode data (JSON)
  - $title: Title
  - $show_link: Whether to show title link (default true)
--}}

@php
    $show_link = $show_link ?? true;
@endphp

<div class="bg-base-200/50 rounded-lg hover:bg-base-200">
    <div class="p-4 grid grid-cols-[95px_1fr_30px] items-center">
        <div>
            <a href="{{ get_permalink($post_id) }}" class="relative block w-20 h-20 rounded-lg overflow-hidden">
                @if(has_post_thumbnail($post_id))
                    <img src="{{ get_the_post_thumbnail_url($post_id, 'thumbnail') }}"
                         alt="{{ get_the_title($post_id) }}"
                         class="w-20 h-20 rounded-md object-cover" />
                    <div class="pointer-events-none absolute inset-0 bg-base-900/30 flex items-center justify-center">
                        <i data-lucide="podcast" class="w-5 h-5 text-base-100"></i>
                    </div>
                @else
                    <div class="w-20 h-20 rounded-md bg-base-300/50 flex items-center justify-center">
                        <i data-lucide="podcast" class="w-5 h-5 text-base-content/70"></i>
                    </div>
                @endif
            </a>
        </div>
        <div class="grid grid-flow-row gap-1 overflow-hidden">
            <h4 class="text-md font-bold line-clamp-2">
                @if($show_link)
                    <a href="{{ get_permalink($post_id) }}">{{ $title }}</a>
                @else
                    {{ $title }}
                @endif
            </h4>
            @include('partials.entry-meta', ['post_id' => $post_id])
        </div>
        <div class="flex gap-2">
            @if($audio_file)
                <button type="button"
                    @click="
                        if ($store.player.currentEpisode && $store.player.currentEpisode.id === episode.id) {
                            if ($store.player.isPlaying) {
                                $store.player.pause();
                            } else {
                                $store.player.play();
                            }
                        } else {
                            $store.player.addEpisode(episode);
                        }
                    "
                    class="cursor-pointer hover:text-primary transition-colors"
                    :title="$store.player.currentEpisode && $store.player.currentEpisode.id === episode.id && $store.player.isPlaying ? '{{ __('Pause', 'sage') }}' : '{{ __('Play', 'sage') }}'">
                    <i data-lucide="pause" 
                       class="text-xs h-4"
                       x-show="$store.player.currentEpisode && $store.player.currentEpisode.id === episode.id && $store.player.isPlaying"></i>
                    <i data-lucide="play" 
                       class="text-xs h-4"
                       x-show="!($store.player.currentEpisode && $store.player.currentEpisode.id === episode.id && $store.player.isPlaying)"></i>
                </button>
            @endif
            <!-- <i data-lucide="ellipsis-vertical" class="text-xs h-4 cursor-pointer"></i> -->
        </div>
    </div>
</div>
