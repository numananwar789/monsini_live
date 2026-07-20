@extends('layouts.app')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <div class="row">
                            <div class="col">
                                <h5 class="mb-0 text-uppercase">Regular Email Bodies</h5>
                                <hr />
                                <div class="card">
                                    <div class="card-body">
                                        <form action="{{ route('email-templates.update') }}" method="POST">
                                            @csrf
                                            <div class="form-floating">
                                                <textarea class="form-control" name="email_body" placeholder="Fill Out the Email Body" id="vendor-email-body" style="height: 200px">{{ old('email_body', $vendorTemplate->email_body) }}</textarea>
                                                <label for="vendor-email-body">Email body for vendor</label>
                                            </div>
                                            <input type="hidden" name="email_role" value="vendor">
                                            <button type="submit" class="mt-3 btn btn-primary">Save</button>
                                        </form>
                                        
                                        <form action="{{ route('email-templates.update') }}" method="POST" class="mt-5">
                                            @csrf
                                            <div class="form-floating">
                                                <textarea class="form-control" name="email_body" placeholder="Fill Out the Email Body" id="customer-email-body" style="height: 200px">{{ old('email_body', $customerTemplate->email_body) }}</textarea>
                                                <label for="customer-email-body">Email body for Customer</label>
                                            </div>
                                            <input type="hidden" name="email_role" value="customer">
                                            <button type="submit" class="mt-3 btn btn-primary">Save</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="mb-0 text-uppercase">In-Stock and Inventory Email Bodies</h5>
                                <hr />
                                <div class="card">
                                    <div class="card-body">
                                        <form action="{{ route('email-templates.update') }}" method="POST">
                                            @csrf
                                            <div class="form-floating">
                                                <textarea class="form-control" name="email_body" placeholder="Fill Out the Email Body" id="stock-customer-email-body" style="height: 200px">{{ old('email_body', $stockCustomerTemplate->email_body) }}</textarea>
                                                <label for="stock-customer-email-body">Email body for customers for in-stock and inventory</label>
                                            </div>
                                            <input type="hidden" name="email_role" value="stock_customer">
                                            <button type="submit" class="mt-3 btn btn-primary">Save</button>
                                        </form>
                                    </div>
                                </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script>
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    @if($errors->any())
        toastr.error('{{ $errors->first() }}');
    @endif
</script>
@endsection