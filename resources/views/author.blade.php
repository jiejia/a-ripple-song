@extends('layouts.app')

@section('content')

    @include('partials.page-header')

    {{-- Use the main query which has been modified via pre_get_posts hook --}}
    @if(have_posts())
        @while(have_posts())
            @php(the_post())
            @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
        @endwhile

        {!! the_posts_pagination() !!}
    @else
        <x-alert type="warning">
            {!! __('Sorry, no results were found.', 'sage') !!}
        </x-alert>
    @endif

@endsection

@section('sidebar')
@include('sections.sidebar')
@endsection