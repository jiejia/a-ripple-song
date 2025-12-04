<footer class="text-center text-base-content/70 text-xs bg-base-100/75">
  <div class="max-w-screen-xl mx-auto p-4">
    @php(dynamic_sidebar('sidebar-footer'))
    <div class="grid md:grid-cols-4 grid-cols-2 justify-items-stretch gap-4 mb-2">
      <div class="text-left">
        <h4 class="text-base-content/70 text-lg font-bold">{{ __('Contact', 'sage') }}</h4>
        <ul class="grid grid-flow-row gap-2">
          <li><a href="mailto:hello@aripplesong.com">hello@aripplesong.com</a></li>
          <li><span>San Francisco, CA</span></li>
        </ul>
      </div>
      <div class="text-left">
        <h4 class="text-base-content/70 text-lg font-bold">{{ __('Navigate', 'sage') }}</h4>
        <ul class="grid grid-flow-row gap-2">
          <li><a href="{{ home_url('/') }}">{{ __('Home', 'sage') }}</a></li>
          <li><a href="{{ home_url('/episodes') }}">{{ __('Episodes', 'sage') }}</a></li>
          <li><a href="{{ home_url('/about') }}">{{ __('About Us', 'sage') }}</a></li>
          <li><a href="{{ home_url('/contact') }}">{{ __('Contact', 'sage') }}</a></li>
        </ul>
      </div>
      <div class="text-left">
        <h4 class="text-base-content/70 text-lg font-bold">{{ __('Support', 'sage') }}</h4>
        <ul class="grid grid-flow-row gap-2">
          <li><a href="{{ home_url('/faq') }}">{{ __('FAQ', 'sage') }}</a></li>
          <li><a href="{{ home_url('/privacy-policy') }}">{{ __('Privacy Policy', 'sage') }}</a></li>
          <li><a href="{{ home_url('/terms') }}">{{ __('Terms of Service', 'sage') }}</a></li>
        </ul>
      </div>
      <div class="text-left">
        <h4 class="text-base-content/70 text-lg font-bold">{{ __('Listen On', 'sage') }}</h4>
        <ul class="grid grid-flow-row gap-2">
          <li><a href="https://podcasts.apple.com" target="_blank" rel="noopener">Apple Podcasts</a></li>
          <li><a href="https://open.spotify.com" target="_blank" rel="noopener">Spotify</a></li>
          <li><a href="https://podcasts.google.com" target="_blank" rel="noopener">Google Podcasts</a></li>
          <li><a href="https://overcast.fm" target="_blank" rel="noopener">Overcast</a></li>
          <li><a href="{{ home_url('/feed/podcast') }}">{{ __('RSS Feed', 'sage') }}</a></li>
        </ul>
      </div>
    </div>

    <div class="grid md:grid-cols-2 grid-flow-row gap-2 md:justify-between border-t-1 border-dotted border-base-content/25 pt-2">
      <div class="md:justify-self-start">{!! sprintf(__('© %s A Ripple Song. Designed by %s', 'sage'), '2025', '<a href="https://github.com/jiejia/a-ripple-song" target="_blank" class="text-primary">Jamie</a>') !!}</div>
      @php($socialLinks = \App\Customizer\SocialLinks::getConfiguredLinks())
      @if(!empty($socialLinks))
        <div class="md:justify-self-end">
          <ul class="flex justify-center gap-2">
            @foreach($socialLinks as $key => $social)
              <li>
                <a href="{{ $social['url'] }}" target="_blank" rel="noopener" title="{{ $social['label'] }}">
                  <i data-lucide="{{ $social['icon'] }}" class="w-4 h-4"></i>
                </a>
              </li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>
  </div>
</footer>