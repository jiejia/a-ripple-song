@extends('layouts.app')

@section('content')
<div class="">
  @if(is_active_sidebar(\App\Theme::SIDEBAR_HOME_MAIN))
    @php dynamic_sidebar(\App\Theme::SIDEBAR_HOME_MAIN) @endphp
  @else
    <div class="rounded-lg bg-base-100 p-8 text-center text-base-content/50">
      <p>{!! __('Please add widgets to "Home Main" area in Appearance > Widgets in the admin panel.', 'sage') !!}</p>
    </div>
  @endif
</div>

@endsection

@section('sidebar')
@include('sections.sidebar')
@endsection
