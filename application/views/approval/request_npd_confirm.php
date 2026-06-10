<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar" data-options="
    url: '<?= base_url('approvals/confirmRequestNpd/' . base64_encode($created_by) . '/' . base64_encode($confirm_by)) ?>',
    pagination: false,
    clientPaging: false,
    rownumbers: true,
    fit: true">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'confirm_date',width:200,halign:'center'">Procurement Confirm Date</th>
            <th data-options="field:'confirm_by',width:150,halign:'center'">Confirm By</th>
            <th data-options="field:'request_no',width:120,halign:'center'">Request No</th>
            <th data-options="field:'item_npd_id',width:150,halign:'center'">Part No</th>
            <th data-options="field:'item_npd_name',width:100,halign:'center'">Part Name</th>
            <th data-options="field:'product_family',width:100,halign:'center'">Product Family</th>
            <th data-options="field:'maker',width:200,halign:'center'">Maker</th>
            <th data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th data-options="field:'qty',width:80,halign:'center',align:'right'">Qty</th>
            <th data-options="field:'requirement_date',width:100,align:'center'">Requirement</th>
            <th data-options="field:'request_reason',width:100,align:'center'">Remarks</th>
            <th data-options="field:'confirm',width:100,align:'center', styler:cellStylerConfirm, formatter:cellFormatterConfirm,">Confirm</th>
            <th data-options="field:'confirm_reason',width:200,halign:'center'">Reason</th>
            <th data-options="field:'supplier_name',width:200,halign:'center'">Vendor Name</th>
            <th data-options="field:'item_rm_number',width:150,halign:'center'">Part No BEI</th>
            <th data-options="field:'item_supplier',width:100,halign:'center'">Part No Vendor</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="approve()"><i class="fa fa-check"></i> Confirm</a>
    <!-- <a href="javascript:void(0)" id="approveacheck" class="easyui-linkbutton" data-options="plain:true" onclick="disapprove()"><i class="fa fa-times"></i> Disapprove</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="export_excel()"><i class="fa fa-file"></i> Export Excel</a> -->
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="reload()"><i class="fa fa-refresh"></i> Reload</a>
</div>

<script>
    $(function() {
        $('#dg').datagrid('enableFilter');
    });

    //RELOAD
    function reload() {
        window.location.reload();
    }

    function export_excel() {
        $('#dg').datagrid('toExcel', "approval_<?= $table ?>.xls");
    }

    function approve() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Confirm Data',
                text: "Are you sure? You want to confirm this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Confirm it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Please Wait...',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    requestApprove(rows.length, rows);

                    function requestApprove(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            var row = json[number - 1];
                            value = Math.floor((number / total) * 100);

                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('approvals/confirm') ?>',
                                data: {
                                    id: row.id,
                                    tablename: "<?= $table ?>"
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                    requestApprove(total, json, number + 1, value);

                                    if (number == total) {
                                        $('#dg').datagrid('reload');
                                        Swal.close();
                                        Swal.fire(
                                            'Confirm Completed',
                                            'Confirm Data has been completed, You cannot restore data that has been confirmed',
                                            'success'
                                        );
                                    }
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    toastr.error(jqXHR.statusText);
                                },
                            });
                        }
                    }
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }

    function disapprove() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Dispprove Data',
                text: "Are you sure? You want to Dispprove this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Dispprove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.messager.prompt('Reason', 'Please input your reason!!', function(message) {
                        if (message) {
                            Swal.fire({
                                title: 'Please Wait...',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            requestApprove(rows.length, rows);

                            function requestApprove(total, json, number = 1, value = 0) {
                                if (value < 100) {
                                    var row = json[number - 1];
                                    value = Math.floor((number / total) * 100);

                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('approvals/disapprove') ?>',
                                        data: {
                                            id: row.id,
                                            tablename: "<?= $table ?>",
                                            message: message
                                        },
                                        success: function(result) {
                                            var result = eval('(' + result + ')');
                                            requestApprove(total, json, number + 1, value);

                                            if (number == total) {
                                                $('#dg').datagrid('reload');
                                                Swal.close();
                                                Swal.fire(
                                                    'Dispprove Completed',
                                                    'Dispprove Data has been completed, You cannot restore data that has been Dispproved',
                                                    'success'
                                                );
                                            }
                                        },
                                        error: function(jqXHR, textStatus, errorThrown) {
                                            toastr.error(jqXHR.statusText);
                                        },
                                    });
                                }
                            }
                        } else {
                            Swal.fire(
                                'Cannot Process',
                                'Please Input your reason to Process Disapprove',
                                'error'
                            );
                        }
                    });
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID');

        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatPrice(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2
        });

        return "<b>" + formatter.format(value) + "</b>";
    }


    function cellStylerConfirm(value, row, index) {
        if (value == null || value == "") {
            return 'background: #ffcc6dff; color:white;';
        } else if (value == "CONFIRMED") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterConfirm(value, row) {
        if (value == null || value == "") {
            return 'PENDING';
        } else if (value == "CONFIRMED") {
            return 'CONFIRMED';
        } else {
            return value;
        }
    };
</script>