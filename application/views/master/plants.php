<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:80,halign:'center'">Code</th>
            <th rowspan="2" data-options="field:'name',width:200,halign:'center'">Name</th>
            <th rowspan="2" data-options="field:'description',width:150,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'detail',width:80,align:'center',formatter: btnDetails">Address</th>
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
</div>

<!-- TOOLBAR CUSTOMERS ADDRESS DATAGRID -->
<div id="toolbar2" style="height: 35px;">
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="add2()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="update2()"><i class="fa fa-edit"></i> Update</a>
    <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="deleted2()"><i class="fa fa-trash"></i> Delete</a>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Code</span>
                <input style="width:30%;" name="number" id="number" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Name</span>
                <input style="width:60%;" name="name" id="name" required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Description</span>
                <input style="width:60%;" name="description" id="description" class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- DIALOG CUSTOMER PLANT -->
<div id="dlg_details" class="easyui-dialog" title="Plant Address" data-options="closed: true,modal:true" style="width: 80%; height: 500px; top:10px; left: 10px;">
    <table id="dg2" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2">
        <thead>
            <tr>
                <th rowspan="2" field="ck" checkbox="true"></th>
                <th rowspan="2" data-options="field:'number',width:100,halign:'center'">Plant</th>
                <th rowspan="2" data-options="field:'name',width:100,halign:'center'">Name</th>
                <th rowspan="2" data-options="field:'address',width:300,halign:'center'">Address</th>
                <th rowspan="2" data-options="field:'default',width:80,halign:'center'">Default</th>
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
</div>

<!-- DIALOG SAVE AND UPDATE PLANT ADDRESS -->
<div id="dlg_insert2" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_insert2" method="post" novalidate>
        <div style="float: left; width: 100%;">
            <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
                <legend><b>Basic Information</b></legend>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="number" id="number_address" readonly class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Name</span>
                    <input style="width:60%;" name="name" id="name" required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Address</span>
                    <input style="width:60%; height: 60px;" name="address" id="address" required multiline="true" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Default</span>
                    <select style="width:60%;" name="default" id="default" class="easyui-combobox" panelHeight="auto" editable="false">
                        <option value="YES">YES</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
            </fieldset>
        </div>
    </form>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('master/plants/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //ADD DATA
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('master/plants/create') ?>';
        $('#frm_insert').form('clear');
    }

    //ADD DATA
    function add2() {
        $('#dlg_insert2').dialog('open');
        var number_address = $("#number_address").textbox('getValue');
        url_save2 = '<?= base_url('master/plants/create2') ?>';
        $('#frm_insert2').form('clear');
        $("#number_address").textbox('setValue', number_address);
        $("#number_address").textbox('enable');
    }
    
    //EDIT DATA
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            $('#dlg_insert').dialog('open');
            $('#frm_insert').form('load', row);
            url_save = '<?= base_url('master/plants/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function update2() {
        var row = $('#dg2').datagrid('getSelected');
        if (row) {
            $('#dlg_insert2').dialog('open');
            $('#frm_insert2').form('load', row);
            url_save2 = '<?= base_url('master/plants/update2') ?>?id=' + btoa(row.id);
            $("#number_address").textbox('disable');
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
                                url: '<?= base_url('master/plants/delete') ?>',
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

    function deleted2() {
        var rows = $('#dg2').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        $.ajax({
                            method: 'post',
                            url: '<?= base_url('master/plants/delete2') ?>',
                            data: {
                                id: row.id
                            },
                            success: function(result) {
                                var result = eval('(' + result + ')');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                toastr.error("This data is already used in other modules, You must delete it First", "Error");

                            },
                            complete: function(data) {
                                $('#dg2').datagrid('reload');
                            }
                        });
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function btnDetails(val, row) {
        var details = "details('" + row.number + "')"; //mengambil id dari customers kemudian di simpan di function details
        return '<a class="btn btn-primary w-100" onClick="' + details + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-list"></i> Detail</a>';
    }

    function details(number) {
        $("#dlg_details").dialog('open');
        $("#number_address").textbox('setValue', number); // id customer di simpan di textbox customer_id sekaligus saat add id tersimpan

        $('#dg2').datagrid({
            url: '<?= base_url('master/plants/datatables2/') ?>' + number,
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit:true
        }).datagrid('enableFilter');
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }
    //PRINT EXCEL
    function excel() {
        window.location.assign('<?= base_url('master/plants/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/plants/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

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

        $('#dlg_insert2').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert2').form('submit', {
                        url: url_save2,
                        onSubmit: function() {
                            return $(this).form('validate');
                        },
                        success: function(result) {
                            var result = eval('(' + result + ')');
                            if (result.theme == "success") {
                                toastr.success(result.message, result.title);
                            } else {
                                toastr.error(result.message, result.title);
                            }
                            $('#dlg_insert2').dialog('close');
                            $('#dg2').datagrid('reload');
                        }
                    });
                }
            }]
        });
    });
</script>