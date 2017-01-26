<?php

namespace App\Http\Controllers;

use App\Post;
use App\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\RssFeed;
use App\Services\SiteMap;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $tag = $request->get('tag');
        //$data = $this->dispatch(new BlogIndexData($tag));
        if ($tag) {
            $data = $this->tagIndexData($tag);
        } else {
            $data = $this->normalIndexData();
        }
        $layout = $tag ? Tag::layout($tag) : 'blog.layouts.index';

        return view($layout, $data);
    }

    public function siteMap(SiteMap $siteMap){
        $map = $siteMap->getSiteMap();
        return response($map)->header('Content-type', 'text/xml');
    }

    public function rss(RssFeed $feed){

        $rss = $feed->getRSS();
        return response($rss)->header('Content-type', 'application/rss+xml');
    }

    public function showPost($slug, Request $request)
    {
        $post = Post::with('tags')->whereSlug($slug)->firstOrFail();
        $tag = $request->get('tag');
        if ($tag) {
            $tag = Tag::whereTag($tag)->firstOrFail();
        }

        return view($post->layout, compact('post', 'tag', 'slug'));
    }

    /**
     * Return data for normal index page
     *
     * @return array
     */
    protected function normalIndexData()
    {
        $posts = Post::with('tags')
            ->where('published_at', '<=', Carbon::now())
            ->where('is_draft', 0)
            ->orderBy('published_at', 'desc')
            ->simplePaginate(config('blog.posts_per_page'));

        return [
            'title' => config('blog.title'),
            'subtitle' => config('blog.subtitle'),
            'posts' => $posts,
            'page_image' => config('blog.page_image'),
            'meta_description' => config('blog.description'),
            'reverse_direction' => false,
            'tag' => null,
        ];
    }

    /**
     * Return data for a tag index page
     *
     * @param string $tag
     * @return array
     */
    protected function tagIndexData($tag)
    {
        $tag = Tag::where('tag', $tag)->firstOrFail();
        $reverse_direction = (bool)$tag->reverse_direction;

        $posts = Post::where('published_at', '<=', Carbon::now())
            ->whereHas('tags', function ($q) use ($tag) {
                $q->where('tag', '=', $tag->tag);
            })
            ->where('is_draft', 0)
            ->orderBy('published_at', $reverse_direction ? 'asc' : 'desc')
            ->simplePaginate(config('blog.posts_per_page'));
        $posts->addQuery('tag', $tag->tag);

        $page_image = $tag->page_image ?: config('blog.page_image');

        return [
            'title' => $tag->title,
            'subtitle' => $tag->subtitle,
            'posts' => $posts,
            'page_image' => $page_image,
            'tag' => $tag,
            'reverse_direction' => $reverse_direction,
            'meta_description' => $tag->meta_description ?: config('blog.description'),
        ];
    }
}
