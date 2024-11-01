<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles and Scripts from Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Include Ziggy's routes -->
    @routes

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <title>Lexis Pay Dashboard</title>
    <link rel="stylesheet" href="{{URL::to('assets/css/bootstrap.min.css')}}">
   
    <link rel="stylesheet" href="{{URL::to('assets/css/feathericon.min.css')}}">
    
    <link rel="stylesheet" href="{{URL::to('assets/css/style.css')}}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css') }}">
    <script src="{{ URL::to('assets/js/toastr_jquery.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/toastr.min.js') }}"></script>
    <link rel="stylesheet" href="{{URL::to('assets/css/front-end.css')}}">
    <!-- Other styles and links -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link href="https://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css" rel="stylesheet">

    <!-- Bootstrap JS and dependencies -->
    <!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->
    

    @yield('head')

    <style>
        /* .navbar {
            border-bottom: 3px solid red;
        }
        .navbar-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .navbar-links-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 5px;
            width: 100%;
        }
        .navbar-links-row a {
            margin-left: 20px;
        }
        .status-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-box .badge {
            background-color: red;
            font-size: 1rem;
        }
        .section-title {
            font-weight: bold;
        }
        .new-requisition-btn {
            margin-bottom: 20px;
        }

        a li{
            color:#4c4d4f;
        }

        a li:hover{
            background-color: #fafafa;
        } */
        
    </style>
</head>
<body>

    <!-- Navbar -->
   <!--  <nav class="navbar navbar-light bg-light">
        <div class="container">
            <!-- First row: navbar-brand on the left, Logged in as / Log out on the right --
            <div class="navbar-row">
                <a class="navbar-brand" href="#">Iconis® Pay</a>
                <div>
                    <span>Logged in as: Peter Ajakaiye (Strauss Daly Incorporated)</span>
                    <a href="{{ url('/logout') }}" class="ml-3">Log out</a>
                </div>
            </div>
            <!-- Second row: Home, Matters, Accounts, Reports aligned to the right --
            <div class="navbar-links-row">
                <a href="{{ url('/home') }}" class="mr-3">Home</a>
                <a href="#" class="mr-3">Matters</a>
                <a href="#" class="mr-3">Accounts</a>
                <a href="#">Reports</a>
            </div>
        </div>
    </nav> -->

    <div class="">
        <!-- Main Content -->
        @yield('content')
    </div>
    <script>
        
        $(function () {
            $('[data-toggle="popover"]').popover()
        })
    </script>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <!--   <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script> -->
   <!--  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->
    @yield('scripts')
</body>
</html>
