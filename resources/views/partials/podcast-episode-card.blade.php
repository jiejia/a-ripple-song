{{--
  播客节目卡片组件
  
  参数:
  - $post_id: 文章ID
  - $audio_file: 音频文件URL
  - $episode_data: 节目数据（JSON格式）
  - $title: 标题
  - $show_link: 是否显示标题链接（默认 true）
--}}

@php
    $show_link = $show_link ?? true;
@endphp

<div class="bg-base-200/50 rounded-lg hover:bg-base-200">
    <div class="p-4 grid grid-cols-[60px_1fr_30px] items-center">
        <div>
            <a href="{{ get_permalink($post_id) }}" class="relative block w-10 h-10 rounded-lg overflow-hidden">
                @if(has_post_thumbnail($post_id))
                    <img src="{{ get_the_post_thumbnail_url($post_id, 'thumbnail') }}"
                         alt="{{ get_the_title($post_id) }}"
                         class="w-10 h-10 rounded-md object-cover" />
                    <div class="pointer-events-none absolute inset-0 bg-base-900/30 flex items-center justify-center">
                        <i data-lucide="podcast" class="w-5 h-5 text-base-100"></i>
                    </div>
                @else
                    <div class="w-10 h-10 rounded-md bg-base-300/50 flex items-center justify-center">
                        <i data-lucide="podcast" class="w-5 h-5 text-base-content/70"></i>
                    </div>
                @endif
            </a>
        </div>
        <div class="grid grid-flow-row gap-1">
            <h4 class="text-md font-bold">
                @if($show_link)
                    <a href="{{ get_permalink($post_id) }}">{!! $title !!}</a>
                @else
                    {!! $title !!}
                @endif
            </h4>
            @include('partials.entry-meta')
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
                    :title="$store.player.currentEpisode && $store.player.currentEpisode.id === episode.id && $store.player.isPlaying ? '暂停' : '播放'">
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

