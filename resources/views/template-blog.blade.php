@extends('layouts.app')

@section('content')

    @include('partials.page-header')
    @php(query_posts(['post_type' => 'post']))

    @while(have_posts()) @php(the_post())
    @includeFirst(['partials.content'])
    @endwhile

    {!! the_posts_pagination() !!}

@endsection

@section('sidebar')
@include('sections.sidebar')
@endsection