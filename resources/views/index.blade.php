@extends('layouts.app')

@section('content')
<div class="">
  @if(is_active_sidebar('home-main'))
    @php dynamic_sidebar('home-main') @endphp
  @else
    <div class="rounded-lg bg-base-100 p-8 text-center text-base-content/50">
      <p>请在后台的 外观 > 小工具 中添加小工具到"首页主要区域"。</p>
    </div>
  @endif
</div>

@endsection

@section('sidebar')
@include('sections.sidebar')
@endsection
