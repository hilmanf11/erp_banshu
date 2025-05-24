<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" data-options="field:'details',width:50,align:'center', formatter:btnDetails">View</th>
            <th rowspan="2" data-options="field:'status',width:60,align:'center',styler:styleStatus, formatter:formatStatus">Status</th>
            <th rowspan="2" data-options="field:'source',width:60,align:'center'">Source</th>
            <th rowspan="2" data-options="field:'trans_date',width:100,align:'center'">Invoice Date</th>
            <th rowspan="2" data-options="field:'due_date',width:100,align:'center'">Payment Due</th>
            <th rowspan="2" data-options="field:'document_no',width:120,halign:'center'">Document No</th>
            <th rowspan="2" data-options="field:'invoice_no',width:200,halign:'center'">Invoice No</th>
            <th rowspan="2" data-options="field:'description',width:200,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'voucher_date',width:100,align:'center'">Posting Date</th>
            <th rowspan="2" data-options="field:'voucher_no',width:120,halign:'center'">Posting No</th>
            <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account No</th>
            <th rowspan="2" data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th colspan="4" data-options="field:'local_currency',width:80,align:'center'">Local Currency</th>
        </tr>
        <tr>
            <th data-options="field:'local_debit',width:100,halign:'center',align:'right', formatter:numberformat">Debit</th>
            <th data-options="field:'local_credit',width:100,halign:'center',align:'right', formatter:numberformat">Credit</th>
            <th data-options="field:'balance',width:100,halign:'center',align:'right', formatter:numberformat">Balance</th>
            <th data-options="field:'accumulated',width:100,halign:'center',align:'right', formatter:numberformat">Accumulated</th>
        </tr>
    </thead>
</table>

<div id="dlg_detail" class="easyui-window" title="Journal Detail" data-options="closed: true,modal:true" style="width: 800px; height: 500px; top: 20px; left:10px;">
    <table id="dg3" class="easyui-datagrid" style="width:100%;" showFooter="true">
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'trans_date',width:100,halign:'center'">Trans Date</th>
                <th rowspan="2" data-options="field:'document_no',width:150,halign:'center'">Document No</th>
                <th rowspan="2" data-options="field:'invoice_no',width:150,halign:'center'">Supplier Invoice</th>
                <th rowspan="2" data-options="field:'company_name',width:200,halign:'center'">Company Name</th>
                <th rowspan="2" data-options="field:'account_number',width:100,halign:'center'">Account No</th>
                <th rowspan="2" data-options="field:'account_name',width:200,halign:'center'">Account Name</th>
                <th rowspan="2" data-options="field:'description',width:600,halign:'center'">Description</th>
                <th colspan="3" data-options="field:'',width:100">Original Currency</th>
                <th colspan="3" data-options="field:'',width:100">Local Currency</th>
            </tr>
            <tr>
                <th data-options="field:'currency',width:80,align:'center'">Currency</th>
                <th data-options="field:'original_debit',width:100,halign:'center',align:'right',formatter:numberformat">Debit</th>
                <th data-options="field:'original_credit',width:100,halign:'center',align:'right',formatter:numberformat">Credit</th>
                <th data-options="field:'rates',width:100,halign:'center',align:'right',formatter:numberformat">Rates</th>
                <th data-options="field:'local_debit',width:100,halign:'center',align:'right',formatter:numberformat">Debit</th>
                <th data-options="field:'local_credit',width:100,halign:'center',align:'right',formatter:numberformat">Credit</th>
            </tr>
        </thead>
    </table>
</div>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 200px; padding: 10px;">
    <fieldset style="width: 80%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float: left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Trans Date</span>
                <input style="width:30%;" id="filter_from" value="<?= date("Y-m-01") ?>" class="easyui-datebox" data-options="prompt:'Start Date',formatter:myformatter,parser:myparser, editable:false">
                <input style="width:30%;" id="filter_to" value="<?= date("Y-m-t") ?>" class="easyui-datebox" data-options="prompt:'Finish Date',formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Supplier</span>
                <input style="width:60%;" id="filter_supplier" class="easyui-combobox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Purchase Invoice</span>
                <input style="width:60%;" id="filter_purchase_invoice" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 50%; float: left;">
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Currency</span>
                <input style="width:60%;" id="filter_currency" class="easyui-combobox">
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Payment</span>
                <select style="width:60%;" id="filter_payment" class="easyui-combobox" panelHeight="auto">
                    <option value="">Choose All</option>
                    <option value="0">OPEN</option>
                    <option value="1">CLOSE</option>
                </select>
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Status</span>
                <select style="width:60%;" id="filter_status" class="easyui-combobox" panelHeight="auto">
                    <option value="">Choose All</option>
                    <option value="0">OPEN</option>
                    <option value="1">CLOSE</option>
                </select>
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Display</span>
                <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                    <!-- <option value="Summary">Summary</option> -->
                    <option value="Detail">Detail</option>
                </select>
            </div>
        </div>
    </fieldset>
    <?= $button ?>
</div>

