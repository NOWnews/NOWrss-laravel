<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Corcel\Model\Post;
use Corcel\Model\User;
use Corcel\Model\Taxonomy;
use Corcel\Model\Attachment;
use App\Models\Feed;

class RssController extends Controller
{
    private $develop = false;

    //
    public function create_uuid($posts_id)
    {
        $str = md5($posts_id);
        $UUID = substr($str, 0, 8) . '-';
        $UUID .= substr($str, 8, 4) . '-';
        $UUID .= substr($str, 12, 4) . '-';
        $UUID .= substr($str, 16, 4) . '-';
        $UUID .= substr($str, 20, 12);
        return $UUID;
    }

    public function getYoutubeLink($PostId, $FeedParam)
    {
        $startTime1 = time();

        $post = Post::find($PostId);
        $link = "";
        if (!$post->meta->youtubeLink) {
            $link = "";
        } else {
            $link = $post->meta->youtubeLink;
        }

        if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
            echo "getYoutubeLink spend: " . (time() - $startTime1) . "<br>\r\n";
        }
        return $link;
    }

    public function get_sameCatNews($PostId, $category, $FeedParam)
    {
        $startTime2 = time();

        $sameCatNews = [];
        $countNewsNum = null;
        $sameCatNewsNum = '4';//same cat news you want +1
        $Post_params = null;
        if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
            dump($category->term_id);
            $Post_params = Cache::get('postparams' . $category->term_id);
        } else {
//		$Post_params = Cache::get('postparams'.$PostId);
            $Post_params = Cache::get('postparams' . $category->term_id);
        }
//	$Post_params = Cache::get('postparams'.$PostId);

        if (!$Post_params) {
            if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
                echo "get_sameCatNews has no cache" . "</br>\r\n";
            }

            $Post_params = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['can_send_rss' => '1'])->take($sameCatNewsNum)->get();
            if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
                Cache::put('postparams' . $category->term_id, $Post_params, 5);
            } else {
//			Cache::put('postparams'.$PostId, $Post_params, 5);
                Cache::put('postparams' . $category->term_id, $Post_params, 5);
            }
        }
        foreach ($Post_params as $Post_param) {
            if ($PostId == $Post_param->ID || $sameCatNewsNum - 1 == $countNewsNum) {
                continue;
            }
            $countNewsNum++;
            if ($FeedParam['language'] != 'traditional') {
                $PostTitle = $this->convert_language($Post_param->post_title);
            } else {
                $PostTitle = $Post_param->post_title;
            }
            $PostTitle = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $PostTitle);
            $sameCatNews[] = [
                'ID' => $Post_param->ID,
                'title' => $PostTitle,
                'guid' => $Post_param->guid,
            ];
        }
        if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
            echo $PostId . " getSameCatNews part1 spend: " . (time() - $startTime2) . "<br>\r\n";
        }
        $startTime3 = time();

