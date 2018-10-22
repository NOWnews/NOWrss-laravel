@extends('feeds.layout')


@section('content')
    <style>
	.page-item.active .page-link{
	    background-color: #62317d;
    	    border-color: #62317d;
	}
	.pagination > .active > a, .pagination > .active > a:focus, .pagination > .active > a:hover, .pagination > .active > span, .pagination > .active > span:focus, .pagination > .active > span:hover{
	    background-color: #a082a6;
            border-color: #a082a6;
	}
    </style>
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Feeds 2018</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-success" href="{{ route('feeds.create') }}"><i class="fa fa-plus"></i> 新增</a>
            </div>
        </div>
    </div>
    {!! Form::open(['method' => 'GET', 'route' => 'feeds.index', 'role' => 'search', 'style'=>'display:inline']) !!}
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>顯示</strong>
            {!! Form::select('pagination', array('25' => '25', '50' => '50', '100' => '100')) !!}
	    <strong>筆</strong>
	    {!! Form::submit('送出', ['class' => 'btn btn-info', 'style'=>'font-size:14px']) !!}
        </div>
    </div>
    {!! Form::close() !!}
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif


    <table class="table table-bordered">
        <tr>
            <th>編號</th>
	    <th>狀態</th>
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
	@if($feed->status == '1')
            <td>啟用</td>
        @else
            <td>停用</td>
        @endif
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
            <a class="btn btn-primary" href="{{ route('feeds.edit',$feed->id) }}"><i class="fa fa-edit"></i> 編輯</a>

 
            {!! Form::open(['method' => 'DELETE','route' => ['feeds.destroy', $feed->id], 'style'=>'display:inline', 'onsubmit' => 'return confirm("確定要移除此rss？")']) !!}
            {!! Form::submit('刪除', ['class' => 'btn btn-danger', 'style'=>'font-size:14px']) !!}
            {!! Form::close() !!}
        </td>
    </tr>
    @endforeach
    </table>


    {!! $feeds->appends(request()->all())->links() !!}
@endsection
