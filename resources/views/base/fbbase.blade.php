<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?><rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title><![CDATA[NOWnews 今日新聞網]]></title>
        <image>
            <url>https://www.nownews.com/logo.jpg</url>
            <title>NOWnews 今日新聞網</title>
            <link>https://www.nownews.com</link>
        </image>
        <link>https://www.nownews.com</link>
        <language>zh-tw</language>
        <pubDate>{{ date('D d M Y H:i:s O') }}</pubDate>
        <lastBuildDate>{{ date('D d M Y H:i:s O') }}</lastBuildDate>
        <description>Latest news from www.nownews.com</description>
        <copyright>Copyright 2016, NOWnews Network Inc.</copyright>
        <ttl>6</ttl>
        @yield('block')
    </channel>
</rss>
