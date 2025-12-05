{{--
  Template Name: Blog Template
--}}

@extends('layouts.app')

@section('content')
@php(query_posts([
'post_type' => 'post',
'posts_per_page' => get_option('posts_per_page'),
'paged' => get_query_var('paged') ? get_query_var('paged') : 1
]))
@include('partials.page-header')

@while(have_posts()) @php(the_post())
@includeFirst(['partials.content'])
@endwhile

{!! the_posts_pagination() !!}

@endsection

@section('sidebar')
@include('sections.sidebar')
@endsection