<div class="drawer drawer-start z-[101]" id="swup-mobile-menu">
  <input type="checkbox" id="mobile-menu" class="drawer-toggle" />
  <div class="drawer-side">
    <label for="mobile-menu" aria-label="close sidebar" class="drawer-overlay"></label>
    <div class="bg-base-100 h-full w-80 max-w-xs">
      <div class="sticky top-0 bg-base-100 p-4 border-b border-base-300 flex items-center justify-between">
        <h3 class="font-bold text-lg">{!! __('Menu', 'sage') !!}</h3>
        <label for="mobile-menu" class="btn btn-sm btn-circle btn-ghost">✕</label>
      </div>
      
      <ul class="menu p-4 w-full">
        @php
          $menu_items = get_primary_navigation_menu_items();
          $current_url = home_url($_SERVER['REQUEST_URI']);
        @endphp
        
        @foreach ($menu_items as $menu_data)
          @php
            $item = $menu_data['item'];
            $children = $menu_data['children'];
            $has_children = !empty($children);
            
            // Check if this item itself (not children) is the current page
            $is_current_page = $item->url === $current_url;
            
            // Check if menu item is active (current page or has active child)
            $is_active = is_menu_item_active($item, $children, $current_url);
            
            // Set CSS classes based on active state
            $active_class = $is_active ? 'active font-semibold' : '';
          @endphp
          
          @if ($has_children)
            <li>
              <details open>
                <summary class="flex justify-between items-center {{ $is_current_page ? 'bg-base-200/50' : '' }}">
                  <a href="{{ $item->url }}" class="flex-1 {{ $active_class }}" onclick="event.stopPropagation()">
                    {{ $item->title }}
                  </a>
                </summary>
                <ul>
                  @foreach ($children as $child_data)
                    @php
                      $child = $child_data['item'];
                      $grandchildren = $child_data['children'];
                      $has_grandchildren = !empty($grandchildren);
                      
                      // Check if this child itself (not grandchildren) is the current page
                      $child_is_current_page = $child->url === $current_url;
                      
                      $child_is_active = is_menu_item_active($child, $grandchildren, $current_url);
                      $child_active_class = $child_is_active ? 'active font-semibold' : '';
                    @endphp
                    
                    @if ($has_grandchildren)
                      <li>
                        <details open>
                          <summary class="flex justify-between items-center {{ $child_is_current_page ? 'bg-base-200/50' : '' }}">
                            <a href="{{ $child->url }}" class="flex-1 {{ $child_active_class }}" onclick="event.stopPropagation()">
                              {{ $child->title }}
                            </a>
                          </summary>
                          <ul>
                            @foreach ($grandchildren as $grandchild_data)
                              @php
                                $grandchild = $grandchild_data['item'];
                                $grandchild_is_active = $grandchild->url === $current_url;
                                $grandchild_active_class = $grandchild_is_active ? 'active font-semibold' : '';
                              @endphp
                              <li><a href="{{ $grandchild->url }}" class="{{ $grandchild_active_class }} {{ $grandchild_is_active ? 'bg-base-200/50' : '' }}">{{ $grandchild->title }}</a></li>
                            @endforeach
                          </ul>
                        </details>
                      </li>
                    @else
                      <li><a href="{{ $child->url }}" class="{{ $child_active_class }} {{ $child_is_active ? 'bg-base-200/50' : '' }}">{{ $child->title }}</a></li>
                    @endif
                  @endforeach
                </ul>
              </details>
            </li>
          @else
            <li><a href="{{ $item->url }}" class="{{ $active_class }} {{ $is_current_page ? 'bg-base-200/50' : '' }}">{{ $item->title }}</a></li>
          @endif
        @endforeach
      </ul>
    </div>
  </div>
</div>