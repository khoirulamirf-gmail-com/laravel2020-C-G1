<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Gate;
use App\User;

class UserController extends Controller
{   

    public function __construct()
    {
        # code...
        $this->middleware(function($request, $next){

            if(Gate::allows('manage-users')) return $next($request);

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
       

        $filterKeyword = $request->get('keyword');

        $status = $request->get('status');

        if ($status) {
            # code...
            $users = User::where('status', $status)->paginate(10);

        } else {

            $users = User::paginate(10);
            
        }

        if($filterKeyword){

            if ($status) {
                # code...
                $users = User::where('email', 'LIKE', "%$filterKeyword%")->where('status', $status)
                ->paginate(10);

            } else {
    
                $users = User::where('email', 'LIKE', "%$filterKeyword%")->paginate(10);
                
            }
        }

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserCreateRequest $request)
    {
        
        $new_user = new User();

        $new_user->name = $request->input('name');
        $new_user->username = $request->input('username');
        $new_user->roles = json_encode($request->input('roles'));
        $new_user->address = $request->input('address');
        $new_user->phone = $request->input('phone');
        $new_user->email = $request->input('email');
        $new_user->password = bcrypt($request->input('password'));

        if($request->file('avatar')){

            $file = $request->file('avatar')->store('avatars', 'public');

            $new_user->avatar = $file;

        }

        $new_user->save();

        return redirect()->back()->with('success', 'The User Has Been Created');
           
        
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
        $user = User::findOrFail($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   

        $user = User::findOrFail($id);


        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserUpdateRequest $request, $id)
    {
        $user = User::findOrFail($id);

        $user->name = $request->input('name');
        $user->roles = json_encode($request->input('roles'));
        $user->address = $request->input('address');
        $user->phone = $request->input('phone');
        

        if($request->file('avatar')){
            
            if($user->avatar && file_exists(storage_path('app/public/' . $user->avatar))){

                \Storage::delete('public/'.$user->avatar);

            }

            $file = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $file;
        }

        $user->save();

        return redirect('/users')->with('success', 'User Has Been Updated');
           
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $user = User::findOrFail($id);

        $user->delete();

        return redirect('/users')->with('success', 'User has been deleted');
    }
}
