@extends('layouts.appDefault')

@section('head')

     <!-- Pass necessary Laravel variables -->
     <script>
        

        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            user: {!! json_encode(Auth::check() ? Auth::user()->load('roles', 'permissions') : null) !!}
        };
    </script>

@endsection

@section('content')

    <div id="app">
        <!-- Vue app will be mounted here -->
    </div>

         <!-- Accounts Section -->
       <!--  <div class="card mb-4">
            <div class="card-header">
                <h5>Accounts <span class="pull-right"><button class="btn btn-white btn-sm">View</button><button class="btn btn-white btn-sm ml-1">Refresh</button></span></h5>
                
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <ul class="list-group list-group-flush">
                            <a href="#"><li class="list-group-item">Ready for Payment <span class="pull-right"> 0 matter(s), 0 payment(s) <span class="badge badge-pill badge-secondary">0</span></span></li></a>
                            <a href="#"><li class="list-group-item">Awaiting Pay Action <span class="pull-right"> 0 matter(s), 0 payment(s) <span class="badge badge-pill badge-secondary">0</span></span></li></a>
                            <a href="#"><li class="list-group-item">Pending confirmation <span class="pull-right"> 0 matter(s), 0 payment(s) <span class="badge badge-pill badge-secondary">0</span></span></li></a>
                            <a href="#"><li class="list-group-item">Settles Today <span class="pull-right"> 0 matter(s), 0 payment(s) <span class="badge badge-pill badge-secondary">0</span></span></li></a>
                            <a href="#"><li class="list-group-item">Failed today  <span class="pull-right"> 0 matter(s), 0 payment(s) <span class="badge badge-pill badge-secondary">0</span></span></li></a>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5>Search by Generated Date:</h5>
                        <div class="input-group mb-3">
                            <input type="date" class="form-control" placeholder="Generated Date" aria-label="Generated Date">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">Go</button>
                            </div>
                        </div>
                       
                    </div>
                </div>    
                
            </div>
        </div> -->
@endsection
