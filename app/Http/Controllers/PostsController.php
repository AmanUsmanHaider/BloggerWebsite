<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class PostsController extends Controller
{

    public function create(Request $request)
    {
        $userId = $request->input('user_id');

        $tagIds = $request->input('tags', []);

        $user = User::find($userId);
        try {
            if ($user) {
                if ($user->hasRole('blogger')) {

                    DB::connection('bloggerwebsite')->beginTransaction();
                    $post = Post::query()->create($request->all());
                    $post->tags()->attach($tagIds);

                    $response =   generateresponse($post, "Post Created Successfully", null, 200);
                }
            }
        } catch (Exception $e) {
            $response = generateresponse(null, "", $e->getMessage(), 404);
        }

        return response()->json($response);
    }


    public function index()
    {
        $posts = Post::with('category', 'tags')->get();
        $data = PostResource::collection($posts);
        $response = generateresponse($data, "Successfully retrieve", null, 200);
        return response()->json($response);
    }


    public function delete($id)
    {
        $post = Post::find($id);
        if ($post) {
            $post->delete();

            $response = generateresponse($post, "Move To Trashed", null, 200);
            return $response;
        } else {
            $response = generateresponse($post, "Not Found", null, 404);
            return $response;
        }
    }
    public function permanentlyDelete($id)
    {
        $post = Post::withTrashed()->find($id);

        if ($post) {
            if ($post->trashed()) {
                $post->forceDelete();
                $response = generateresponse($post, "Post Permanently Deleted", null, 200);
            } else {
                $post->delete();
                $response = generateresponse($post,  "Move To Trashed", null, 200);
            }
        } else {
            $response = generateresponse(null, "Post Not Found", null, 404);
        }

        return response()->json($response);
    }



    public function getter($req = null)
    {
        $posts = Post::query();

        if ($req !== null) {
            if ($req->trash == 'with') {
                $posts->withTrashed()->get();
            }
            if ($req->trash == 'only') {
                $posts->onlyTrashed()->get();
            }
            if ($req->search_type != null && $req->search != null) {
                if ($req->search_type == 'id') {
                    $posts->where('id', $req->search)->get();
                } else {
                    $posts->where('name', 'like', '%' . $req->search . '%')->get();
                }
            }
            if ($req->column != null && $req->sort != null) {

                $posts->orderBy($req->column, $req->sort);
            }
        }
        return $posts->get();
    }
    public function filter(Request $request)
    {
        $posts = $this->getter($request);

        $response = generateresponse($posts, "Success", null, 200);
        return response()->json($response);
    }

    public function update(Request $request)
    {
        $userId = $request->input('user_id');
        $tagIds = $request->input('tags', []);

        $user = User::find($userId);
        try {
            if ($user && $user->hasRole('blogger')) {
                DB::connection('bloggerwebsite')->beginTransaction();


                $post = Post::findOrFail($request->input('id'));


                $post->update($request->all());


                $post->tags()->sync($tagIds);



                $response = generateresponse($post, "Post Updated Successfully", null, 200);
            }
        } catch (Exception $e) {

            DB::connection('bloggerwebsite')->rollBack();

            $response = generateresponse(null, "", $e->getMessage(), 404);
        }

        return response()->json($response);
    }
}
