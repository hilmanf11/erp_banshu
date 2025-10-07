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
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th rowspan="2" data-options="field:'total_amount',width:120,halign:'center',align:'right',formatter:numberformatPrice2">Amount</th>
            <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Detail</th>
            <th rowspan="2" data-options="field:'print_po',width:80,halign:'center',formatter:BtnPrint">Print PO</th>
            <!-- <th rowspan="2" data-options="field:'terms',width:100,halign:'center',formatter:btnTerms">Terms<br>Condition</th> -->
            <!-- <th colspan="2" data-options="field:'scedule',width:80,halign:'center'">Schedule Delivery</th> -->
            <!-- <th rowspan="2" data-options="field:'erp',width:80,align:'center',formatter:BtnPush">Push ERP</th> -->
            <!-- <th rowspan="2" data-options="field:'push_date',width:150,align:'center'">Push Date</th> -->
            <th colspan="3" data-options="field:'Send_mail',width:100,halign:'center'">Send Mail</th>
            <!-- <th hidden colspan="2" data-options="field:'Send_mail',width:100,halign:'center'">Vendor Confirm</th> -->
            <!-- <th rowspan="2" data-options="field:'note',width:200,halign:'center'">Note</th> -->
            <th colspan="3" data-options="field:'Send_mail',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'Send_mail',width:100,halign:'center'">Created</th>
        </tr>
        <tr>
            <!-- <th data-options="field:'print_sd1',width:80,align:'center',formatter:BtnCreateSd">Create</th> -->
            <!-- <th data-options="field:'print_sd2',width:80,align:'center',formatter:BtnPrintSd">Print</th> -->
            <th data-options="field:'send',width:80,align:'center',formatter:BtnSend">Send</th>
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
                    <span style="width:35%; display:inline-block;">PO No</span>
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
                    <a href="javascript:;" id="completeMail" class="easyui-linkbutton c1" onclick="completeMail()"><i class="fa fa-check"></i> Complete Mail</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="d_product_family" class="easyui-textbox" readonly>
                </div>
                <div class="fitem" id="completeMailDate">
                    <span style="width:35%; display:inline-block;">Send Date</span>
                    <input style="width:60%;" id="d_email_date" class="easyui-datebox" value="<?= date("Y-m-d") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
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
                <th data-options="field:'price',width:100,halign:'center',align:'right',formatter:numberformatPrice">Price</th>
                <th data-options="field:'sync',width:80,align:'center', formatter:BtnSync">Sync</th>
                <th data-options="field:'total',width:100,halign:'center',align:'right',formatter:numberformat">Total</th>
                <th data-options="field:'currency',width:80,align:'center'">Currency</th>
                <th data-options="field:'rev',width:50,align:'center'">Rev</th>
                <th data-options="field:'sync_date',width:150,halign:'center'">Sync Date</th>
                <th data-options="field:'sync_by',width:100,halign:'center'">Sync By</th>
                <th data-options="field:'price_old',width:100,halign:'center',align:'right',formatter:numberformatPrice">Old Price</th>
                <th data-options="field:'reason',width:150,halign:'center'">Reason</th>
                <th data-options="field:'remarks',width:200,halign:'center'">Remarks</th>
            </tr>
        </thead>
    </table>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Update Data" data-options="closed: true,modal:true" style="width: 500px; height: 500px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Revision</span>
                <input style="width:20%;" name="rev" id="rev" readonly class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Del. Sch No</span>
                <input style="width:50%;" name="po_no" id="po_no" class="easyui-combogrid">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Supplier ID</span>
                <input style="width:50%;" name="supplier_id" id="supplier_id" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier No</span>
                <input style="width:50%;" name="supplier_no" id="supplier_no" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier Name</span>
                <input style="width:50%;" name="supplier_name" id="supplier_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:50%;" name="item_rm_number" id="item_rm_number" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part Name</span>
                <input style="width:50%;" name="item_rm_name" id="item_rm_name" readonly class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Currency</span>
                <input style="width:50%;" name="currency" id="currency" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">MRP Qty</span>
                <input style="width:30%;" name="mrp_qty" id="mrp_qty" disabled class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO Qty</span>
                <input style="width:30%;" name="qty" id="qty" required="true" class="easyui-numberbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Price</span>
                <input style="width:30%;" name="price" id="price" readonly class="easyui-numberbox" data-options="precision: 4">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Total</span>
                <input style="width:30%;" name="total" id="total" readonly class="easyui-numberbox" data-options="precision: 4">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Remarks</span>
                <input style="width:50%;" name="remarks" id="remarks" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Reason</span>
                <input style="width:50%;" name="reason" id="reason" required class="easyui-textbox">
            </div>
        </fieldset>
    </form>
