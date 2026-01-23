{{--
  Template Name: Podcast Template
--}}

@extends('layouts.app')

@section('content')
@php
global $wp_query;

$episode_post_type = function_exists('aripplesong_get_podcast_post_type') ? aripplesong_get_podcast_post_type() : null;
$original_query = $wp_query;
$paged = max(1, (int) get_query_var('paged'));

$wp_query = $episode_post_type
  ? new WP_Query([
    'post_type' => $episode_post_type,
    'posts_per_page' => (int) get_option('posts_per_page'),
    'paged' => $paged,
  ])
  : null;
@endphp

@include('partials.page-header')

@if($wp_query)
  @while(have_posts())
    @php(the_post())
    @includeFirst(['partials.content-' . $episode_post_type, 'partials.content'])
  @endwhile

  {!! the_posts_pagination() !!}
@else
  @if(is_user_logged_in() && current_user_can('manage_options'))
    <div class="rounded-lg bg-base-100 p-4 text-sm text-base-content/70">
      {{ __('Podcast plugin is not active. Install/activate a-ripple-song-podcast to show episodes.', 'sage') }}
    </div>
  @endif
@endif

@php(wp_reset_postdata())
@php($wp_query = $original_query)

@endsection

@section('sidebar')
@include('sections.sidebar')
@endsection
