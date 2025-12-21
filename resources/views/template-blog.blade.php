{{--
  Template Name: Blog Template
--}}

@extends('layouts.app')

@section('content')
@php
global $wp_query;

$original_query = $wp_query;
$paged = max(1, (int) get_query_var('paged'));

$wp_query = new WP_Query([
  'post_type' => 'post',
  'posts_per_page' => (int) get_option('posts_per_page'),
  'paged' => $paged,
]);
@endphp
@include('partials.page-header')

@while(have_posts())
  @php(the_post())
  @includeFirst(['partials.content'])
@endwhile

{!! the_posts_pagination() !!}

@php(wp_reset_postdata())
@php($wp_query = $original_query)

@endsection

@section('sidebar')
@include('sections.sidebar')
@endsection
