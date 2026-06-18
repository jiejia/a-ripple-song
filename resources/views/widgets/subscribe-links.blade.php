{{--
Subscribe Links Widget Template
@param string $title
@param string $apple_podcast_url
@param string $spotify_url
@param string $youtube_music_url
--}}

<div>
    <h2 class="text-lg font-bold">{{ esc_html($title) }}</h2>

    @if(!empty($apple_podcast_url))
      <a href="{{ esc_url($apple_podcast_url) }}"
         target="_blank"
         rel="noopener noreferrer"
         class="btn bg-gradient-to-r from-gray-600 via-gray-800 to-black btn-sm text-white border-black transition-all duration-500 ease-in-out hover:from-black hover:via-gray-800 hover:to-gray-600">
        <i data-lucide="podcast" class="w-4 h-4"></i>
        Apple Podcast
      </a>
    @endif

    @if(!empty($spotify_url))
      <a href="{{ esc_url($spotify_url) }}"
         target="_blank"
         rel="noopener noreferrer"
         class="btn bg-gradient-to-r from-green-400 via-green-500 to-[#03C755] btn-sm text-white border-[#00b544] transition-all duration-500 ease-in-out hover:from-[#03C755] hover:via-green-500 hover:to-green-400">
        <i data-lucide="music" class="w-4 h-4"></i>
        Spotify
      </a>
    @endif

    @if(!empty($youtube_music_url))
      <a href="{{ esc_url($youtube_music_url) }}"
         target="_blank"
         rel="noopener noreferrer"
         class="btn bg-gradient-to-r from-yellow-300 via-yellow-400 to-[#FEE502] btn-sm text-[#181600] border-[#f1d800] transition-all duration-500 ease-in-out hover:from-[#FEE502] hover:via-yellow-400 hover:to-yellow-300">
        <i data-lucide="youtube" class="w-4 h-4"></i>
        Youtube Music
      </a>
    @endif
</div>
