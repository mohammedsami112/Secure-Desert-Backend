<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * Check if the task exists
     *
     * @param  int  $id
     * @return Category
     */
    public function category($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->fail('Category not found', 404);
        }

        return $category;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();

        return $this->respond('All categories', 200, $categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
        ]);

        $category = Category::create($validated);
        return $this->respond('Category created', 201, $category);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = $this->category($id);

        return $this->respond('Category found', 200, $category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = $this->category($id);
        $validated = $request->validate([
            'title' => 'required|string',
        ]);

        $category->update($validated);

        return $this->respond('Category updated', 200, $category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = $this->category($id);
        $category->delete();

        return $this->respond('Category deleted', 200);
    }
}
