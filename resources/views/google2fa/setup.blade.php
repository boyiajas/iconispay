@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">{{ __('Set up Google Authenticator') }}</h4>
                </div>

                <div class="card-body text-center">
                    <p>
                        Set up your two-factor authentication by scanning the barcode below. Alternatively, you can use the code:
                        <strong>{{ $secret }}</strong>
                    </p>

                    <div class="my-3">
                        {!! $QR_Image !!}
                    </div>

                    <p>
                        You must set up your Google Authenticator app before continuing. You will be unable to log in otherwise.
                    </p>

                    <div class="mt-4">
                        <a href="{{ route('complete.2fa.setup') }}" class="btn btn-primary">
                            {{ __('Complete Registration') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection