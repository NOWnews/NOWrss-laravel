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
                        <div class="main-photo"><img src="{!! $rssPost->image->guid !!}" alt="{!! $rssPost->image->title !!}" /><cite>{!! $rssPost->image->title !!}</cite></div>
                        @endif
                        {!! $rssPost->content !!}
			@if ($rssPost->sameCatNews)
			<div>
			    <h2>相關新聞</h2>
			    @foreach ($rssPost->sameCatNews as $News)
			    <h3><a href="{{ $News->guid }}&amp;utm_source=sina&amp;utm_medium=rss&amp;utm_campaign={{ date('Ymd') }}">{{ $News->title }}</a><h3>
			    @endforeach
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
