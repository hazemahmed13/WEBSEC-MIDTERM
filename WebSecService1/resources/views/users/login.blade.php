@extends('layouts.master')
@section('title', 'Login')
@section('content')
<div class="d-flex justify-content-center">
  <div class="card m-4 col-sm-6">
    <div class="card-body">
      <form action="{{route('do_login')}}" method="post">
      {{ csrf_field() }}
      <div class="form-group">
        @foreach($errors->all() as $error)
        <div class="alert alert-danger">
          <strong>Error!</strong> {{$error}}
        </div>
        @endforeach
      </div>
      <div class="form-group mb-2">
        <label for="model" class="form-label">Email:</label>
        <input type="email" class="form-control" placeholder="email" name="email" required>
      </div>
      <div class="form-group mb-2">
        <label for="model" class="form-label">Password:</label>
        <input type="password" class="form-control" placeholder="password" name="password" required>
      </div>
      <div class="form-group mb-2">
        <button type="submit" class="btn btn-primary">Login</button>
        <a href="{{ route('password.request') }}" class="btn btn-link">Forgot Password?</a>
      </div>
    </form>
    </div>
  </div>
</div>
<div class="d-flex justify-content-center gap-3">
    <a href="{{ url('auth/google') }}" class="btn btn-outline-danger">
        <i class="fab fa-google"></i> Login with Google
    </a>
    <a href="{{ url('auth/github') }}" class="btn btn-outline-dark">
        <i class="fab fa-github"></i> Login with GitHub
    </a>
    <a href="{{ url('auth/facebook') }}" class="btn btn-outline-primary">
        <i class="fab fa-facebook"></i> Login with Facebook
    </a>
</div>
@endsection
