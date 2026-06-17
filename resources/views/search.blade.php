@extends('layouts.app')

@section('content')
    @include('partials.page-header')
    @if (!have_posts())
        <x-alert type="warning">
            {!! __('Sorry, no results were found.', 'sage') !!}
        </x-alert>

        {!! get_search_form(false) !!}
    @endif

    <ul class="grid grid-flow-row gap-y-2">

        @while (have_posts())
            @php(the_post())
            @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
        @endwhile
    </ul>


    {!! the_posts_pagination() !!}
@endsection


@section('sidebar')
    @include('sections.sidebar')
@endsection