</div>

<!-- Upload -->
<div id="dlg_upload" class="easyui-dialog" title="Upload Data" data-options="closed: true,modal:true" style="width: 500px; padding:10px; top: 20px;">
    <form id="frm_upload" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">File Upload</span>
                <input name="file_upload" style="width: 60%;" required="" accept=".xls" id="file_excel" class="easyui-filebox">
            </div>
        </fieldset>
    </form>
    <span style="float: left; color:green;">SUCCESS : <b id="p_success">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed">0</b></span>
    <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
    <div id="p_remarks2" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function formula(){
        Swal.fire({
            width: 800,
            html: `<div style="text-align:left;">
                        <center><b style="font-size:16px !important;">CREATE PO</b><hr></center>
                        <ul>
                            <li><b>MPS Production Plan</b> is taken from Generate MPS</li>
                        </ul>
                    </div>`,
        });
    }

    function filterMrp(){
        var mrp_month = $("#mrp_month").combobox('getValue');
        var mrp_year = $("#mrp_year").combobox('getValue');
        var mrp_revision = $("#mrp_revision").textbox('getValue');
        var mrp_plant = $("#mrp_plant").combobox('getValue');
        var mrp_profam = $("#mrp_profam").combobox('getValue');
        var mrp_item_rm_id = $("#mrp_item_rm_id").combogrid('getValue');
        var mrp_supplier = $("#mrp_supplier").combogrid('getValue');

        if(mrp_supplier != "" && mrp_revision != "" && mrp_plant != ""){
            $('#dg_mrp').datagrid({
                url: '<?= base_url('purchase/purchase_orders/datatableMrp?mrp_month=') ?>' + btoa(mrp_month) + "&mrp_year=" + btoa(mrp_year) + 
                    "&mrp_revision=" + btoa(mrp_revision) + 
                    "&mrp_plant=" + btoa(mrp_plant) + 
                    "&mrp_profam=" + btoa(mrp_profam) + 
                    "&mrp_supplier=" + btoa(mrp_supplier) + 
                    "&mrp_item_rm_id=" + btoa(mrp_item_rm_id),
                // clientPaging: false,
                // remoteFilter: true,
                rownumbers: true,
                singleSelect: false,
                fit:true,
            }).datagrid('enableFilter');
        }else{
            toastr.info("Please Select Revision & Supplier");
        }
    }

    function createPo(){
        var mrp_month = $("#mrp_month").combobox('getValue');
        var mrp_year = $("#mrp_year").combobox('getValue');
        var mrp_po_date = $("#mrp_po_date").datebox('getValue');

        var rows = $('#dg_mrp').datagrid('getSelections');
        if (rows.length > 0) {
            $("#dlg_mrp").dialog('close');

            Swal.fire({
                title: 'Convert to PO',
                text: "Are you sure? You want to convert to PO this data!",
                icon: 'warning',
                showCancelButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'Yes, Do it!'
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

                    $.ajax({
                        type: "post",
                        url: "<?= base_url("purchase/purchase_orders/purchaseOrderNo/" . $id_menu . "/add") ?>",
                        data: "p_month=" + mrp_month + "&p_year=" + mrp_year,
                        dataType: "json",
                        success: function (po_no) {
                            requestCreate(rows.length, rows);
                            function requestCreate(total, json, number = 1, value = 0) {
                                if (value < 100) {
                                    var row = json[number-1];
                                    value = Math.floor((number / total) * 100);

                                    $.ajax({
                                        method: 'post',
                                        url: '<?= base_url('purchase/purchase_orders/create') ?>',
                                        data: {
                                            po_no : po_no.code,
                                            po_date : mrp_po_date,
                                            data: row
                                        },
                                        success: function(result) {
                                            var result = eval('(' + result + ')');
                                            requestCreate(total, json, number + 1, value);

                                            if (number == total) {
                                                $('#dg').datagrid('reload');
                                                Swal.close();

                                                Swal.fire({
                                                    title: "Create Completed",
                                                    text: "Create Data has been completed, You cannot create Repeat PO",
                                                    icon: "success",
                                                    confirmButtonText: 'Ok',
                                                    allowOutsideClick: false,
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $("#dlg_mrp").dialog('open');
                                                        $('#dg_mrp').datagrid("reload");
                                                    }
                                                });
                                            }
                                        },
                                        error: function(jqXHR, textStatus, errorThrown) {
                                            toastr.error("Cannot Process your request");
                                            Swal.close();
                                            $("#dlg_mrp").dialog('open');
                                            $('#dg_mrp').datagrid("reload");
                                        },
                                    });
                                }
                            }
                        }
                    });
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }

    // function checkConfirmPo(){
    //     $.ajax({
    //         type: "post",
    //         url: '<?= base_url('purchase/purchase_orders/datatableConfirm') ?>',
    //         dataType: "json",
    //         success: function (response) {
    //             if(response.total > 0){
    //                 $("#confirmPo").show();
    //             }else{
    //                 $("#confirmPo").hide();
    //             }
    //         }
    //     });
    // }

    // checkConfirmPo();
    // setInterval(checkConfirmPo, 10000);

    // function confirmPo() {
    //     $("#dlg_confirm").dialog('open');
        
    //     $('#dg_confirm').datagrid({
    //         url: '<?= base_url('purchase/purchase_orders/datatableConfirm') ?>',
    //         clientPaging: false,
    //         remoteFilter: true,
    //         rownumbers: true,
    //         singleSelect: false,
    //         fit:true,
    //     });
    // }

    // function confirm(request_no) {
    //     $.ajax({
    //         type: "post",
    //         url: '<?= base_url('purchase/purchase_orders/updateConfirm') ?>',
    //         data: "request_no=" + request_no,
    //         dataType: "json",
    //         success: function (response) {
    //             toastr.success("Data has been Confirmed");
    //             $("#dg_confirm").datagrid("reload");
    //         }
    //     });
    // }
    
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
            $("#completeMail").hide();
            $("#sendBlink").hide();
            $("#completeMailDate").hide();
        }else if(push == "push"){
            $("#pushData").show();
            $("#detailEdit").hide();
            $("#sendMail").hide();
            $("#completeMail").hide();
            $("#sendBlink").hide();
            $("#completeMailDate").hide();
        }else{
            $("#pushData").hide();
            $("#detailEdit").hide();
            $("#sendMail").show();
            $("#completeMail").show();
            $("#sendBlink").show();
            $("#completeMailDate").show();
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
        var row = $('#dg_purchase_order').datagrid('getSelected');
        if (row) {
            if(row.status == 0){
                $('#dlg_insert').dialog('open');

                $("#po_no").combogrid({
                    url: '<?= base_url('purchase/purchase_orders/readPo/') ?>' + row.p_month + '/' + row.p_year + '/' + row.revision + '/' + row.item_family_id,
                    panelWidth: 650,
                    idField: 'po_no',
                    textField: 'po_no',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select PO No",
                    columns: [
                        [{
                            field: 'po_no',
                            title: 'PO No',
                            width: 200
                        }, {
                            field: 'supplier_name',
                            title: 'Name',
                            width: 250
                        }, {
                            field: 'item_family_name',
                            title: 'Product Family',
                            width: 150
                        }]
                    ],
                    onSelect: function(index, po){
                        $("#supplier_id").textbox("setValue", po.supplier_id);
                        $("#supplier_no").textbox("setValue", po.supplier_no);
                        $("#supplier_name").textbox("setValue", po.supplier_name);
                    }
                });

                $('#frm_insert').form('load', row);

                url_save = '<?= base_url('purchase/purchase_orders/updateDetails') ?>?id=' + btoa(row.id);
            }else{
                toastr.error("Cannot Update because the Delivery Confirmation has been Added");
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function pushData(){
        toastr.error("Still Development");
    //     var rows = $('#dg_purchase_order').datagrid('getSelections');
    //     if (rows.length > 0) {
    //         $.messager.prompt('Push Data PO', 'Please input Password to Push', function(r){
    //             if (r == "PUSHDATA"){
    //                 $('#dlg_purchase_order').dialog('close');
    //                 requestData2(rows.length, rows);

    //                 function requestData2(total, json, number = 1, value = 0, success = 1, failed = 1) {
    //                     if (value < 100) {
    //                         value = Math.floor((number / total) * 100);
    //                         $('#p_upload').progressbar('setValue', value);
    //                         $('#p_start').html(number);
    //                         $('#p_finish').html(total);

    //                         $.post('<?= base_url('purchase/purchase_orders/pushData') ?>', {
    //                             data: json[number - 1]
    //                         }, function(note) {
    //                             var result = eval('(' + note + ')');

    //                             if (result.theme == "success") {
    //                                 requestData2(total, json, number + 1, value, success + 1, failed + 0);
    //                             } else {
    //                                 requestData2(total, json, number + 1, value, success + 0, failed + 1);
    //                             }

    //                             if (value == 100) {
    //                                 Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
    //                                 $("#dg").datagrid('reload');
    //                             }
    //                         }).fail(function(jqXHR, textStatus) {
    //                             if (textStatus == "error") {
    //                                 toastr.error("Connection time out");

    //                                 requestData2(total, json, number, value, success + 0, failed + 1);
    //                             }
    //                         });
    //                     }
    //                 }
    //             }
    //         });

    //         $('.messager-input').attr('type', 'password');
    //     }else{
    //         toastr.warning("Please select one of the data in the table first!");
    //     }
    }

    function sendMail(){
        toastr.error("Not Available");
        // $.ajax({
        //     type: "post",
        //     url: "<?= base_url("purchase/config_mail/reads/purchase_orders/") ?>" + val,
        //     dataType: "json",
        //     success: function (response) {
        //         if(response == null){
        //             toastr.info("Not Available format, please input in master Setting Mail");
        //             $("#mail_message").html("");
        //         }else{
        //             $("#mail_message").html(response.description);
        //         }
        //     }
        // });
    }

    function completeMail(){
        $.messager.prompt('Complete Mail', 'Please input Password to Complete Mail PO', function(r){
            if (r == "COMPLETEMAIL"){
                var purchase_order_no = $("#d_po_no").textbox('getValue');
                var email_date = $("#d_email_date").datebox('getValue');

                $.ajax({
                    type: "post",
                    url: "<?= base_url("purchase/purchase_orders/completeMail") ?>",
                    data: "po_no=" + purchase_order_no + "&email_date=" + email_date,
                    dataType: "json",
                    success: function (response) {
                        toastr.success(response.message);
                        $("#dlg_purchase_order").dialog('close');
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
                            toastr.warning("Please Generate Purchase Request & Purchase Order First");
                        }
                    }
                });
            }
        });
    }

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
                                url: '<?= base_url('purchase/purchase_orders/delete') ?>',
                                data: {
                                    po_no: row.po_no
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
                                    toastr.error("Delete Failed, Please Check Data Relations");
                                    swal.close();
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

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_purchase_orders.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    function updateNote(newData, old){
        var po_no = $("#d_po_no").textbox('getValue');

        if(old != "" && old != null && old != "null"){
            $.ajax({
                type: "post",
                url: "<?= base_url('purchase/purchase_orders/updateNote') ?>",
                data: {po_no: po_no, note: newData},
                dataType: "json",
                success: function (result) {
                    if (result.theme == "success") {
                        toastr.success(result.message, result.title);
                    } else {
                        toastr.error(result.message, result.title);
                    }
                    $("#dg").datagrid('reload');
                }
            });
        }
    }

    function syncPrice(id, price, price_old){
        $.messager.prompt('Sync Price to '+price, 'Please input Password for Sync Price', function(r){
            if (r == "PROCUREMENTUMI2025"){
                $.ajax({
                    type: "post",
                    url: "<?= base_url('purchase/purchase_orders/updatePrice') ?>",
                    data: {id: id, price: price, price_old: price_old},
                    dataType: "json",
                    success: function (result) {
                        if (result.theme == "success") {
                            toastr.success(result.message, result.title);
                        } else {
                            toastr.error(result.message, result.title);
                        }
                        $("#dg_purchase_order").datagrid('reload');
                    }
                });
            }
        });
    }

    function statusChange(id, sts){
        $.messager.prompt('Password!!', 'Change Status Purchase Orders', function(r){
            if (r == "CHANGE"){
                $.ajax({
                    type: "post",
                    url: "<?= base_url('purchase/purchase_orders/updateStatus') ?>",
                    data: {id: id, status: sts},
                    dataType: "json",
                    success: function (result) {
                        if (result.theme == "success") {
                            toastr.success(result.message, result.title);
                        } else {
                            toastr.error(result.message, result.title);
                        }

                        $("#dg_purchase_order").datagrid('reload');
                    }
                });
            }
        });
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

        $("#qty").numberbox({
            onChange: function(qty){
                var price = $("#price").numberbox("getValue");
                $("#total").numberbox("setValue", (parseInt(qty) * parseFloat(price)));
            }
        });

        $("#price").numberbox({
            onChange: function(price){
                var qty = $("#qty").numberbox("getValue");
                $("#total").numberbox("setValue", (parseInt(qty) * parseFloat(price)));
            }
        })

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
                            $('#dg_purchase_order').datagrid('reload');
                            Swal.close();
                        }
                    });
                }
            }]
        });

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

        // UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('purchase/item_fg/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('purchase/purchase_orders/upload') ?>',
                        onSubmit: function() {
                            if ($(this).form('validate') == false) {
                                return $(this).form('validate');
                            } else {
                                $.messager.progress({
                                    title: 'Please Wait',
                                    msg: 'Importing Excel to Database'
                                });
                            }
                        },
                        success: function(result) {
                            $.messager.progress('close');
                            // $("#dlg_upload").dialog('close');
                            //Clear File
                            $.ajax({
                                url: "<?= base_url('purchase/purchase_orders/uploadclearFailed') ?>"
                            });
                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload2').progressbar('setValue', value);
                                    $('#p_start2').html(number);
                                    $('#p_finish2').html(total);

                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('purchase/purchase_orders/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                $('#p_success2').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed2').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                //Json Failed
                                                $.ajax({
                                                    type: "POST",
                                                    async: true,
                                                    url: "<?= base_url('purchase/purchase_orders/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });
                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }
                                            $("#p_remarks2").append(title + "<br>");
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
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

    function BtnConfirm(val, row) {
        var confirm = "confirm('"+row.request_no+"')";
        return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+confirm+'"><i class="fa fa-check"></i> Confirm</a>';
    }

    function BtnPrint(val, row) {
        // if(row.approved_to == "" || row.approved_to == null){
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('purchase/purchase_orders/print_po/') ?>'+btoa(row.po_no)+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
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
            return "-";
        } else if (row.approved_to == null || row.approved_to == "") {
            var details = "details('"+row.po_no+"','"+row.supplier_name+"','"+row.note+"','"+row.item_family_name+"','mail','"+row.email+"')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-send"></i> Send</a>';
        } else {
            return '-';
        }
    }

    function BtnSync(val, row) {
        if(val == "SYNC"){
            var details = "syncPrice('"+row.id+"','"+row.new_price+"','"+row.price+"')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-money"></i> Sync</a>';
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