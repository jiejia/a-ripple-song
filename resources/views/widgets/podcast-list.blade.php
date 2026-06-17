{{--
Podcast List Widget Template
@param string $title
@param bool $show_see_all
@param array $podcast_data
--}}

<div class="rounded-lg bg-base-100 p-4"
     x-data="{
       activeTab: 'recent',
       podcastData: @json($podcast_data)
     }">
  <div class="grid grid-cols-[1fr_auto] items-center">
    <h2 class="text-lg font-bold">
      {{ esc_html($title) }}
    </h2>
    @if($show_see_all)
      <span class="text-xs text-base-content/70">
        <a href="{{ esc_url(get_permalink(get_page_by_path('podcasts'))) }}">{{ __('See all', 'sage') }}</a>
      </span>
    @endif
  </div>

  <ul class="flex gap-2 mt-2">
    <li>
      <button
        @click="activeTab = 'recent'"
        :class="activeTab === 'recent' ? 'bg-base-200' : 'bg-base-100'"
        class="btn rounded-full btn-sm">
        {{ __('Recent', 'sage') }}
      </button>
    </li>
    <li>
      <button
        @click="activeTab = 'popular'"
        :class="activeTab === 'popular' ? 'bg-base-200' : 'bg-base-100'"
        class="btn rounded-full btn-sm">
        {{ __('Popular', 'sage') }}
      </button>
    </li>
    <li>
      <button
        @click="activeTab = 'random'"
        :class="activeTab === 'random' ? 'bg-base-200' : 'bg-base-100'"
        class="btn rounded-full btn-sm">
        {{ __('Random', 'sage') }}
      </button>
    </li>
  </ul>

  @foreach(['recent', 'popular', 'random'] as $tab)
    <ul class="grid grid-flow-row gap-y-4 mt-4" x-show="activeTab === '{{ $tab }}'" @if($tab !== 'recent') style="display: none;" @endif>
      @if(!empty($podcast_data[$tab]))
        @foreach($podcast_data[$tab] as $podcast)
          <li x-data="{ episode: @json($podcast['episode_data']) }">
            {!! \Roots\view('partials.podcast-episode-card', [
              'post_id' => $podcast['post_id'],
              'audio_file' => $podcast['audio_file'],
              'episode_data' => $podcast['episode_data'],
              'title' => $podcast['title'],
              'show_link' => true,
            ])->render() !!}
          </li>
        @endforeach
      @else
        <li class="text-center text-base-content/50 py-8">{{ __('No podcast content', 'sage') }}</li>
      @endif
    </ul>
  @endforeach
</div>