<iframe id="printout" src="" style="width: 100%;" hidden></iframe>
<script>
    function filter() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_supplier = $("#filter_supplier").combobox("getValue");
        var filter_currency = $("#filter_currency").combobox("getValue");
        var filter_payment = $("#filter_payment").combobox("getValue");
        var filter_display = $("#filter_display").combobox("getValue");
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox("getValue");
        var filter_status = $("#filter_status").combobox("getValue");

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + filter_supplier +
            "&filter_currency=" + filter_currency +
            "&filter_display=" + filter_display +
            "&filter_purchase_invoice=" + btoa(filter_purchase_invoice) +
            "&filter_status=" + filter_status +
            "&filter_payment=" + filter_payment;

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date!");
        } else {
            $('#dg').datagrid('loading');
            $.ajax({
                url: '<?= base_url('finance/report_ap_v2/datatables') ?>',
                type: 'POST',
                data: {
                    filter_from: filter_from,
                    filter_to: filter_to,
                    filter_supplier: filter_supplier,
                    filter_currency: filter_currency,
                    filter_payment: filter_payment,
                    filter_display: filter_display,
                    filter_purchase_invoice: filter_purchase_invoice,
                    filter_status: filter_status,
                },
                dataType: 'json',
                success: function(result) {
                    $('#dg').datagrid('loadData', result).datagrid('loaded');
                }
            });

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('finance/report_ap_v2/print') ?>' + url);
        }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox("getValue");
        var filter_to = $("#filter_to").datebox("getValue");
        var filter_supplier = $("#filter_supplier").combobox("getValue");
        var filter_currency = $("#filter_currency").combobox("getValue");
        var filter_payment = $("#filter_payment").combobox("getValue");
        var filter_display = $("#filter_display").combobox("getValue");
        var filter_purchase_invoice = $("#filter_purchase_invoice").combobox("getValue");
        var filter_status = $("#filter_status").combobox("getValue");

        var url = "?filter_from=" + window.btoa(filter_from) +
            "&filter_to=" + window.btoa(filter_to) +
            "&filter_supplier=" + filter_supplier +
            "&filter_currency=" + filter_currency +
            "&filter_display=" + filter_display +
            "&filter_purchase_invoice=" + btoa(filter_purchase_invoice) +
            "&filter_status=" + filter_status +
            "&filter_payment=" + filter_payment;

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date!");
        } else {
            window.location.assign('<?= base_url('finance/report_ap_v2/print/excel') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
        $('#dg').datagrid({
            pagination: true,
            clientPaging: true,
            rownumbers: true,
            fit: true,
            showFooter: true,
            collapsible:true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view:groupview,
            groupField:'supplier_name',
            groupFormatter:function(value,rows){
                return value + ' - ' + rows.length + ' Item(s) | Begin Balance (' + (rows[0].begin).toFixed(2) + ')';
            }
        }).datagrid('enableFilter');

        $('#filter_supplier').combobox({
            url: '<?php echo base_url('master/suppliers/reads'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Choose Supplier Name',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row) {
                $("#filter_purchase_invoice").combobox({
                    url: '<?php echo base_url('finance/report_ap/readPi/'); ?>' + row.id,
                    valueField: 'number',
                    textField: 'number',
                    prompt: 'Choose Purchase Invoice',
                    icons: [{
                        iconCls: 'icon-clear',
                        handler: function(e) {
                            $(e.data.target).combobox('clear').combobox('textbox').focus();
                        }
                    }],
                });
            }
        });

        $('#filter_currency').combobox({
            url: '<?php echo base_url('master/currencies/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Choose Currency',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
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

    function btnDetails(val, row) {
        if(row.status != null){
            var details = "viewDetails('" + row.source + "','"+row.document_no+"')";
            return '<a class="btn btn-primary w-100" onClick="' + details + '" style="pointer-events: visible; opacity:1;"><i class="fa fa-eye"></i></a>';
        }
    }

    function viewDetails(source, number) {
        $("#dlg_detail").window('open');
        $("#dlg_detail").window('setTitle', "Detail of " + number);

        $('#dg3').datagrid({
            url: '<?= base_url('finance/report_ap_v2/datatableDetails?source=') ?>' + btoa(source) + "&number=" + btoa(number),
            pagination: false,
            rownumbers: true,
            remoteFilter: true,
            fit: true
        }).datagrid('enableFilter');
    }

    function numberformat(value, row) {
        if (value) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 0
            });

            if(value >= 0){
                return "<b>" + formatter.format(value) + "</b>";
            }else{
                return "<b style='color:red;'>" + formatter.format(value) + "</b>";
            }
        }else{
            return "<b>0</b>";
        }
    }

    function numberStyle(value, row, index) {
        if (value <= 0) {
            return 'background-color:#FFC8C8;';
        } else {
            return 'background-color:#C8FFCC;';
        }
    }

    function formatStatus(value, row) {
        if(row.status != null){
            if (value == 0) {
                return "<b style='color:green;'>OPEN</b>";
            } else {
                return "<b style='color:red;'>CLOSE</b>";
            }
        }
    }

    function styleStatus(value, row, index) {
        if(row.status != null){
            if (value == 0) {
                return 'background-color:#C8FFCC;';
            } else {
                return 'background-color:#FFC8C8;';
            }
        }
    }
</script>