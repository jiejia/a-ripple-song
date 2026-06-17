<article @php(post_class("rounded-lg bg-base-100 p-4"))>
<div class="grid grid-flow-row gap-2">
    <div class="grid grid-flow-row gap-1">
      <h4 class="text-md font-bold"><a href="#">{{ $title }}</a></h4>
      @include('partials.entry-meta')
    </div>
    <div class="prose max-w-none text-sm text-base-content/80 [&_p]:py-2 [&_img]:mx-auto [&_img]:cursor-pointer [&_img]:rounded-lg [&_img]:shadow-md" id="content">
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
</article>
