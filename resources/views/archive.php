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
        <li class="bg-base-100 rounded-lg hover:bg-base-100 p-4">
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
            <div class="text-base text-base-content/80 leading-relaxed text-sm mt-2" id="content">
                <p>
                    本期节目我们邀请了一位独立开发者，分享他从零开始到成功创业的完整历程。在这段对话中，你将了解到独立开发者如何平衡技术学习与产品开发，如何找到合适的市场定位，以及面对挑战时的应对策略。无论你是正在考虑独立开发的程序员，还是对创业故事感兴趣的听众。
                </p>
            </div>
            <div class="grid grid-flow-row gap-2 mt-2">
                <ul class="flex gap-2 justify-center">
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Company</a>
                    </li>
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Technology</a>
                    </li>
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Product</a>
                    </li>
                </ul>
            </div>
            <div class="avatar-group -space-x-2 mt-2 justify-center">
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/batperson@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/spiderperson@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/averagebulk@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/wonderperson@192.webp" />
                    </a>
                </div>
            </div>
        </li>
        <li class="bg-base-100 rounded-lg hover:bg-base-100 p-4">
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
            <div class="text-base text-base-content/80 leading-relaxed text-sm mt-2" id="content">
                <p>
                    本期节目我们邀请了一位独立开发者，分享他从零开始到成功创业的完整历程。在这段对话中，你将了解到独立开发者如何平衡技术学习与产品开发，如何找到合适的市场定位，以及面对挑战时的应对策略。无论你是正在考虑独立开发的程序员，还是对创业故事感兴趣的听众。
                </p>
            </div>
            <div class="grid grid-flow-row gap-2 mt-2">
                <ul class="flex gap-2 justify-center">
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Company</a>
                    </li>
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Technology</a>
                    </li>
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Product</a>
                    </li>
                </ul>
            </div>
            <div class="avatar-group -space-x-2 mt-2 justify-center">
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/batperson@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/spiderperson@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/averagebulk@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/wonderperson@192.webp" />
                    </a>
                </div>
            </div>
        </li>
        <li class="bg-base-100 rounded-lg hover:bg-base-100 p-4">
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
            <div class="text-base text-base-content/80 leading-relaxed text-sm mt-2" id="content">
                <p>
                    本期节目我们邀请了一位独立开发者，分享他从零开始到成功创业的完整历程。在这段对话中，你将了解到独立开发者如何平衡技术学习与产品开发，如何找到合适的市场定位，以及面对挑战时的应对策略。无论你是正在考虑独立开发的程序员，还是对创业故事感兴趣的听众。
                </p>
            </div>
            <div class="grid grid-flow-row gap-2 mt-2">
                <ul class="flex gap-2 justify-center">
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Company</a>
                    </li>
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Technology</a>
                    </li>
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Product</a>
                    </li>
                </ul>
            </div>
            <div class="avatar-group -space-x-2 mt-2 justify-center">
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/batperson@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/spiderperson@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/averagebulk@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/wonderperson@192.webp" />
                    </a>
                </div>
            </div>
        </li>
        <li class="bg-base-100 rounded-lg hover:bg-base-100 p-4">
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
            <div class="text-base text-base-content/80 leading-relaxed text-sm mt-2" id="content">
                <p>
                    本期节目我们邀请了一位独立开发者，分享他从零开始到成功创业的完整历程。在这段对话中，你将了解到独立开发者如何平衡技术学习与产品开发，如何找到合适的市场定位，以及面对挑战时的应对策略。无论你是正在考虑独立开发的程序员，还是对创业故事感兴趣的听众。
                </p>
            </div>
            <div class="grid grid-flow-row gap-2 mt-2">
                <ul class="flex gap-2 justify-center">
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Company</a>
                    </li>
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Technology</a>
                    </li>
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Product</a>
                    </li>
                </ul>
            </div>
            <div class="avatar-group -space-x-2 mt-2 justify-center">
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/batperson@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/spiderperson@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/averagebulk@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-5 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/wonderperson@192.webp" />
                    </a>
                </div>
            </div>
        </li>
        <li class="bg-base-100 rounded-lg hover:bg-base-100 p-4">
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
            <div class="text-base text-base-content/80 leading-relaxed text-sm mt-2" id="content">
                <p>
                    本期节目我们邀请了一位独立开发者，分享他从零开始到成功创业的完整历程。在这段对话中，你将了解到独立开发者如何平衡技术学习与产品开发，如何找到合适的市场定位，以及面对挑战时的应对策略。无论你是正在考虑独立开发的程序员，还是对创业故事感兴趣的听众。
                </p>
            </div>
            <div class="grid grid-flow-row gap-2 mt-2">
                <ul class="flex gap-2 justify-center">
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Company</a>
                    </li>
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Technology</a>
                    </li>
                    <li>
                        <a href="/" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># Product</a>
                    </li>
                </ul>
            </div>
            <div class="avatar-group -space-x-2 mt-2 justify-center">
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/batperson@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/spiderperson@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/averagebulk@192.webp" />
                    </a>
                </div>
                <div class="avatar">
                    <a href="/" class="w-6 block" title="Jamie">
                        <img src="https://img.daisyui.com/images/profile/demo/wonderperson@192.webp" />
                    </a>
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