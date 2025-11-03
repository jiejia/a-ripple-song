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

  <div class="mt-4 rounded-lg bg-base-100 p-4">
    <div class="grid grid-cols-[1fr_auto] items-center">
      <h2 class="text-lg font-bold">
        PODCAST
      </h2>
      <span class="text-xs text-base-content/70"><a href="#">See all</a></span>
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
      <li>
        <div class="bg-base-200/50 rounded-lg hover:bg-base-200">
          <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center">
            <div>
              <a href="#" class="block w-10 h-10 rounded-lg overflow-hidden">
                <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="科技前沿对话" class="w-10 h-10 rounded-md" />
              </a>
            </div>
            <div class="grid grid-flow-row gap-1">
              <h4 class="text-md font-bold"><a href="#">科技前沿对话：AI时代的创业机会</a></h4>
              <p class="text-xs text-base-content/50">
                <span>October 15, 2025</span>
                <span>•</span>
                <span>235k views</span>
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
        <div class="bg-base-200/50 rounded-lg hover:bg-base-200">
          <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center">
            <div>
              <a href="#" class="block w-10 h-10 rounded-lg overflow-hidden">
                <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="深夜电台" class="w-10 h-10 rounded-md" />
              </a>
            </div>
            <div class="grid grid-flow-row gap-1">
              <h4 class="text-md font-bold"><a href="#">深夜电台：那些年我们听过的民谣</a></h4>
              <p class="text-xs text-base-content/50">
                <span>September 22, 2025</span>
                <span>•</span>
                <span>89k views</span>
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
        <div class="bg-base-200/50 rounded-lg hover:bg-base-200">
          <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center">
            <div>
              <a href="#" class="block w-10 h-10 rounded-lg overflow-hidden">
                <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="人物访谈" class="w-10 h-10 rounded-md" />
              </a>
            </div>
            <div class="grid grid-flow-row gap-1">
              <h4 class="text-md font-bold"><a href="#">人物访谈：独立开发者的成长之路</a></h4>
              <p class="text-xs text-base-content/50">
                <span>October 5, 2025</span>
                <span>•</span>
                <span>167k views</span>
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

  <div class="mt-4 rounded-lg bg-base-100 p-4">
    <div class="grid grid-cols-[1fr_auto] items-center">
      <h2 class="text-lg font-bold">
        BLOG
      </h2>
      <span class="text-xs text-base-content/70"><a href="#">See all</a></span>
    </div>
    <ul class="grid grid-cols-3 gap-4 gap-y-8 mt-4">
      <li class="bg-base-200/50 rounded-lg p-4 hover:bg-base-200">
        <h3 class="text-md font-bold"><a href="#">探索 Web3.0：去中心化网络的未来趋势</a></h3>
        <div class="grid grid-flow-row gap-1 mt-2">
          <span class="text-xs text-base-content">
            <span><a href="#">技术</a></span>
          </span>
          <span class="text-xs text-base-content/50">
            <span>October 12, 2025</span>
          </span>
      </li>
      <li class="bg-base-200/50 rounded-lg p-4 hover:bg-base-200">
        <h3 class="text-md font-bold"><a href="#">设计思维：如何打造用户喜爱的产品体验</a></h3>
        <div class="grid grid-flow-row gap-1 mt-2">
          <span class="text-xs text-base-content">
            <span><a href="#">设计</a></span>
          </span>
          <span class="text-xs text-base-content/50">
            <span>September 28, 2025</span>
          </span>
      </li>
      <li class="bg-base-200/50 rounded-lg p-4">
        <h3 class="text-md font-bold"><a href="#">远程办公时代：如何保持团队高效协作</a></h3>
        <div class="grid grid-flow-row gap-1 mt-2">
          <span class="text-xs text-base-content">
            <span><a href="#">职场</a></span>
          </span>
          <span class="text-xs text-base-content/50">
            <span>October 3, 2025</span>
          </span>
      </li>
      <li class="bg-base-200/50 rounded-lg p-4 hover:bg-base-200">
        <h3 class="text-md font-bold"><a href="#">机器学习入门：从零开始的实战指南</a></h3>
        <div class="grid grid-flow-row gap-1 mt-2">
          <span class="text-xs text-base-content">
            <span><a href="#">编程</a></span>
          </span>
          <span class="text-xs text-base-content/50">
            <span>September 15, 2025</span>
          </span>
      </li>
      <li class="bg-base-200/50 rounded-lg p-4 hover:bg-base-200">
        <h3 class="text-md font-bold"><a href="#">可持续发展：科技如何助力环保事业</a></h3>
        <div class="grid grid-flow-row gap-1 mt-2">
          <span class="text-xs text-base-content">
            <span><a href="#">环保</a></span>
          </span>
          <span class="text-xs text-base-content/50">
            <span>October 20, 2025</span>
          </span>
      </li>
      <li class="bg-base-200/50 rounded-lg p-4 hover:bg-base-200">
        <h3 class="text-md font-bold"><a href="#">创业者的自我修养：从想法到落地的实战经验</a></h3>
        <div class="grid grid-flow-row gap-1 mt-2">
          <span class="text-xs text-base-content">
            <span><a href="#">创业</a></span>
          </span>
          <span class="text-xs text-base-content/50">
            <span>October 8, 2025</span>
          </span>
      </li>
    </ul>
  </div>
</div>

@endsection

@section('sidebar')
@include('sections.sidebar')
@endsection