//	if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4') {
//		$post = Post::find($PostId);
//		$relatedTitle1 = $post->meta->relatedArticleTitle1;
//		$relatedLink1 = $post->meta->relatedArticleLink1;
//		if ($relatedTitle1 != "" && $relatedLink1 != "" && strpos($relatedLink1, "http") == 0) {
//			$sameCatNews[0]['title'] = $relatedTitle1;
//			$sameCatNews[0]['guid'] = $relatedLink1;
//		}
//		$relatedTitle2 = $post->meta->relatedArticleTitle2;
//		$relatedLink2 = $post->meta->relatedArtitleLink2;
//		if ($relatedTitle2 != "" && $relatedLink2 != "" && strpos($relatedLink2, "http") == 0) {
//			$sameCatNews[1]['title'] = $relatedTitle2;
//			$sameCatNews[1]['guid'] = $relatedLink2;
//		}
//		$relatedTitle3 = $post->meta->relatedArticleTitle3;
//		$relatedLink3 = $post->meta->relatedArticleLink3;
//		if ($relatedTitle3 != "" && $relatedLink3 != "" && strpos($relatedLink3, "http") == 0) {
//			$sameCatNews[2]['title'] = $relatedTitle3;
//			$sameCatNews[2]['guid'] = $relatedLink3;
//		}
//	}
//	else {	
        $post = Post::find($PostId);
        $relatedTitle1 = $post->meta->relatedArticleTitle1;
        $relatedLink1 = $post->meta->relatedArticleLink1;
        if ($relatedTitle1 != "" && $relatedLink1 != "" && strpos($relatedLink1, 'http') === 0) {
	    $parseValid0 = false;
            if (strpos($relatedLink1, 'preview_id=') == true) {
                $splitUrl1 = explode('preview_id=', $relatedLink1);
                $sameCatNews[0]['ID'] = $splitUrl1[1];
                $sameCatNews[0]['title'] = $relatedTitle1;
                $sameCatNews[0]['guid'] = 'https://nownews.com/?p=' . $splitUrl1[1];
		$parseValid0 = true;
            } elseif (strpos($relatedLink1, 'game.nownews.com') == true) {
                $splitUrl1 = explode('/', $relatedLink1);
                $sameCatNews[0]['ID'] = $splitUrl1[5];
                $sameCatNews[0]['title'] = $relatedTitle1;
                $sameCatNews[0]['guid'] = $relatedLink1 . '?id=' . $splitUrl1[5];
		$parseValid0 = true;
            } elseif (strpos($relatedLink1, 'action=edit') == true) {
                $splitUrl1 = explode('post=', $relatedLink1)[1];
                $sameCatNews[0]['ID'] = explode('&', $splitUrl1)[0];
                $sameCatNews[0]['title'] = $relatedTitle1;
                $sameCatNews[0]['guid'] = $relatedLink1 . '?id=' . $sameCatNews[0]['ID'];
		$parseValid0 = true;
            } else {
                $splitUrl1 = explode('/', $relatedLink1);
		if (count($splitUrl1) > 5) {
                    $sameCatNews[0]['ID'] = $splitUrl1[5] ?? null;
                    $sameCatNews[0]['title'] = $relatedTitle1;
                    $sameCatNews[0]['guid'] = 'https://nownews.com/?p=' . $splitUrl1[5];
		    $parseValid0 = true;
		}
            }
	    if ($parseValid0) {
                $sameCatNews[0]['relatedLink'] = $relatedLink1;
	    }
        }
        $relatedTitle2 = $post->meta->relatedArticleTitle2;
        $relatedLink2 = $post->meta->relatedArticleLink2;
        if ($relatedTitle2 != "" && $relatedLink2 != "" && strpos($relatedLink2, 'http') === 0) {
	    $parseValid1 = false;
            if (strpos($relatedLink2, 'preview_id=') == true) {
                $splitUrl2 = explode('preview_id=', $relatedLink2);
                $sameCatNews[1]['ID'] = $splitUrl2[1];
                $sameCatNews[1]['title'] = $relatedTitle2;
                $sameCatNews[1]['guid'] = 'https://nownews.com/?p=' . $splitUrl2[1];
		$parseValid1 = true;
            } elseif (strpos($relatedLink2, 'game.nownews.com') == true) {
                $splitUrl2 = explode('/', $relatedLink2);
                $sameCatNews[1]['ID'] = $splitUrl2[5];
                $sameCatNews[1]['title'] = $relatedTitle2;
                $sameCatNews[1]['guid'] = $relatedLink2 . '?id=' . $splitUrl2[5];
		$parseValid1 = true;
            } elseif (strpos($relatedLink2, 'action=edit') == true) {
                $splitUrl2 = explode('post=', $relatedLink2)[1];
                $sameCatNews[1]['ID'] = explode('&', $splitUrl2)[0];
                $sameCatNews[1]['title'] = $relatedTitle2;
                $sameCatNews[1]['guid'] = $relatedLink2 . '?id=' . $sameCatNews[1]['ID'];
		$parseValid1 = true;
            } else {
                $splitUrl2 = explode('/', $relatedLink2);
		if (count($splitUrl2) > 5) {
                    $sameCatNews[1]['ID'] = $splitUrl2[5] ?? null;
                    $sameCatNews[1]['title'] = $relatedTitle2;
                    $sameCatNews[1]['guid'] = 'https://nownews.com/?p=' . $splitUrl2[5];
		    $parseValid1 = true;
		}
            }
	    if ($parseValid1) {
                $sameCatNews[1]['relatedLink'] = $relatedLink2;
	    }
        }
        $relatedTitle3 = $post->meta->relatedArticleTitle3;
        $relatedLink3 = $post->meta->relatedArticleLink3;
        if ($relatedTitle3 != "" && $relatedLink3 != "" && strpos($relatedLink3, 'http') === 0) {
	    $parseValid2 = false;
            if (strpos($relatedLink3, 'preview_id=') == true) {
                $splitUrl3 = explode('preview_id=', $relatedLink3);
                $sameCatNews[2]['ID'] = $splitUrl3[1];
                $sameCatNews[2]['title'] = $relatedTitle3;
                $sameCatNews[2]['guid'] = 'https://nownews.com/?p=' . $splitUrl3[1];
		$parseValid2 = true;
            } elseif (strpos($relatedLink3, 'game.nownews.com') == true) {
                $splitUrl3 = explode('/', $relatedLink3);
                $sameCatNews[2]['ID'] = $splitUrl3[5];
                $sameCatNews[2]['title'] = $relatedTitle3;
                $sameCatNews[2]['guid'] = $relatedLink3 . '?id=' . $splitUrl3[5];
		$parseValid2 = true;
            } elseif (strpos($relatedLink3, 'action=edit') == true) {
                $splitUrl3 = explode('post=', $relatedLink3)[1];
                $sameCatNews[2]['ID'] = explode('&', $splitUrl3)[0];
                $sameCatNews[2]['title'] = $relatedTitle3;
                $sameCatNews[2]['guid'] = $relatedLink3 . '?id=' . $sameCatNews[2]['ID'];
		$parseValid2 = true;
            } else {
                $splitUrl3 = explode('/', $relatedLink3);
		if (count($splitUrl3) > 5) {
                    $sameCatNews[2]['ID'] = $splitUrl3[5] ?? null;
                    $sameCatNews[2]['title'] = $relatedTitle3;
                    $sameCatNews[2]['guid'] = 'https://nownews.com/?p=' . $splitUrl3[5];
		    $parseValid2 = true;
		}
            }
	    if ($parseValid2) {
                $sameCatNews[2]['relatedLink'] = $relatedLink3;
	    }
        }
