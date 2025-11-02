@extends('layouts.app')

@section('content')
<div class="mt-4 rounded-lg bg-base-100 p-4">
    <div class="grid grid-cols-[1fr_auto] items-center">
        <h2 class="text-lg font-bold">
            PODCAST
        </h2>
    </div>
</div>
<div class="mt-4">
    <ul class="grid grid-flow-row gap-y-4">
        <li>
            <div class="bg-base-100 rounded-lg hover:bg-base-100/75">
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
            <div class="bg-base-100 rounded-lg hover:bg-base-100/75">
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
            <div class="bg-base-100 rounded-lg hover:bg-base-100/75">
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
        <li>
            <div class="bg-base-100 rounded-lg hover:bg-base-100/75">
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
        <li>
            <div class="bg-base-100 rounded-lg hover:bg-base-100/75">
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
        <li>
            <div class="bg-base-100 rounded-lg hover:bg-base-100/75">
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
        <li>
            <div class="bg-base-100 rounded-lg hover:bg-base-100/75">
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
    <div class="join mt-4 text-center justify-center flex">
        <input
            class="join-item btn btn-square bg-base-100"
            type="radio"
            name="options"
            aria-label="1"
            checked="checked" />
        <input class="join-item btn btn-square bg-base-100" type="radio" name="options" aria-label="2" />
        <input class="join-item btn btn-square bg-base-100" type="radio" name="options" aria-label="3" />
        <input class="join-item btn btn-square bg-base-100" type="radio" name="options" aria-label="4" />
    </div>
</div>

@endsection

@section('sidebar')
@include('sections.sidebar')
@endsection