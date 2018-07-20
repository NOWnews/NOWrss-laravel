@extends('layout')


@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Feeds 2018</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-success" href="{{ route('feeds.create') }}"> Create New Feed</a>
            </div>
        </div>
    </div>


    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif


    <table class="table table-bordered">
        <tr>
            <th>編號</th>
            <th>建立時間</th>
            <th>名稱</th>
            <th>語言</th>
            <th>頻道ID</th>
            <th>版型</th>
            <th width="270px">編輯</th>
        </tr>
    @foreach ($feeds as $feed)
    <tr>
        <td>{{ ++$i }}</td>
        <td>{{ $feed->created_at}}</td>
        <td>{{ $feed->title}}</td>
        @if($feed->language == 'traditional' || !$feed->language)
            <td>繁體</td>
        @else
            <td>簡體</td>
        @endif
        <td>{{ $feed->uuid}}</td>
        <td>{{ $feed->layout}}</td>
        <td>
            <a class="btn btn-info" href="{{ url('/rss\/').$feed->uuid }}" target="_blank">RSS連結</a>
            <a class="btn btn-primary" href="{{ route('feeds.edit',$feed->id) }}">編輯</a>

 
            {!! Form::open(['method' => 'DELETE','route' => ['feeds.destroy', $feed->id], 'style'=>'display:inline', 'onsubmit' => 'return confirm("確定要移除此rss？")']) !!}
            {!! Form::submit('刪除', ['class' => 'btn btn-danger', 'style'=>'font-size:14px']) !!}
            {!! Form::close() !!}
        </td>
    </tr>
    @endforeach
    </table>


    {!! $feeds->links() !!}
@endsection
