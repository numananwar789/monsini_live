@extends('layouts.app')

@section('page-css')
    <style>
        .btn-width {
            width: 5rem;
            margin-bottom: 6px !important;
        }

        .zoom {
            transition: transform .2s;
        }

        .zoom:hover {
            transform: scale(1.5);
        }

        .dropdown-scroll {
            max-height: 300px;
            overflow-y: auto;
        }

        .dropdown-scroll>div {
            padding: 12px;
        }

        .dropdown-scroll .d-flex {
            padding: 8px 4px;
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

                                    @if (session('import_errors'))
                                        <div class="alert alert-danger">
                                            <strong>Import failed:</strong>
                                            <ul class="mb-0">
                                                @foreach (session('import_errors') as $line)
                                                    <li>{{ $line }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <hr />
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <div class="d-flex justify-content-start align-items-center">
                                                    <label for="check-all" class="mb-0" style="font-size:18px"><b>Check
                                                            all the entries</b></label>
                                                    <input type="checkbox" name="check-all" id="check-all" class="ml-2">
                                                    <span id="selection-count" class="ml-3 text-muted"></span>
                                                </div>

                                                <table id="example" class="table table-striped table-bordered"
                                                    style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Check</th>
                                                            <th>Image</th>
                                                            <th>Style</th>
                                                            <th>F. Style</th>
                                                            <th>Color</th>
                                                            <th>Sub Products</th>
                                                            <th>Size Range</th>
                                                            <th>Total Cost</th>
                                                            <th>Total Price</th>
                                                            <th>Vendor</th>
                                                            @if (auth()->user()->admin_role === 'superadmin' || auth()->user()->user_name == 'admin1')
                                                                <th style="width: 175px!important;">Edit/Delete</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {{-- Rows are now loaded via AJAX by DataTables (serverSide),
                                                             see the getProductsData() endpoint. Nothing rendered here. --}}
                                                    </tbody>
                                                </table>

                                            </div>
                                        </div>
                                    </div>

                                    @if (auth()->user()->admin_role == 'superadmin' || auth()->user()->user_name == 'admin1' || true)
                                        <div class="card-block">
                                            <a class="btn btn-primary" id="add-products-order"
                                                href="{{ route('products.create') }}">Add New Product</a>
                                            <a class="btn btn-primary text-white" data-toggle="modal"
                                                data-target="#importModal">Import Products</a>
                                            <a href="{{ route('admin-products.download') }}"
                                                class="btn btn-success float-right">Download Product Data</a>

                                            <button type="button" class="btn btn-warning float-right"
                                                data-toggle="modal" data-target="#archiveModal">Archive Data</button>

                                            <a href="{{ route('sub-products.index') }}"
                                                class="btn btn-success float-right">Sub Products</a>
                                        </div>
                                    @endif


                                    <hr>

                                    <h5 class="mt-4 mb-3">Year Publishing Control</h5>

                                    <div class="dropdown">

                                        <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                            id="yearDropdown" data-toggle="dropdown" aria-expanded="false">

                                            Manage Year Publishing

                                        </button>

                                        <div class="dropdown-menu p-3" aria-labelledby="yearDropdown"
                                            style="min-width: 350px;">


                                            <div class="dropdown-scroll">
                                                @foreach ($years as $year)
                                                    <div class="d-flex justify-content-between align-items-center mb-3">

                                                        <div>
                                                            <strong>Azure {{ $year->year }}</strong>
                                                            <br>

                                                            <small class="text-muted">
                                                                {{ $year->count }} Products
                                                            </small>
                                                        </div>

                                                        <div class="text-right">

                                                            @if ($year->is_published)
                                                                <span class="badge badge-success mb-1">
                                                                    Published
                                                                </span>
                                                            @else
                                                                <span class="badge badge-secondary mb-1">
                                                                    Hidden
                                                                </span>
                                                            @endif

                                                            <br>

                                                            <label class="mb-0">

                                                                <input type="checkbox" class="toggle-year-checkbox"
                                                                    data-year="{{ $year->year }}"
                                                                    {{ $year->is_published ? 'checked' : '' }}>

                                                                Publish

                                                            </label>

                                                        </div>

                                                    </div>

                                                    @if (!$loop->last)
                                                        <div class="dropdown-divider"></div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Import Modal -->
                                    <div class="modal fade" id="importModal" tabindex="-1" role="dialog"
                                        aria-labelledby="importModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="importModalLabel">Upload Excel File</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form method="POST" action="{{ route('admin-products.import') }}"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="productFile">Upload File</label>
                                                            <input name="file" type="file" class="form-control-file"
                                                                id="productFile" accept=".xls,.xlsx" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Submit</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal -->
                                    <div class="modal fade" id="archiveModal" tabindex="-1"
                                        aria-labelledby="archiveModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="archiveModalLabel">Archive Products</h5>
                                                    <button type="submit" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="archive-name" class="col-form-label">Archive
                                                            Name:</label>
                                                        <input type="text" class="form-control" id="archive-name"
                                                            name="archive-name">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button id='profileclick' onclick="xpandTablePrintNew()"
                                                        type="submit" class="btn btn-primary">Save</button>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Modal -->

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

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
    {{-- The local 2.1MB DataTables bundle used to be loaded here, but the CDN
         copy below overwrites $.fn.dataTable wholesale — so its extensions and
         its Bootstrap-5 integration were being thrown away, its stylesheet was
         never even referenced, and its stale scroll handlers threw on every
         scroll. It was 2.1MB downloaded and parsed on each page load for
         nothing, so it's gone; the CDN build below is the one actually in use. --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>

    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>

    {{-- Scroller (virtual scrolling) — must load AFTER the DataTables core
         above, which overwrites the bundled copy in
         /assets/plugins/datatable/js/ along with all of its extensions. --}}
    <link rel="stylesheet" href="/assets/plugins/datatable/css/scroller.dataTables.min.css">
    <script src="/assets/plugins/datatable/js/dataTables.scroller.min.js"></script>

    <!-- toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script>
        // Which products are selected has to be tracked in JS, not read off
        // the DOM: with server-side processing (and now virtual scrolling)
        // only a few dozen rows exist at any moment, so ":checked" can never
        // see the full selection.
        //
        // selectAllMatching = "every record matching the current search".
        // In that mode we track the holes (excludedStyles) instead of the
        // 10,000 individual picks, and the server resolves the real list.
        var selectedStyles = new Set();
        var excludedStyles = new Set();
        var selectAllMatching = false;

        function updateSelectionCount() {
            var count;

            if (selectAllMatching) {
                var info = table ? table.page.info() : null;
                count = (info ? info.recordsDisplay : 0) - excludedStyles.size;
            } else {
                count = selectedStyles.size;
            }

            $('#selection-count').text(count > 0 ? count.toLocaleString() + ' selected' : '');
        }

        // This used to call table.page.len(-1).draw() before submitting — a
        // leftover from the client-side era, when every row had to be in the
        // DOM for ":checked" to find it. Under server-side processing that
        // just yanks all 10,000 styles into the browser (precisely what
        // crashes the tab) and still wouldn't have found anything, since
        // rows on other pages were never checked in the DOM to begin with.
        function xpandTablePrintNew() {
            var archiveName = $.trim($('#archive-name').val() || '');

            if (!archiveName) {
                alert('Please enter an archive name.');
                return;
            }

            var payload = {
                archiveName: archiveName
            };

            if (selectAllMatching) {
                // Send the filter, not the list. Shipping 10k style numbers
                // would be silently truncated by PHP's max_input_vars limit
                // anyway, so the server re-runs the search and archives the
                // matching set minus whatever was unticked.
                payload.selectAll = 1;
                payload.search = table.search() || '';
                payload.excluded = Array.from(excludedStyles);
            } else {
                payload.selectedItems = Array.from(selectedStyles);

                if (!payload.selectedItems.length) {
                    alert('Please select at least one product to archive.');
                    return;
                }
            }

            var $save = $('#profileclick').prop('disabled', true).text('Archiving…');

            $.ajax({
                url: '/products/archive',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: payload,
                success: function(response) {
                    alert('Archived ' + (response.archived || 0) + ' products successfully!');
                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    $save.prop('disabled', false).text('Save');
                    alert(xhr.responseText);
                }
            });
        }

        // Bound once on document — genuine delegation, so rows that DataTables
        // renders later are covered automatically. The old version re-ran a
        // direct .on() against every .js-select2 on each draw, which stacked
        // duplicate handlers (and fired one AJAX call per stacked copy) once
        // rows started being re-rendered as often as scrolling does now.
        $(document)
            .on('select2:select', '.js-select2', function(e) {
                setColorStatus(e.params.data.id, e.params.data.text, 'Active');
            })
            .on('select2:unselect', '.js-select2', function(e) {
                setColorStatus(e.params.data.id, e.params.data.text, 'Inactive');
            });

        function setColorStatus(prodID, colorProd, action) {
            $.ajax({
                url: "{{ route('admin-products.action') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: prodID,
                    action: action
                },
                success: function(response) {
                    if (!response.success) {
                        return;
                    }

                    if (action === 'Active') {
                        toastr.success('Color ' + colorProd + ' has been activated',
                            'Activated', {
                                progressBar: true,
                                closeHtml: '<button type="button">&times;</button>',
                                newestOnTop: true,
                            });
                    } else {
                        toastr.error('Color ' + colorProd + ' has been de-activated',
                            'Deactivated', {
                                progressBar: true,
                                closeHtml: '<button type="button">&times;</button>',
                                newestOnTop: true,
                            });
                    }
                }
            });
        }

        $(document).ready(function() {

            var canEdit =
                {{ auth()->user()->admin_role === 'superadmin' || auth()->user()->user_name == 'admin1' ? 'true' : 'false' }};

            var columns = [{
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'image',
                    name: 'image',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'style',
                    name: 'product_style'
                },
                {
                    data: 'factory_style',
                    name: 'factory_style'
                },
                {
                    data: 'color',
                    name: 'product_color',
                    orderable: false
                },
                {
                    data: 'sub_products',
                    name: 'sub_products',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'size_range',
                    name: 'product_size_range'
                },
                {
                    data: 'cost',
                    name: 'product_cost'
                },
                {
                    data: 'price',
                    name: 'product_wholesale_price'
                },
                {
                    data: 'vendor',
                    name: 'product_vendor_name'
                },
            ];

            if (canEdit) {
                columns.push({
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                });
            }

            // The table runs in one of two modes:
            //
            //   paged    — the original behaviour, unchanged: pick 10/25/50/
            //              100/200 from the dropdown and page through.
            //   scroller — used only when "All" is picked. DataTables'
            //              Scroller keeps just the rows near the viewport in
            //              the DOM (a couple of dozen instead of 10,000) while
            //              the scrollbar still spans the whole catalogue.
            //
            // "All" used to render every row at once, which meant 10,000 rows
            // AND 10,000 select2 colour widgets — that's what killed the tab.
            // In scroller mode you still reach every record, just by scrolling
            // instead of by rendering them all up front.
            var scrollerMode = false;

            // Tracked across rebuilds: search.dt also fires when the table is
            // re-created, and without comparing the actual term that would
            // silently wipe the user's selection every time they switch modes.
            var lastSearchTerm = '';

            function tableConfig(useScroller, pageLength) {
                var config = {
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('admin-products.datatable') }}",
                        type: 'GET'
                    },
                    columns: columns,
                    aLengthMenu: [
                        [10, 25, 50, 100, 200, -1],
                        [10, 25, 50, 100, 200, "All"]
                    ],
                    buttons: buttons,
                    drawCallback: onDraw
                };

                if (useScroller) {
                    config.deferRender = true;
                    config.scrollY = '60vh';
                    config.scrollX = true;
                    config.scrollCollapse = true;
                    config.scroller = {
                        loadingIndicator: true,
                        // Rendered rows ≈ displayBuffer × page length. These
                        // rows are heavy (image + select2 + action form), so
                        // keep the rendered window deliberately small.
                        displayBuffer: 2
                    };
                    // No pager: scrolling is the navigation in this mode.
                    config.dom = 'lBfrti';
                } else {
                    config.dom = 'lBfrtip';
                    // Keep whatever size the user picked when coming back out
                    // of "All", rather than snapping them to the default.
                    config.pageLength = pageLength || 10;
                }

                return config;
            }

            function initTable(useScroller, pageLength) {
                if ($.fn.dataTable.isDataTable('#example')) {
                    // Tear the old instance down cleanly — select2 widgets
                    // have to be destroyed explicitly or they leak their
                    // detached DOM behind the table.
                    $('#example').find('.js-select2').each(function() {
                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }
                    });

                    $('#example').DataTable().destroy();
                    $('#example').find('tbody').empty();
                }

                scrollerMode = useScroller;
                table = $('#example').DataTable(tableConfig(useScroller, pageLength));

                // search.dt lives on the instance, so it needs rebinding
                // whenever the table is rebuilt.
                table.on('search.dt', function() {
                    var term = table.search();

                    if (term === lastSearchTerm) {
                        return;
                    }

                    lastSearchTerm = term;

                    // A different filter means a different "everything", so
                    // drop the select-all flag. Explicit picks stay valid.
                    if (selectAllMatching) {
                        selectAllMatching = false;
                        excludedStyles.clear();
                        $('#check-all').prop('checked', false);
                    }

                    updateSelectionCount();
                });
            }

            var buttons = [{
                    extend: 'print',
                    autoPrint: true,
                    text: 'Print',
                    exportOptions: {
                        columns: function(idx, data, node) {
                            if (node.innerHTML == "Edit/Delete" || node.innerHTML == "Image")
                                return false;
                            return true;
                        }
                    },
                    customize: function(win) {
                        $(win.document.body).css('font-size', '11pt');
                        $(win.document.body).find('table').addClass('compact').css('font-size',
                            'inherit');

                        var css = '@page { size: landscape; }',
                            head = win.document.head || win.document.getElementsByTagName(
                                'head')[0],
                            style = win.document.createElement('style');

                        style.type = 'text/css';
                        style.media = 'print';

                        if (style.styleSheet) {
                            style.styleSheet.cssText = css;
                        } else {
                            style.appendChild(win.document.createTextNode(css));
                        }

                        head.appendChild(style);
                    }
                }];

            function onDraw() {
                // Only initialise selects that aren't already select2 widgets:
                // in scroller mode this runs on every scroll chunk, and
                // re-initialising a live widget duplicates its DOM.
                $('#example').find('.js-select2:not(.select2-hidden-accessible)').select2({
                    closeOnSelect: false,
                    placeholder: "Colors",
                    allowHtml: true,
                    allowClear: true,
                    tags: false
                });

                // Rows are re-rendered from scratch on every draw, so the
                // ticked state has to be restored from the tracked selection
                // rather than surviving in the DOM.
                $('#example').find('input[name="products[]"]').each(function() {
                    this.checked = selectAllMatching ?
                        !excludedStyles.has(this.value) :
                        selectedStyles.has(this.value);
                });

                // Scroller manages the page length itself (it sizes chunks to
                // the viewport), which leaves the dropdown showing whatever
                // number it landed on. Keep it reading "All", since that's the
                // mode the user actually chose.
                if (scrollerMode) {
                    $('#example_length select').val('-1');
                }
            }

            initTable(false);

            // Switching to "All" rebuilds the table in scroller mode, and
            // picking any real page size rebuilds it as a normal paged table.
            // This is bound to the <select> rather than DataTables' length.dt
            // event because Scroller fires that event itself when it resizes
            // its chunks, which would look like a user choice and bounce the
            // table straight back out of scroller mode.
            $(document).on('change', '#example_length select', function() {
                var length = parseInt(this.value, 10);
                var wantScroller = length === -1;

                if (wantScroller !== scrollerMode) {
                    // Defer so DataTables finishes handling this change first.
                    setTimeout(function() {
                        initTable(wantScroller, wantScroller ? null : length);
                    }, 0);
                }
            });

            // "Check all the entries" means every record matching the current
            // search — not just the rows that happen to be rendered. The real
            // list is resolved server-side when archiving.
            $('#check-all').on('change', function() {
                selectAllMatching = this.checked;
                selectedStyles.clear();
                excludedStyles.clear();

                $('#example').find('input[name="products[]"]').each(function() {
                    this.checked = selectAllMatching;
                });

                updateSelectionCount();
            });

            $('#example').on('change', 'input[name="products[]"]', function() {
                if (selectAllMatching) {
                    // Stay in "all matching" mode, just remember the holes.
                    if (this.checked) {
                        excludedStyles.delete(this.value);
                    } else {
                        excludedStyles.add(this.value);
                    }
                } else if (this.checked) {
                    selectedStyles.add(this.value);
                } else {
                    selectedStyles.delete(this.value);
                }

                updateSelectionCount();
            });

            $(document).on('change', '.toggle-inventory-override', function() {

                let style = $(this).data('style');
                let status = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: "{{ route('admin-products.toggle-inventory') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        style: style,
                        status: status
                    },
                    success: function(response) {

                        if (status === 1) {
                            toastr.success('Show from Inventory enabled');
                        } else {
                            toastr.warning('Show from Inventory disabled');
                        }

                    },
                    error: function() {
                        toastr.error('Something went wrong');
                    }
                });

            });
        });

        $(document).on('change', '.toggle-year-checkbox', function() {

            let checkbox = $(this);

            let year = checkbox.data('year');

            let status = checkbox.is(':checked') ? 1 : 0;

            $.ajax({
                url: "{{ route('admin-products.toggle-year') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    year: year,
                    status: status
                },

                success: function(res) {

                    toastr.success(res.message);

                    let badge = checkbox.closest('.text-right').find('.badge');

                    if (status === 1) {

                        badge
                            .removeClass('badge-secondary')
                            .addClass('badge-success')
                            .text('Published');

                    } else {

                        badge
                            .removeClass('badge-success')
                            .addClass('badge-secondary')
                            .text('Hidden');
                    }
                },

                error: function() {

                    toastr.error('Failed to update year status');

                    checkbox.prop('checked', !status);
                }
            });

        });
    </script>
@endsection
