<div class="rounded-lg bg-base-100 p-4 mb-4">
    <div class="grid grid-cols-[1fr_auto] items-center">
        <h2 class="text-lg font-bold">
            {!! wp_kses_post($title) !!}
        </h2>
        @php
        global $wp_query;
        $total = $wp_query->found_posts ?? 0;
        @endphp

        <div class="text-sm text-base-content/70 bg-base-200 rounded-md px-2 py-1">
            {{ $total }}
        </div>
    </div>
</div>
