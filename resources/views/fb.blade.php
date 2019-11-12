@extends('base.fbbase')

@section('block')
    @foreach ($rssPosts as $rssPost)
        <item>
            <title>{{ $rssPost->title }}</title>
            <link>{{ $rssPost->guid }}</link>
            <description>{{ $rssPost->expert }}</description>
            <pubDate>{{ $rssPost->date }}</pubDate>
            <guid isPermaLink="true">{{ $rssPost->guid }}</guid>
            <category>{{ $rssPost->subcategory }}</category>
            <author>{{ $rssPost->author }}</author>
            <content:encoded>
                <![CDATA[
		{!! $rssPost->content !!}
                @if ($rssPost->sameCatNews)
			{{ $rssPost->sameCatNews }}
                @endif
                ]]>
            </content:encoded>
        </item>
    @endforeach
@endsection
