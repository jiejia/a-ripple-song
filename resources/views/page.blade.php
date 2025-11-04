@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @includeFirst(['partials.content-single-page', 'partials.content'])
  @endwhile
@endsection



@section('sidebar')
@include('sections.sidebar')
@endsection