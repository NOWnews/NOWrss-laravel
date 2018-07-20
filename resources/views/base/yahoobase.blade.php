<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?><rss version="2.0" xmlns:media="https://yahoo.com" xmlns:content="https://yahoo.com">
    <channel>
        <title><![CDATA[NOWnews 今日新聞網]]></title>
        <image>
            <url>https://www.nownews.com/logo.jpg</url>
            <title>NOWnews 今日新聞網</title>
            <link>https://www.nownews.com</link>
        </image>
        <link>https://www.nownews.com</link>
        <language>zh-tw</language>
        <pubDate>{{ date('D, d M Y H:i:s \G\M\TP') }}</pubDate>
        <lastBuildDate>{{ date('D, d M Y H:i:s \G\M\TP') }}</lastBuildDate>
        <description>Latest news from www.nownews.com</description>
        <copyright>Copyright 2016, NOWnews Network Inc.</copyright>
        <ttl>6</ttl>
        @yield('block')
    </channel>
</rss>
