<!-- home.blade.php -->
@extends('feeds.layout')

@section('content')
<div class="content-wrapper">
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
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif
    <section class="content">
        <div class="row">
            <table id="pageTable" class="table table-bordered table-hover">
                <thead>
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
                </thead>

                <tbody>
                @foreach ($feeds as $feed)
                <tr>
                    <td>
                        {{ $feed->id }}
                    </td>
                    @if($feed->status == '1')
                        <td>啟用</td>
                    @else
                        <td>停用</td>
                    @endif
                    <td>
                        {{ $feed->created_at }}
                    </td>
                    <td>
                        {{ $feed->title }}
                    </td>
                    @if($feed->language == 'traditional' || !$feed->language)
                        <td>繁體</td>
                    @else
                        <td>簡體</td>
                    @endif
                    <td>
                        {{ $feed->uuid }}
                    </td>
                    <td>
                        {{ $feed->layout }}
                    </td>
                    <td>
                        <a class="btn btn-info" href="{{ url('/rss\/').$feed->uuid }}" target="_blank">RSS連結</a>
                        <a class="btn btn-primary" href="{{ route('feeds.edit',$feed->id) }}"><i class="fa fa-edit"></i> 編輯</a>


                        {!! Form::open(['method' => 'DELETE','route' => ['feeds.destroy', $feed->id], 'style'=>'display:inline', 'onsubmit' => 'return confirm("確定要移除此rss？")']) !!}
                        {!! Form::submit('刪除', ['class' => 'btn btn-danger', 'style'=>'font-size:14px']) !!}
                        {!! Form::close() !!}
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
<script>
    jQuery(function($) {
        //initiate dataTables plugin
        var myTable =
            $('#pageTable')
                .wrap("<div class='dataTables_borderWrap col-12' />")   //if you are applying horizontal scrolling (sScrollX)
                .DataTable( {
                    bAutoWidth: false,
                    "aoColumns": [
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null
                    ],
                    "aaSorting": [],
                    "oLanguage": {
                        "sSearch": "搜尋",
                        "sZeroRecords": "無此搜尋結果，請重新查詢或通知管理員。",
                        "oPaginate": {
                            "sPrevious": "上一頁",
                            "sNext": "下一頁"
                        }
                    },

                    //"bProcessing": true,
                    //"bServerSide": true,
                    //"sAjaxSource": "http://127.0.0.1/table.php"   ,

                    //,
                    //"sScrollY": "100%",
                    //"bPaginate": true,

                    //"sScrollX": "100%",
                    //"sScrollXInner": "100%",
                    //"bScrollCollapse": true,
                    //Note: if you are applying horizontal scrolling (sScrollX) on a ".table-bordered"
                    //you may want to wrap the table inside a "div.dataTables_borderWrap" element

                    //"iDisplayLength": 50

                    select: {
                        style: 'multi'
                    }
                });
    });
</script>
@endsection

