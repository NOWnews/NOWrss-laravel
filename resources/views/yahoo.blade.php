@extends('base.yahoobase')

@section('block')
@foreach ($rssPosts as $rssPost)
        <item>
            <title>{{ $rssPost->title }}</title>
            <link>{{ $rssPost->guid }}</link>
            <description>{{ $rssPost->expert }}</description>
            <pubDate>{{ $rssPost->UTCdate }}</pubDate>
            <guid isPermaLink="true">{{ $rssPost->guid }}</guid>
            <category>{{ $rssPost->subcategory }}</category>
            <author>{{ $rssPost->author }}</author>
            @if ($rssPost->image) <media:content url="{{ $rssPost->guid }}" type="image/jpeg"></media:content> @endif
            <content:encoded>
	    <![CDATA[
            @if ($rssPost->image) <div class="main-photo"><img src="{!! $rssPost->image->guid !!}" alt="{!! $rssPost->image->content !!}" /></div> @endif {!! $rssPost->content !!}
            @if ($rssPost->sameCatNews)
                <div>
		    <p class="read-more-vendor"><span>更多 NOWnews 今日新聞報導</span>
		    @foreach ($rssPost->sameCatNews as $News)
			<br/><a href="{!! $News->guid !!}&amp;utm_source=yahoo&amp;utm_medium=rss&amp;utm_campaign={{ date('Ymd') }}">{{ $News->title }}</a>
		    @endforeach
		    </p>
                </div>
            @endif
	    ]]>
            </content:encoded>
        </item>
@endforeach
@endsection
