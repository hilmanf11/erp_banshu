<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'plant',width:100,halign:'center'">Plant</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No BEI</th>
            <th rowspan="2" data-options="field:'item_rm_alias',width:200,halign:'center'">Part No Vendor</th>
            <th rowspan="2" data-options="field:'description',width:250,halign:'center'">Description</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>
<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 35px;">
    <?= $button ?>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="sync()"><i class="fa fa-retweet"></i> Synchronize</a>
</div>
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Plant</span>
                <input style="width:60%;" name="plant" id="plant" required class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No BEI</span>
                <input style="width:60%;" name="number" id="number" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No Vendor</span>
                <input style="width:60%;" name="alias" id="alias" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Description</span>
                <input style="width:60%;" name="description" id="description" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>
<!-- Synchronize erp -->
<div id="dlg_sync" class="easyui-dialog" title="Synchronize Data Item Alias From MRP" data-options="closed: true,modal:true" style="width: 800px; height: 550px; padding:10px; top: 20px;">
    <form id="frm_sync_erp" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Plant</span>
                    <input style="width:65%;" name="sync_plant" id="sync_plant" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_sync_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_sync_failed">0</b></span>
    <div id="p_sync_erp" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start_sync">0</b> Of <b id="p_finish_sync">0</b></center>
    <div id="p_remarks_sync" title="History Synchronize" class="easyui-panel" style="width:100%; height:350px; padding:10px; margin-top: 10px;">
        <ul id="remarks_sync">
        </ul>
    </div>
</div>
<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/item_alias/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    function sync() {
        $('#dlg_sync').dialog('open');
    }

    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/item_alias/create') ?>';
        $('#frm_insert').form('clear');
    }
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/item_alias/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }
    //DELETE DATA
    function deleted() {
        var rows = $('#dg').datagrid('getSelections');
        if (rows.length > 0) {
            Swal.fire({
                title: 'Delete Data',
                text: "Are you sure? You want to delete this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, delete it!'
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

                    requestDelete(rows.length, rows);
                    function requestDelete(total, json, number = 1, value = 0) {
                        if (value < 100) {
                            var row = json[number-1];
                            value = Math.floor((number / total) * 100);

                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('master/item_alias/delete') ?>',
                                data: {
                                    id: row.id
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                    requestDelete(total, json, number + 1, value);

                                    if (number == total) {
                                        $('#dg').datagrid('reload');
                                        Swal.close();
                                        Swal.fire(
                                            'Delete Completed',
                                            'Delete Data has been completed, You cannot restore data that has been deleted',
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
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/item_alias/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/item_alias/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        $('#sync_plant').combobox({
            url: '<?php echo base_url('master/plants/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Plant',
        });

        $('#plant').combobox({
            url: '<?php echo base_url('master/plants/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Plant',
        });

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
                        onSubmit: function() {
                            if($(this).form('validate') === true){
                                $('#dlg_insert').dialog('close');
                                
                                Swal.fire({
                                    title: 'Please Wait...',
                                    showConfirmButton: false,
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    },
                                });
                            }else{
                                return $(this).form('validate');
                            }
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');

                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }

                            $('#dg').datagrid('reload');
                            Swal.close();
                        }
                    });
                }
            }]
        });

        //Synchronize Data
        $('#dlg_sync').dialog({
            buttons: [{
                text: 'Start Synchronize',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_sync_erp').form('submit', {
                        url: "<?= base_url('api/mrp_banshu/getDataAlias') ?>",
                        onSubmit: function() {
                            Swal.fire({
                                title: 'Collecting data..',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });
                        },
                        success: function(result) {
                            Swal.close();
                            var json = eval('(' + result + ')');
                            if (json.total == 0) {
                                var title = "<b style='color: red;'>Data Not Found</b><br>";
                                $("#p_remarks_sync").append(title);
                            } else {
                                requestData(json.total, json);
                            }

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_sync_erp').progressbar('setValue', value);
                                    $('#p_start_sync').html(number);
                                    $('#p_finish_sync').html(total);

                                    $.post('<?= base_url('master/item_alias/syncPullCreate') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');
                                        if (result.theme == "success") {
                                            Swal.close();
                                            $('#p_sync_success').html(success);
                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            Swal.close();
                                            $('#p_sync_failed').html(failed);
                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }
                                        $("#p_remarks_sync").prepend(title + "<br>");
                                    }).fail(function(jqXHR, textStatus) {
                                        Swal.fire({
                                            title: 'Connection Time Out, Re-connecting..',
                                            showConfirmButton: false,
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            },
                                        });

                                        requestData(total, json, number, value, success, failed);
                                    });
                                }
                            }
                        },
                    });
                }
            }]
        });

        $('#number').combogrid({
            url: '<?= base_url('master/item_rm/reads') ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part No BEI",
            columns: [
                [{
                    field: 'id',
                    title: 'Part No EBWS',
                    width: 100
                }, {
                    field: 'number',
                    title: 'Part No Supplier',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 200
                }, ]
            ],
        });

        $('#alias').combogrid({
            url: '<?= base_url('master/item_rm/reads') ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part No Vendor",
            columns: [
                [{
                    field: 'id',
                    title: 'Part No EBWS',
                    width: 100
                }, {
                    field: 'number',
                    title: 'Part No Supplier',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 200
                }, ]
            ],
        });
    });
</script>