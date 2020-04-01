<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\BookCreateRequest;
use App\Http\Requests\BookUpdateRequest;

use Illuminate\Support\Facades\Gate;


use App\Book;

class BookController extends Controller
{


    public function __construct()
    {
        # code...
        $this->middleware(function($request, $next){

            if(Gate::allows('manage-books')) return $next($request);

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

        $status = $request->get('status');

        $filterKeyword = $request->get('keyword') ? $request->get('keyword') : '';

        if ($status) {

            # code...
            // buat string uppercase
            // strtoupper
            $books = Book::with('categories')->where('title', 'LIKE', "%$filterKeyword%")->where('status', strtoupper($status))->paginate(10);

        } else {

            $books = Book::with('categories')->where('title', 'LIKE', "%$filterKeyword%")->paginate(10);

        }

        


        return view('books.index', compact('books'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('books.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BookCreateRequest $request)
    {
        $new_book = new Book();

        $new_book->title = $request->input('title');
        $new_book->description = $request->input('description');
        $new_book->author = $request->input('author');
        $new_book->publisher = $request->input('publisher');
        $new_book->price = $request->input('price');
        $new_book->stock = $request->input('stock');
        
        $new_book->status = $request->input('save_action');

        if ($request->file('cover')) {
            # code...
            $cover_path = $request->file('cover')->store('books-cover', 'public');

            $new_book->cover = $cover_path;
        }

        $new_book->slug = \Str::slug($request->input('title'));

        $new_book->created_by = \Auth::user()->id;

        $new_book->save();
        
        $new_book->categories()->attach($request->get('categories'));

        if ($request->input('save_action') == 'PUBLISH') {

            # code...
            return redirect('/books')->with('success', 'The Book has been saved and published');


        } else {

            return redirect('/books')->with('success', 'The Book has been saved as draft');

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $book = Book::findOrFail($id);

        return view('books.edit', compact('book'));
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

        $book = Book::findOrFail($id);

        $book->title = $request->input('title');
        $book->slug = $request->input('slug');
        $book->description = $request->input('description');
        $book->author = $request->input('author');
        $book->publisher = $request->input('publisher');
        $book->stock = $request->input('stock');
        $book->price = $request->input('price');
        $new_cover = $request->file('cover');

        if($new_cover){

            if($book->cover && file_exists(storage_path('app/public/' .$book->cover))){

                \Storage::delete('public/'. $book->cover);

            }

            $new_cover_path = $new_cover->store('book-covers', 'public');

            $book->cover = $new_cover_path;

        }

        $book->updated_by = \Auth::user()->id;
        $book->status = $request->input('status');

        $book->save();

        $book->categories()->sync($request->get('categories'));

        return redirect()->route('books.edit', [$book->id])->with('success',
       'Book successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        $book->delete();

        return redirect()->back()->with('success', 'The book has been moved to trash');
    }

    public function trash()
    {
        # code...
        $books = Book::onlyTrashed()->paginate(10);

        return view('books.trash', compact('books'));
    }

    public function restore($id)
    {
        # code...
        $book = Book::withTrashed()->findOrFail($id);

        if ($book->trashed()) {
            # code...

            $book->restore();

            return redirect()->back()->with('success', 'The book has been restored');

        } else {

            return redirect()->back()->with('success', 'The Book is not in the trash');
        }

       
    }

    public function deletePermanent($id)
    {
        # code...
        $book = Book::withTrashed()->findOrFail($id);

        if (!$book->trashed()) {
            # code...
            return redirect()->back()->with('error', 'The book is not in trash');

        } else {

            $book->categories()->detach();

            $book->forceDelete();

            return redirect()->back()->with('success', 'The book has been delete permanently');
        }
    }

   
}
