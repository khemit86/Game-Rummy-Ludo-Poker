<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered dt-responsive nowrap"
                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead>
                    <tr>
                            <th>Sr no.</th>
                            <th>Game Id</th>
                            <th>Date and Time</th>
                            <th>Winner</th>
                            <th>Winner ID</th>
                            <th>Winning Amount</th>
                            <th>User Amount</th>
                            <th>Admin Comission</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- end col -->
</div>
<script>
function ChangeStatus(id, status) {
    jQuery.ajax({
        url: "<?= base_url('backend/Game/ChangeStatus') ?>",
        type: "POST",
        data: {
            'id': id,
            'status': status
        },
        success: function(data) {
            if (data) {
                alert('Successfully Change status');
            }
            location.reload();
        }
    });
}
$(document).ready(function() {
    $.fn.dataTable.ext.errMode = 'throw';
    $(".table").DataTable({
        // stateSave: true,
        searchDelay: 1000,
        processing: true,
        serverSide: true,
        scrollX: true,
        serverMethod: 'post',
        ajax: {
            url: "<?= base_url('backend/Pokers/Gethistory') ?>"
        },
        columns: [{
                data: 'id'
            },
            {
                data: 'game_id'
            },
            {
                data: 'added_date'
            },
            {
                data: 'name'
            },
            {
                data: 'winner_id'
            },
            {
                data: 'amount'
            },
            {
                data: 'user_winning_amt'
            },
            {
                data: 'admin_winning_amt'
            },
            
            
        ],

        lengthMenu: [
            [10, 50, 100, 200, -1],
            [10, 50, 100, 200, "All"]
        ],
        pageLength: 10,
        dom: 'Bfrtip',
        "buttons": [
            'excel'
        ]

    });
});
</script>