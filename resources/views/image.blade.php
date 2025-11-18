@extends('layouts.app')

@section('content')
@while(have_posts()) @php(the_post())
    @include('partials.content-single-image')
  @endwhile
@endsection


@section('sidebar')
@include('sections.sidebar')
@endsection