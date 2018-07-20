<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Corcel\Model\Post;
use Corcel\Model\User;
use Corcel\Model\Taxonomy;
use Corcel\Model\Attachment;
use App\Feed;

class RssController extends Controller
{
    //
    public function create_uuid()
    {
        $str = md5(uniqid(mt_rand(), true));  
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

	$Post_params = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['can_send_rss' => '1'])->take($sameCatNewsNum)->get();
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
        $all_cats = Taxonomy::where('taxonomy', 'category')->get();
        foreach($all_cats as $cat){
            if(in_array($cat->term_id, $cat_params)){
//                $catid = $cat->term_id;
                $catSlug = $cat->term->slug;
                $cats[] = $catSlug;
            }else{
                continue;
//                $catid = $cat->parent;
//                $catname = $cat->term->name;
//                echo $catid.$catname." NOT A MOTHER CAT<br>";
            }
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
	$PostContent = mb_substr(preg_replace('#\[(.*?)\](.*?)\[(.*?)\]|<(.*?)>#is', '', $PostContent), 0, 150, 'utf8');
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
	$PostContent = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $PostContent);
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
	$FeedLanguage = $feed->language;
	if($feed){
	    /*********************
	     *Set Feed Info Start*
	     *********************/
	    date_default_timezone_set('Etc/GMT-8');
	    $UUID = $this->create_uuid();
	    $milliseconds = (int)round(microtime(true) * 1000);
	    $countNewsMutiple = 35;//cat >= 2
            $countNewsSingle = 70;//cat = 1
	    /*Set Feed Info End*/

	    /**********************
	     *Get Feed Posts Start*
	     **********************/
	    $Posts = collect();
	    $catSlugs = $this->get_category($feed);
	    if(count($catSlugs) > '1'){
	        foreach ($catSlugs as $catSlug){
                    $category = Taxonomy::where('taxonomy', 'category')->slug($catSlug)->first();
		    $Posts_res = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['is_age_restriction' => '0', 'can_send_rss' => '1'])->take($countNewsMutiple)->get();
	            $Posts = $Posts->merge($Posts_res);
	    	}
	    }elseif(count($catSlugs) == '1'){
		$category = Taxonomy::where('taxonomy', 'category')->slug($catSlugs)->first();
		$Posts_res = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['is_age_restriction' => '0', 'can_send_rss' => '1'])->take($countNewsSingle)->get();
		$Posts = $Posts_res;
	    }
	    //resort collection
	    $Posts = $Posts->unique(function ($Posts) {
                return $Posts->ID;
            })->sortByDesc('post_date')->values(); 
	    /*Get Feed Posts End*/

            /**************************
	     *Convert Into Array Start*
	     **************************/
	    $rssPosts = array();
	    $rssPosts = $Posts->map(function ($Posts) use ($FeedLanguage) {
		$res = [
		    'ID'               => $Posts->ID,
		    //'author_own'     => User::find($Posts->post_author)->display_name,
		    'author'           => $this->convert_param(Post::find($Posts->ID)->byline, $FeedLanguage),
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
	    //dd('123');

	    $rssPosts = array_slice(json_decode(json_encode($rssPosts), FALSE),0,60);
	    /*Convert Into Array End*/

	    //dd('123');

            return response()->view(strtolower($feed->layout), ['rssPosts'=>$rssPosts,'milliseconds'=>$milliseconds,'UUID'=>$UUID] )->header('Content-Type', 'text/xml');
        }else{
	    return view('welcome')->with('uuid', $uuid);
	}
    }
}
