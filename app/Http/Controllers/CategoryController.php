<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index()
    {
        return Category::all()->sortByDesc('name')->values();
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required',
            'description' => 'nullable'
        ]);
        return Category::create($request->all());
    }


    public function show($id)
    {
        return Category::find($id);
    }


    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if ($category) {
            $category->update($request->all());
            return $category;
        } else {
            return response(['message' => 'Not Found'], 404);
        }
    }


    public function destroy($id)
    {
        $response = Category::destroy($id);
        if ($response) {
            return response(['message' => 'Deleted Successfully'], 200);
        } else {
            return response(['message' => 'Not Found'], 404);
        }
    }
}
