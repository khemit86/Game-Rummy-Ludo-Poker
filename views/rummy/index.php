<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body table-responsive">
                <div class="form-group">
                    <label for="game-category">Game Category:</label>
                    <select class="form-control" id="game-category">
                        <option value="0">Public</option>
                        <option value="1">Private</option>
                        <option value="2">Custom</option>
                    </select>
                </div>
                <table id="game-table" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                    <thead>
                        <tr>
                            <th>Sr no.</th>
                            <th>Game Id</th>
                            <th>Date and Time</th>
                            <th>Game Type</th>
                            <th>Winner</th>
                            <th>Winner ID</th>
                            <th>Winning Amount</th>
                            <th>User Amount</th>
                            <th>Admin Commission</th>
                        </tr>
                    </thead>
                    <!-- Add table body rows here -->
                </table>
            </div>
        </div>
    </div>
    <!-- end col -->
</div>
<script>
    document.getElementById('game-category').addEventListener('change', function () {
    var table = document.getElementById('game-table');
    var rows = table.getElementsByTagName('tr');
    var selectedOption = this.value;

    for (var i = 1; i < rows.length; i++) {
        var gameTypeCell = rows[i].getElementsByTagName('td')[3];
        var gameType = parseInt(gameTypeCell.textContent || gameTypeCell.innerText);

        if (selectedOption === '0' && gameType === 0) {
            rows[i].style.display = '';
        } else if (selectedOption === '1' && gameType === 1) {
            rows[i].style.display = '';
        } else if (selectedOption === '2' && gameType === 2) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
});

</script>
<script>
function ChangeStatus(id, status) {
    jQuery.ajax({
        url: "<?= base_url('backend/Rummy/ChangeStatus') ?>",
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
            url: "<?= base_url('backend/Rummy/Gethistory') ?>"
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
                data: 'private'
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