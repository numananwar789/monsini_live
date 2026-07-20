@extends('layouts.app')

@section('page-css')
    <link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <div class="row">
                            <div class="col">
                                <h5 class="mb-0 text-uppercase">All Customers</h5>
                                <hr />
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="example" class="table table-striped table-bordered" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Name</th>
                                                        <th>Username</th>
                                                        <th>Company</th>
                                                        <th>Address</th>
                                                        <th>Phone</th>
                                                        <th>Email</th>
                                                        <th>Fax</th>
                                                        <th>Sales Rep</th>
                                                        @if(auth()->user()->isSuperAdmin())
                                                        <th>Status</th>
                                                        @endif
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($customers as $customer)
                                                    <tr>
                                                        <td>{{ $customer->cust_ID }}</td>
                                                        <td>{{ $customer->full_name }}</td>
                                                        <td>{{ $customer->cust_username }}</td>
                                                        <td>{{ $customer->cust_comp_name }}</td>
                                                        <td>{{ $customer->cust_address }}</td>
                                                        <td>{{ $customer->cust_phone }}</td>
                                                        <td>{{ $customer->cust_email }}</td>
                                                        <td>{{ $customer->cust_fax }}</td>
                                                        <td>{{ $customer->cust_sales_rep }}</td>
                                                        
                                                        @if(auth()->user()->isSuperAdmin())
                                                        <td>
                                                            @if($customer->cust_status === 'allow')
                                                                <span class="badge badge-success">Approved</span>
                                                            @else
                                                                <span class="badge badge-warning">Pending</span>
                                                            @endif
                                                        </td>
                                                        @endif
                                                        
                                                        <td class="text-center">
                                                            <a href="{{ route('customers.edit', $customer->cust_ID) }}" 
                                                               class="btn btn-success btn-sm">Edit</a>
                                                            
                                                            @if(auth()->user()->isSuperAdmin())
                                                            <form method="POST" action="{{ route('customers.destroy', $customer->cust_ID) }}" 
                                                                  style="display: inline-block;">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm"
                                                                        onclick="return confirm('Are you sure?')">Delete</button>
                                                            </form>
                                                            
                                                            @if($customer->cust_status !== 'allow')
                                                            <form method="POST" action="{{ route('customers.approve', $customer->cust_ID) }}" 
                                                                  style="display: inline-block;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-warning btn-sm">Approve</button>
                                                            </form>
                                                            @endif
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                @if(auth()->user()->isSuperAdmin())
                                <div class="card-block">
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#importModal">
                                        Import Customers
                                    </button>
                                </div>
                                
                                <!-- Import Modal -->
                                <div class="modal fade" id="importModal" tabindex="-1" role="dialog" 
                                     aria-labelledby="importModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="importModalLabel">Import Customers</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="POST" action="{{ route('customers.import') }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="customerFile">Select Excel File</label>
                                                        <input type="file" class="form-control-file" id="customerFile" 
                                                               name="file" accept=".xls,.xlsx" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Import</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-js')
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script> --}}

    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                dom: 'Bfrtip',
                buttons: ['print'],
                responsive: true,
                pageLength: 25
            });
        });
    </script>
@endsection