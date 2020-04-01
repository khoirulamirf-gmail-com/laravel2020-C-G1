@extends('layouts.global')

@section('title') Detail user @endsection 

@section('content')
  <div class="col-md-8">
    <div class="card">
      <div class="card-body">
        <b>Name:</b> <br/>
        {{$user->name}}
        <br><br>

        @if($user->avatar)
          <img src="{{asset('storage/'. $user->avatar)}}" width="128px"/>
        @else 
          No avatar
        @endif 

        <br>
        <br>
        <b>Username:</b><br>
        {{$user->email}}

        <br>
        <br>
        <b>Phone number</b> <br>
        {{$user->phone}}

        <br><br>
        <b>Address</b> <br>
        {{$user->address}}
        
        <br>
        <br>
        <b>Roles:</b> <br>
        @foreach (json_decode($user->roles) as $role)
             {{$role}} <br>
        @endforeach

        <br>
        <br>
        <a href="{{route('users.index')}}" class="btn btn-outline-warning">Back</a>
      </div>

      
    </div>
  </div>
@endsection

@section('footer-scripts')

    
    
@endsection