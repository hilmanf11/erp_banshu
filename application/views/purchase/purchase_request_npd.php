<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'plant',width:80,halign:'center'">Plant</th>
            <th rowspan="2" data-options="field:'request_no',width:150,halign:'center'">PR No</th>
            <th rowspan="2" data-options="field:'request_date',width:100,halign:'center'">PR Date</th>
            <th rowspan="2" data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <th rowspan="2" data-options="field:'item_family_name',width:120,halign:'center'">Product Family</th>
            <th rowspan="2" data-options="field:'total_pr',width:100,halign:'center',align:'right',formatter:numberformat">Total PR</th>
            <th rowspan="2" data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">Detail</th>
            <th rowspan="2" data-options="field:'print_pr',width:80,halign:'center',formatter:BtnPrint">Print PR</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Create PO</th>
            <th rowspan="2" data-options="field:'attachment',width:80,align:'center',formatter: btnDetails">Attachment</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Confirm PR</th>
            <th colspan="3" data-options="field:'',width:100,halign:'center'">Approved</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'">Updated</th>
        </tr>
        <tr>
            <th data-options="field:'send',width:120,align:'center',formatter:BtnSend">Create</th>
            <th data-options="field:'create_po',width:150,align:'center'">Date</th>
            <th data-options="field:'confirm',width:100,align:'center',formatter:BtnConfirm">Send</th>
            <th data-options="field:'confirm_date',width:150,align:'center'">Date</th>
            <th data-options="field:'approved_to',width:80,align:'center', styler:cellStylerApproval, formatter:cellFormatterApproval">Status</th>
            <th data-options="field:'approved_by',width:100,align:'center'">By</th>
            <th data-options="field:'approved_date',width:120,align:'center'">Date</th>
            <th data-options="field:'created_by',width:100,align:'center'">By</th>
            <th data-options="field:'created_date',width:120,align:'center'">Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'">By</th>
            <th data-options="field:'updated_date',width:120,align:'center'">Date</th>
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar3" style="height: auto; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Detail</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request No</span>
                    <input style="width:60%;" id="d_request_no" class="easyui-textbox" readonly>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" id="detailEdit" class="easyui-linkbutton" onclick="detailEdit()"><i class="fa fa-edit"></i> Edit Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" id="d_supplier" class="easyui-textbox" readonly>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="d_item_family_id" class="easyui-textbox" readonly>
                </div>
            </div>
        </fieldset>
    </div>
</div>

<div id="toolbar2">
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="append()"><i class="fa fa-plus"></i> Add</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" data-options="plain:true" onclick="removeit()"><i class="fa fa-times"></i> Remove</a>
</div>

<div id="toolbar" style="height: 230px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Request Date</span>
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
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request</span>
                    <input style="width:60%;" id="filter_request_no" class="easyui-combobox">
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
        </fieldset>
        <fieldset style="width: 30%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Generating Process</b></legend>

            <b>Generate NPD to Purchase Request</b>
            <div id="p_upload" class="easyui-progressbar" style="width:100%;"></div>
            <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<div id="dlg_update" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 800px; padding:10px; top: 20px;">
    <form id="frm_update" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" name="p_month" disabled class="easyui-combobox" required data-options="prompt:'Month'">
                    <input style="width:30%;" name="p_year" disabled class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <select style="width:60%;" name="revision" disabled required class="easyui-combobox" panelHeight="auto">
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" name="supplier_id" disabled class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Name</span>
                    <input style="width:60%;" name="supplier_name" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" name="item_family_id" disabled class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" name="item_rm_number" disabled class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part Name</span>
                    <input style="width:60%;" name="item_rm_name" disabled class="easyui-textbox">
                </div>
            </div>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Purchase Request No</span>
                    <input style="width:60%;" name="request_no" id="update_request_no" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">MPQ / MOQ</span>
                    <input style="width:30%;" name="mpq" id="mpq" readonly class="easyui-numberbox">
                    <input style="width:30%;" name="moq" id="moq" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Share Order</span>
                    <input style="width:30%;" name="share_order" id="share_order" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Leadtime</span>
                    <input style="width:30%;" name="leadtime" id="leadtime" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Need</span>
                    <input style="width:25%;" name="need" id="need" required="true" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Qty PR</span>
                    <input style="width:25%;" id="qty_edit" required="true" class="easyui-numberbox">
                    <input style="width:25%;" name="qty" id="qty" required="true" readonly class="easyui-numberbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Remarks</span>
                    <input style="width:60%;" name="remarks" id="remarks" required class="easyui-textbox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Status</span>
                    <select style="width:60%;" name="flag" id="flag" class="easyui-combobox" panelHeight="auto">
                        <option value="OK">OK</option>
                        <option value="CHECK">CHECK</option>
                    </select>
                </div>
                <div class="fitem" hidden>
                    <span style="width:35%; display:inline-block;">Status</span>
                    <input style="width:50%;" name="status_data" id="status_data" required class="easyui-textbox">
                </div>
            </div>
        </fieldset>
    </form>
