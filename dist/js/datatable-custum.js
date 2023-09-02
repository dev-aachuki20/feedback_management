$(document).ready( function () {
    $('#manage-table').DataTable({
        "sPaginationType": "simple_numbers",
        "aoColumnDefs": [
            { 'bSortable': false, 'aTargets': [2] }
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    });

    $('#common-table').DataTable({
        "sPaginationType": "simple_numbers",
        "aoColumnDefs": [
            { 'bSortable': false}
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
    });
});