//	}

        // remove items which relatedLink is not under nownews website
        $sameCatNews = collect($sameCatNews)
            ->filter(function ($item) {
                if (!isset($item['relatedLink'])) {
                    return false;
                }

                return (bool)preg_match('/^https:\/\/(www|game)\.nownews\.com(\/.*$|$)/', $item['relatedLink']);
            })
            ->map(function ($item) {
                unset($item['relatedLink']);
                return $item;
            })
            ->toArray();

        if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
            echo $PostId . " getSameCatNews part2 spend: " . (time() - $startTime3) . "<br>\r\n";
//		dump($sameCatNews);
        }

        return json_decode(json_encode($sameCatNews), false);
    }

    public function get_category($feed)
    {
        $startTime3 = time();

        $category = $feed->category;
        $escapes = ["124251,", "141,", "48,", "10,"];
        $replacements = ["", "", "", ""];
        $category = str_replace($escapes, $replacements, $category);
        $feed->category = $category;
        $cat_params = explode(",", $feed->category, -1);
        $cats = [];
        $all_cats = Cache::get("allcats");
        if (!$all_cats) {
            $all_cats = Taxonomy::where('taxonomy', 'category')->get();
            Cache::put("allcats", $all_cats, 5);
        }
        $allCatId = $all_cats->mapWithKeys(function ($item) {
            return [$item['term_id'] => $item->slug];
        });
        foreach ($cat_params as $cat) {
            $cats[] = $allCatId[$cat];
        }

        if ($feed['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
            echo "get_category spend: " . (time() - $startTime3) . "<br>\r\n";
        }

        return $cats;
    }

    public function get_current_category($PostCats, $FeedParam)
    {
        $startTime4 = time();

        $postCat = null;
        foreach ($PostCats as $PostCat) {
            if (in_array($PostCat->term->slug, $FeedParam['slug'])) {
                $postCat = $PostCat->term->name;
            } else {
                $postCat .= '';
            }
        }
        if ($FeedParam['language'] != 'traditional') {
            $postCat = $this->convert_language($postCat);
        }
        if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
            echo "get_current_category spend: " . (time() - $startTime4) . "<br>\r\n";
        }

        return $postCat;
    }

    public function get_featuredImage($PostId, $FeedParam)
    {
        $startTime5 = time();

        $image = [];
        $post_param = Post::find($PostId);
        if (!empty($post_param->thumbnail->attachment)) {
            $image_param = $post_param->thumbnail->attachment;
            $metas = $image_param->meta;
            $meta_value = null;
            foreach ($metas as $meta) {
                if ($meta->meta_key == 'can-send-by-rss') {
                    $meta_value = $meta->meta_value;
                } else {
                    continue;
                }
            }
            if ($meta_value == '1') {
                $imageTitle = $image_param->post_title;
                $imageTitle = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $imageTitle);
                if ($post_param->_FSMCFIC_featured_image_caption && $post_param->_FSMCFIC_featured_image_caption != "") {
                    $imageContent = $post_param->_FSMCFIC_featured_image_caption;
                } else {
                    $imageContent = $image_param->post_excerpt;
                }
                if ($FeedParam['language'] != 'traditional') {
                    $imageTitle = $this->convert_language($imageTitle);
                    $imageContent = $this->convert_language($imageContent);
                }
                $imageContent = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $imageContent);
                $imageGuid = $image_param->guid;
                $imageGuid = preg_replace('/beta.nownews.com/', 'www.nownews.com', $imageGuid);
                $imageGuid = preg_replace('/migrate.tmder.club/', 'www.nownews.com', $imageGuid);
                $imageGuid = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $imageGuid);
                $image = [
                    'ID' => $image_param->ID,
                    'title' => $imageTitle,
                    'content' => $imageContent,
                    'guid' => $imageGuid,
                ];
            }
        }
        //dd($metas);
        //dd($image);
        if ($FeedParam['uuid'] == 'A89FE992-76D5-72F1-21F9-CD622EA397E7') {
            echo "get_featuredImage spend: " . (time() - $startTime5) . "<br>\r\n";
        }

        return $image;
    }

    public function parser_expert($PostContent, $FeedParam)
    {
        $startTime6 = time();

        $PostContent = preg_replace('#\[(.*?)\](.*?)\[(.*?)\]|<(.*?)>#is', '', $PostContent);
        $escapes = ["\x08"];
        $replacements = ["\\f"];
        $PostContent = str_replace($escapes, $replacements, $PostContent);
        $charLength = 150;
        $PostContent = mb_strlen($PostContent, 'UTF-8') <= $charLength ? $PostContent : mb_substr($PostContent, 0, $charLength, 'UTF-8') . '...';
        if ($FeedParam['language'] != 'traditional') {
            $PostContent = $this->convert_language($PostContent);
        }
        $PostContent = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $PostContent);
        if ($FeedParam['uuid'] == 'A89FE992-76D5-72F1-21F9-CD622EA397E7') {
            //dump($PostContent);
        }
        if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
            echo "parser_expert spend: " . (time() - $startTime6) . "<br>\r\n";
        }

        return $PostContent;
    }

    //public function appendImageBeforeDescription($Post) {
//	$Post->dump();
    //  }

    public function parser_content($PostContent, $FeedParam, $editedMedia)
    {
        $startTime7 = time();

        $image_param = [];
        preg_match_all('#<img class(.*?)wp-image-(.[0-9]*)(.*?)\/>#is', $PostContent, $result);
        foreach ($result[2] as $res) {
            if (!Attachment::find($res))
                return;
            $metas = Attachment::find($res)->meta;
            $meta_value = null;
            foreach ($metas as $meta) {
                if ($meta->meta_key == 'can-send-by-rss') {
                    $meta_value = $meta->meta_value;
                } else {
                    continue;
                }
            }
            //print_r($meta_value);
            if ($meta_value != '1') {
                $rep = '#\[caption id="attachment_(.*?)<img class="(.*?)wp-image-' . $res . '(.*?)\/>(.*?)\[\/caption\]|<img class="(.*?)wp-image-' . $res . '(.*?)\/>#';
                $PostContent = preg_replace($rep, '', $PostContent);
            }
        }
        $PostContent = str_replace("\r\n", '<br/>', $PostContent);
        if ($FeedParam['uuid'] == '5C8E8AFD-0B71-4C2E-B2B8-CCA5E7E8FE6F') {
            $PostContent = str_replace('<br/>', '<p>', $PostContent);
        }
        $PostContent = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $PostContent);
        $escapes = ["\x08"];
        $replacements = ["\\f"];
        $PostContent = str_replace($escapes, $replacements, $PostContent);
        $PostContent = preg_replace('/alt="(.*?)"/', '', $PostContent);
        $PostContent = preg_replace('/\[caption(.*?)\]|\[\/caption\]/', '', $PostContent);
        $PostContent = preg_replace('/\[embed\]/', '<iframe width="100%" height="auto" src="', $PostContent);
        $PostContent = preg_replace('/watch\?v=/', 'embed/', $PostContent);
        $PostContent = preg_replace('/\[\/embed\]/', '" frameborder="0" ></iframe>', $PostContent);
        $PostContent = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $PostContent);
        $PostContent = preg_replace('/\[sc name=\"smoke\"(.*?)\]/', '<hr><p>※<a href="https://www.nownews.com/">【 NOWnews 今日新聞 】</a>提醒您 吸菸會導致肺癌、心臟血管疾病，未滿18歲不得吸菸！</p>', $PostContent);
        $PostContent = preg_replace('/\[sc name=\"drup\"(.*?)\]/', '<hr><p>※<a href="https://www.nownews.com/">【 NOWnews 今日新聞 】</a> 提醒您：<br />少一份毒品，多一分健康；吸毒一時，終身危害。<br />※ 戒毒諮詢專線：0800-770-885(0800-請請您-幫幫我)<br />※ 安心專線：0800-788-995(0800-請幫幫-救救我)<br />※ 張老師專線：1980<br />※ 生命線專線：1995</p>', $PostContent);
        $PostContent = preg_replace('/\[sc name=\"suicide\"(.*?)\]/', '<hr><p>※<a href="https://www.nownews.com/">【 NOWnews 今日新聞 】</a> 提醒您：<br />自殺不能解決問題，勇敢求救並非弱者，生命一定可以找到出路。<br />透過守門123步驟-1問2應3轉介，你我都可以成為自殺防治守門人。<br />※ 安心專線：0800-788-995(0800-請幫幫-救救我)<br />※ 張老師專線：1980<br />※ 生命線專線：1995</p>', $PostContent);
        $PostContent = preg_replace('/\[sc name=\"alcohol\"(.*?)\]/', '<hr><p>※<a href="https://www.nownews.com/">【 NOWnews 今日新聞 】</a>提醒您 酒後不開車，飲酒過量有礙健康！</p>', $PostContent);
        if ($FeedParam['layout'] == 'HINET' || $FeedParam['layout'] == 'POLLSTER') {
            $PostContent = preg_replace('/width="(.*?)" height="(.*?)"/', '', $PostContent);
        }
        if ($FeedParam['layout'] == 'YAHOO') {
	    if ($editedMedia == '1') {
		preg_match_all("/<iframe[^>]+>.*?<\/iframe>/", $PostContent, $matches); //find all iframes
		if ($matches) {
		    foreach($matches[0] as $match) {
			if (strpos($match, 'youtube') !== false) { // remove iframe which contains youtube
			    $PostContent = str_replace($match, '', $PostContent);
			}
		    }
		}
	    }
            $PostContent = str_replace('<br/>', '</p><p>', $PostContent);
            $PostContent = '<p>' . $PostContent . '</p>';
        }

        // remove p tag around img tag when RSS feed IAFB category
        if ($FeedParam['uuid'] === 'EDCEEEA8-EE6B-EC19-C0F1-334253A36D45') {
            $pattern = '/<p><img.*?[^\>]+>/';

            while (true) {
                preg_match($pattern, $PostContent, $matches);

                if (empty($matches)) {
                    break;
                }

                $element = str_replace('<p>', '', $matches[0]);
                $element = "{$element}<p>";
                $PostContent = str_replace($matches[0], $element, $PostContent);
            }
	    $PostContent = "<figure class=\"op-ad\"><iframe width=\"300\" height=\"250\" style=\"border:0; margin:0;\" src=\"https://www.facebook.com/adnw_request?placement=1789112951323406_2424730391094989&adtype=banner300x250\"></iframe></figure>" . $PostContent . "<figure class=\"op-ad\"><iframe width=\"300\" height=\"250\" style=\"border:0; margin:0;\" src=\"https://www.facebook.com/adnw_request?placement=1789112951323406_2429407363960625&adtype=banner300x250\"></iframe></figure>";
        }

        //dd($PostContent);
        //dd($lan);
        if ($FeedParam['language'] != 'traditional') {
            $PostContent = $this->convert_language($PostContent);
        }

        if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
            $pattern = "/<p(>|\s+[^>]*>)<img.*?<\/p>/i";
