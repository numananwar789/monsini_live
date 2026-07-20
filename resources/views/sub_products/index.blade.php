@extends('layouts.app')

@section('page-css')
   <style>
    td{
        padding: 5px  10px !important;
    }
    </style>
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
                                    <h5 class="mb-0 text-uppercase">All Products</h5>

                                    @if (session('success'))
                                        <div class="alert alert-success mt-3">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    @if (session('error'))
                                        <div class="alert alert-danger mt-3">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    <hr />
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            {{-- <th>ID</th> --}}
                                                            <th>Sub Product Name</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="subProductsTable">
                                                        @foreach ($subProducts as $subProduct)
                                                            <tr id="row-{{ $subProduct->id }}">
                                                                {{-- <td>{{ $subProduct->id }}</td> --}}
                                                                <td class="name">{{ $subProduct->sub_product_name }}</td>
                                                                <td>
                                                                    <button class="btn btn-warning btn-sm" onclick="openEditModal({{ $subProduct->id }}, '{{ $subProduct->sub_product_name }}')">Edit</button>
                                                                    <button class="btn btn-danger btn-sm" onclick="deleteSubProduct({{ $subProduct->id }})">Delete</button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>

                                               
                                                  
                                           
                                            </div>
                                        </div>
                                    </div>

                                
                                    <button class="btn btn-primary mb-3" onclick="openAddModal()">+ Add Sub Product</button>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
<div class="modal fade" id="subProductModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="subProductForm">
        @csrf
        <input type="hidden" id="subProductId">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Add Sub Product</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Sub Product Name</label>
                  <input type="text" class="form-control" id="sub_product_name" name="sub_product_name" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
    </form>
  </div>
</div>

@endsection

@section('page-js')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- amchart js -->
    <script src="/assets/plugins/amchart/js/amcharts.js"></script>
    <script src="/assets/plugins/amchart/js/gauge.js"></script>
    <script src="/assets/plugins/amchart/js/serial.js"></script>
    <script src="/assets/plugins/amchart/js/light.js"></script>
    <script src="/assets/plugins/amchart/js/pie.min.js"></script>
    <script src="/assets/plugins/amchart/js/ammap.min.js"></script>
    <script src="/assets/plugins/amchart/js/usaLow.js"></script>
    <script src="/assets/plugins/amchart/js/radar.js"></script>
    <script src="/assets/plugins/amchart/js/worldLow.js"></script>
    <!-- notification Js -->
    <script src="/assets/plugins/notification/js/bootstrap-growl.min.js"></script>

    <!-- dashboard-custom js -->
    <script src="/assets/js/pages/dashboard-custom.js"></script>
    <script src="/assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script src="/assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>

    <!-- datatable date range links -->
    <!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>

    <!-- toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>


    <script>
let modal = new bootstrap.Modal(document.getElementById('subProductModal'));

function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Add Sub Product';
    document.getElementById('saveBtn').innerText = 'Save';
    document.getElementById('subProductForm').reset();
    document.getElementById('subProductId').value = '';
    modal.show();
}

function openEditModal(id, name) {
    document.getElementById('modalTitle').innerText = 'Edit Sub Product';
    document.getElementById('saveBtn').innerText = 'Update';
    document.getElementById('sub_product_name').value = name;
    document.getElementById('subProductId').value = id;
    modal.show();
}

document.getElementById('subProductForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let id = document.getElementById('subProductId').value;
    let name = document.getElementById('sub_product_name').value;
    let url = id ? `/sub-products/${id}` : `/sub-products`;
    let method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: JSON.stringify({ sub_product_name: name })
    })
    .then(res => res.json())
    .then(data => {
        modal.hide();
        if (id) {
            document.querySelector(`#row-${id} .name`).innerText = data.sub_product_name;
            Swal.fire("Updated!", "Sub Product updated successfully!", "success");
        } else {
            let row = `<tr id="row-${data.id}">
                <td class="name">${data.sub_product_name}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="openEditModal(${data.id}, '${data.sub_product_name}')">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteSubProduct(${data.id})">Delete</button>
                </td>
            </tr>`;
            document.querySelector("#subProductsTable").insertAdjacentHTML('beforeend', row);
            Swal.fire("Added!", "Sub Product added successfully!", "success");
        }
    })
    .catch(() => Swal.fire("Error", "Something went wrong!", "error"));
});

function deleteSubProduct(id) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to undo this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/sub-products/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
            .then(res => res.json())
            .then(() => {
                document.getElementById(`row-${id}`).remove();
                Swal.fire("Deleted!", "Sub Product deleted successfully!", "success");
            })
            .catch(() => Swal.fire("Error", "Failed to delete!", "error"));
        }
    });
}
</script>

@endsection
