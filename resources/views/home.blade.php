@extends('layouts.appDefault')

@section('head')

     <!-- Pass necessary Laravel variables -->
     <script>
        window.Laravel = <?php echo json_encode([
           // 'csrfToken' => csrf_token(),
           // 'user' => Auth::check() ? Auth::user()->load('roles', 'permissions') : null,
        ]); ?>

        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            user: {!! json_encode(Auth::check() ? Auth::user()->load('roles', 'permissions', 'organisation') : null) !!}
        };
    </script>

@endsection

@section('content')

    <div id="app">
        <!-- Vue app will be mounted here -->
    </div>

       
@endsection