//		preg_match_all( '/^<p>(<img[^>]+>)(.)<\/p>$/i' , $PostContent, $match );
//		preg_match_all( '/(<p(>|\s+[^>]*>)<img.*?<\/p>)/', $PostContent, $match);
            preg_match_all($pattern, $PostContent, $match);
            //$src = array_pop($match);
            //print_r($src);
            foreach ($match as $value) {
                //	dump($value);
                //$targetImg = '<p>' . $value;
                //dump($targetImg);
                //str_replace($targetImg, $value, $PostContent);
            }
            echo "parser_content spend: " . (time() - $startTime7);
//		dump($PostContent);
        }

        // remove specify string in content
        $PostContent = str_replace('&amp;', '&', $PostContent);
        $PostContent = str_replace('&feature=youtu.be', '', $PostContent);

        return $PostContent;
    }

    public function convert_language($ConvertParam)
    {
        $ConvertParam = iconv("utf-8", "big-5//IGNORE", $ConvertParam);
        $ConvertParam = iconv("big-5", "gb2312//IGNORE", $ConvertParam);
        $ConvertParam = iconv("gb2312", "utf-8//IGNORE", $ConvertParam);
        return $ConvertParam;
    }

    public function convert_param($PostParam, $FeedParam)
    {
        $startTime8 = time();

        if ($FeedParam['language'] != 'traditional') {
            $PostParam = $this->convert_language($PostParam);
        }
        $PostParam = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $PostParam);

        if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
            echo "convert_param spend: " . (time() - $startTime8) . "<br>\r\n";
//		dump($PostParam);
        }

        return $PostParam;
    }

    public function index($uuid)
    {
        $rn = "</br>\r\n";
        $startTime9 = time();
        //select uuid and check layout_style
        $feed = Feed::where(['uuid' => $uuid])->first();
        $FeedParam = [];
        $FeedParam['status'] = '';
        if ($feed) {
            $FeedParam['uuid'] = $uuid;
            $FeedParam['language'] = $feed->language;
            $FeedParam['status'] = $feed->status;
            $FeedParam['layout'] = $feed->layout;
            $FeedParam['slug'] = $this->get_category($feed);
        }

if ($uuid == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
	dump($FeedParam['slug']);
}

        if ($FeedParam['status'] == '1') {
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
            $countNewsSingle = 30;//cat = 1
            $countNewsMutiple = $countNewsSingle / count($FeedParam['slug']);//cat >= 2

            if (count($FeedParam['slug']) > '1') {
                if ($uuid == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
                    echo "slug > 1" . $rn;
                }
                foreach ($FeedParam['slug'] as $catSlug) {
                    if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
                        echo "--category: " . $catSlug . $rn;
                    }

                    $category = Taxonomy::where('taxonomy', 'category')->slug($catSlug)->first();

                    if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
                        echo "===category: " . $category . $rn;
                    }
                    //$Posts_res = Cache::get('postsresmut'.$category);
                    //if (!$Posts_res) {
//                        $Posts_res = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['is_age_restriction' => '0', 'can_send_rss' => '1'])->take($countNewsMutiple)->get(); 
                    // Cache::put('postsresmut'.$category, $Posts_res, 5);
                    //}

                    if (Cache::has('postsresmut' . $category)) {
                        if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
                            echo "has cache" . $rn;
                        }
                        $Posts_res = Cache::get('postsresmut' . $category);
                    } else {
                        if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
                            echo "has no cache" . $rn;
                        }
                        $Posts_res = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['is_age_restriction' => '0', 'can_send_rss' => '1'])->take($countNewsMutiple)->get();
                        Cache::add('postsresmut' . $category, $Posts_res, 5);
                    }
                    $Posts = $Posts->merge($Posts_res);
                }
            } elseif (count($FeedParam['slug']) == '1') {
                if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
                    echo "slug == 1" . $rn;
                }

                $category = Taxonomy::where('taxonomy', 'category')->slug($FeedParam['slug'])->first();


                //$Posts_res = Cache::get('postsressin'.$category);
                //if (!$Posts_res) {
