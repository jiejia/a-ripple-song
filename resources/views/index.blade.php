@extends('layouts.app')

@section('content')
<div class="h-full grid grid-rows-[210px_1fr] h-full">
  <div class="carousel w-full rounded-lg">
    <div id="slide1" class="carousel-item relative w-full rounded-lg">
      <img
        src="https://cdn.pixabay.com/photo/2016/11/29/05/45/astronomy-1867616_1280.jpg"
        class="w-full h-48 object-cover rounded-lg"
        alt="浅棕色抽象背景" />
      <div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
        <a href="#slide4" class="btn btn-circle btn-xs">❮</a>
        <a href="#slide2" class="btn btn-circle btn-xs">❯</a>
      </div>
    </div>
    <div id="slide2" class="carousel-item relative w-full rounded-lg">
      <img
        src="https://cdn.pixabay.com/photo/2016/11/29/12/13/fence-1869401_1280.jpg"
        class="w-full h-48 object-cover rounded-lg"
        alt="棕色木质纹理" />
      <div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
        <a href="#slide1" class="btn btn-circle btn-xs">❮</a>
        <a href="#slide3" class="btn btn-circle btn-xs">❯</a>
      </div>
    </div>
    <div id="slide3" class="carousel-item relative w-full rounded-lg">
      <img
        src="https://cdn.pixabay.com/photo/2017/08/30/01/05/milky-way-2695569_1280.jpg"
        class="w-full h-48 object-cover rounded-lg"
        alt="温暖的棕色调风景" />
      <div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
        <a href="#slide2" class="btn btn-circle btn-xs">❮</a>
        <a href="#slide4" class="btn btn-circle btn-xs">❯</a>
      </div>
    </div>
    <div id="slide4" class="carousel-item relative w-full rounded-lg">
      <img
        src="https://cdn.pixabay.com/photo/2016/11/29/03/53/architecture-1867187_1280.jpg"
        class="w-full h-48 object-cover rounded-lg"
        alt="米色建筑背景" />
      <div class="absolute left-5 right-5 top-1/2 flex -translate-y-1/2 transform justify-between">
        <a href="#slide3" class="btn btn-circle btn-xs">❮</a>
        <a href="#slide1" class="btn btn-circle btn-xs">❯</a>
      </div>
    </div>
  </div>
  <div class="relative h-ful">
    <div class="h-full">
      <div class="">
        <div class="grid grid-cols-[1fr_auto] items-center">
          <h2 class="text-lg font-bold">
            PODCAST
          </h2>
          <span class="text-xs text-base-content/70"><a href="#">See all</a></span>
        </div>
        <ul class="flex gap-2 mt-2">
          <li>
            <button class="btn bg-base-300 rounded-full btn-sm">Recent</button>
          </li>
          <li>
            <button class="btn bg-base-100 rounded-full btn-sm">Popular</button>
          </li>
          <li>
            <button class="btn bg-base-100 rounded-full btn-sm">Random</button>
          </li>
        </ul>
        <ul class="grid grid-flow-row gap-y-4 mt-4">
          <li>
            <div class="bg-base-100 rounded-lg">
              <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center">
                <div>
                  <a href="#" class="block w-10 h-10 rounded-lg overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="Podcast 1" class="w-10 h-10 rounded-md" />
                  </a>
                </div>
                <div class="grid grid-flow-row gap-1">
                  <h4 class="text-md font-bold">Podcast 1</h4>
                  <p class="text-xs text-base-content/50">
                    <span>October 28, 2025</span>
                    <span>•</span>
                    <span>100k views</span>
                  </p>
                </div>
                <div class="flex gap-2">
                  <i data-lucide="heart" class="text-xs h-4"></i>
                  <i data-lucide="ellipsis-vertical" class="text-xs h-4"></i>
                </div>
              </div>
            </div>
          </li>
          <li>
            <div class="bg-base-100 rounded-lg">
              <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center">
                <div>
                  <a href="#" class="block w-10 h-10 rounded-lg overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="Podcast 1" class="w-10 h-10 rounded-md" />
                  </a>
                </div>
                <div class="grid grid-flow-row gap-1">
                  <h4 class="text-md font-bold">Podcast 1</h4>
                  <p class="text-xs text-base-content/50">
                    <span>October 28, 2025</span>
                    <span>•</span>
                    <span>100k views</span>
                  </p>
                </div>
                <div class="flex gap-2">
                  <i data-lucide="heart" class="text-xs h-4"></i>
                  <i data-lucide="ellipsis-vertical" class="text-xs h-4"></i>
                </div>
              </div>
            </div>
          </li>
          <li>
            <div class="bg-base-100 rounded-lg">
              <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center">
                <div>
                  <a href="#" class="block w-10 h-10 rounded-lg overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="Podcast 1" class="w-10 h-10 rounded-md" />
                  </a>
                </div>
                <div class="grid grid-flow-row gap-1">
                  <h4 class="text-md font-bold">Podcast 1</h4>
                  <p class="text-xs text-base-content/50">
                    <span>October 28, 2025</span>
                    <span>•</span>
                    <span>100k views</span>
                  </p>
                </div>
                <div class="flex gap-2">
                  <i data-lucide="heart" class="text-xs h-4"></i>
                  <i data-lucide="ellipsis-vertical" class="text-xs h-4"></i>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
      <div class="mt-4">
        <div class="grid grid-cols-[1fr_auto] items-center">
          <h2 class="text-lg font-bold">
            BLOG
          </h2>
          <span class="text-xs text-base-content/70"><a href="#">See all</a></span>
        </div>
        <ul class="grid grid-cols-3 gap-4 gap-y-8 mt-4">
          <li class="bg-base-100 rounded-lg p-4">
            <h3 class="text-md font-bold"><a href="#">Addendum to GPT-5 System Card: Sensitive conversationsSafety</a></h3>
            <div class="grid grid-flow-row gap-1 mt-2">
              <span class="text-xs text-base-content">
                <span><a href="#">Company</a></span>
              </span>
              <span class="text-xs text-base-content/50">
                <span>October 28, 2025</span>
              </span>
          </li>
          <li class="bg-base-100 rounded-lg p-4">
            <h3 class="text-md font-bold"><a href="#">Addendum to GPT-5 System Card: Sensitive conversationsSafety</a></h3>
            <div class="grid grid-flow-row gap-1 mt-2">
              <span class="text-xs text-base-content">
                <span><a href="#">Company</a></span>
              </span>
              <span class="text-xs text-base-content/50">
                <span>October 28, 2025</span>
              </span>
          </li>
          <li class="bg-base-100 rounded-lg p-4">
            <h3 class="text-md font-bold"><a href="#">Addendum to GPT-5 System Card: Sensitive conversationsSafety</a></h3>
            <div class="grid grid-flow-row gap-1 mt-2">
              <span class="text-xs text-base-content">
                <span><a href="#">Company</a></span>
              </span>
              <span class="text-xs text-base-content/50">
                <span>October 28, 2025</span>
              </span>
          </li>
          <li class="bg-base-100 rounded-lg p-4">
            <h3 class="text-md font-bold"><a href="#">Addendum to GPT-5 System Card: Sensitive conversationsSafety</a></h3>
            <div class="grid grid-flow-row gap-1 mt-2">
              <span class="text-xs text-base-content">
                <span><a href="#">Company</a></span>
              </span>
              <span class="text-xs text-base-content/50">
                <span>October 28, 2025</span>
              </span>
          </li>
          <li class="bg-base-100 rounded-lg p-4">
            <h3 class="text-md font-bold"><a href="#">Addendum to GPT-5 System Card: Sensitive conversationsSafety</a></h3>
            <div class="grid grid-flow-row gap-1 mt-2">
              <span class="text-xs text-base-content">
                <span><a href="#">Company</a></span>
              </span>
              <span class="text-xs text-base-content/50">
                <span>October 28, 2025</span>
              </span>
          </li>
          <li class="bg-base-100 rounded-lg p-4">
            <h3 class="text-md font-bold"><a href="#">Addendum to GPT-5 System Card: Sensitive conversationsSafety</a></h3>
            <div class="grid grid-flow-row gap-1 mt-2">
              <span class="text-xs text-base-content">
                <span><a href="#">Company</a></span>
              </span>
              <span class="text-xs text-base-content/50">
                <span>October 28, 2025</span>
              </span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- @include('partials.page-header')

  @if (! have_posts())
    <x-alert type="warning">
      {!! __('Sorry, no results were found.', 'sage') !!}
    </x-alert>

    {!! get_search_form(false) !!}
  @endif

  @while(have_posts()) @php(the_post())
    @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
  @endwhile

  {!! get_the_posts_navigation() !!} -->
@endsection

@section('sidebar')
@include('sections.sidebar')
@endsection