@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">User Profile</h2>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th width="150">Name</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Roles</th>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @if($user->hasAnyRole(['admin', 'employee']))
                        <tr>
                            <th>Permissions</th>
                            <td>
                                @foreach($permissions as $permission)
                                    <span class="badge bg-success">{{ ucfirst(str_replace('-', ' ', $permission->name)) }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endif
                    </table>

                    <div class="mt-4">
                        <div class="d-flex justify-content-end gap-2">
                            @if(auth()->id() == $user->id || auth()->user()->hasRole('admin'))
                                <a href="{{ route('edit_password', $user->id) }}" class="btn btn-secondary">
                                    Change Password
                                </a>
                                <a href="{{ route('users_edit', $user->id) }}" class="btn btn-primary">
                                    Edit Profile
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
