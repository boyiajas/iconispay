@extends('layouts.appDefault')

@section('content')
    <!-- Matters Section -->
    <div class="">
        <h4 class="section-title">Requisition</h4>
        
        <!-- Status Cards for Matters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>New Requisition <span class="pull-right"><button class="btn btn-white btn-sm">Refresh</button></span></h5>
                
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <form>
                            <div class="form-group">
                                <label for="file-reference">File Reference: *</label>
                                <input type="email" class="form-control" id="file-reference" aria-describedby="emailHelp" placeholder="Enter file reference">
                                
                            </div>
                            <div class="form-group">
                                <label for="reason">Reason</label>
                                <input type="text" class="form-control" id="reason" placeholder="Enter an optional reason for this payment Requisition e.g. Levies, Final Settlement">
                            </div>
                            <div class="form-group">
                                <label for="parties">Parties</label>
                                <input type="text" class="form-control" id="parties" placeholder="Enter Party description">
                            </div>
                            <div class="form-group">
                                <label for="properties">Properties</label>
                                <input type="text" class="form-control" id="properties" placeholder="Enter property description">
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="/home" class="btn">Cancel</a>
                        </form>
                    </div>
                </div>
                
            </div>
        </div>
       
    </div>
@endsection