//                	$Posts_res = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['is_age_restriction' => '0', 'can_send_rss' => '1'])->take($countNewsSingle)->get();
                //	Cache::put('postsressin'.$category, $Posts_res, 5);
                //}


                if (Cache::has('postsressin' . $category)) {
                    if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
                        echo "has cache" . $rn;
                    }
                    $Posts_res = Cache::get('postsressin' . $category);
                } else {
                    if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
                        echo "has no cache" . $rn;
                    }
                    $Posts_res = $category->posts()->newest()->status('publish')->type('post')->hasMeta(['is_age_restriction' => '0', 'can_send_rss' => '1'])->take($countNewsSingle)->get();
                    Cache::add('postsressin' . $category, $Posts_res, 5);
                }
                $Posts = $Posts_res;
            }

            if ($uuid == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
                echo "get Posts spend: " . (time() - $startTime9) . "</br>\r\n";
            }

            //resort collection
            $Posts = $Posts->unique(function ($Posts) {
                return $Posts->ID;
            })->sortByDesc('post_date')->values();
            /*Get Feed Posts End*/

            if ($uuid == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
//		dump($Posts);
            }
            /**************************
             *Convert Into Array Start*
             **************************/
            $rssPosts = [];
            $rssPosts = $Posts->map(function ($Posts) use ($FeedParam) {
                $startTime = time();
                $res = [
                    'ID' => $Posts->ID,
                    'author' => $this->convert_param(preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', Post::find($Posts->ID)->byline), $FeedParam),
                    'date' => date_format($Posts->post_date, 'D d M Y H:i:s O'),
                    'content' => $this->parser_content($Posts->post_content, $FeedParam, $Posts->meta->editedMedia),
                    'expert' => $this->parser_expert($Posts->post_content, $FeedParam),
                    'title' => $this->convert_param(preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $Posts->post_title), $FeedParam),
                    'guid' => $Posts->guid,
                    'subcategory' => $this->get_current_category(Post::find($Posts->ID)->taxonomies->where('taxonomy', 'category'), $FeedParam),
                    'image' => $this->get_featuredImage($Posts->ID, $FeedParam),
                    'startYmdtUnix' => strtotime($Posts->post_date) * 1000,
                    'publishTimeUnix' => strtotime($Posts->post_date) * 1000,
                    'updateTimeUnix' => strtotime($Posts->post_modified) * 1000,
                    'UTCdate' => date_format($Posts->post_date, 'D, d M Y H:i:s \G\M\TP'),
                    'TaiwanMobileDate' => date_format($Posts->post_date, 'D M d Y H:i:s \G\M\TO'),
                    'sameCatNews' => $this->get_sameCatNews($Posts->ID, Post::find($Posts->ID)->taxonomies->first(), $FeedParam),
                    'videoLink' => $this->getYoutubeLink($Posts->ID, $FeedParam),
                    'readMoreVendor' => '更多 NOWnews 今日新聞 報導',
                ];

                // rewrite readMoreVendor if Yahoo_HK
//                if ($FeedParam['uuid'] === '53F317D5-3B3A-4487-9505-CA526A9D54B8') {
//                    $res['readMoreVendor'] = '更多 NOWnews 今日新聞 報導';
//                }
		if ($FeedParam['uuid'] === 'E83A4F6A-5327-4835-92B9-876179BC68EE') {
		    $res['readMoreVendor'] = '更多 今日新聞 報導';
		}

                $endTime = time();
                if ($FeedParam['uuid'] == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4' && $this->develop == true) {
                    echo "map array total spend: " . (time() - $startTime) . "</br>\r\n";
                }
                return $res;
            });
