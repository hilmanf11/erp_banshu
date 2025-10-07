<!-- <style>
    @keyframes blink {
        0%, 50%, 100% {
            opacity: 1;
        }
        25%, 75% {
            opacity: 0;
        }
    }

    .blink {
        animation: blink 2s infinite;
        font-size: 20px !important;
        color: red;
        font-weight: bold;
    }
</style> -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
    <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStylerStatus, formatter:cellFormatterStatus">Status</th>
            <th rowspan="2" data-options="field:'plant',width:80,halign:'center'">Plant</th>
            <th rowspan="2" data-options="field:'supplier_id',width:100,halign:'center'">Supplier ID</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'supplier_type',width:80,halign:'center'">Type</th>
            <th rowspan="2" data-options="field:'item_family_name',width:120,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'request_no',width:120,halign:'center'">PR No</th>
            <th rowspan="2" data-options="field:'po_no',width:150,halign:'center'">PO No</th>
            <th rowspan="2" data-options="field:'po_date',width:100,halign:'center'">PO Date</th>
            <th rowspan="2" data-options="field:'total_qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty</th>
            <!-- <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th> -->
            <!-- <th rowspan="2" data-options="field:'total_amount',width:120,halign:'center',align:'right',formatter:numberformatPrice2">Amount</th> -->
            <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Detail</th>
            <th rowspan="2" data-options="field:'print_po',width:80,halign:'center',formatter:BtnPrint">Print PO</th>
            <!-- <th rowspan="2" data-options="field:'terms',width:100,halign:'center',formatter:btnTerms">Terms<br>Condition</th> -->
            <th colspan="2" data-options="field:'scedule',width:80,halign:'center'">Schedule Delivery Import</th>
            <th rowspan="2" data-options="field:'erp',width:80,align:'center',formatter:BtnPush">Push ERP</th>
            <th rowspan="2" data-options="field:'push_date',width:150,align:'center'">Push Date</th>
            <th colspan="2" data-options="field:'Send_mail',width:100,halign:'center'">Send Mail</th>
            <!-- <th hidden colspan="2" data-options="field:'Send_mail',width:100,halign:'center'">Vendor Confirm</th> -->
            <!-- <th rowspan="2" data-options="field:'note',width:200,halign:'center'">Note</th> -->
            <th colspan="3" data-options="field:'Send_mail',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'Send_mail',width:100,halign:'center'">Created</th>
        </tr>
        <tr>
            <th data-options="field:'print_sd1',width:100,align:'center',formatter:BtnCreateSd">Create</th>
            <th data-options="field:'print_sd2',width:100,align:'center',formatter:BtnPrintSd">Print</th>
            <!-- <th data-options="field:'send',width:100,align:'center',formatter:BtnSend">Send</th> -->
            <th data-options="field:'email_status',width:80,align:'center', styler:cellStylerSend, formatter:cellFormatterSend">Status</th>
            <th data-options="field:'email_date',width:130,align:'center'">Date</th>
            <!-- <th hidden data-options="field:'confirm',width:100,align:'center', styler:cellStylerConfirm, formatter:cellFormatterConfirm">Status</th>
            <th hidden data-options="field:'confirm_date',width:120,align:'center'">Date</th> -->
            <th data-options="field:'status_approved',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:120,align:'center'">By</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
            <th data-options="field:'created_by',width:80,align:'center'">By</th>
            <th data-options="field:'created_date',width:120,align:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 230px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Filter Data</b></legend>
            <div style="width: 35%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period Cutoff</span>
                    <input style="width:30%;" name="filter_month" value="<?= date("m") ?>" id="filter_month" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" value="<?= date("Y") ?>" id="filter_year" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:60%;" id="filter_revision" class="easyui-combobox" panelHeight="auto">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" id="filter_supplier" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 35%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">PO No</span>
                    <input style="width:60%;" id="filter_po" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_item_family" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_rm" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Type</span>
                    <select style="width:60%;" id="filter_type" class="easyui-combobox" panelHeight="auto">
                        <option value="">ALL</option>
                        <option value="LOCAL">LOCAL</option>
                        <option value="IMPORT">IMPORT</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status PO</span>
                    <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                        <option value="">ALL</option>
                        <option value="0">OPEN</option>
                        <option value="1">CLOSE</option>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset style="width: 20%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Generating Process</b></legend>

            <b>Push Data</b>
            <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
        </fieldset>
    </div>
    <?= $button ?>
    <!-- <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="formula()"><i class="fa fa-info"></i> Help</a> -->
