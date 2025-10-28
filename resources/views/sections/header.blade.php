<header class="bg-base-100 rounded-2xl p-4">
  <div>
    <h1 class="text-xl font-bold text-center">
      <a href="{{ home_url('/') }}">
        {!! $siteName !!}
      </a>
    </h1>
  </div>

  @if (has_nav_menu('primary_navigation'))
  <nav class="nav-primary mt-4" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">
    <ul class="grid grid-flow-row gap-1 text-center">
      @php
        $locations = get_nav_menu_locations();
        $menu_items = isset($locations['primary_navigation']) 
          ? wp_get_nav_menu_items($locations['primary_navigation']) 
          : false;
        
        // get current page/article id
        $current_id = get_queried_object_id();
        $current_url = home_url(add_query_arg(array(), $_SERVER['REQUEST_URI']));
      @endphp

      @if($menu_items)
        @foreach($menu_items as $item)
        @php
          // check if the item is the current page/article
          $is_current = ($item->object_id == $current_id) || ($item->url == $current_url);
        @endphp
        <li class="">
          <a href="{{ $item->url }}"
            class="block px-4 py-2 rounded-lg hover:bg-base-200 transition-colors {{ $is_current ? 'bg-base-200 text-primary-content' : '' }}">
            {{ $item->title }}
          </a>
        </li>
        @endforeach
      @endif
    </ul>
  </nav>
  @endif
</header>