//	    dd($rssPosts);
            //This site will filter the posts before 2018/10/01
            if ($uuid == "692AEC15-5C02-F284-59B5-42CE31878B71") {
                $filterRssPosts = [];
                $filterRssPosts = $rssPosts->filter(function ($filterPost) {
                    return data_get($filterPost, "publishTimeUnix") > 1538352000000;
                });
                $rssPosts = $filterRssPosts->all();
            } elseif ($uuid == '9B3A422A-1605-086A-1292-6A56B8F671C1') {
                //dump($rssPosts);
            } elseif ($uuid == "FAB52423-E1AA-B444-2850-D19331C6C14B") { //Newtalks
//		$filterRssPosts = array();
//		$filterRssPosts = $rssPosts->map(function ($item, $key) {
//			if ($item['image']) {
//				$item['content'] = $item['image']['guid'] . ' ' . $item['image']['content'] . ' ' . $item['content'];
//			}
//			return $item;
//		});
//		$rssPosts = $filterRssPosts->all();
            } elseif ($uuid == 'A89FE992-76D5-72F1-21F9-CD622EA397E7') {
//		$filterRssPosts = array();
//		$filterRssPosts = $rssPosts->filter(function ($filterPost) {
//			return !str_contains(data_get($filterPost, "content"), "圖／美聯社／達志影像");
//		})->values();
//		$rssPosts = $filterRssPosts->all();

//		$rssPosts->dump();

                $filterRssPosts = [];
                $filterRssPosts = $rssPosts->map(function ($item, $key) {
                    if ($item['image']) {
                        $item['content'] = '<p><img src="' . $item['image']['guid'] . '" alt="' . $item['image']['title'] . '"</br>' . $item['content'];
                    }
//			dump($item);
                    return $item;
                });

                $rssPosts = $filterRssPosts->all();
            }
            $rssPosts = array_slice(json_decode(json_encode($rssPosts), false), 0, 30);
            /*Convert Into Array End*/

            /*****************************
             *Create UUID For Feeds Start*
             *****************************/
            $posts_id = null;
            foreach ($rssPosts as $rsspost) {
                $posts_id .= $rsspost->ID;
            }
            $UUID = $this->create_uuid($posts_id);
            /*Create UUID For Feeds End*/
            //dd('123');

            if ($uuid == 'B1729FBF-F5C5-2F05-F930-6D4E4678C7F4') {
                dump($rssPosts);
                echo "index spend: " . (time() - $startTime9) . "<br>\r\n";
            }

            return response()->view(strtolower($feed->layout), ['rssPosts' => $rssPosts, 'milliseconds' => $milliseconds, 'UUID' => $UUID])->header('Content-Type', 'text/xml');
        } elseif ($FeedParam['status'] == '0') {
            return view('welcome')->with(['uuid' => $uuid, 'errmsg' => '此Rss已停用，請通知管理員']);
        } else {
            return view('welcome')->with('uuid', $uuid);
        }
    }

    public function petsmaoRss(Request $request) {
	$url = "https://petsmao.nownews.com/wp-json/wp/v2/posts?page=1&per_page=50&_embed";
	$content = file_get_contents("$url");
	$jsonContent = json_decode($content, true);
	$milliseconds = (int)round(microtime(true) * 1000);
	$rssPosts = [];
	$posts_id = null;

	foreach($jsonContent as $data) {
	    $posts_id .= $data['id'];
	    $categories = $data["categories"];
	    $minCategory = min($categories);
	    $minCategoryIndex = array_search($minCategory, $categories);

	    if (isset($data["metadata"]["can_send_rss"]) && $data["metadata"]["can_send_rss"][0] === "0") {
		continue;
	    }

	    $item = [
		'ID' => $data["id"],
		'title' => $data["title"]["rendered"],
		'author' => $data["_embedded"]["author"][0]["name"],
		'guid' => $data["guid"]["rendered"],
		'content' => $data["content"]["rendered"] . $this->getRelatedArticles($data),
		'expert' => $data["excerpt"]["rendered"],
		'subcategory' => $data["_embedded"]["wp:term"][0][$minCategoryIndex]["name"],
		'date' => date_format(date_create($data["date"]), 'D d M Y H:i:s O'),
		'startYmdtUnix' => strtotime($data["date"]) * 1000,
                'publishTimeUnix' => strtotime($data["date"]) * 1000,
                'updateTimeUnix' => strtotime($data["modified"]) * 1000,
		'image' => $this->getFeaturedImageData($data),
		'videoLink' => ""
	    ];
	    array_push($rssPosts, (object)$item);

	    if (count($rssPosts) >= 30) {
		break;
	    }
	}

	$UUID = $this->create_uuid($posts_id);
	return response()->view(strtolower('line'), ['rssPosts' => $rssPosts, 'milliseconds' => $milliseconds, 'UUID' => $UUID])->header('Content-Type', 'text/xml');
    }

    private function getFeaturedImageData($data) {
	if (isset($data["_embedded"]["wp:featuredmedia"][0]["id"])) {
	$image = [
	    'ID' => $data["_embedded"]["wp:featuredmedia"][0]["id"],
            'title' => $data["_embedded"]["wp:featuredmedia"][0]["title"]["rendered"],
            'content' => $data["_embedded"]["wp:featuredmedia"][0]["caption"]["rendered"],
            'guid' => $data["_embedded"]["wp:featuredmedia"][0]["source_url"]
	];
	    return (object)$image;
	}
	else {
	    $image = [];
	    return (object)$image;
	}
    }

    private function getFeaturedImageDataObject($data) {
        if (isset($data["_embedded"]["wp:featuredmedia"][0]["id"])) {
    	    $image = [
                'ID' => $data["_embedded"]["wp:featuredmedia"][0]["id"],
                'title' => $data["_embedded"]["wp:featuredmedia"][0]["title"]["rendered"],
                'content' => $data["_embedded"]["wp:featuredmedia"][0]["caption"]["rendered"],
                'guid' => $data["_embedded"]["wp:featuredmedia"][0]["source_url"]
            ];
            return $image;
        }
        else {
            $image = [];
            return (object)$image;
        }
    }

    private function getRelatedArticles($data) {
	$hasMore = false;
	$moreStr = "";
	$more1 = "";
	$more2 = "";
	$more3 = "";
	$count = 0;
	if (isset($data["metadata"]["relatedArticleTitle1"]) && isset($data["metadata"]["relatedArticleLink1"]) && $count < 2) {
	    $title1 = $data["metadata"]["relatedArticleTitle1"][0];
	    $link1 = $data["metadata"]["relatedArticleLink1"][0] . "?from=lntoday&utm_source=NaturalLink&utm_medium=lntoday";
	    $hasMore = true;
	    $more1 = "<br/><a href=\"" . $link1 . "\">" . $title1 . "</a>"; 
	    $count++;
	}
	if (isset($data["metadata"]["relatedArticleTitle2"]) && isset($data["metadata"]["relatedArticleLink2"]) && $count < 2) {
            $title2 = $data["metadata"]["relatedArticleTitle2"][0];
            $link2 = $data["metadata"]["relatedArticleLink2"][0] . "?from=lntoday&utm_source=NaturalLink&utm_medium=lntoday";
            $hasMore = true;
            $more2 = "<br/><a href=\"" . $link2 . "\">" . $title2 . "</a>";
	    $count++;
        }
	if (isset($data["metadata"]["relatedArticleTitle3"]) && isset($data["metadata"]["relatedArticleLink3"]) && $count < 2) {
            $title3 = $data["metadata"]["relatedArticleTitle3"][0];
            $link3 = $data["metadata"]["relatedArticleLink3"][0] . "?from=lntoday&utm_source=NaturalLink&utm_medium=lntoday";
            $hasMore = true;
            $more3 = "<br/><a href=\"" . $link3 . "\">" . $title3 . "</a>";
	    $count++;
        }

	if ($hasMore) {
	    $moreStr = "<div><p class=\"read-more-vendor\"><span>更多寵毛網新聞</span>" . $more1 . $more2 . $more3 . "</p></div>";
	}
	
	$moreStr = $moreStr . "<div><br/><a href=\"http://line.me/ti/p/@nownews\">想看更多寵物資訊！立即加入NOWnews今日新聞官方帳號</a></div>"; 

	return $moreStr;
    }

    private function getRelatedArticlesArr($data, $websiteTitle) {
	$hasMore = false;
	$moreStr = "";
        $more1 = "";
        $more2 = "";
        $more3 = "";
        $count = 0;
	if (isset($data["metadata"]["relatedArticleTitle1"]) && isset($data["metadata"]["relatedArticleLink1"])) {
            $title1 = $data["metadata"]["relatedArticleTitle1"][0];
	    $link1 = $data["metadata"]["relatedArticleLink1"][0] . "?from=lntoday&utm_source=NaturalLink&utm_medium=lntoday";
            $hasMore = true;
            $more1 = "<br/><a href=\"" . $link1 . "\">" . $title1 . "</a>";
        }
	if (isset($data["metadata"]["relatedArticleTitle2"]) && isset($data["metadata"]["relatedArticleLink2"])) {
            $title2 = $data["metadata"]["relatedArticleTitle2"][0];
            $link2 = $data["metadata"]["relatedArticleLink2"][0] . "?from=lntoday&utm_source=NaturalLink&utm_medium=lntoday";
            $hasMore = true;
            $more2 = "<br/><a href=\"" . $link2 . "\">" . $title2 . "</a>";
            $count++;
        }
        if (isset($data["metadata"]["relatedArticleTitle3"]) && isset($data["metadata"]["relatedArticleLink3"])) {
            $title3 = $data["metadata"]["relatedArticleTitle3"][0];
            $link3 = $data["metadata"]["relatedArticleLink3"][0] . "?from=lntoday&utm_source=NaturalLink&utm_medium=lntoday";
            $hasMore = true;
            $more3 = "<br/><a href=\"" . $link3 . "\">" . $title3 . "</a>";
            $count++;
        }

	if ($hasMore) {
            $moreStr = "<div><p class=\"read-more-vendor\"><span>更多" . $websiteTitle . "新聞</span>" . $more1 . $more2 . $more3 . "</p></div>";
        }

	return $moreStr;
    }

    public function babyouRss($type) {
	$url = "https://babyou.nownews.com/wp-json/wp/v2/posts?page=1&per_page=50&_embed&categories_exclude=15";
        $content = file_get_contents("$url");
        $jsonContent = json_decode($content, true);
        $milliseconds = (int)round(microtime(true) * 1000);
        $rssPosts = [];
        $posts_id = null;

        foreach($jsonContent as $data) {
            $posts_id .= $data['id'];
            $categories = $data["categories"];
            $minCategory = min($categories);
            $minCategoryIndex = array_search($minCategory, $categories);

            if (isset($data["metadata"]["can_send_rss"]) && $data["metadata"]["can_send_rss"][0] === "0") {
//                continue;
            }

            $item = [
                'ID' => $data["id"],
                'title' => $data["title"]["rendered"],
                'author' => $data["_embedded"]["author"][0]["name"],
                'guid' => $data["guid"]["rendered"],
                'content' => $data["content"]["rendered"] . "新聞來源為「NOWnews今日新聞」",
                'expert' => $data["excerpt"]["rendered"],
                'subcategory' => $data["_embedded"]["wp:term"][0][$minCategoryIndex]["name"],
                'date' => date_format(date_create($data["date"]), 'D d M Y H:i:s O'),
                'startYmdtUnix' => strtotime($data["date"]) * 1000,
                'publishTimeUnix' => strtotime($data["date"]) * 1000,
                'updateTimeUnix' => strtotime($data["modified"]) * 1000,
                'image' => $this->getFeaturedImageDataObject($data),
                'videoLink' => "",
		'sameCatNews' => $this->getRelatedArticlesArr($data, "姊妹淘")
            ];


	    if (array_get($item, 'image')) {
		$imageGuid = array_get(array_get($item, 'image'), 'guid');
		$imageContent = array_get(array_get($item, 'image'), 'content');
		$postContent = array_get($item, 'content');
		$postContent = "<div class=\"main-photo\"><img src=\"" . $imageGuid . "\" alt=\"" . $imageContent . "\"/></div>" . $postContent;
		array_set($item, 'content', $postContent);
	    }

            array_push($rssPosts, (object)$item);

            if (count($rssPosts) >= 30) {
                break;
            }
        }

        $UUID = $this->create_uuid($posts_id);
        return response()->view(strtolower($type), ['rssPosts' => $rssPosts, 'milliseconds' => $milliseconds, 'UUID' => $UUID])->header('Content-Type', 'text/xml');
    }
}
