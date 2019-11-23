<?php


namespace App\Services;


use Carbon\Carbon;
use Corcel\Model\Post;
use Illuminate\Support\Collection;

class WordpressPostRssService
{
    const SITES_RSS_MAPPING = [
        'babyouRss' => [
            'name' => '姊妹淘',
            'url' => 'https://babyou.nownews.com/wp-json/wp/v2/posts?page=1&per_page=50&_embed&categories_exclude=15',
            'hasVideo' => false,
        ],
        'rssPetsmao' => [
            'name' => '寵毛網',
            'url' => 'https://petsmao.nownews.com/wp-json/wp/v2/posts?page=1&per_page=50&_embed',
            'hasVideo' => false,
        ],
    ];

    const RSS_TEMPLATES = [
        'fb' => [
            'urlQueries' => [
                'from' => 'fb',
                'utm_source' => 'NaturalLink',
                'utm_medium' => 'fb',
            ],
        ],
        'line' => [
            'urlQueries' => [
                'from' => 'lntoday',
                'utm_source' => 'NaturalLink',
                'utm_medium' => 'lntoday',
            ],
        ],
        'yahoo' => [
            'urlQueries' => [
                'from' => 'yahoo',
                'utm_source' => 'NaturalLink',
                'utm_medium' => 'yahoo',
            ],
        ],
        'sina' => [
            'urlQueries' => [
                'from' => 'sina',
                'utm_source' => 'NaturalLink',
                'utm_medium' => 'sina',
            ],
        ],
        'newtalk2' => [
            'urlQueries' => [
                'from' => 'newtalk2',
                'utm_source' => 'NaturalLink',
                'utm_medium' => 'newtalk2',
            ],
        ],
    ];

    protected $postProperties;
    protected $templateProperties;

    public function __construct(string $site, string $template)
    {
        $this->postProperties = self::SITES_RSS_MAPPING[$site];
        $this->templateProperties = self::RSS_TEMPLATES[$template];
    }

    public function getPostsRss(string $site): Collection
    {
        $postProperties = $this->postProperties;
        $content = file_get_contents($postProperties['url']);
        $posts = collect(json_decode($content, true));
        $categorySubIndex = null;

        $posts = $posts->map(function ($post) use ($postProperties) {
            return (object)[
                'ID' => $post['id'],
                'title' => $this->replaceInvalidChar($post['title']['rendered']),
                'author' => $post['_embedded']['author'][0]['name'],
                'guid' => $post['guid']['rendered'],
                'content' => $this->replaceInvalidChar("{$post['content']['rendered']}新聞來源為「NOWnews今日新聞」"),
                'expert' => $post['excerpt']['rendered'],
                'subcategory' => $this->getSubCategoryName($post),
                'date' => Carbon::parse($post['date'], 'Asia/Taipei')->format('D d M Y H:i:s O'),
                'startYmdtUnix' => Carbon::parse($post['date'], 'Asia/Taipei')->timestamp * 1000,
                'publishTimeUnix' => Carbon::parse($post['date'], 'Asia/Taipei')->timestamp * 1000,
                'updateTimeUnix' => Carbon::parse($post['modified'], 'Asia/Taipei')->timestamp * 1000,
                'image' => $this->getFeaturedImage($post),
                'videoLink' => $postProperties['hasVideo'] ? ($this->getPostMeta($post['id'], 'youtubeLink') ?? '') : '',
                'sameCatNews' => $this->getReadMoreHtml($post, $postProperties['name']),
                'readMoreVendor' => "更多{$postProperties['name']}新聞",
            ];
        });

        return $posts;
    }

    protected function getSubCategoryName(array $post): ?string
    {
        $subCategoryName = null;
        $minCategoryId = min($post['categories']);

        $subcategory = collect($post['_embedded']['wp:term'])
            ->first(function ($terms) use ($minCategoryId, &$categorySubIndex) {
                $valid = false;

                foreach ($terms as $index => $term) {
                    if ($term['id'] === $minCategoryId) {
                        $valid = true;
                        $categorySubIndex = $index;
                        break;
                    }
                }

                return $valid;
            });

        if (isset($subcategory)) {
            $subCategoryName = $subcategory[$categorySubIndex]['name'];
        }

        return $subCategoryName;
    }

    protected function getFeaturedImage(array $post): ?object
    {
        $featuredMedia = $post["_embedded"]["wp:featuredmedia"][0] ?? null;
        $featuredImage = null;

        if (!isset($featuredMedia['id'])) {
            return $featuredImage;
        }

        $featuredImage = (object)[
            'ID' => $featuredMedia['id'],
            'title' => $featuredMedia['title']['rendered'],
            'content' => $featuredMedia['caption']['rendered'],
            'guid' => $featuredMedia['source_url'],
        ];

        return $featuredImage;
    }

    protected function getPostMeta(int $postId, string $meta)
    {
        $meta = null;
        $post = Post::find($postId);

        if (!isset($post)) {
            return $meta;
        }

        $meta = $post->meta->{$meta} ?? null;

        return $meta;
    }

    protected function getReadMoreHtml(array $post, string $siteName): ?string
    {
        $hasMore = false;
        $postsTemplate = '';
        $readMoreTemplate = null;
        $urlQueries = $this->templateProperties['urlQueries'];

        $urlQueries['utm_campaign'] = date('Ymd');
        $linkQueryString = http_build_query($urlQueries);

        for ($i = 1; $i <= 3; $i++) {
            if (!isset($post['metadata'])) {
                continue;
            }

            $title = $post['metadata']["relatedArticleTitle{$i}"];
            $link = $post['metadata']["relatedArticleLink{$i}"];

            if (!isset($title) || !isset($link)) {
                continue;
            }

            $hasMore = true;
            $postsTemplate .= "<br/><a href=\"{$link[0]}?{$linkQueryString}\">{$title[0]}</a>>";
        }

        if (!$hasMore) {
            return $readMoreTemplate;
        }

        $readMoreTemplate = "<div><p class=\"read-more-vendor\"><span>更多{$siteName}新聞</span>{$postsTemplate}</p></div>";

        return $readMoreTemplate;
    }

    protected function replaceInvalidChar(string $string): string
    {
        return preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $string);
    }
}
