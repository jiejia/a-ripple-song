@php
    $tags = get_the_tags();
@endphp

@if($tags && !empty($tags))
<div class="grid grid-flow-row gap-2 mt-2">
    <ul class="flex flex-wrap gap-2">
        @foreach($tags as $tag)
        <li>
            <a href="{{ get_tag_link($tag->term_id) }}" class="text-xs text-base-content/50 bg-base-200/50 hover:bg-base-200 rounded-full py-0.5 px-2"># {{ $tag->name }}</a>
        </li>
        @endforeach
    </ul>
</div>
@endif