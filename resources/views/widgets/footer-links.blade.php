{{--
  Footer Links Widget Template
  @param string $title - Widget title
  @param array $items - Array of link items with text, url, and new_tab properties
--}}

@if(empty($items) && empty($title))
  {{-- Empty state - render nothing --}}
@else
  <div class="text-left bg-base-100/60 rounded-lg p-4">
    @if(!empty($title))
      <h4 class="text-base-content/70 text-lg font-bold mb-2">{{ esc_html($title) }}</h4>
    @endif
    
    @if(!empty($items))
      <ul class="grid grid-flow-row gap-2">
        @foreach($items as $item)
          <li>
            @if(!empty($item['url']))
              <a href="{{ esc_url($item['url']) }}"
                 @if(!empty($item['new_tab'])) target="_blank" rel="noopener" @endif>
                {{ esc_html($item['text']) }}
              </a>
            @else
              <span>{{ esc_html($item['text']) }}</span>
            @endif
          </li>
        @endforeach
      </ul>
    @endif
  </div>
@endif

