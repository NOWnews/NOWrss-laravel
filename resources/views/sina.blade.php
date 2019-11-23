@extends('base.base')

@section('block')
@foreach ($rssPosts as $rssPost)
        <item>
            <id>{{ $rssPost->ID }}</id>
            <title><![CDATA[{{ $rssPost->title }}]]></title>
            <link>{{ $rssPost->guid }}</link>
            <description>
                <![CDATA[
                        @if ($rssPost->image)
                        <div class="main-photo"><img src="{!! $rssPost->image->guid !!}" alt="{!! $rssPost->image->title !!}" /><cite>{!! $rssPost->image->content !!}</cite></div>
                        @endif
                        {!! $rssPost->content !!}
			@if ($rssPost->sameCatNews)
			<div>
			    <h2>相關新聞</h2>
                {!! $rssPost->sameCatNews !!}
			</div>
			@endif
                ]]>
            </description>
            <summary>
                        {{ $rssPost->expert }}
            </summary>
            <guid isPermaLink="true">{{ $rssPost->guid }}</guid>
            <subcategory>
                <![CDATA[
                    {{ $rssPost->subcategory }}
                ]]>
            </subcategory>
            <author>{{ $rssPost->author }}</author>
            <pubDate>{{ $rssPost->date }}</pubDate>
        </item>
@endforeach
@endsection
