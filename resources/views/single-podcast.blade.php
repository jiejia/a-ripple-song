@extends('layouts.app')

@section('content')
<div class="mt-4 rounded-lg bg-base-100 p-4">
    <div class="grid grid-flow-row gap-2">
        <div class="bg-base-200/50 rounded-lg hover:bg-base-200">
            <div class="p-4 grid grid-cols-[1fr_60px] items-center pb-2">
                <div class="grid grid-flow-row gap-1">
                    <h2 class="text-lg font-bold"><a href="#">科技前沿对话：AI时代的创业机会</a></h2>
                    <div class="text-xs text-base-content/50 grid grid-flow-col gap-2 justify-start content-center items-center">
                        <div>
                            <a href="/" class="text-xs text-base-content/50">Company</a>
                            <span>•</span>
                            <span>October 15, 2025</span>
                            <span>•</span>
                            <span>167k views</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <i data-lucide="heart" class="text-xs h-4"></i>
                    <i data-lucide="ellipsis-vertical" class="text-xs h-4"></i>
                </div>
            </div>
        </div>
        <div class="text-base text-base-content/80 leading-relaxed text-sm" id="content">
            <p class="indent-6">
                本期节目我们邀请了一位独立开发者，分享他从零开始到成功创业的完整历程。在这段对话中，你将了解到独立开发者如何平衡技术学习与产品开发，如何找到合适的市场定位，以及面对挑战时的应对策略。无论你是正在考虑独立开发的程序员，还是对创业故事感兴趣的听众，这期内容都将为你带来启发和思考。
            </p>
            <p class="indent-6">
                访谈中，嘉宾详细讲述了自己最初是如何从一个普通的程序员转变为独立开发者的。他分享了在决定独立开发之前所做的准备，包括技术栈的选择、资金规划、以及心理建设。这些经验对于想要踏上独立开发之路的朋友来说，都是非常宝贵的参考。
            </p>
            <p class="indent-6">
                在技术层面，我们深入探讨了如何选择合适的技术栈来构建产品。嘉宾强调，技术选型不仅要考虑当前的需求，更要思考未来的扩展性。他分享了自己在开发过程中遇到的技术难题，以及如何通过持续学习和社区支持来解决这些问题。
            </p>
            <p class="indent-6">
                除了技术问题，本期节目还重点讨论了独立开发者在产品营销和用户获取方面的策略。嘉宾坦诚地分享了自己在早期阶段犯过的错误，以及从中吸取的教训。他强调了用户反馈的重要性，以及如何通过迭代改进来打造真正有价值的产品。
            </p>
            <p class="indent-6">
                最后，我们还聊到了独立开发者的生活方式和工作节奏。如何在保持工作热情的同时，避免过度疲劳？如何平衡开发工作和个人生活？这些问题对于长期从事独立开发的创作者来说都是至关重要的。嘉宾分享了自己的时间管理方法和保持动力的秘诀。
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
    </div>
</div>
<div class="mt-4 rounded-lg bg-base-100 p-4">
    <!-- <ul class="grid grid-flow-col gap-4 justify-center">
        <li>
            <a href="#" class="grid grid-flow-row gap-1 text-center">
                <div class="avatar">
                    <div class="ring-base-content/50 ring-offset-base-100 w-10 rounded-full ring-1 ring-offset-1">
                        <img src="https://img.daisyui.com/images/profile/demo/superperson@192.webp" />
                    </div>
                </div>
                <h4 class="text-xs text-base-content/80">
                    Jamie
                </h4
            </a>
        </li>
        <li>
            <a href="#" class="grid grid-flow-row gap-1 text-center">
                <div class="avatar">
                    <div class="ring-base-content/50 ring-offset-base-100 w-10 rounded-full ring-1 ring-offset-1">
                        <img src="https://img.daisyui.com/images/profile/demo/superperson@192.webp" />
                    </div>
                </div>
                <h4 class="text-xs text-base-content/80">
                    Jamie
                </h4
            </a>
        </li>
        <li>
            <a href="#" class="grid grid-flow-row gap-1 text-center">
                <div class="avatar">
                    <div class="ring-base-content/50 ring-offset-base-100 w-10 rounded-full ring-1 ring-offset-1">
                        <img src="https://img.daisyui.com/images/profile/demo/superperson@192.webp" />
                    </div>
                </div>
                <h4 class="text-xs text-base-content/80">
                    Jamie
                </h4
            </a>
        </li>
    </ul> -->
</div>

@endsection

@section('sidebar')
@include('sections.sidebar')
@endsection