</div>

<div id="toolbar2" style="height: auto; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">        
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Detail</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Del. Sch No</span>
                    <input style="width:60%;" id="d_po_no" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" id="d_supplier" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" id="detailEdit" class="easyui-linkbutton" onclick="detailEdit()"><i class="fa fa-edit"></i> Edit Data</a>
                    <a href="javascript:;" id="pushData" class="easyui-linkbutton c5" onclick="pushData()"><i class="fa fa-database"></i> Push Data</a>
                    <a href="javascript:;" id="sendMail" class="easyui-linkbutton" onclick="sendMail()"><i class="fa fa-send"></i> Send Mail</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="d_product_family" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Note</span>
                    <input style="width:60%;" id="d_note" class="easyui-textbox">
                </div>
            </div>

            <div style="width: 100%; float: left;">
                <div class="alert alert-danger" role="alert" id="sendBlink">
                    <small>Please Recheck before send Mail to</small> <b id="sendMailText"></b>
                </div>
            </div>
        </fieldset>
    </div>
</div>

<div id="dlg_purchase_order" class="easyui-dialog" title="Detail Purchase Order" data-options="closed: true,modal:true,fit:true" style="width: 100%; top: 0px; left: 0px;">
    <table id="dg_purchase_order" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar2" showFooter="true">
        <thead>
            <tr>
                <th field="ck" checkbox="true"></th>
                <!-- <th data-options="field:'status_is', width:100,halign:'center',formatter:BtnStatus">Status</th> -->
                <th data-options="field:'status',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Approval</th>
                <!-- <th data-options="field:'status',width:100,align:'center', styler:cellStylerDelivery, formatter:cellFormatterDelivery">Delivery<br>Confirmation</th> -->
                <th data-options="field:'item_rm_id',width:100,halign:'center'">Part No<br>Original</th>
                <th data-options="field:'item_rm_number',width:200,halign:'center'">Part No BEI</th>
                <th data-options="field:'item_supplier',width:200,halign:'center'">Part No Supplier</th>
                <th data-options="field:'item_rm_name',width:200,halign:'center'">Description</th>
                <!-- <th data-options="field:'product_family',width:120,halign:'center'">Product Family</th> -->
                <th data-options="field:'uom',width:80,align:'center'">Uom</th>
                <th data-options="field:'mpq',width:80,halign:'center'">MPQ</th>
                <th data-options="field:'moq',width:80,halign:'center'">MOQ</th>
                <th data-options="field:'leadtime',width:80,halign:'center'">Leadtime</th>
                <th data-options="field:'qty',width:80,halign:'center',align:'right',formatter:numberformat">Qty</th>
                <!-- <th data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformatPrice">Price</th> -->
                <!-- <th data-options="field:'sync',width:80,align:'center', formatter:BtnSync">Sync</th> -->
                <!-- <th data-options="field:'total',width:100,halign:'center',align:'right',formatter:numberformat">Total</th> -->
                <!-- <th data-options="field:'currency',width:80,align:'center'">Currency</th> -->
                <th data-options="field:'rev',width:50,align:'center'">Rev</th>
                <!-- <th data-options="field:'sync_date',width:150,halign:'center'">Sync Date</th> -->
                <!-- <th data-options="field:'sync_by',width:100,halign:'center'">Sync By</th> -->
                <!-- <th data-options="field:'price_old',width:100,halign:'center',align:'right',formatter:numberformatPrice">Old Price</th> -->
                <th data-options="field:'reason',width:150,halign:'center'">Reason</th>
                <th data-options="field:'remarks',width:200,halign:'center'">Remarks</th>
            </tr>
        </thead>
    </table>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function details(po_no, supplier, note, product_family, push="", email = "", email_cc = "") {
        $("#dlg_purchase_order").dialog('open');

        $("#d_po_no").textbox('setValue', po_no);
        $("#d_supplier").textbox('setValue', supplier);
        $("#d_product_family").textbox('setValue', product_family);
        $("#d_note").textbox('setValue', note);
        
        if(push == ""){
            $("#pushData").hide();
            $("#detailEdit").show();
            $("#sendMail").hide();
            $("#sendBlink").hide();
        }else if(push == "push"){
            $("#pushData").show();
            $("#detailEdit").hide();
            $("#sendMail").hide();
            $("#sendBlink").hide();
        }else{
            $("#pushData").hide();
            $("#detailEdit").hide();
            $("#sendMail").show();
            $("#sendBlink").show();
            $("#sendMailText").html(email);
            $("#mail_to").textbox("setValue", email);
            $("#mail_cc").textbox("setValue", email_cc);
            $("#mail_po").textbox("setValue", po_no);
            $("#mail_subject").textbox("setValue", "PO#" + po_no + "_" + supplier);
        }

        var revision = $("#filter_revision").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        
        $('#dg_purchase_order').datagrid({
            url: '<?= base_url('purchase/purchase_orders/datatableDetails/') ?>' + btoa(po_no) + "/" + revision + "?filter_item_rm=" + btoa(filter_item_rm),
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: false,
            fit:true,
        });
    }

    function detailEdit(){
        toastr.error("Cannot Edit Data");
    }

    function pushData(){
        // toastr.error("Still Development");
        var rows = $('#dg_purchase_order').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.prompt('Push Data PO', 'Please input Password to Push', function(r){
                if (r == "PUSHDATA"){
                    $('#dlg_purchase_order').dialog('close');
                    requestData2(rows.length, rows);

                    function requestData2(total, json, number = 1, value = 0, success = 1, failed = 1) {
                        if (value < 100) {
                            value = Math.floor((number / total) * 100);
                            $('#p_upload').progressbar('setValue', value);
                            $('#p_start').html(number);
                            $('#p_finish').html(total);

                            $.post('<?= base_url('purchase/purchase_orders/pushData') ?>', {
                                data: json[number - 1]
                            }, function(note) {
                                var result = eval('(' + note + ')');

                                if (result.theme == "success") {
                                    requestData2(total, json, number + 1, value, success + 1, failed + 0);
                                } else {
                                    requestData2(total, json, number + 1, value, success + 0, failed + 1);
                                }

                                if (value == 100) {
                                    Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                    $("#dg").datagrid('reload');
                                }
                            }).fail(function(jqXHR, textStatus) {
                                if (textStatus == "error") {
                                    toastr.error("Connection time out");

                                    requestData2(total, json, number, value, success + 0, failed + 1);
                                }
                            });
                        }
                    }
                }
            });

            $('.messager-input').attr('type', 'password');
        }else{
            toastr.warning("Please select one of the data in the table first!");
        }
    }

    function sendMail(po_no, status){
        $.messager.prompt('Update Send Mail', 'Please input "SENDMAIL" for Update Status', function(r){
            if (r == "SENDMAIL"){
                Swal.fire({
                    title: 'Please Wait for Create Data',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "post",
                    url: "<?= base_url("purchase/purchase_orders/sendMail") ?>",
                    data: "po_no=" + po_no + "&status=" + status, 
                    dataType: "json",
                    success: function (response) {
                        Swal.close();
                        Swal.fire('Good job!', 'Update Status Send Mail Completed!', 'success');
                        $("#dg").datagrid('reload');
                    }
                });
            }
        });
    }

    function createsd(po_no){
        $.messager.prompt('Create Delivery Schedule Import', 'Please input "DELIVERYIMP" for Generate, Be careful if data is generated for this period it will be deleted', function(r){
            if (r == "DELIVERYIMP"){
                Swal.fire({
                    title: 'Please Wait for Create Data',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "get",
                    url: "<?= base_url('purchase/delivery_schedules/getData') ?>",
                    data: "&po_no=" + window.btoa(po_no),
                    dataType: "json",
                    success: function(rows) {
                        if(rows['total'] > 0){
                            requestData(rows['total'], rows);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.post('<?= base_url('purchase/delivery_schedules/create') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');

                                        if (result.theme == "success") {
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }

                                        if (value == 100) {
                                            Swal.close();
                                            Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                            $("#dg").datagrid('reload');
                                        }
                                    }).fail(function(jqXHR, textStatus) {
                                        if (textStatus == "error") {
                                            Swal.close();
                                            toastr.error("Connection time out");

                                            requestData(total, json, number, value, success + 0, failed + 1);
                                        }
                                    });
                                }
                            }
                        }else{
                            Swal.close();
                            toastr.warning("Data not found, only IMPORT type maker can be created");
                        }
                    }
                });
            }
        });
    }

    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_po = $("#filter_po").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_po=" + window.btoa(filter_po) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_type=" + window.btoa(filter_type) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_item_rm=" + window.btoa(filter_item_rm);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('purchase/purchase_orders/datatables') ?>' + url,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('purchase/purchase_orders/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_revision = $("#filter_revision").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_po = $("#filter_po").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_supplier = $("#filter_supplier").combogrid('getValue');
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_revision=" + window.btoa(filter_revision) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_po=" + window.btoa(filter_po) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_type=" + window.btoa(filter_type) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_item_rm=" + window.btoa(filter_item_rm);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            window.location.assign('<?= base_url('purchase/purchase_orders/print/excel') ?>' + url);
        }
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: true,
            fit:true,
            rowStyler: function(index,row){
                if (row.color == ""){
                    return 'background-color:#FFD2D2;';
                }
            }
        });

        filter();
        
        $('#filter_month').combobox({
            url: '<?php echo base_url('planning/mst_data/readMonths'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Month',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_revision').combobox({
            url: '<?php echo base_url('planning/mst_data/readRevisions'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Revision',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_plant').combobox({
            url: '<?php echo base_url('master/plants/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Plant',
        });

        $('#filter_item_family').combobox({
            url: '<?php echo base_url('master/item_familys/readss/C01'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Product Family',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row){
                $('#filter_item_rm').combogrid({
                    url: '<?= base_url('master/item_rm/reads/') ?>' + row.id,
                    panelWidth: 550,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Part No",
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                        }
                    }],
                    columns: [
                        [{
                            field: 'id',
                            title: 'Part No Original',
                            width: 120
                        }, {
                            field: 'number',
                            title: 'Part No Supplier',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Description',
                            width: 200
                        }]
                    ]
                });
            }
        });

        $('#filter_item_rm').combogrid({
            url: '<?= base_url('master/item_rm/reads') ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'id',
                    title: 'Part No Original',
                    width: 120
                }, {
                    field: 'number',
                    title: 'Part No Supplier',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 200
                }]
            ]
        });

        $('#filter_supplier').combogrid({
            url: '<?= base_url('master/suppliers/reads') ?>',
            panelWidth: 400,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Supplier",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'id',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 250
                }]
            ],
            onSelect: function(val, row){
                var filter_month = $("#filter_month").combobox('getValue');
                var filter_year = $("#filter_year").combobox('getValue');
                var filter_revision = $("#filter_revision").combobox('getValue');

                $('#filter_po').combobox({
                    url: '<?php echo base_url('purchase/purchase_orders/readPoSearch?filter_month='); ?>' + window.btoa(filter_month) + '&filter_year=' + btoa(filter_year) + '&filter_revision=' + btoa(filter_revision) + "&filter_supplier=" + btoa(row.id),
                    valueField: 'po_no',
                    textField: 'po_no',
                    prompt: 'Select PO No',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });
    });

    //Format Datepicker
    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    //Format Datepicker
    function myparser(s) {
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0], 10);
        var m = parseInt(ss[1], 10);
        var d = parseInt(ss[2], 10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
            return new Date(y, m - 1, d);
        } else {
            return new Date();
        }
    }

    function cellStyler(value, row, index) {
        if (value == "OPEN") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 2
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatPrice(value, row){
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 4
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatPrice2(value, row){
        if(row.supplier_type == "LOCAL"){
            var decimal = 0;
        }else{
            var decimal = 2;
        }
        
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: decimal
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function BtnDetail(val, row) {
        var details = "details('"+row.po_no+"','"+row.supplier_name+"','"+row.note+"','"+row.item_family_name+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function BtnPrint(val, row) {
        // if(row.approved_to == "" || row.approved_to == null){
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('purchase/purchase_order_mcl/print_po/') ?>'+btoa(row.po_no)+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
        // }else{
        //     return "-";
        // }
    }

    function BtnCreateSd(val, row) {
        if(row.print_sd == 0){
            var confirm = "createsd('"+row.po_no+"')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+confirm+'"><i class="fa fa-plus"></i> Create</a>';
        } else {
            return '-';
        }
    }

    function BtnPrintSd(val, row) {
        if(row.print_sd > 0){
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('purchase/purchase_orders/print_delivery/') ?>'+btoa(row.po_no)+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
        } else {
            return '-';
        }
    }
    
    function BtnPush(val, row) {
        if(row.push == "OK") {
            return 'DONE';
        // } else if ((row.approved_to == null || row.approved_to == "") && row.email_status == "SEND") {
        } else if (row.approved_to == null || row.approved_to == "") {
            var details = "details('"+row.po_no+"','"+row.supplier_name+"','"+row.note+"','"+row.item_family_name+"','push')";
            return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-share"></i> Push</a>';
        } else {
            return '-';
        }
    }

    function BtnSend(val, row) {
        if(row.email_status == "SENT"){
            var details = "sendMail('"+row.po_no+"','PENDING')";
            return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-times"></i> Cancel</a>';
        } else if (row.approved_to == null || row.approved_to == "") {
            // var details = "sendMail('"+row.po_no+"','"+row.supplier_name+"','"+row.note+"','"+row.item_family_name+"','mail','"+row.email+"')";
            var details = "sendMail('"+row.po_no+"','SENT')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-send"></i> Send</a>';
        } else {
            return '-';
        }
    }

    function BtnStatus(val, row) {
        if(row.status == 0){
            var details = "statusChange('"+row.id+"','"+row.status+"')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'">OPEN</a>';
            // return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;">OPEN</a>';
        } else if(row.complete == 1) {
            var details = "statusChange('"+row.id+"','"+row.status+"')";
            return '<a class="btn btn-warning w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'">COMPLETE</a>';
            // return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;">CLOSE</a>';
        } else if(row.status == 1) {
            var details = "statusChange('"+row.id+"','"+row.status+"')";
            return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'">CLOSE</a>';
            // return '<a class="btn btn-danger w-100" style="pointer-events: visible; opacity:1;">CLOSE</a>';
        }
    }

    function cellStylerApproval(value, row, index) {
        if (row.approved_to == null) {
            return 'background: #FF5F5F; color:white;';
        } else if(row.approved_to == ""){
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (row.approved_to == null) {
            return 'Not Setting';
        } else if(row.approved_to == ""){
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    function cellStylerSend(value, row, index) {
        if (value == "PENDING") {
            return 'background: #FF5F5F; color:white;';
        } else {
            return 'background: #53D636; color:white;';
        }
    }

    function cellFormatterSend(value, row) {
        if (value == "PENDING") {
            return 'Pending';
        } else {
            return 'Delivered';
        }
    };
    
    function cellStylerStatus(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterStatus(value, row) {
        if (value == 0) {
            return 'OPEN';
        } else {
            return 'CLOSE';
        }
    };

    function cellStylerDelivery(value, row, index) {
        if (value == 0) {
            return 'background: #ffb26e; color:white;';
        } else if(value == 1) {
            return 'background: #53D636; color:white;';
        } else {
            return '-';
        }
    }

    function cellFormatterDelivery(value, row) {
        if (value == 0) {
            return 'Pending';
        } else if(value == 1) {
            return 'Delivered';
        } else {
            return '-';
        }
    };

    function cellStylerConfirm(value, row, index) {
        if (value == null) {
            return 'background: #FF5F5F; color:white;';
        } else if(value == "Pending"){
            return 'background: #AB6F2B; color:white;';
        }else{
            return 'background: #53D636; color:white;';
        }
    }

    function cellFormatterConfirm(value, row) {
        if (value == null) {
            return 'Not Confirm';
        } else if(value == "Pending"){
            return 'Pending';
        } else {
            return 'Confirm';
        }
    };
</script>