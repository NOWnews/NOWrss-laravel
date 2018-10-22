@extends('feeds.layout')


@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>建立廠商 RSS 介面</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-primary" href="{{ route('feeds.index') }}"><i class="fas fa-undo"></i> 返回</a>
            </div>
        </div>
    </div>


    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>警告！</strong> 填入的資料似乎有些小麻煩 <br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {!! Form::open(array('route' => 'feeds.store','method'=>'POST')) !!}
         @include('feeds.form')
    {!! Form::close() !!}


@endsection
