<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagCollection;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagsController extends Controller
{
    public function index()
    {
        $tags = Tag::get();

        $data = TagResource::collection($tags);
        $response = generateresponse($data, "Successfully retrieve", null, 200);
        return response()->json($response);
    }
    public function create(Request $request)
    {

        try {
            DB::connection('bloggerwebsite')->beginTransaction();
            $tags = Tag::query()->create($request->all());
            $response =   generateresponse($tags, "Tag Created Successfully", null, 200);
        } catch (Exception $e) {
            $response = generateresponse(null, "", $e->getMessage(), 404);
        }
        return response()->json($response);
    }
    public function delete($id)
    {
        $tags = Tag::find($id);
        if ($tags) {
            $tags->delete();

            $response = generateresponse($tags, "Move To Trashed", null, 200);
            return $response;
        } else {
            $response = generateresponse($tags, "Not Found", null, 404);
            return $response;
        }
    }
    public function permanentlyDelete($id)
    {
        $tag = Tag::withTrashed()->find($id);

        if ($tag) {
            if ($tag->trashed()) {
                $tag->forceDelete();
                $response = generateresponse($tag, "tag Permanently Deleted", null, 200);
            } else {
                $tag->delete();
                $response = generateresponse($tag,  "Move To Trashed", null, 200);
            }
        } else {
            $response = generateresponse(null, "Tag Not Found", null, 404);
        }

        return response()->json($response);
    }

    public function getter($req = null)
    {
        $tag = Tag::query();

        if ($req !== null) {
            if ($req->trash == 'with') {
                $tag->withTrashed()->get();
            }
            if ($req->trash == 'only') {
                $tag->onlyTrashed()->get();
            }
            if ($req->column != null && $req->search != null) {
                if ($req->column == 'id') {
                    $tag->where('id', $req->search)->get();
                } else {
                    $tag->where('name', 'like', '%' . $req->search . '%')->get();
                }
            }
            if ($req->column != null && $req->sort != null) {

                $tag->orderBy($req->column, $req->sort);
            }
        }
        return $tag->get();
    }
    public function filter(Request $request)
    {
        $tag = $this->getter($request);

        $response = generateresponse($tag, "Success", null, 200);
        return response()->json($response);
    }
    public function update(Request $request)
    {
        try {

            $tag = Tag::find($request->input('id'));
            if (!$tag) {
                $response = generateresponse(null, "Tag not found", null, 404);
                return response()->json($response);
            }
            DB::connection('bloggerwebsite')->beginTransaction();

            $tag->update($request->all());



            $response = generateresponse($tag, " Tag Successfully Updated", null, 200);
            return response()->json($response);
        } catch (Exception $e) {

            DB::connection('bloggerwebsite')->rollBack();
            $response = generateresponse(null, "Record not updated Successfully", $e->getMessage(), 500);
            return response()->json($response);
        }
    }
}
