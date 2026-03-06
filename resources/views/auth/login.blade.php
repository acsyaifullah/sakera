@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">
    <div class="card shadow-lg p-4" style="width:400px;">
        
        <div class="text-center mb-4">
            <h3 class="fw-bold">SAKERA</h3>
            <p class="text-muted mb-0">Sistem Arsip Kepegawaian Responsif & Akurat</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">NIP</label>
                <input type="text" 
                       name="nip" 
                       class="form-control" 
                       value="{{ old('nip') }}"
                       required autofocus>
                @error('nip')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" 
                       name="password" 
                       class="form-control" 
                       required>
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    Login
                </button>
            </div>

        </form>
    </div>
</div>
@endsection