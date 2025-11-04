<div class="mt-4 rounded-lg bg-base-100 p-4">
    <div class="grid grid-flow-row gap-2">
        <div class="bg-base-200/50 rounded-lg hover:bg-base-200">
            <div class="p-4 grid grid-cols-[60px_1fr_60px] items-center">
                <div>
                    <a href="#" class="block w-10 h-10 rounded-lg overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="科技前沿对话" class="w-10 h-10 rounded-md" />
                    </a>
                </div>
                <div class="grid grid-flow-row gap-1">
                    <h4 class="text-md font-bold"><a href="{{ get_permalink() }}">{!! $title !!}</a></h4>
                    @include('partials.entry-meta')
                </div>
                <div class="flex gap-2">
                    <i data-lucide="heart" class="text-xs h-4"></i>
                    <i data-lucide="ellipsis-vertical" class="text-xs h-4"></i>
                </div>
            </div>
        </div>
        <div class="text-base text-base-content/80 leading-relaxed text-sm" id="content">
            @php(the_excerpt())
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