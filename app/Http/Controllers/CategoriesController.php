<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categories\CreateRequest;
use App\Http\Requests\PostRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Tag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = Category::get();
        $categoryResource = CategoryResource::collection($categories);

        $response = generateresponse($categoryResource, "Successfully retrieve", null, 200);
        return response()->json($response);
    }
    public function create(CreateRequest $request)
    {

        try {
            DB::connection('bloggerwebsite')->beginTransaction();
            $category = Category::query()->create($request->all());
            $response =   generateresponse($category, "Category Created Successfully", null, 200);
        } catch (Exception $e) {
            $response = generateresponse(null, "", $e->getMessage(), 404);
        }
        return response()->json($response);
    }
    public function delete($id)
    {
        $category = Category::find($id);
        if ($category) {
            $category->delete();

            $response = generateresponse($category, "Move To Trashed", null, 200);
            return $response;
        } else {
            $response = generateresponse($category, "Not Found", null, 404);
            return $response;
        }
    }
    public function permanentlyDelete($id)
    {
        $category = Category::withTrashed()->find($id);

        if ($category) {
            if ($category->trashed()) {
                $category->forceDelete();
                $response = generateresponse($category, "category Permanently Deleted", null, 200);
            } else {
                $category->delete();
                $response = generateresponse($category,  "Move To Trashed", null, 200);
            }
        } else {
            $response = generateresponse(null, "Category Not Found", null, 404);
        }

        return response()->json($response);
    }

    public function getter($req = null)
    {
        $category = Category::query();

        if ($req !== null) {
            if ($req->trash == 'with') {
                $category->withTrashed()->get();
            }
            if ($req->trash == 'only') {
                $category->onlyTrashed()->get();
            }
            if ($req->search_type != null && $req->search != null) {
                if ($req->search_type == 'id') {
                    $category->where('id', $req->search)->get();
                } else {
                    $category->where('name', 'like', '%' . $req->search . '%')->get();
                }
            }
            if ($req->column != null && $req->sort != null) {

                $category->orderBy($req->column, $req->sort);
            }
        }
        return $category->get();
    }
    public function filter(Request $request)
    {
        $category = $this->getter($request);

        $response = generateresponse($category, "Success", null, 200);
        return response()->json($response);
    }
    public function update(Request $request)
    {
        try {

            $category = Category::find($request->input('id'));
            if (!$category) {
                $response = generateresponse(null, "Category not found", null, 404);
                return response()->json($response);
            }
            DB::connection('bloggerwebsite')->beginTransaction();

            $category->update($request->all());

            DB::connection('bloggerwebsite')->commit();

            $response = generateresponse($category, "Category Successfully Updated", null, 200);
            return response()->json($response);
        } catch (Exception $e) {

            DB::connection('bloggerwebsite')->rollBack();
            $response = generateresponse(null, "Record not updated Successfully", $e->getMessage(), 500);
            return response()->json($response);
        }
    }
}
