@extends('layouts.app')

@section('content')
<div class="">
  <div class="w-full rounded-lg bg-base-100 p-4 pb-2">
    <div class="carousel w-full rounded-lg">

      <div id="slide3" class="carousel-item relative w-full rounded-lg">
        <img
          src="https://cdn.pixabay.com/photo/2023/12/03/15/23/ai-generated-8427689_640.jpg"
          class="w-full h-48 object-cover rounded-lg"
          alt="温暖的棕色调风景" />
        <div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
          <a href="#slide2" class="btn btn-circle btn-xs">❮</a>
          <a href="#slide4" class="btn btn-circle btn-xs">❯</a>
        </div>
      </div>
      <div id="slide4" class="carousel-item relative w-full rounded-lg">
        <img
          src="https://cdn.pixabay.com/photo/2024/09/24/09/47/ai-generated-9070891_640.png"
          class="w-full h-48 object-cover rounded-lg"
          alt="米色建筑背景" />
        <div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
          <a href="#slide3" class="btn btn-circle btn-xs">❮</a>
          <a href="#slide1" class="btn btn-circle btn-xs">❯</a>
        </div>
      </div>
    </div>
  </div>

  @php
    // 查询最新的播客
    $podcasts = new WP_Query([
        'post_type' => 'podcast',
        'posts_per_page' => 3,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
  @endphp

  <div class="mt-4 rounded-lg bg-base-100 p-4">
    <div class="grid grid-cols-[1fr_auto] items-center">
      <h2 class="text-lg font-bold">
        PODCAST
      </h2>
      <span class="text-xs text-base-content/70"><a href="{{ get_permalink(get_page_by_path('podcast')) }}">See all</a></span>
    </div>
    <ul class="flex gap-2 mt-2">
      <li>
        <button class="btn bg-base-200 rounded-full btn-sm">Recent</button>
      </li>
      <li>
        <button class="btn bg-base-100 rounded-full btn-sm">Popular</button>
      </li>
      <li>
        <button class="btn bg-base-100 rounded-full btn-sm">Random</button>
      </li>
    </ul>
    <ul class="grid grid-flow-row gap-y-4 mt-4">
      @if($podcasts->have_posts())
        @while($podcasts->have_posts())
          @php $podcasts->the_post() @endphp
          <li>
            <div class="bg-base-200/50 rounded-lg hover:bg-base-200">
              <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center">
                <div>
                  <a href="{{ get_permalink() }}" class="block w-10 h-10 rounded-lg overflow-hidden">
                    @if(has_post_thumbnail())
                      <img src="{{ get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') }}" alt="{{ get_the_title() }}" class="w-10 h-10 rounded-md object-cover" />
                    @else
                      <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="{{ get_the_title() }}" class="w-10 h-10 rounded-md" />
                    @endif
                  </a>
                </div>
                <div class="grid grid-flow-row gap-1">
                  <h4 class="text-md font-bold"><a href="{{ get_permalink() }}">{{ get_the_title() }}</a></h4>
                  <p class="text-xs text-base-content/50">
                    <span>{{ get_the_date() }}</span>
                    <span>•</span>
                    <span>{{ get_post_meta(get_the_ID(), '_post_views_count', true) ?: 0 }} views</span>
                  </p>
                </div>
                <div class="flex gap-2">
                  <i data-lucide="heart" class="text-xs h-4"></i>
                  <i data-lucide="ellipsis-vertical" class="text-xs h-4"></i>
                </div>
              </div>
            </div>
          </li>
        @endwhile
        @php wp_reset_postdata() @endphp
      @else
        <li class="text-center text-base-content/50 py-8">暂无播客内容</li>
      @endif
    </ul>
  </div>

  @php
    // 查询最新的博客文章
    $posts = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => 6,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
  @endphp

  <div class="mt-4 rounded-lg bg-base-100 p-4">
    <div class="grid grid-cols-[1fr_auto] items-center">
      <h2 class="text-lg font-bold">
        BLOG
      </h2>
      <span class="text-xs text-base-content/70"><a href="{{ get_permalink(get_page_by_path('blog')) }}">See all</a></span>
    </div>
    <ul class="grid grid-cols-3 gap-4 gap-y-8 mt-4">
      @if($posts->have_posts())
        @while($posts->have_posts())
          @php $posts->the_post() @endphp
          <li class="bg-base-200/50 rounded-lg p-4 hover:bg-base-200">
            <h3 class="text-md font-bold"><a href="{{ get_permalink() }}">{{ get_the_title() }}</a></h3>
            <div class="grid grid-flow-row gap-1 mt-2">
              @php
                $categories = get_the_category();
              @endphp
              @if(!empty($categories))
                <span class="text-xs text-base-content">
                  <span><a href="{{ get_category_link($categories[0]->term_id) }}">{{ $categories[0]->name }}</a></span>
                </span>
              @endif
              <span class="text-xs text-base-content/50">
                <span>{{ get_the_date() }}</span>
              </span>
            </div>
          </li>
        @endwhile
        @php wp_reset_postdata() @endphp
      @else
        <li class="col-span-3 text-center text-base-content/50 py-8">暂无博客文章</li>
      @endif
    </ul>
  </div>
</div>

@endsection

@section('sidebar')
@include('sections.sidebar')
@endsection