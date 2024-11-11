@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('OTP Verification') }}</div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>{{ $errors->first() }}</strong>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verify.2fa') }}">
                        @csrf

                        <div class="row mb-3">
                            <p class="col-md-12 text-center">
                                Please enter the <strong>OTP</strong> generated on your Authenticator App. <br>
                                Ensure you submit the current one because it refreshes every 30 seconds.
                            </p>
                        </div>

                        <div class="row mb-3">
                            <label for="one_time_password" class="col-md-4 col-form-label text-md-end">{{ __('One Time Password') }}</label>

                            <div class="col-md-6">
                                <input id="one_time_password" type="number" class="form-control @error('one_time_password') is-invalid @enderror" name="one_time_password" required autofocus>

                                @error('one_time_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
