<div id="f" class="easyui-panel" style="width:99.5%; background: #F4F4F4;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; margin-left: 10px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width:35%; float: left;">
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
            <div style="width: 30%; float: left;">
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
            <div style="width: 30%; float: left;">
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
    </div>
    <div style="margin-left: 10px; margin-bottom:5px;">
        <?= $button ?>
    </div>
</div>

<div id="p" class="easyui-panel" title="Print Preview" style="width:100%;">
    <iframe id="printout" src="" style="width: 100%; height: 450px; border: 0;"></iframe>
</div>

<script>
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

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date & Supplier!");
        } else {
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('purchase/report_delivery_confirmation/print') ?>' + url);
        }
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

        if (filter_from == "" || filter_to == "") {
            toastr.warning("Please select Trans Date & Supplier!");
        } else {
            window.location.assign('<?= base_url('purchase/report_delivery_confirmation/print/excel') ?>' + url);
        }
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function reload() {
        window.location.reload();
    }

    $(function() {
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
</script>