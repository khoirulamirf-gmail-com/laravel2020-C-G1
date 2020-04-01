<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Requests\CategoryCreateRequest;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use App\Category;
use Auth;


class CategoryController extends Controller
{

    public function __construct()
    {
        # code...
        $this->middleware(function($request, $next){

            if(Gate::allows('manage-categories')) return $next($request);

            abort(403, 'Anda tidak memiliki cukup hak akses');

        });
           
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $categories = Category::paginate(10);

        $filterkeyword = $request->get('name');

        if ($filterkeyword) {
            # code...

            $categories = Category::where('name', 'LIKE', "%$filterkeyword%")->paginate(10);


        }

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryCreateRequest $request)
    {
        $category = new Category();

        $name = $request->input('name');

        $category->name = $name;

        if ($request->file('image')) {
            # code...
            $file = $request->file('image')->store('category_image', 'public');

            $category->image = $file;
        }

        $category->created_by = \Auth::user()->id;

        $category->slug = \Str::slug($name, '-');

        $category->save();

        return redirect('/categories')->with('success', 'The Category has been created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);

        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);

        return view('categories.edit', compact('category'));
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
        $category = Category::findOrFail($id);

        \Validator::make($request->all(), [
            "name" => "required|min:3|max:20",
            "image" => "required",
            "slug" => ["required", Rule::unique("categories")->ignore($category->slug, "slug")]

        ])->validate();
           

        $category->name = $request->input('name');
        $category->slug = $request->input('slug');

        if ($request->file('image')) {
            # code...
            if ($category->image && file_exists(storage_path('app/public'. $category->image))) {
                # code...
                \Storage::delete('public/' . $category->name);
            }

            $new_image = $request->file('image')->store('category_images', 'public');

            $category->image = $new_image;
        }

        $category->updated_by = \Auth::user()->id;

        $category->slug = \Str::slug($request->input('name'));

        $category->save();

        return redirect('/categories')->with('success', 'The Category has been created');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        $category->delete();

        return redirect('/categories')->with('success', 'Category has been moved to trash');
    }

    public function trash()
    {
        # code...

        $categories = Category::onlyTrashed()->paginate(10);

        return view('categories.trash', compact('categories'));
    }

    public function restore($id)
    {
        # code...
        $category = Category::withTrashed()->findOrFail($id);

        if($category->trashed()){

            $category->restore();

        } else {

            return redirect()->route('categories.index')->with('success', 'Category is not in trash');

        }
      
        return redirect()->route('categories.index')->with('success', 'Category successfully restored');

    }

    public function deletePermanent($id)
    {
        # code...
        $category = Category::withTrashed()->findOrFail($id);

        if(!$category->trashed()){

            return redirect()->route('categories.index')->with('success', 'Can not delete permanent active category');

        } else {

            $category->forceDelete();
            
            return redirect()->route('categories.index')->with('success', 'Category permanently deleted');
        }
    }

    public function ajaxSearch(Request $request)
    {
        $keyword = $request->get('q');

        $categories = Category::where("name", "LIKE", "%$keyword%")->get();

        return $categories;
    }
       

}
