@extends('feeds.layout')


@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2> Show Feed</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-primary" href="{{ route('feeds.index') }}"> Back</a>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Title:</strong>
                {{ $feed->title}}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>UUID:</strong>
                {{ $feed->uuid}}
            </div>
        </div>
    </div>
@endsection
