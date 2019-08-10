<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class TriggerUrlService {
	public function trigger($import) {
		echo "trigger start";

		//yahoo rss url
		$url_yahoo = "https://feed.nownews.com/rss/53F317D5-3B3A-4487-9505-CA526A9D54B8";
//		$url_testRss = "https://feed.nownews.com/rss/B1729FBF-F5C5-2F05-F930-6D4E4678C7F4";

		file_get_contents("$url_yahoo");
//		file_get_contents("$url_yahoo");
//		file_get_contents("$url_yahoo");
//		echo "trigger end";
	}
}
