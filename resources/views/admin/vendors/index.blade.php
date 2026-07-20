@extends('layouts.app')

@section('page-css')
    <!-- Vendor CSS -->
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet" />
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
                                    <h5 class="mb-0 text-uppercase">All Vendors</h5>
                                    <hr />
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table id="example" class="table table-striped table-bordered"
                                                    style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Name</th>
                                                            <th>Company Name</th>
                                                            <th>Address</th>
                                                            <th>Phone</th>
                                                            <th>Email</th>
                                                            <th>Fax</th>
                                                            <th>Agent</th>
                                                            <th>Days</th>
                                                            <th>Days(In Stock)</th>
                                                            <th>Edit/Delete</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($vendors as $vendor)
                                                            <tr>
                                                                <form method="POST"
                                                                    action="{{ route('vendors.destroy', $vendor->vendor_ID) }}">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <td>{{ $vendor->vendor_ID }}</td>
                                                                    <td>{{ $vendor->vendor_name }}</td>
                                                                    <td>{{ $vendor->vendor_comp_name }}</td>
                                                                    <td>{{ $vendor->vendor_address }}</td>
                                                                    <td>{{ $vendor->vendor_phone }}</td>
                                                                    <td>{{ $vendor->vendor_email }}</td>
                                                                    <td>{{ $vendor->vendor_fax }}</td>
                                                                    <td>{{ $vendor->vendor_agent }}</td>
                                                                    <td>{{ $vendor->vendor_days }}</td>
                                                                    <td>{{ $vendor->vendor_days_stock }}</td>
                                                                    <td class="text-center">
                                                                        <a target="_self"
                                                                            class="btn btn-success mb-0 btn-sm"
                                                                            href="{{ route('vendors.edit', $vendor->vendor_ID) }}">Edit</a>
                                                                        @if (auth()->user()->isSuperAdmin())
                                                                            <button type="submit"
                                                                                class="btn btn-danger mb-0 mr-0 btn-sm">Delete</button>
                                                                        @endif
                                                                    </td>
                                                                </form>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>

                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    @if (auth()->user()->isSuperAdmin() || auth()->user()->user_name == 'admin1')
                                        <div class="card-block">
                                            <a class="btn btn-primary" id="add-vendor-btn"
                                                href="{{ route('vendors.create') }}">Add New Vendor</a>
                                            <button class="btn btn-primary text-white" data-toggle="modal"
                                                data-target="#importModal">Import Vendors</button>
                                        </div>
                                    @endif

                                    <!-- Import Modal -->
                                    <div class="modal fade" id="importModal" tabindex="-1" role="dialog"
                                        aria-labelledby="importModalTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="importModalTitle">Upload Excel File</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                {{-- <form method="POST" action="{{ route('vendors.import') }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="vendorFile">Upload File</label>
                                                        <input name="file" type="file" class="form-control-file" 
                                                               id="vendorFile" accept=".xls,.xlsx" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </form> --}}
                                            </div>
                                        </div>
                                    </div>
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
    {{-- <script src="{{ asset('assets/js/vendor-all.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/pcoded.min.js') }}"></script> --}}

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>pt>

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

        // Number input handling
        document.querySelectorAll('.btn-number').forEach(e => {
            e.addEventListener('click', function(event) {
                const fieldName = $(this).attr('data-field');
                const type = $(this).attr('data-type');
                const input = $(`input[name='${fieldName}']`);
                const currentVal = parseInt(input.val());

                if (!isNaN(currentVal)) {
                    if (type === 'minus') {
                        if (currentVal > input.attr('min')) {
                            input.val(currentVal - 1).change();
                        }
                        if (parseInt(input.val()) == input.attr('min')) {
                            $(this).attr('disabled', true);
                        }
                    } else if (type === 'plus') {
                        if (currentVal < input.attr('max')) {
                            input.val(currentVal + 1).change();
                        }
                        if (parseInt(input.val()) == input.attr('max')) {
                            $(this).attr('disabled', true);
                        }
                    }
                } else {
                    input.val(0);
                }
            });
        });

        $('.input-number').focusin(function() {
            $(this).data('oldValue', $(this).val());
        }).change(function() {
            const minValue = parseInt($(this).attr('min'));
            const maxValue = parseInt($(this).attr('max'));
            const valueCurrent = parseInt($(this).val());
            const name = $(this).attr('name');

            if (valueCurrent >= minValue) {
                $(`.btn-number[data-type='minus'][data-field='${name}']`).removeAttr('disabled');
            } else {
                alert('Sorry, the minimum value was reached');
                $(this).val($(this).data('oldValue'));
            }

            if (valueCurrent <= maxValue) {
                $(`.btn-number[data-type='plus'][data-field='${name}']`).removeAttr('disabled');
            } else {
                alert('Sorry, the maximum value was reached');
                $(this).val($(this).data('oldValue'));
            }
        });

        $(".input-number").keydown(function(e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
                // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    </script>
@endsection
