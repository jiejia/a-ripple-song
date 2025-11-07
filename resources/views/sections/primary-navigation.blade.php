<ul class="xl:grid hidden grid-flow-col gap-2 text-md justify-center" id="menu-1">
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
      $active_class = $is_active ? 'text-base-content font-semibold bg-base-200/50' : 'text-base-content/80 hover:text-base-content';
    @endphp
    <li>
      @if ($has_children)
        <div class="dropdown dropdown-hover dropdown-start h-full w-full">
          <a class="grid place-items-center h-full w-full text-center px-4 rounded-lg {{ $active_class }}" href="{{ $item->url }}" data-pjax>
            {{ $item->title }}
          </a>
          <ul tabindex="-1" class="dropdown-content menu bg-base-200/75 rounded-box z-[100] w-52 p-2 shadow-sm">
            @foreach ($children as $child_data)
              @php
                $child = $child_data['item'];
                $grandchildren = $child_data['children'];
                $has_grandchildren = !empty($grandchildren);
              @endphp
              @if ($has_grandchildren)
                <li class="relative group/submenu">
                  <a href="{{ $child->url }}" class="flex items-center justify-between" data-pjax>
                    {{ $child->title }}
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                  </a>
                  <ul class="absolute left-full top-0 ml-1 hidden group-hover/submenu:block menu bg-base-200/90 rounded-box z-[100] w-52 p-2 shadow-sm">
                    @foreach ($grandchildren as $grandchild_data)
                      <li><a href="{{ $grandchild_data['item']->url }}" data-pjax>{{ $grandchild_data['item']->title }}</a></li>
                    @endforeach
                  </ul>
                </li>
              @else
                <li><a href="{{ $child->url }}" data-pjax>{{ $child->title }}</a></li>
              @endif
            @endforeach
          </ul>
        </div>
      @else
        <a href="{{ $item->url }}" class="grid place-items-center h-full w-full text-center px-4 rounded-lg {{ $active_class }}" data-pjax>
          {{ $item->title }}
        </a>
      @endif
    </li>
  @endforeach
</ul>