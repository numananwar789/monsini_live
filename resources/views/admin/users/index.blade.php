@extends('layouts.app')

@section('page-css')
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}">
@endsection

@section('content')
<!-- [ Main Content ] start -->
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <!-- [ Main Content ] start -->
                        <div class="row">
                            <div class="col">
                                <h5 class="mb-0 text-uppercase">All Admins</h5>
                                <hr />
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="example" class="table table-striped table-bordered" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Username</th>
                                                        <th>Email</th>
                                                        <th>Role</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                        @if(auth()->user()->isSuperAdmin())
                                                        <th>Approve</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($users as $user)
                                                    <tr>
                                                        <td>{{ $user->id }}</td>
                                                        <td>{{ $user->user_name }}</td>
                                                        <td>{{ $user->email }}</td>
                                                        <td>{{ ucfirst($user->admin_role) }}</td>
                                                        <td>
                                                            @if($user->admin_status === 'allow')
                                                                <span class="badge badge-success">Active</span>
                                                            @else
                                                                <span class="badge badge-warning">Pending</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if(auth()->user()->isSuperAdmin())
                                                            <a href="{{ route('users.edit', $user->id) }}" 
                                                               class="btn btn-success btn-sm">Edit</a>
                                                            @endif
                                                        </td>
                                                        @if(auth()->user()->isSuperAdmin())
                                                        <td>
                                                            <form method="POST" action="{{ route('users.update', $user->id) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                
                                                                @if($user->status !== 'allow')
                                                                <button type="submit" name="approve" value="1" 
                                                                        class="btn btn-warning btn-sm">Approve</button>
                                                                @else
                                                                <button type="button" class="btn btn-secondary btn-sm" disabled>Approved</button>
                                                                @endif
                                                                
                                                                @if(auth()->user()->isSuperAdmin())
                                                                <button type="submit" name="delete" value="1" 
                                                                        class="btn btn-danger btn-sm" 
                                                                        onclick="return confirm('Are you sure?')">Delete</button>
                                                                @endif
                                                            </form>
                                                        </td>
                                                        @endif
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                @if(auth()->user()->isSuperAdmin())
                                <div class="card-block">
                                    <a href="{{ route('users.create') }}" class="btn btn-primary">Add Admin</a>
                                    <a href="/backup/download2" class="btn btn-success float-right">Backup Database</a>
                                </div>
                                @endif
                            </div>
                        </div>
                        <!-- [ Main Content ] end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ Main Content ] end -->
@endsection

@section('page-js')
<!-- Required Js -->
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $('#example').DataTable({
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        });
    });
</script>
@endsection