</div>

<div id="dlg_purchase_request" class="easyui-dialog" title="Detail Purchase Request" data-options="closed: true,modal:true, fit:true" style="width: 100%; top: 0px; left: 0px;">
    <table id="dg_purchase_request" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar3" showFooter="true"></table>
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
    <span style="float: left; color:green;">SUCCESS : <b id="p_success2">0</b></span><span style="float: right; color:red;"> FAILED : <b id="p_failed2">0</b></span>
    <div id="p_upload2" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start2">0</b> Of <b id="p_finish2">0</b></center>
    <div id="p_remarks2" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks2">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function add() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');

        if (filter_from != "" && filter_to != "" && filter_plant != "") {
            $.messager.prompt('Generate PR', 'Please input Password Generate, Be careful if data is generated for this period it will be deleted', function(r){
                if (r == "PURCHASEREQUEST"){
                    Swal.fire({
                        title: 'Please Wait for Generating Data',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: "get",
                        url: "<?= base_url('purchase/purchase_request_npd/getDataNpd') ?>",
                        data: "filter_from=" + window.btoa(filter_from) +
                            "&filter_to=" + window.btoa(filter_to) +
                            "&filter_plant=" + window.btoa(filter_plant) +
                            "&filter_supplier=" + window.btoa(filter_supplier) +
                            "&filter_item_family=" + window.btoa(filter_item_family) +
                            "&filter_item_rm=" + window.btoa(filter_item_rm) +
                            "&id_menu=<?= base64_encode($id_menu) ?>",
                        dataType: "json",
                        success: function(rows) {
                            Swal.close();

                            if(rows['total'] > 0){
                                requestData(rows['total'], rows);

                                function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                    if (value < 100) {
                                        value = Math.floor((number / total) * 100);
                                        $('#p_upload').progressbar('setValue', value);
                                        $('#p_start').html(number);
                                        $('#p_finish').html(total);

                                        $.post('<?= base_url('purchase/purchase_request_npd/create') ?>', {
                                            data: json[number - 1]
                                        }, function(note) {
                                            var result = eval('(' + note + ')');

                                            if (result.theme == "success") {
                                                requestData(total, json, number + 1, value, success + 1, failed + 0);
                                            } else {
                                                requestData(total, json, number + 1, value, success + 0, failed + 1);
                                            }

                                            if (value == 100) {
                                                Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                                filter();
                                            }
                                        }).fail(function(jqXHR, textStatus) {
                                            if (textStatus == "error") {
                                                toastr.error("Connection time out");

                                                requestData(total, json, number, value, success + 0, failed + 1);
                                            }
                                        });
                                    }
                                }
                            }else{
                                Swal.fire('Failed!', 'Data MRP Not Found or Data has been Generated, please check first!', 'error');
                            }
                        },
                        error: function(rows) {
                            Swal.fire('Failed!', 'Generate Purchase Requst is Failed, Please Try Again!', 'error');
                        }
                    });
                }
            });

            $('.messager-input').attr('type', 'password');
        } else {
            Swal.close();
            toastr.warning("Please Select Period, Revision & Plant", "Information");
        }
    }

    //Edit Data
    function update() {
        toastr.info("Not Ready this Function");
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
                                url: '<?= base_url('purchase/purchase_request_npd/delete') ?>',
                                data: {
                                    request_no: row.request_no,
                                    request_npd: row.request_npd,
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
                                    toastr.error("Cannot Process your request");
                                    Swal.close();
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
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_request_no=" + window.btoa(filter_request_no) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_rm=" + window.btoa(filter_item_rm);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('purchase/purchase_request_npd/datatables') ?>' + url,
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('purchase/purchase_request_npd/print') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_supplier = $("#filter_supplier").combobox('getValue');
        var filter_request_no = $("#filter_request_no").combobox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_supplier=" + window.btoa(filter_supplier) +
            "&filter_request_no=" + window.btoa(filter_request_no) +
            "&filter_item_family=" + window.btoa(filter_item_family) +
            "&filter_item_rm=" + window.btoa(filter_item_rm);

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            window.location.assign('<?= base_url('purchase/purchase_request_npd/print/excel') ?>' + url);
        }
    }

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_purchase/request_npd.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    function detailEdit(){
        var row = $('#dg_purchase_request').datagrid('getSelected');
        if (row) {
            if(row.approved_to == ""){
                toastr.error("You can't edit the quantity because data has been approved");
                return false;
            }

            $('#dlg_update').dialog('open');
            $('#frm_update').form('load', row);

            $('#update_request_no').combogrid({
                url: '<?= base_url('purchase/purchase_request_npd/readPr/') ?>' + btoa(row.supplier_id) + '/' + btoa(row.item_family_id),
                panelWidth: 500,
                idField: 'request_no',
                textField: 'request_no',
                mode: 'remote',
                fitColumns: true,
                prompt: "Select Purchase Request No",
                columns: [
                    [{
                        field: 'request_no',
                        title: 'PR No',
                        width: 200
                    }, {
                        field: 'supplier_name',
                        title: 'Supplier Name',
                        width: 250
                    }]
                ]
            });

            $("#update_request_no").combogrid('setValue', row.request_no);

            $("#qty_edit").numberbox({
                onChange: function(val){
                    // if(parseInt(val) <= parseInt(row.purchase_order)){
                        if(parseInt(val) == 0){
                            var hasil = Math.ceil(parseInt(val));
                        }else{
                            if(parseInt(val) > row.moq){
                                var hasil = (Math.ceil(parseInt(val) / row.mpq) * row.mpq);
                            }else{
                                var hasil = (Math.ceil(parseInt(val) / row.moq) * row.moq);
                            }
                        }
                            
                        $("#qty").numberbox('setValue', hasil);
                    // }else{
                    //     toastr.error("Your Qty > Mrp Results");
                    //     $("#purchase_request").numberbox('setValue', row.purchase_order);
                    // }
                }
            });
            
            $('#status_data').textbox('setValue', "EDIT");
            // $('#p_month').combobox('disable');
            // $('#p_year').combobox('disable');
            // $('#revision').combobox('disable');
            // $('#supplier_id').combobox('disable');
            // $('#item_family_id').combobox('disable');
            // $('#part_no').combogrid('disable');
            // $('#part_name').textbox('disable');

            url_save = '<?= base_url('purchase/purchase_request_npd/update') ?>?id=' + btoa(row.id);
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function details(request_no, supplier, item_family_id, push="") {
        $("#dlg_purchase_request").dialog('open');

        $("#d_request_no").textbox('setValue', request_no);
        $("#d_supplier").textbox('setValue', supplier);
        $("#d_item_family_id").textbox('setValue', item_family_id);

        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');


        $('#dg_purchase_request').datagrid('disableFilter');

        $('#dg_purchase_request').datagrid({
            url: '<?= base_url('purchase/purchase_request_npd/datatableDetails/') ?>' + btoa(request_no) + "/0?filter_item_rm=" + btoa(filter_item_rm) + "&filter_from=" + btoa(filter_from) + "&filter_to=" + btoa(filter_to),
            clientPaging: true,
            remoteFilter: false,
            rownumbers: true,
            singleSelect: false,
            fit:true,
            columns: [
                [{
                    field: 'ck',
                    checkbox: true
                }, {
                    field: 'approved_to',
                    title: 'Approval',
                    width: 100,
                    align: 'center',
                    styler:cellStylerApproval, formatter:cellFormatterApproval
                }, {
                    field: 'approved_by',
                    title: 'Approval By',
                    width: 120,
                    align: 'center',
                }, {
                    field: 'approved_date',
                    title: 'Approval Date',
                    width: 150,
                    align: 'center',
                }, {
                    field: 'flag',
                    title: 'Status',
                    width: 80,
                    align: 'center',
                    styler:cellStylerFlag
                }, {
                    field: 'po_no',
                    title: 'PO No',
                    width: 150,
                    halign: 'center',
                }, {
                    field: 'item_rm_number',
                    title: 'Part No BEI',
                    width: 200,
                    halign: 'center',
                }, {
                    field: 'item_supplier',
                    title: 'Part No Supplier',
                    width: 200,
                    halign: 'center',
                }, {
                    field: 'item_rm_name',
                    title: 'Part Name',
                    width: 200,
                    halign: 'center',
                }, {
                    field: 'share_order',
                    title: 'Share<br>Order',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                }, {
                    field: 'mpq',
                    title: 'MPQ',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                },{
                    field: 'moq',
                    title: 'MOQ',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                },{
                    field: 'leadtime',
                    title: 'Leadtime',
                    width: 80,
                    halign: 'center',
                    align: 'right',
                },{
                    field: 'need',
                    title: 'Need',
                    width: 100,
                    halign: 'center',
                    align: 'right',
                    formatter: numberformat
                },{
                    field: 'qty',
                    title: 'Qty',
                    width: 100,
                    halign: 'center',
                    align: 'right',
                    formatter: numberformat
                },{
                    field: 'uom',
                    title: 'Uom',
                    width: 80,
                    halign: 'center',
                    align: 'center',
                }, {
                    field: 'remarks',
                    title: 'Remarks',
                    width: 200,
                    halign: 'center',
                }, {
                    field: 'status_data',
                    title: 'Status',
                    width: 80,
                    align: 'center',
                }]
            ]
        });

        $('#dg_purchase_request').treegrid('enableFilter');
    }

    function sendPo(request_no) {
        $.messager.prompt('Generate PO', 'Please input Password Generate, Be careful if data is generated for this period it will be deleted', function(r){
            if (r == "PURCHASEORDER"){
                Swal.fire({
                    title: 'Please Wait for Generating Data',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: "get",
                    url: "<?= base_url('purchase/purchase_order_npd/getData') ?>",
                    data: "request_no=" + window.btoa(request_no) + "&id_menu=20240103000001",
                    dataType: "json",
                    success: function(rows) {
                        Swal.close();

                        if(rows['total'] > 0){
                            requestData(rows['total'], rows);

                            function requestData(total, json, number = 1, value = 0, success = 1, failed = 1) {
                                if (value < 100) {
                                    value = Math.floor((number / total) * 100);
                                    $('#p_upload').progressbar('setValue', value);
                                    $('#p_start').html(number);
                                    $('#p_finish').html(total);

                                    $.post('<?= base_url('purchase/purchase_order_npd/create2') ?>', {
                                        data: json[number - 1]
                                    }, function(note) {
                                        var result = eval('(' + note + ')');

                                        if (result.theme == "success") {
                                            requestData(total, json, number + 1, value, success + 1, failed + 0);
                                        } else {
                                            requestData(total, json, number + 1, value, success + 0, failed + 1);
                                        }

                                        if (value == 100) {
                                            Swal.fire('Good job!', 'Process Generate Data Completed!', 'success');
                                            $("#dg").datagrid('reload');
                                        }
                                    }).fail(function(jqXHR, textStatus) {
                                        if (textStatus == "error") {
                                            toastr.error("Connection time out");

                                            requestData(total, json, number, value, success + 0, failed + 1);
                                        }
                                    });
                                }
                            }
                        }else{
                            Swal.close();
                            Swal.fire('Failed!', 'This PR and the items in it that have been approved have been created.', 'error');
                        }
                    },
                    error: function(rows) {
                        Swal.close();
                        Swal.fire('Failed!', 'Failed to Create PO, Please Try Again!', 'error');
                    }
                });
            }
        });
    }

    function confirm(request_no, created_by) {
        if(created_by == "<?= $this->session->username ?>"){
            $.messager.prompt('CONFIRM PR', 'Please input Password confirm, for Send to Approval', function(r){
                if (r == "CONFIRMPR"){
                    Swal.fire({
                        title: 'Please Wait, the process data for sending to approval',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: "post",
                        url: '<?= base_url('purchase/purchase_request_npd/updateConfirm') ?>',
                        data: "request_no=" + request_no,
                        dataType: "json",
                        success: function (response) {
                            Swal.close();
                            toastr.success("Data has been Confirmed");
                            $("#dg").datagrid("reload");
                        }
                    });
                }
            });
        }else{
            toastr.error("Cannot Confrim by you");
        }
    }

    function BtnPrint(val, row) {
        return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('purchase/purchase_request_npd/print_request/') ?>'+btoa(row.request_no)+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-print"></i> Print</a>';
    }

    $(function() {
    	$("#add").html("Convert NPD to PR");

        $('#dg').datagrid({
            url: '<?= base_url('purchase/purchase_request_npd/datatables') ?>',
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        filter();

        $('#dlg_update').dialog({
            buttons: [{
                text: 'Save',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_update').form('submit', {
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

                            $('#dlg_update').dialog('close');
                            $('#dg_purchase_request').datagrid('reload');
                            $('#dg').datagrid('reload');
                        }
                    });
                }
            }]
        });

        $('#attachment_upload').filebox({
            buttonText: 'Browse File',
            accept: '.jpg, .png, .pdf',
            onChange: function () {
                var files = $(this).filebox('files');
                var formData = new FormData();

                for (var i = 0; i < files.length; i++) {
                    var file = files[i];
                    formData.append('file', file, file.name);
                }

                $.ajax({
                    url: '<?= base_url('purchase/purchase_request_npd/uploadatt') ?>',
                    type: 'post',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function (data) {
                        if (data.success == true) {
                            toastr.success(data.message);
                            $('#attachment').textbox('setValue', data.filename); // Mengatur nilai pada textbox
                        } else {
                            toastr.error(data.message);
                        }
                    }
                });
            }
        });

        $('#plant').combobox({
            url: '<?php echo base_url('master/plants/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Plant',
        });

        $('#supplier_id').combobox({
            url: '<?php echo base_url('master/suppliers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Supplier',
            onSelect: function(supp){
                $('#item_family_id').combobox({
                    url: '<?php echo base_url('purchase/purchase_request_npd/readprodfam/'); ?>' + btoa(supp.id),
                    valueField: 'id',
                    textField: 'name',
                    prompt: 'Select Product Family',
                    onSelect: function(prodfam){
                        var plant = $('#plant').combobox('getValue');

                        addTable(supp.id, prodfam.id, plant);

                        var p_month = $("#p_month").combobox('getValue');
                        var p_year = $("#p_year").combobox('getValue');
                        
                        var code = "PR"+supp.number+p_year+p_month+"A"+prodfam.number;
                        
                        $.ajax({
                            type: "post",
                            url: "<?= base_url("purchase/purchase_request_npd/purchaseRequestNo/") ?>"+btoa(code)+"/adds",
                            // url: "<?= base_url("purchase/purchase_request_npd/purchaseRequestNo//adds") ?>",
                            dataType: "html",
                            success: function (response) {
                                if(response == ""){
                                    toastr.info("Please Check Configuration in Serial No First");
                                }else{
                                    $("#request_no").textbox('setValue', response);
                                }
                            }
                        });
                    }
                });
            }
        });

        $('#filter_plant').combobox({
            url: '<?php echo base_url('master/plants/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Plant',
        });

        $('#filter_supplier').combobox({
            url: '<?php echo base_url('master/suppliers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Supplier',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_request_no').combobox({
            url: '<?php echo base_url('purchase/purchase_request_npd/reads'); ?>',
            valueField: 'request_no',
            textField: 'request_no',
            prompt: 'Select Request No',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
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
                    panelWidth: 400,
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
                            field: 'number',
                            title: 'Part No',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Part Name',
                            width: 200
                        }]
                    ]
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
            minimumFractionDigits: 0
        });

        return formatter.format(value);
    }

    function numberformatdecimal(value, row) {
        const formatter = new Intl.NumberFormat('ja-JP', {
            minimumFractionDigits: 4
        });

        return formatter.format(value);
    }

    function BtnDetail(val, row) {
        var details = "details('"+row.request_no+"','"+row.supplier_name+"','"+row.item_family_name+"')";
        return '<a class="btn btn-primary w-100" style="pointer-events: visible; opacity:1;" onClick="'+details+'"><i class="fa fa-eye"></i> View</a>';
    }

    function BtnSend(val, row) {
        if(row.total_data != row.total_po){
            if((row.approved_to == null || row.approved_to == "") && row.confirm == "1"){
                var send = "sendPo('"+row.request_no+"')";
                return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+send+'"><i class="fa fa-plus"></i> Create PO</a>';
            }else{
                return '-';
            }
        }else{
            return '-';
        }
    }

    function BtnConfirm(val, row) {
        if(val == 0){
            var confirm = "confirm('"+row.request_no+"','"+row.created_by+"')";
            return '<a class="btn btn-success w-100" style="pointer-events: visible; opacity:1;" onClick="'+confirm+'"><i class="fa fa-check"></i> Confirm</a>';
        }else{
            return 'CONFIRMED';
        }
    }

    function cellStylerApproval(value, row, index) {
        if (row.approved_to == null) {
            return 'background: #ffc25fff; color:white;';
        } else if(row.approved_to == ""){
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellStylerFlag(value, row, index) {
        if (row.flag == "OK") {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #ffc25fff; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (row.approved_to == null) {
            return 'Pending';
        } else if(row.approved_to == ""){
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

    function btnDetails(val, row, index) {
        var attachment = row.attachment;
        
        if (attachment != null && attachment != "") {
            return '<a class="btn btn-primary w-100" target="_blank" href="<?= base_url('assets/documents/purchase/request_npd/') ?>'+row.attachment+'" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i> View</a>';
        } else {
            return '-';
        }
    }
</script>