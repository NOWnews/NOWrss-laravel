@extends('base.base')

@section('block')
@foreach ($rssPosts as $rssPost)
        <item>
            <id>{{ $rssPost->ID }}</id>
            <title><![CDATA[{{ $rssPost->title }}]]></title>
            <link>{{ $rssPost->guid }}</link>
            <description>
                <![CDATA[
                        {!! $rssPost->content !!}
		@if ($rssPost->sameCatNews)
                    <div>
                        <p class="read-more-vendor"><span>延伸閱讀：</span>
                            {!! $rssPost->sameCatNews !!}
                        </p>
                    </div>
                @endif
                ]]>
            </description>
	    <image>
		<![CDATA[
                        @if ($rssPost->image)
                        {!! $rssPost->image->guid !!}
                        @endif
                ]]>
	    </image>
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
