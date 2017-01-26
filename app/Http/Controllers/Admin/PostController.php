<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostCreateRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Jobs\PostFormFields;
use App\Post;
use App\Tag;
use Carbon\Carbon;

class PostController extends Controller
{
    protected $fieldList = [
        'title' => '',
        'subtitle' => '',
        'page_image' => '',
        'content' => '',
        'meta_description' => '',
        'is_draft' => "0",
        'publish_date' => '',
        'publish_time' => '',
        'layout' => 'blog.layouts.post',
        'tags' => [],
    ];

    /**
     * Display a listing of the posts.
     */
    public function index()
    {
        return view('admin.post.index')
            ->withPosts(Post::all());
    }

    /**
     * Show the new post form
     */
    public function create()
    {
        $fields = $this->fieldList;
        $when = Carbon::now()->addHour();
        $fields['publish_date'] = $when->format('M-j-Y');
        $fields['publish_time'] = $when->format('g:i A');
        foreach ($fields as $fieldName => $fieldValue) {
            $fields[$fieldName] = old($fieldName, $fieldValue);
        }
        $fields = array_merge(
            $fields,
            ['allTags' => Tag::pluck('tag')->all()]
        );
        //$data = $this->dispatch(new PostFormFields());
        return view('admin.post.create', $fields);
    }

    /**
     * Store a newly created Post
     *
     * @param PostCreateRequest $request
     */
    public function store(PostCreateRequest $request)
    {
        $post = Post::create($request->postFillData());
        $post->syncTags($request->get('tags', []));

        return redirect('/admin/post')
            ->withSuccess('New Post Successfully Created.');
    }

    /**
     * Show the post edit form
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $fields = $this->fieldList;
        //$data = $this->dispatch(new PostFormFields($id));
        $fields = $this->fieldsFromModel($id, $fields);
        foreach ($fields as $fieldName => $fieldValue) {
            $fields[$fieldName] = old($fieldName, $fieldValue);
        }
        $fields = array_merge(
            $fields,
            ['allTags' => Tag::pluck('tag')->all()]
        );
        return view('admin.post.edit', $fields);
    }

    /**
     * Update the Post
     *
     * @param PostUpdateRequest $request
     * @param int $id
     */
    public function update(PostUpdateRequest $request, $id)
    {
        $post = Post::findOrFail($id);
        $post->fill($request->postFillData());
        $post->save();
        $post->syncTags($request->get('tags', []));

        if ($request->action === 'continue') {
            return redirect()
                ->back()
                ->withSuccess('Post saved.');
        }

        return redirect('/admin/post/')
            ->withSuccess('Post saved.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->tags()->detach();
        $post->delete();

        return redirect('/admin/post/')
            ->withSuccess('Post deleted.');
    }

    /**
     * Return the field values from the model
     *
     * @param integer $id
     * @param array $fields
     * @return array
     */
    protected function fieldsFromModel($id, array $fields)
    {
        $post = Post::findOrFail($id);

        $fieldNames = array_keys(array_except($fields, ['tags']));

        $fields = ['id' => $id];
        foreach ($fieldNames as $field) {
            $fields[$field] = $post->{$field};
        }

        $fields['tags'] = $post->tags()->pluck('tag')->all();

        return $fields;
    }
}
