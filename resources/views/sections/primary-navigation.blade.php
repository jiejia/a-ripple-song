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
          <a class="grid place-items-center h-full w-full text-center px-4 rounded-lg {{ $active_class }}" href="{{ $item->url }}">
            {{ $item->title }}
          </a>
          <ul tabindex="-1" class="dropdown-content menu bg-base-200/75 rounded-box z-1 w-52 p-2 shadow-sm">
            @foreach ($children as $child)
              <li><a href="{{ $child->url }}">{{ $child->title }}</a></li>
            @endforeach
          </ul>
        </div>
      @else
        <a href="{{ $item->url }}" class="grid place-items-center h-full w-full text-center px-4 rounded-lg {{ $active_class }}">
          {{ $item->title }}
        </a>
      @endif
    </li>
  @endforeach
</ul>