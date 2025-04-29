@extends('layouts.master')
@section('title', 'Verify Email')
@section('content')
<div class="d-flex justify-content-center">
    <div class="card m-4 col-sm-6">
        <div class="card-body">
            <h4 class="card-title">Verify Your Email Address</h4>
            
            @if (session('resent'))
                <div class="alert alert-success" role="alert">
                    A fresh verification link has been sent to your email address.
                </div>
            @endif

            <p>Before proceeding, please check your email for a verification link.</p>
            <p>If you did not receive the email,</p>
            
            <form class="d-inline" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-link p-0 m-0 align-baseline">click here to request another</button>.
            </form>
        </div>
    </div>
</div>
@endsection 