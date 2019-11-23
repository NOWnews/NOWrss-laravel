@extends('base.linebase')

@section('block')
    @foreach ($rssPosts as $rssPost)
        <article>
            <ID>{{ $rssPost->ID }}</ID>
            <nativeCountry>TW</nativeCountry>
            <language>zh</language>
            <publishCountries>
                <country>TW</country>
            </publishCountries>
            <title><![CDATA[ {{ $rssPost->title }} ]]></title>
            <category>{{ $rssPost->subcategory }}</category>
            <startYmdtUnix>{{ $rssPost->startYmdtUnix }}</startYmdtUnix>
            <endYmdtUnix>{{ $rssPost->startYmdtUnix + 31536000000 }}</endYmdtUnix>
            <publishTimeUnix>{{ $rssPost->publishTimeUnix }}</publishTimeUnix>
            <publishTime>{{ $rssPost->date }}</publishTime>
            <updateTimeUnix>{{ $rssPost->updateTimeUnix }}</updateTimeUnix>
            <contents>
                @if ($rssPost->image)
                    <image>
                        <title>{{ $rssPost->image->title }}</title>
                        <url>{{ $rssPost->image->guid }}</url>
                    </image>
                @endif
                <text>
                    <content>
                        <![CDATA[
                        {!! $rssPost->content !!}
                        ]]>
                    </content>
                </text>
            </contents>
            <author>{{ $rssPost->author }}</author>
            <sourceUrl>{{ $rssPost->guid }}</sourceUrl>
        </article>
    @endforeach
@endsection
