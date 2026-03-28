<div class="page-heading">
    <h3><i class="bi bi-bag-fill me-2"></i> Order History</h3>
</div>

<section class="section">
    <?php $success_message = session()->getFlashdata('success_message'); if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <?= $success_message ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">All Orders</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="orderTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>User</th>
                            <th>Tickets</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<style>
tr.dt-hasChild td { background: #f0f7ff !important; }
.ticket-child-row { padding: .5rem 1rem 1rem 1rem; }
.ticket-child-table { max-width: 520px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var jQuery = window.jQuery;
    var $ = jQuery;

    function formatTickets(tickets) {
        if (!tickets || tickets.length === 0) {
            return '<div class="ticket-child-row"><p class="text-muted mb-0">No tickets found.</p></div>';
        }
        var html = '<div class="ticket-child-row">';
        html += '<table class="table table-bordered table-sm ticket-child-table mb-0">';
        html += '<thead><tr><th>#</th><th>Event</th><th>Ticket No.</th></tr></thead><tbody>';
        tickets.forEach(function (t, i) {
            html += '<tr><td>' + (i + 1) + '</td><td>' + t.game + '</td><td><code>' + t.ticket_no + '</code></td></tr>';
        });
        html += '</tbody></table></div>';
        return html;
    }

    var table = $('#orderTable').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: baseURL + 'web/order_data',
            type: 'GET'
        },
        columns: [
            { data: 'order_no' },
            { data: 'user', orderable: false },
            { data: 'ticket_info', orderable: false, searchable: false },
            { data: 'amount', orderable: false },
            { data: 'payment', orderable: false, searchable: false },
            { data: 'order_id', orderable: false },
            { data: 'date', orderable: false },
            { data: 'status', orderable: false, searchable: false },
            {
                data: null, orderable: false, searchable: false,
                render: function (data) {
                    var ps = data.paid_status;
                    if (ps === 'PAID' || ps === 1 || ps === '1') {
                        return '<button class="btn btn-sm btn-warning release-order" data-id="' + data.raw_id + '" title="Release Order"><i class="bi bi-send-fill"></i> Release</button>';
                    }
                    return '<span class="text-muted">—</span>';
                }
            },
            { data: 'tickets', visible: false, searchable: false, render: function () { return ''; } }
        ],
        pageLength: 10,
        order: [[0, 'desc']],
        language: { search: 'Search orders:' }
    });

    // Release order
    $('#orderTable tbody').on('click', '.release-order', function () {
        var btn = $(this);
        var orderId = btn.data('id');
        if (!confirm('Release order #' + orderId + '? This will mark it as RELEASED and clear the cart.')) return;
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Releasing...');
        $.ajax({
            type: 'POST',
            url: baseURL + 'web/release_order_by_admin',
            data: { orderid: orderId },
            dataType: 'json'
        }).done(function (res) {
            if (res.status === true) {
                table.ajax.reload(null, false);
            } else {
                alert('Release failed. Please try again.');
                btn.prop('disabled', false).html('<i class="bi bi-send-fill"></i> Release');
            }
        }).fail(function () {
            alert('Request failed.');
            btn.prop('disabled', false).html('<i class="bi bi-send-fill"></i> Release');
        });
    });

    // Expand/collapse ticket child row on button click
    $('#orderTable tbody').on('click', '.expand-tickets', function (e) {
        e.stopPropagation();
        var tr  = $(this).closest('tr');
        var row = table.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('dt-hasChild');
            $(this).removeClass('btn-primary').addClass('btn-outline-primary');
        } else {
            row.child(formatTickets(row.data().tickets)).show();
            tr.addClass('dt-hasChild');
            $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        }
    });
});
</script>

