@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Edit Profile</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('users_save', $user->id) }}" method="POST">
                        @csrf
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(auth()->user()->hasRole('admin'))
                            <div class="mb-3">
                                <label for="roles" class="form-label">Roles</label>
                                <select multiple class="form-select @error('roles') is-invalid @enderror" 
                                        id="roles" name="roles[]">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" 
                                                {{ $role->taken ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('roles')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="permissions" class="form-label">Direct Permissions</label>
                                <select multiple class="form-select @error('permissions') is-invalid @enderror" 
                                        id="permissions" name="permissions[]">
                                    @foreach($permissions as $permission)
                                        <option value="{{ $permission->name }}" 
                                                {{ $permission->taken ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('-', ' ', $permission->name)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('permissions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('users.profile', $user->id) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
