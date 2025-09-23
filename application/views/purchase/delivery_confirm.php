<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'status',width:80,align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
            <th rowspan="2" data-options="field:'receipt_no',width:150,halign:'center'">POR No</th>
            <th rowspan="2" data-options="field:'plant',width:80,halign:'center'">Plant</th>
            <th rowspan="2" data-options="field:'supplier_id',width:100,halign:'center'">Supplier ID</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'delivery_no',width:150,halign:'center'">Delivery No</th>
            <th rowspan="2" data-options="field:'invoice_no',width:150,halign:'center'">Invoice No</th>
            <th rowspan="2" data-options="field:'invoice_date',width:100,halign:'center'">Invoice Date</th>
            <th rowspan="2" data-options="field:'shipping',width:120,halign:'center'">Shipping</th>
            <!-- <th rowspan="2" data-options="field:'po_no',width:120,halign:'center'">PO No</th> -->
            <!-- <th rowspan="2" data-options="field:'po_date',width:100,halign:'center'">PO Date</th> -->
            <!-- <th rowspan="2" data-options="field:'item_rm_id',width:100,halign:'center'">Part No Original</th> -->
            <!-- <th rowspan="2" data-options="field:'item_rm_number',width:200,halign:'center'">Part No Supplier</th> -->
            <!-- <th rowspan="2" data-options="field:'item_rm_name',width:200,halign:'center'">Description</th> -->
            <!-- <th rowspan="2" data-options="field:'item_family_name',width:100,halign:'center'">Product<br>Family</th> -->
            <!-- <th rowspan="2" data-options="field:'uom',width:80,halign:'center'">UoM</th> -->
            <!-- <th rowspan="2" data-options="field:'qty',width:100,halign:'center',align:'right',formatter:numberformat">Qty PO</th> -->
            <!-- <th rowspan="2" data-options="field:'delivery',width:100,halign:'center',align:'right',formatter:numberformat">Plan Delivery</th> -->
            <!-- <th rowspan="2" data-options="field:'receipt',width:100,halign:'center',align:'right',formatter:numberformat">Receipt</th> -->
            <th rowspan="2" data-options="field:'etd',width:100,align:'center'">ETD</th>
            <th rowspan="2" data-options="field:'eta',width:100,align:'center'">ETA</th>
            <th rowspan="2" data-options="field:'attachment',width:100,halign:'center', formatter:btnAttachment">Attahment</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,halign:'center'">By</th>
            <th data-options="field:'created_date',width:150,align:'center'">Date</th>
            <th data-options="field:'updated_by',width:100,halign:'center'">By</th>
            <th data-options="field:'updated_date',width:150,align:'center'">Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<div id="toolbar" style="height: 230px; padding: 10px;">
    <fieldset style="width:100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        
    <legend><b>Filter Data</b></legend>
        <div style="width:35%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Create Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Plant</span>
                <input style="width:60%;" id="filter_plant" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 30%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Invoice No</span>
                <input style="width:60%;" id="filter_invoice_no" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">PO No</span>
                <input style="width:60%;" id="filter_po_no" class="easyui-combobox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">PO Date</span>
                <input style="width:60%;" id="filter_po_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">PO Status</span>
                <select style="width:30%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                    <option value="">All</option>
                    <option value="0">Open</option>
                    <option value="1">Close</option>
                </select>
            </div>
        </div>
        <div style="width: 30%;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_product_family" class="easyui-combobox" panelHeight="auto">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product No</span>
                <input style="width:60%;" id="filter_item_rm_id" class="easyui-combogrid">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 98%; height: 550px; padding:10px; top: 10px; left: 10px;">
    <form id="frm_insert" method="post" enctype="multipart/form-data" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="width: 25%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="plant" id="plant" required class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier name</span>
                    <input style="width:60%;" name="supplier_id" id="supplier_id" required class="easyui-combobox">
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="attachment" id="attachment" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Attachment</span>
                    <input style="width:60%;" name="u_attachment" id="u_attachment" class="easyui-filebox" data-options="prompt:'Choose Attachment', accept:'.png, .jpg, .jpeg, .pdf'">
                </div>
            </div>
            <div style="width: 25%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Delivery Note</span>
                    <input style="width:60%;" name="delivery_no" id="delivery_no" required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice No</span>
                    <input style="width:60%;" name="invoice_no" id="invoice_no" required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Invoice Date</span>
                    <input style="width:60%;" name="invoice_date" id="invoice_date" required="" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
            <div style="width: 25%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Shipping</span>
                    <input style="width:60%;" name="shipping" id="shipping" required class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">ETD</span>
                    <input style="width:60%;" name="etd" id="etd" required readonly class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">ETA</span>
                    <input style="width:60%;" name="eta" id="eta" required readonly class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
            <div style="width: 25%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">AWB No</span>
                    <input style="width:60%;" name="awb_no" id="awb_no" class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">AWB Date</span>
                    <input style="width:60%;" name="awb_date" id="awb_date" class="easyui-datebox" data-options="formatter:myformatter,parser:myparser, editable:false">
                </div>
            </div>
        </fieldset>
        <table id="dg2" class="easyui-datagrid" style="width:100%;" title="Delivery Confirmation Lists" toolbar="#toolbar2"></table>
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
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        $('#frm_insert').form('clear');
        $('#dg2').datagrid('loadData', []);
        $("#plant").combobox('enable');
        $("#supplier_id").combogrid('enable');
        $("#delivery_no").textbox('enable');
        $("#invoice_no").textbox('enable');
        $("#awb_date").datebox("setValue", "<?= date("Y-m-d") ?>");
        process = "ADD";
        if($("#awb_date").datebox("getValue") == ""){
            add();
        }
        // $("#invoice_no").textbox('enable');
        // $("#invoice_date").datebox('enable');
    }

    function addTable(supplier_id, link = "") {
        $('#dg2').datagrid({
            url: link,
            singleSelect: true,
            columns: [
                [{
                    field: 'po_no',
                    width: 200,
                    halign: 'center',
                    title: "PO No",
                    editor: {
                        type: 'combobox',
                        options: {
                            url: '<?= base_url('purchase/purchase_orders/readPono?supplier_id='); ?>' + btoa(supplier_id),
                            valueField: 'po_no',
                            textField: 'po_no',
                            prompt: 'Choose Purchase Order',
                            required: true,
                            onSelect: function (po) {
                                var dg = $('#dg2');
                                var row = dg.datagrid('getSelected');
                                var rowIndex = dg.datagrid('getRowIndex', row);

                                var ed = dg.datagrid('getEditor', {index: rowIndex, field: 'item_rm_id'});
                                var ed2 = dg.datagrid('getEditor', {index: rowIndex, field: 'item_rm_number'});
                                var ed3 = dg.datagrid('getEditor', {index: rowIndex, field: 'item_rm_name'});
                                var ed4 = dg.datagrid('getEditor', {index: rowIndex, field: 'po_date'});
                                var ed5 = dg.datagrid('getEditor', {index: rowIndex, field: 'uom'});
                                // var ed6 = dg.datagrid('getEditor', {index: rowIndex, field: 'eta'});
                                // var ed7 = dg.datagrid('getEditor', {index: rowIndex, field: 'etd'});
                                var ed8 = dg.datagrid('getEditor', {index: rowIndex, field: 'qty'});
                                var ed9 = dg.datagrid('getEditor', {index: rowIndex, field: 'delivery'});
                                var ed10 = dg.datagrid('getEditor', {index: rowIndex, field: 'actual'});
                                var ed11 = dg.datagrid('getEditor', {index: rowIndex, field: 'balance'});
                                var ed12 = dg.datagrid('getEditor', {index: rowIndex, field: 'qty_po'});
                                var ed13 = dg.datagrid('getEditor', {index: rowIndex, field: 'currency'});
                                var ed14 = dg.datagrid('getEditor', {index: rowIndex, field: 'price'});
                                var ed15 = dg.datagrid('getEditor', {index: rowIndex, field: 'total'});

                                $(ed4.target).textbox('setValue', po.po_date);
                                $(ed.target).combogrid({
                                    url: '<?= base_url('purchase/delivery_confirm/readProducts/'); ?>' + btoa(po.po_no),
                                    required: true,
                                    panelWidth: 600,
                                    idField: 'id',
                                    textField: 'number',
                                    mode: 'remote',
                                    fitColumns: true,
                                    prompt: 'Choose Part No Original',
                                    columns: [
                                        [{
                                            field: 'id',
                                            title: 'Part No Original',
                                            width: 150
                                        },{
                                            field: 'number',
                                            title: 'Part No Supplier',
                                            width: 150
                                        }, {
                                            field: 'name',
                                            title: 'Description',
                                            width: 200
                                        }, {
                                            field: 'qty',
                                            title: 'Remain',
                                            width: 80
                                        }]
                                    ],
                                    onSelect: function (value, item) {
                                        $(ed2.target).textbox('setValue', item.number);
                                        $(ed3.target).textbox('setValue', item.name);
                                        $(ed5.target).textbox('setValue', item.uom);
                                        // $(ed6.target).textbox('setValue', item.delivery_date);
                                        // $(ed7.target).datebox('setValue', item.today);
                                        $(ed8.target).numberbox('setValue', item.qty);
                                        $(ed9.target).numberbox('setValue', item.delivery);
                                        $(ed10.target).numberbox('setValue', item.actual);
                                        $(ed11.target).numberbox('setValue', (parseInt(item.qty) - parseInt(item.delivery)));
                                        $(ed12.target).numberbox('setValue', item.qty_po);
                                        $(ed13.target).textbox('setValue', item.currency);
                                        $(ed14.target).numberbox('setValue', item.price);
                                        $(ed15.target).numberbox('setValue', (parseFloat(item.qty) * parseFloat(item.price)));
                                        $(ed9.target).numberbox({
                                            onChange: function(val){
                                                if((parseInt(item.qty) - val) <= 0){
                                                    toastr.info("You cannot exceed qty and delivery");
                                                    $(ed11.target).numberbox('setValue', 0);
                                                    $(ed9.target).numberbox('setValue', 0);
                                                }else{
                                                    $(ed11.target).numberbox('setValue', (parseInt(item.qty) - val));
                                                    $(ed15.target).numberbox('setValue', (parseFloat(val) * parseFloat(item.price)));
                                                }
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    }
                }, {
                    field: 'po_date',
                    width: 100,
                    halign: 'center',
                    title: "PO Date",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                },{
                    field: 'item_rm_id',
                    width: 200,
                    halign: 'center',
                    title: "Part No",
                    editor: {
                        type: 'combogrid',
                    }
                }, {
                    field: 'item_rm_number',
                    width: 200,
                    halign: 'center',
                    // hidden: true,
                    title: "Part No Supplier",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'item_rm_name',
                    width: 200,
                    halign: 'center',
                    title: "Description",
                    hidden: true,
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'uom',
                    width: 80,
                    halign: 'center',
                    title: "Uom",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true
                        }
                    }
                }, {
                    field: 'qty_po',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                    title: "PO",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            readonly: true,
                        }
                    }
                }, {
                    field: 'actual',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                    title: "Receipt",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            readonly: true,
                        }
                    }
                }, {
                    field: 'qty',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                    title: "Remain",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            readonly: true,
                        }
                    }
                },{
                    field: 'delivery',
                    width: 100,
                    halign: 'center',
                    align: 'right',
                    title: "Invoice Qty",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                        }
                    }
                },{
                    field: 'balance',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                    title: "Balance",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 0,
                            readonly: true,
                        }
                    }
                },{
                    field: 'currency',
                    width: 80,
                    align: 'center',
                    title: "Currency",
                    editor: {
                        type: 'textbox',
                        options: {
                            readonly: true,
                        }
                    }
                },{
                    field: 'price',
                    width: 100,
                    halign: 'center',
                    align: 'right',
                    title: "Price",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 4
                        }
                    }
                },{
                    field: 'total',
                    width: 100,
                    halign: 'center',
                    align: 'right',
                    title: "Total",
                    editor: {
                        type: 'numberbox',
                        options: {
                            precision: 2,
                            readonly: true,
                        }
                    }
                },{
                    field: 'id',
                    width: 80,
                    hidden: true,
                    halign: 'center',
                    title: "ID",
                    editor: {
                        type: 'textbox',
                    }
                }]
            ],
            onClickCell: function(index, field) {
                if(link == ""){
                    if (editIndex != index) {
                        if (endEditing()) {
                            $('#dg2').datagrid('selectRow', index).datagrid('beginEdit', index);
                            editIndex = index;
                        } else {
                            setTimeout(function() {
                                $('#dg2').datagrid('selectRow', editIndex);
                            }, 0);
                        }
                    }
                }
            }
        });
    }

    var editIndex = undefined;

    function endEditing() {
        if (editIndex == undefined) {
            return true
        }
        if ($('#dg2').datagrid('validateRow', editIndex)) {
            $('#dg2').datagrid('endEdit', editIndex);
            editIndex = undefined;
            return true;
        } else {
            return false;
        }
    }

    function append() {
        var supplier_id = $("#supplier_id").combogrid('getValue');
        if (supplier_id != "") {
            if (endEditing()) {
                $('#dg2').datagrid('appendRow', {
                    qty: '0'
                });
                editIndex = $('#dg2').datagrid('getRows').length - 1;
                $('#dg2').datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
            }
        } else {
            toastr.error("Please Choose Supplier No first");
        }
    }

    function removeit() {
        if (editIndex == undefined) {
            return true;
        }

        $('#dg2').datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
        editIndex = undefined;
    }

    //Edit Data
    function update() {
        var row = $('#dg').datagrid('getSelected');
        if (row) {
            if(row.status == "1"){
                toastr.error("Cannot Update for this data because the status is CLOSED");
            }else{
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                addTable(row.supplier_id, "<?= base_url("purchase/delivery_confirm/datatableUpdates?invoice_no=") ?>" + btoa(row.invoice_no));
                process = "UPDATE";
                $("#plant").combobox('disable');
                $("#supplier_id").combogrid('disable');
                $("#delivery_no").textbox('disable');
                $("#invoice_no").textbox('disable');
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Delete Data
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

                            if(row.status == "1"){
                                toastr.error("Cannot Delete for this data because the status is CLOSED");
                            }else{
                                $.ajax({
                                    method: 'post',
                                    url: '<?= base_url('purchase/delivery_confirm/delete') ?>',
                                    data: {
                                        invoice_no: row.invoice_no,
                                        supplier_id: row.supplier_id
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
                }
            });
        } else {
            toastr.info("Please select one of the data in the table first");
        }
    }

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_delivery_confirm.xls') ?>');
    }
    
    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_po_date = $("#filter_po_date").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + 
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_po_no=" + window.btoa(filter_po_no) +
            "&filter_po_date=" + window.btoa(filter_po_date) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_item_rm_id=" + window.btoa(filter_item_rm_id);

        $('#dg').datagrid({
            url: '<?= base_url('purchase/delivery_confirm/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            singleSelect: true,
            fit: true,
            pageList: [10, 50, 100, 500, 1000],
            pageSize: 10,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.invoice_no + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');

                ddv.datagrid({
                    url: '<?= base_url('purchase/delivery_confirm/datatableDetails?invoice_no=') ?>' + window.btoa(row.invoice_no) + "&supplier_id=" + btoa(row.supplier_id),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'po_no',
                            title: 'PO No',
                            halign: 'center',
                            width: 150
                        },{
                            field: 'po_date',
                            title: 'PO Date',
                            halign: 'center',
                            width: 120
                        },{
                            field: 'item_rm_id',
                            title: 'Part No Original',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_rm_number',
                            title: 'Part No Supplier',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_rm_name',
                            title: 'Description',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'item_family_name',
                            title: 'Product Family',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'uom',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'qty',
                            title: 'Qty PO',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberformat
                        }, {
                            field: 'delivery',
                            title: 'Delivery',
                            halign: 'center',
                            align: 'right',
                            width: 80,
                            formatter: numberformat
                        }, {
                            field: 'currency',
                            title: 'Currency',
                            align: 'center',
                            width: 80,
                        },{
                            field: 'price',
                            title: 'Price',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                            formatter: numberformatprice
                        }, {
                            field: 'total',
                            title: 'Total',
                            halign: 'center',
                            align: 'right',
                            width: 100,
                            formatter: numberformatprice
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        $("#printout").attr('src', '<?= base_url('purchase/delivery_confirm/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_status = $("#filter_status").combobox('getValue');
        var filter_po_no = $("#filter_po_no").combobox('getValue');
        var filter_po_date = $("#filter_po_date").combobox('getValue');
        var filter_product_family = $("#filter_product_family").combobox('getValue');
        var filter_invoice_no = $("#filter_invoice_no").combobox('getValue');
        var filter_item_rm_id = $("#filter_item_rm_id").combogrid('getValue');

        var url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + 
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_status=" + window.btoa(filter_status) +
            "&filter_po_no=" + window.btoa(filter_po_no) +
            "&filter_po_date=" + window.btoa(filter_po_date) +
            "&filter_product_family=" + window.btoa(filter_product_family) +
            "&filter_invoice_no=" + window.btoa(filter_invoice_no) +
            "&filter_item_rm_id=" + window.btoa(filter_item_rm_id);

        window.location.assign('<?= base_url('purchase/delivery_confirm/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        filter();

        $("#u_attachment").filebox({
            onChange: function () {
                var attachment = $("#attachment").textbox('getValue');
                var files = $(this).filebox('files')[0];

                var formData = new FormData();
                formData.append('file', files);

                $.ajax({
                    url: "<?= base_url('purchase/delivery_confirm/uploadAttachment/') ?>" + btoa(attachment),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        var data = JSON.parse(response);
                        toastr.info(data.msg);
                        $("#attachment").textbox('setValue', data.file);
                    }
                });
            }
        });

        //Save Data
        $('#dlg_insert').dialog({
            buttons: [{
                text: 'Save All',
                iconCls: 'icon-ok',
                handler: function() {
                    var plant = $("#plant").combobox('getValue');
                    var supplier_id = $("#supplier_id").combogrid('getValue');
                    var delivery_no = $("#delivery_no").textbox('getValue');
                    var invoice_no = $("#invoice_no").textbox('getValue');
                    var invoice_date = $("#invoice_date").datebox('getValue');
                    var etd = $("#etd").datebox('getValue');
                    var eta = $("#eta").datebox('getValue');
                    var shipping = $("#shipping").combogrid('getValue');
                    var awb_no = $("#awb_no").textbox('getValue');
                    var awb_date = $("#awb_date").datebox('getValue');
                    var attachment = $("#attachment").textbox('getValue');

                    if(supplier_id != "" && delivery_no != "" && invoice_no != "" && invoice_date != "" && plant != "" && etd != "" && eta != "" && shipping != ""){
                        $.ajax({
                            type: "post",
                            url: "<?= base_url('purchase/delivery_confirm/checkInvoice') ?>",
                            data: "invoice_no=" + invoice_no,
                            dataType: "html",
                            success: function (data) {
                                if(data == "NG" && process == "ADD"){
                                    toastr.error("Invoice No already created, Please check again");
                                    return false;
                                }

                                $('#dg2').datagrid('acceptChanges');
                                var rows = $('#dg2').datagrid('getRows');
                                var totalrows = rows.length;
                                endEditing();

                                for (let z = 0; z < totalrows; z++) {
                                    var jsonCheck = rows[z];
                                    if((parseFloat(jsonCheck.qty) - parseFloat(jsonCheck.delivery)) < 0){
                                        toastr.error("Please Check Invoice Qty cannot > Remain");
                                        return false;
                                    }

                                    if(parseFloat(jsonCheck.delivery) == 0){
                                        toastr.error("Invoice Qty Still 0");
                                        return false;
                                    }
                                }

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

                                requestData(totalrows, rows);
                                function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                    var i = (number - 1);
                                    if (value < 100) {
                                        value = Math.floor((number / total) * 100);

                                        if(json[i].balance >= 0){
                                            $.ajax({
                                                type: "post",
                                                url: '<?= base_url('purchase/delivery_confirm/create') ?>',
                                                data: {
                                                    plant: plant,
                                                    supplier_id: supplier_id,
                                                    delivery_no: delivery_no,
                                                    invoice_no: invoice_no,
                                                    invoice_date: invoice_date,
                                                    etd: etd,
                                                    eta: eta,
                                                    shipping: shipping,
                                                    awb_no: awb_no,
                                                    awb_date: awb_date,
                                                    attachment: attachment,
                                                    po_no: json[i].po_no,
                                                    po_date: json[i].po_date,
                                                    item_rm_id: json[i].item_rm_id,
                                                    uom: json[i].uom,
                                                    currency: json[i].currency,
                                                    price: json[i].price,
                                                    total: json[i].total,
                                                    qty: json[i].qty,
                                                    delivery: json[i].delivery,
                                                    balance: json[i].balance,
                                                    id: json[i].id,
                                                },
                                                dataType: "json",
                                                success: function(result) {
                                                    if (i == (total - 1)) {
                                                        Swal.close();
                                                        Swal.fire({
                                                            title: result.message,
                                                            icon: result.theme,
                                                            confirmButtonText: 'Ok',
                                                            allowOutsideClick: false,
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                window.location.reload();
                                                                // $('#dg').datagrid('reload');
                                                            }
                                                        });
                                                    }

                                                    requestData(total, json, number + 1, value, success + 1, failed + 0);
                                                }
                                            });
                                        }else{
                                            toastr.error("Balance Cannot Minus"); 
                                        }
                                    }
                                }
                            }
                        });
                    }else{
                        toastr.error("Please Complete Your input");    
                    }
                }
            }]
        });

        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('purchase/delivery_confirm/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('purchase/delivery_confirm/upload') ?>',
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
                            //Clear File
                            $.ajax({
                                url: "<?= base_url('purchase/delivery_confirm/uploadclearFailed') ?>"
                            });
                            var json = eval('(' + result + ')');
                            requestData(json.total, json);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.ajax({
                                        type: "POST",
                                        async: true,
                                        url: "<?= base_url('purchase/delivery_confirm/uploadCreate') ?>",
                                        data: {
                                            "data": json[number - 1]
                                        },
                                        cache: false,
                                        dataType: "json",
                                        success: function(result) {
                                            if (result.theme == "success") {
                                                $('#p_success').html(success);
                                                var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                $('#p_failed').html(failed);
                                                var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                //Json Failed
                                                $.ajax({
                                                    type: "POST",
                                                    async: true,
                                                    url: "<?= base_url('purchase/delivery_confirm/uploadcreateFailed') ?>",
                                                    data: {
                                                        data: json[number - 1],
                                                        message: result.message
                                                    },
                                                    cache: false
                                                });
                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }
                                            $("#p_remarks").append(title + "<br>");
                                        }
                                    });
                                }
                            }
                        }
                    });
                }
            }]
        });

        $('#filter_item_rm_id').combogrid({
            url: '<?= base_url('master/item_rm/reads') ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Part No Original",
            columns: [
                [{
                    field: 'id',
                    title: 'Part No Original',
                    width: 150
                }, {
                    field: 'number',
                    title: 'Part No Supplier',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 200
                }]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });

        $('#filter_plant').combobox({
            url: '<?php echo base_url('master/plants/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Plant',
        });

        $("#filter_supplier").combogrid({
            url: '<?php echo base_url('purchase/delivery_confirm/filterSupplier'); ?>',
            panelWidth: 300,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'id',
                    title: 'Supplier ID',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 200
                }]
            ],
            onSelect: function(val, supp){
                $('#filter_invoice_no').combobox({
                    url: '<?php echo base_url('purchase/delivery_confirm/filterInvoiceNo/'); ?>' + btoa(supp.id),
                    valueField: 'invoice_no',
                    textField: 'invoice_no',
                    prompt: 'Choose Invoice No',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $('#filter_product_family').combobox({
                    url: '<?php echo base_url('purchase/delivery_confirm/filterProdfam/'); ?>' + btoa(supp.id),
                    valueField: 'id',
                    textField: 'name',
                    prompt: 'Choose Product Family',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });

                $('#filter_po_no').combobox({
                    url: '<?php echo base_url('purchase/delivery_confirm/filterPo/'); ?>' + btoa(supp.id),
                    valueField: 'po_no',
                    textField: 'po_no',
                    prompt: 'Choose PO No',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $('#plant').combobox({
            url: '<?php echo base_url('master/plants/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Plant',
        });

        $("#shipping").combogrid({
            url: '<?php echo base_url('master/shipping/reads'); ?>',
            panelWidth: 500,
            idField: 'name',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Shipping Method",
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 150
                }, {
                    field: 'country',
                    title: 'Country of Origin',
                    width: 150
                }, {
                    field: 'estimate',
                    title: 'Estimate (Days)',
                    width: 80
                }]
            ],
            onSelect: function(val, ship){
                var day = ship.estimate;

                $("#etd").datebox({
                    readonly: false,
                    onChange: function(etd){
                        let etdDate = new Date(etd);

                        if(day > 0){
                            etdDate.setDate(etdDate.getDate() + day);
                        }

                        let etaStr = etdDate.toISOString().split('T')[0];

                        // Set nilai ETA
                        $("#eta").datebox('setValue', etaStr);
                    }
                });
            }
        });

        $("#supplier_id").combogrid({
            url: '<?php echo base_url('purchase/purchase_orders/readSupplier'); ?>',
            panelWidth: 300,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'id',
                    title: 'Supplier ID',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 200
                }]
            ],
            onSelect: function(val, supp){
                if(supp.type == "LOCAL"){
                    $.ajax({
                        type: "post",
                        url: "<?= base_url("purchase/delivery_confirm/autoInvoice") ?>",
                        data: "plant=" + plant.number,
                        dataType: "json",
                        success: function (response) {
                            $("#invoice_no").textbox('setValue', response.invoice_no);
                            $("#delivery_no").textbox('setValue', response.delivery_no);
                        }
                    });
                }

                addTable(supp.id);
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

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if (value == 0) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    //FORMATTER STATUS
    function cellFormatter(value) {
        if (value == 0) {
            return 'OPEN';
        } else {
            return 'CLOSED';
        }
    };

    function btnAttachment(val){
        if(val == null || val == ""){
            return "-";
        }else{
            return "<a class='btn btn-primary btn-sm' style='pointer-events: auto; opacity:1; width:100%;' target='_blank' href='<?= base_url('assets/documents/delivery_confirm/') ?>"+val+"'><i class='fa fa-eye'></i> View</a>";
        }
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
    }

    function numberformatprice(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 4
        });

        return "<b>" + formatter.format(value) + "</b>";
    }
</script>