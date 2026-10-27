@extends('admin.layouts.app')

@section('title', 'Admin Profile')

@section('content')
    <div class="page-heading">
        <h1>Admin Profile</h1>
        <p>Manage your profile picture and personal information.</p>
    </div>

    <div class="panel">
        <div style="display:flex; align-items:center; gap:18px; margin-bottom:24px; flex-wrap:wrap;">
            <div style="width:100px; height:100px; border-radius:50%; overflow:hidden; border:3px solid #245333; background:#e8f1e5; display:flex; align-items:center; justify-content:center; color:#245333; font-size:30px; font-weight:bold; flex-shrink:0;">
                @if ($user->profile_picture)
                    <img
                        src="{{ asset('storage/' . $user->profile_picture) }}"
                        alt="Admin profile picture"
                        style="width:100%; height:100%; object-fit:cover;"
                    >
                @else
                    {{ collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: 'A' }}
                @endif
            </div>

            <div>
                <h2 style="margin:0 0 7px;">{{ $user->name }}</h2>
                <p class="muted" style="margin:0;">Administrator account</p>
                <p class="muted" style="margin:5px 0 0;">{{ $user->email }}</p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.profile.update') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PATCH')

            <div class="field" style="margin-bottom:20px;">
                <label for="profile_picture">Profile Picture</label>
                <input
                    type="file"
                    id="profile_picture"
                    name="profile_picture"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >
                <p class="muted" style="margin:7px 0 0;">
                    Accepted formats: JPG, JPEG, PNG, and WebP. Maximum size: 2 MB.
                </p>

                @error('profile_picture')
                    <p style="color:#a52b24; margin-top:7px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="fields">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        maxlength="255"
                        required
                    >
                    @error('name')
                        <p style="color:#a52b24;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        maxlength="255"
                        required
                    >
                    @error('email')
                        <p style="color:#a52b24;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="phone">Contact Number</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $user->phone) }}"
                        maxlength="30"
                        placeholder="Enter your contact number"
                    >
                    @error('phone')
                        <p style="color:#a52b24;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:24px;">
                <button type="submit" class="button">
                    Save Changes
                </button>

                <a href="{{ route('admin.dashboard') }}"
                   class="button"
                   style="background:#657267;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection