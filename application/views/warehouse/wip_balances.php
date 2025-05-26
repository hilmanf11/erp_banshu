<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'supply_type',width:80,align:'center',formatter:issuedformat,styler:statusIssued">Status</th>
            <th rowspan="2" data-options="field:'item_rm_id',width:120,halign:'center'">Part No</th>
            <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th>
            <!-- <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th> -->
            <th rowspan="2" data-options="field:'uom',width:80,align:'center'">Uom</th>
            <th rowspan="2" data-options="field:'request_no',width:150,align:'center'">Kanban No</th>
            <th rowspan="2" data-options="field:'workorder',width:150,align:'center'">Workorder No</th>
            <th rowspan="2" data-options="field:'qty_act',width:80,halign:'center',align:'right', formatter:numberformat">Need</th>
            <th rowspan="2" data-options="field:'issued',width:80,halign:'center',align:'right', formatter:numberformat">Issued</th>
            <th rowspan="2" data-options="field:'qty_issued_bal',width:80,halign:'center',align:'right', formatter:numberformat,styler:numberStyle">Balance</th>
            <!-- <th rowspan="2" data-options="field:'warehouse',width:80,halign:'center',align:'right', formatter:numberformat">Warehouse</th> -->
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
<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Item ID</span>
                <input style="width:60%;" name="item_rm_id" id="item_rm_id" required="" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supply Sheet</span>
                <input style="width:60%;" name="request_no" id="request_no" required="" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Uom</span>
                <input style="width:60%;" name="uom" id="uom" disabled class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Begin</span>
                <input style="width:30%;" name="begin" class="easyui-numberbox" required data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Need</span>
                <input style="width:30%;" name="need" class="easyui-numberbox" required data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Issued</span>
                <input style="width:30%;" name="issued" class="easyui-numberbox" required data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Balance</span>
                <input style="width:30%;" name="balance" class="easyui-numberbox" required data-options="precision:2">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Warehouse</span>
                <input style="width:30%;" name="warehouse" class="easyui-numberbox" required data-options="precision:2">
            </div>
        </fieldset>
    </form>
</div>

<!-- PDF -->
<!-- <iframe id="printout" src="<?= base_url('warehouse/wip_balances/print') ?>" style="width: 100%;" hidden></iframe> -->
<script>
    //ADD DATA
    function add() {
        // $('#dlg_insert').dialog('open');
        // url_save = '<?= base_url('warehouse/wip_balances/create') ?>';
        // $('#frm_insert').form('clear');
    }

    //EDIT DATA
    function update() {
        // var row = $('#dg').datagrid('getSelected');
        // if (row) {
        //     $('#dlg_insert').dialog('open');
        //     $('#frm_insert').form('load', row);
        //     url_save = '<?= base_url('warehouse/wip_balances/update') ?>?id=' + btoa(row.id);
        // } else {
        //     toastr.warning("Please select one of the data in the table first!", "Information");
        // }
    }

    //DELETE DATA
    // function deleted() {
    //     var rows = $('#dg').datagrid('getSelections');
    //     if (rows.length > 0) {
    //         $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
    //             if (r) {
    //                 for (var i = 0; i < rows.length; i++) {
    //                     var row = rows[i];
    //                     $.ajax({
    //                         method: 'post',
    //                         url: '<?= base_url('warehouse/wip_balances/delete') ?>',
    //                         data: {
    //                             id: row.id,
    //                             workorder: row.workorder,
    //                         },
    //                         success: function(result) {
    //                             var result = eval('(' + result + ')');
    //                         },
    //                         error: function(jqXHR, textStatus, errorThrown) {
    //                             toastr.error("This data is already used in other modules, You must delete it First", "Error");
                                
    //                         },
    //                         complete: function(data) {
    //                             $('#dg').datagrid('reload');
    //                         }
    //                     });
    //                 }
    //             }
    //         });
    //     } else {
    //         toastr.warning("Please select one of the data in the table first!", "Information");
    //     }
    // }

    //PRINT PDF
    function pdf() {
        // $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        // var supply_type = $('#dg').datagrid('getFilterRule', 'supply_type');
        // var item_rm_id = $('#dg').datagrid('getFilterRule', 'item_rm_id');
        // var item_rm_number = $('#dg').datagrid('getFilterRule', 'item_rm_number');
        // var uom = $('#dg').datagrid('getFilterRule', 'uom');
        // var request_no = $('#dg').datagrid('getFilterRule', 'request_no');
        // var workorder = $('#dg').datagrid('getFilterRule', 'workorder');
        // var qty_act = $('#dg').datagrid('getFilterRule', 'qty_act');
        // var issued = $('#dg').datagrid('getFilterRule', 'issued');
        // var qty_issued_bal = $('#dg').datagrid('getFilterRule', 'qty_issued_bal');
        // var created_by = $('#dg').datagrid('getFilterRule', 'created_by');
        // var created_date = $('#dg').datagrid('getFilterRule', 'created_date');
        // var updated_by = $('#dg').datagrid('getFilterRule', 'updated_by');
        // var updated_date = $('#dg').datagrid('getFilterRule', 'updated_date');
        
        // var url = "?supply_type=" + window.btoa(supply_type.value) +
        //     "&item_rm_id=" + window.btoa(item_rm_id.value) +
            // "&item_rm_number=" + window.btoa(item_rm_number.value) +
            // "&uom=" + window.btoa(uom.value) +
            // "&request_no=" + window.btoa(request_no.value) +
            // "&workorder=" + window.btoa(workorder.value) +
            // "&qty_act=" + window.btoa(qty_act.value) +
            // "&issued=" + window.btoa(issued.value) +
            // "&qty_issued_bal=" + window.btoa(qty_issued_bal.value) +
            // "&created_by=" + window.btoa(created_by.value) +
            // "&created_date=" + window.btoa(created_date.value) +
            // "&updated_by=" + window.btoa(updated_by.value) +
            // "&updated_date=" + window.btoa(updated_date.value);

        $('#dg').datagrid('toExcel','wip_balances.xls');
        // window.location.assign('<?= base_url('warehouse/wip_balances/print/excel') ?>');
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }
    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('warehouse/wip_balances/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        }).datagrid('enableFilter');

        // $('#dg').datagrid('addFilterRule', {
        //     field: 'created_date',
        //     op: 'contains',
        //     value: "<?= date("Y-m-d") ?>"
        // });

        // $('#dg').datagrid('doFilter');

        //SAVE DATA
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_insert').form('submit', {
                        url: url_save,
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
                            // $('#dlg_insert').dialog('close');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#item_rm_id').combogrid({
            url: '<?= base_url('master/item_rm/reads') ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product",
            columns: [
                [{
                    field: 'number',
                    title: 'Product No',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Product Name',
                    width: 200
                }]
            ],
            onSelect: function(valItem, rowItem) {
                $("#uom").textbox('setValue', rowItem.uom);
            }
        });
        $("#request_no").combobox({
            url: '<?= base_url('planning/supply_sheets/readRequestNo') ?>',
            valueField: 'request_no',
            textField: 'request_no',
            prompt: "Choose Supply Sheet"
        });
    });
    function numberformat(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberStyle(value, row, index) {
        if (value < 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function statusIssued(value, row, index) {
        if (value == "OPEN") {
            return 'background-color:#C8FFCC;';
        } else {
            return 'background-color:#FFC8C8;';
        }
    }

    function issuedformat(value, row) {
        if (value == "OPEN") {
            return "<b style='color:green;'>OPEN</b>";
        } else {
            return "<b style='color:red;'>CLOSED</b>";
        }
    }
</script>