<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Corcel\Model\Post;
use Corcel\Model\User;
use Corcel\Model\Taxonomy;
use Corcel\Model\Attachment;
use App\Feed;

class RssController extends Controller
{
    //
    public function create_uuid($posts_id)
    {
        $str = md5($posts_id);  
        $UUID  = substr($str,0,8) . '-';  
        $UUID .= substr($str,8,4) . '-';  
        $UUID .= substr($str,12,4) . '-';  
        $UUID .= substr($str,16,4) . '-';  
        $UUID .= substr($str,20,12);  
        return $UUID;
    }

    public function get_sameCatNews($PostId, $category, $FeedLanguage)
    {
	$sameCatNews = array();
	$countNewsNum = null;
	$sameCatNewsNum = '3';//same cat news you want +1
	$Post_params = Cache::get('postparams'.$PostId);
        if (!$Post_params) {
                $Post_params = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['can_send_rss' => '1'])->take($sameCatNewsNum)->get();
                Cache::put('postparams'.$PostId, $Post_params, 5);
        }
	//$Post_params = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['can_send_rss' => '1'])->take($sameCatNewsNum)->get();
	foreach($Post_params as $Post_param){
	    if($PostId == $Post_param->ID || $sameCatNewsNum-1 == $countNewsNum){
		continue;
	    }
	    $countNewsNum++;
	    if($FeedLanguage != 'traditional'){
		$PostTitle = $this->convert_language($Post_param->post_title);
	    }else{
		$PostTitle = $Post_param->post_title;
	    }
	    $sameCatNews[] = [
		'ID'    => $Post_param->ID,
		'title' => $PostTitle,
		'guid'  => $Post_param->guid,
	    ];
	}
        return json_decode(json_encode($sameCatNews), FALSE);
    }

    public function get_category($feed)
    {
	$cat_params = explode(",", $feed->category, -1);
        $cats = array();
	$all_cats = Cache::get("allcats");
	if (!$all_cats) {
		$all_cats = Taxonomy::where('taxonomy', 'category')->get();
        	Cache::put("allcats", $all_cats, 5);
	}
	$allCatId = $all_cats->mapWithKeys(function ($item) {
		return [$item['term_id'] => $item->slug];
	});
	foreach($cat_params as $cat){
		$cats[] = $allCatId[$cat];
	}
        return $cats;
    }

    public function get_featuredImage($PostId, $FeedLanguage)
    {
	$image = array();
	$post_param = Post::find($PostId);
	if (!empty($post_param->thumbnail->attachment)){
	    $image_param = $post_param->thumbnail->attachment;
	    $metas = $image_param->meta;
	    $meta_value = null;
	    foreach ($metas as $meta){
	        if($meta->meta_key == 'can-send-by-rss'){
         	    $meta_key   = $meta->meta_key;
		    $meta_value = $meta->meta_value;
	        }else{
		    continue;
	        }
	    }
	//$image_param = Attachment::hasMeta(['can-send-by-rss' => '1'])->first();
	    if($meta_value == '1'){
		if($FeedLanguage != 'traditional'){
		    $imageTitle = $this->convert_language($image_param->post_title);
		    $imageContent = $this->convert_language($image_param->post_content);
		}else{
		    $imageTitle = $image_param->post_title;
                    $imageContent = $image_param->post_content;
		}
	        $image = [
                    'ID'      => $image_param->ID,
                    'title'   => $imageTitle,
	            'content' => $imageContent,
                    'guid'    => $image_param->guid,
                ];
 	    }
	}
	//dd($metas);
        //dd($image);

	return $image;
    }

    public function parser_expert($PostContent, $FeedLanguage)
    {
	//$PostContent = mb_substr(preg_replace('#\[(.*?)\](.*?)\[(.*?)\]|<(.*?)>#is', '', $PostContent), 0, 150, 'utf8');
	$PostContent = preg_replace('#\[(.*?)\](.*?)\[(.*?)\]|<(.*?)>#is', '', $PostContent);
        $charLength = 150;
        $PostContent = mb_strlen($PostContent, 'UTF-8') <= $charLength ? $PostContent : mb_substr($PostContent, 0,$charLength,'UTF-8') . '...';
	if($FeedLanguage != 'traditional'){
	    $PostContent = $this->convert_language($PostContent);
	}
	return $PostContent;
    }

    public function parser_content($PostContent, $FeedLanguage)
    {
	$image_param = array();
	preg_match_all('#<img class(.*?)wp-image-(.[0-9]*)(.*?)\/>#is', $PostContent, $result);
	foreach($result[2] as $res){
	    $metas = Attachment::find($res)->meta;
	    $meta_value = null;
	    foreach ($metas as $meta){
                if($meta->meta_key == 'can-send-by-rss'){
                    $meta_key   = $meta->meta_key;
                    $meta_value = $meta->meta_value;
                }else{
                    continue;
                }
            }
	    //print_r($meta_value);
	    if($meta_value != '1'){
	        $rep = '#\[caption id="attachment_(.*?)<img class="(.*?)wp-image-'.$res.'(.*?)\/>(.*?)\[\/caption\]|<img class="(.*?)wp-image-'.$res.'(.*?)\/>#';
		$PostContent = preg_replace($rep, '', $PostContent);
	    }
	}
	$PostContent = str_replace("\r\n", '<br/>', $PostContent);
	$PostContent = preg_replace('/\[caption(.*?)\]|\[\/caption\]/', '', $PostContent);
	$PostContent = preg_replace('/\[embed\]/', '<iframe width="100%" height="auto" src="', $PostContent);
        $PostContent = preg_replace('/watch\?v=/', 'embed/', $PostContent);
        $PostContent = preg_replace('/\[\/embed\]/', '" frameborder="0" ></iframe>', $PostContent);
	$PostContent = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $PostContent);

        $PostContent = preg_replace('/\[sc name=\"smoke\"(.*?)\]/', '<hr><p>※<a href="https://www.nownews.com/">【 NOWnews 今日新聞 】</a>提醒您 吸菸會導致肺癌、心臟血管疾病，未滿18歲不得吸菸！</p>', $PostContent);
        $PostContent = preg_replace('/\[sc name=\"drup\"(.*?)\]/', '<hr><p>※<a href="https://www.nownews.com/">【 NOWnews 今日新聞 】</a> 提醒您：<br />少一份毒品，多一分健康；吸毒一時，終身危害。<br />※ 戒毒諮詢專線：0800-770-885(0800-請請您-幫幫我)<br />※ 安心專線：0800-788-995(0800-請幫幫-救救我)<br />※ 張老師專線：1980<br />※ 生命線專線：1995</p>', $PostContent);
        $PostContent = preg_replace('/\[sc name=\"suicide\"(.*?)\]/', '<hr><p>※<a href="https://www.nownews.com/">【 NOWnews 今日新聞 】</a> 提醒您：<br />自殺不能解決問題，勇敢求救並非弱者，生命一定可以找到出路。<br />透過守門123步驟-1問2應3轉介，你我都可以成為自殺防治守門人。<br />※ 安心專線：0800-788-995(0800-請幫幫-救救我)<br />※ 張老師專線：1980<br />※ 生命線專線：1995</p>', $PostContent);
        $PostContent = preg_replace('/\[sc name=\"alcohol\"(.*?)\]/', '<hr><p>※<a href="https://www.nownews.com/">【 NOWnews 今日新聞 】</a>提醒您 酒後不開車，飲酒過量有礙健康！</p>', $PostContent);

	//dd($PostContent);
	//dd($lan);
	if($FeedLanguage != 'traditional'){
            $PostContent = $this->convert_language($PostContent);
        }
	return $PostContent;
    }

    public function convert_language($ConvertParam)
    {
        $ConvertParam = iconv("utf-8","big-5//IGNORE",$ConvertParam);
        $ConvertParam = iconv("big-5","gb2312//IGNORE",$ConvertParam);
        $ConvertParam = iconv("gb2312","utf-8//IGNORE",$ConvertParam);
	return $ConvertParam;
    }

    public function convert_param($PostParam, $FeedLanguage)
    {
	if($FeedLanguage != 'traditional'){
		$PostParam = $this->convert_language($PostParam);
	}
	return $PostParam;
    }

    public function index($uuid)
    {
	//select uuid and check layout_style
	$feed = Feed::where(['uuid'=>$uuid])->first();
	$FeedStatus = '';
	if($feed){
	    $FeedLanguage = $feed->language;
            $FeedStatus = $feed->status;
	}
	if($FeedStatus == '1'){
	    /*********************
	     *Set Feed Info Start*
	     *********************/
	    date_default_timezone_set('Etc/GMT-8');
	    $milliseconds = (int)round(microtime(true) * 1000);
	    /*Set Feed Info End*/

	    /**********************
	     *Get Feed Posts Start*
	     **********************/
	    $Posts = collect();
	    $catSlugs = $this->get_category($feed);
	    $countNewsSingle = 50;//cat = 1
	    $countNewsMutiple = $countNewsSingle / count($catSlugs);//cat >= 2

	    if(count($catSlugs) > '1'){
	        foreach ($catSlugs as $catSlug){
                    $category = Taxonomy::where('taxonomy', 'category')->slug($catSlug)->first();
		    $Posts_res = Cache::get('postsresmut'.$category);
		    if (!$Posts_res) {
                        $Posts_res = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['is_age_restriction' => '0', 'can_send_rss' => '1'])->take($countNewsMutiple)->get(); 
                        Cache::put('postsresmut'.$category, $Posts_res, 5);
                    }
		    //$Posts_res = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['is_age_restriction' => '0', 'can_send_rss' => '1'])->take($countNewsMutiple)->get();
	            $Posts = $Posts->merge($Posts_res);
	    	}
	    }elseif(count($catSlugs) == '1'){
		$category = Taxonomy::where('taxonomy', 'category')->slug($catSlugs)->first();
		$Posts_res = Cache::get('postsressin'.$category);
        	if (!$Posts_res) { 
                	$Posts_res = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['is_age_restriction' => '0', 'can_send_rss' => '1'])->take($countNewsSingle)->get();
                	Cache::put('postsressin'.$category, $Posts_res, 5);
        	}
		//$Posts_res = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['is_age_restriction' => '0', 'can_send_rss' => '1'])->take($countNewsSingle)->get();
		$Posts = $Posts_res;
	    }
	    //resort collection
	    $Posts = $Posts->unique(function ($Posts) {
                return $Posts->ID;
            })->sortByDesc('post_date')->values(); 
	    /*Get Feed Posts End*/

	    /*****************************
             *Create UUID For Feeds Start*
             *****************************/
            $posts_id = null;
            foreach ($Posts as $post){
                $posts_id .= $post->ID;
            }
            $UUID = $this->create_uuid($posts_id);
            /*Create UUID For Feeds End*/


            /**************************
	     *Convert Into Array Start*
	     **************************/
	    $rssPosts = array();
	    $rssPosts = $Posts->map(function ($Posts) use ($FeedLanguage) {
		$res = [
		    'ID'               => $Posts->ID,
		    //'author_own'     => User::find($Posts->post_author)->display_name,
		    'author'           => $this->convert_param(preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', Post::find($Posts->ID)->byline), $FeedLanguage),
		    'date'             => date_format($Posts->post_date, 'D d M Y H:i:s O'),
		    //'content_p'      => $Posts->post_content,
		    'content'          => $this->parser_content($Posts->post_content, $FeedLanguage),
		    'expert'           => $this->parser_expert($Posts->post_content, $FeedLanguage),
		    //'expert1'        => mb_substr(preg_replace('#\[(.*?)\](.*?)\[(.*?)\]|<(.*?)>#is', '', $Posts->post_content), 0, 150, 'utf8'),
		    'title'            => $this->convert_param($Posts->post_title, $FeedLanguage),
		    'guid'             => $Posts->guid,
		    'subcategory'      => $this->convert_param(Post::find($Posts->ID)->taxonomies->first()->term->name, $FeedLanguage),
		    'image'            => $this->get_featuredImage($Posts->ID, $FeedLanguage),
		    'startYmdtUnix'    => strtotime($Posts->post_date) * 1000,
		    'publishTimeUnix'  => strtotime($Posts->post_date) * 1000,
		    'updateTimeUnix'   => strtotime($Posts->post_modified) * 1000,
	            'UTCdate'          => date_format($Posts->post_date, 'D, d M Y H:i:s \G\M\TP'),
		    'TaiwanMobileDate' => date_format($Posts->post_date, 'D M d Y H:i:s \G\M\TO'),
		    'sameCatNews'      => $this->get_sameCatNews($Posts->ID, Post::find($Posts->ID)->taxonomies->first(), $FeedLanguage),
		];
                return $res;
            });
	    //dd($rssPosts);

	    $rssPosts = array_slice(json_decode(json_encode($rssPosts), FALSE),0,30);
	    /*Convert Into Array End*/

	    //dd('123');

            return response()->view(strtolower($feed->layout), ['rssPosts'=>$rssPosts,'milliseconds'=>$milliseconds,'UUID'=>$UUID] )->header('Content-Type', 'text/xml');
        }elseif($FeedStatus == '0'){
	    return view('welcome')->with(['uuid'=>$uuid, 'errmsg'=>'此Rss已停用，請通知管理員']);
	}else{
	    return view('welcome')->with('uuid', $uuid);
	}
    }
}
