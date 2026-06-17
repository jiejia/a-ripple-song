<footer class="text-center text-base-content/70 text-xs">
  <div class="max-w-screen-xl mx-auto p-4 pt-0">
    @php($footerCopyright = \App\Customizers\Copyright::getCopyright())
    @php($defaultCopyright = sprintf(__('Powered by %s', 'sage'), '<a href="https://github.com/jiejia/a-ripple-song" target="_blank" class="text-primary">A Ripple Song</a> Theme'))
    @php($copyrightYear = sprintf('© %s', date_i18n('Y')))
    @if(is_active_sidebar(\App\Theme::SIDEBAR_FOOTER_LINKS))
      <div class="grid md:[grid-template-columns:repeat(auto-fit,minmax(calc(25%-0.75rem),1fr))] grid-cols-2 justify-items-stretch gap-4 mb-4">
        @php(dynamic_sidebar(\App\Theme::SIDEBAR_FOOTER_LINKS))
      </div>
    @endif

    <div class="grid md:grid-cols-2 grid-flow-row gap-2 md:justify-between bg-base-100/60 rounded-lg p-4">
      <div class="md:justify-self-start">{!! wp_kses_post($copyrightYear . ' ' . ($footerCopyright ?: $defaultCopyright)) !!}</div>
      @php($socialLinks = \App\Customizers\SocialLinks::getConfiguredLinks())
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