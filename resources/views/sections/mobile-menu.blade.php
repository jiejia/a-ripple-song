<input type="checkbox" id="mobile-menu" class="modal-toggle" />
<div class="modal modal-start" role="dialog">
  <div class="modal-box rounded-none h-full w-80 max-w-xs p-0">
    <div class="sticky top-0 bg-base-100 p-4 border-b border-base-300 flex items-center justify-between">
      <h3 class="font-bold text-lg">菜单</h3>
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
          
          // Check if menu item is active (current page or has active child)
          $is_active = is_menu_item_active($item, $children, $current_url);
          
          // Set CSS classes based on active state
          $active_class = $is_active ? 'active font-semibold' : '';
        @endphp
        
        @if ($has_children)
          <li>
            <details open>
              <summary class="{{ $active_class }} flex justify-between items-center">
                <a href="{{ $item->url }}" class="flex-1" onclick="event.stopPropagation()">
                  {{ $item->title }}
                </a>
              </summary>
              <ul>
                @foreach ($children as $child_data)
                  @php
                    $child = $child_data['item'];
                    $grandchildren = $child_data['children'];
                    $has_grandchildren = !empty($grandchildren);
                    
                    $child_is_active = is_menu_item_active($child, $grandchildren, $current_url);
                    $child_active_class = $child_is_active ? 'active font-semibold' : '';
                  @endphp
                  
                  @if ($has_grandchildren)
                    <li>
                      <details open>
                        <summary class="{{ $child_active_class }} flex justify-between items-center">
                          <a href="{{ $child->url }}" class="flex-1" onclick="event.stopPropagation()">
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
                            <li><a href="{{ $grandchild->url }}" class="{{ $grandchild_active_class }}">{{ $grandchild->title }}</a></li>
                          @endforeach
                        </ul>
                      </details>
                    </li>
                  @else
                    <li><a href="{{ $child->url }}" class="{{ $child_active_class }}">{{ $child->title }}</a></li>
                  @endif
                @endforeach
              </ul>
            </details>
          </li>
        @else
          <li><a href="{{ $item->url }}" class="{{ $active_class }}">{{ $item->title }}</a></li>
        @endif
      @endforeach
    </ul>
  </div>
  <label class="modal-backdrop" for="mobile-menu">Close</label>
</div>