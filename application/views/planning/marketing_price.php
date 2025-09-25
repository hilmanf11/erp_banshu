<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <!-- <th data-options="field:'detail',width:80,halign:'center',formatter:BtnDetail">History</th> -->
            <th data-options="field:'plant',width:50,halign:'center'">Plant</th>
            <th data-options="field:'supplier_id',width:80,halign:'center'">Supplier ID</th>
            <!-- <th rowspan="2" data-options="field:'supplier_po',width:150,halign:'center'">Supplier PO</th> -->
            <th data-options="field:'supplier_name',width:250,halign:'center'">Supplier Name</th>
            <th data-options="field:'item_rm_id',width:120,halign:'center'">Original Part No</th>
            <th data-options="field:'item_rm_number',width:200,halign:'center'">Part No BEI</th>
            <th data-options="field:'item_supplier',width:150,halign:'center'">Part No Supplier</th>
            <th data-options="field:'item_rm_name',width:150,halign:'center'">Description</th>
            <th data-options="field:'type',width:100,align:'center'">Supplier Type</th>
            <!-- <th rowspan="2" data-options="field:'origin',width:120,halign:'center'">Supplier Origin</th> -->
            <th data-options="field:'mpq',width:80,halign:'center',align:'right',formatter:numberformatComa">MPQ</th>
            <th data-options="field:'moq',width:80,halign:'center',align:'right',formatter:numberformatComa">MOQ</th>
            <th data-options="field:'leadtime',width:80,align:'center'">Leadtime</th>
            <th data-options="field:'maker_name',width:150,halign:'center'">Maker</th>
            <th data-options="field:'maker_type',width:100,halign:'center'">Maker Type</th>
            <th data-options="field:'currency',width:80,align:'center'">Currency</th>
            <th data-options="field:'price',width:80,halign:'center',align:'right',formatter:numberformat">Price</th>
            <th data-options="field:'assurance_price',width:100,halign:'center',align:'right',formatter:numberformat">Insurance</th>
            <th data-options="field:'transportation_price',width:100,halign:'center',align:'right',formatter:numberformat">Transportation</th>
            <th data-options="field:'import_duty_percentage',width:100,halign:'center',align:'right',formatter:numberformatComa">Import Duty %</th>
            <th data-options="field:'import_duty_price',width:100,halign:'center',align:'right',formatter:numberformat">Import Duty</th>
            <th data-options="field:'total_price',width:100,halign:'center',align:'right',formatter:numberformat">Total Price</th>
            <!-- <th rowspan="2" data-options="field:'item_family_name',width:120,halign:'center'">Product Family</th> -->
            <!-- <th rowspan="2" data-options="field:'coo_name',width:120,halign:'center'">COO Name</th> -->
            <!-- <th rowspan="2" data-options="field:'spq',width:80,halign:'center',align:'right',formatter:numberformatComa">SPQ</th> -->
            <!-- <th rowspan="2" data-options="field:'uom',width:80,align:'center'">UoM</th> -->
            <!-- <th rowspan="2" data-options="field:'start_date',width:100,align:'center'">Start Date</th>
            <th rowspan="2" data-options="field:'valid_date',width:100,align:'center'">Valid Date</th>
            <th rowspan="2" data-options="field:'attachment',width:100,halign:'center',formatter:BtnAttachment">Attachment</th>
            <th rowspan="2" data-options="field:'attachment2',width:100,halign:'center',formatter:BtnAttachment2">Attachment 2</th>
            <th rowspan="2" data-options="field:'attachment3',width:100,halign:'center',formatter:BtnAttachment3">Attachment 3</th>
            <th rowspan="2" data-options="field:'landed_cost',width:80,align:'center'">Landed<br>Cost</th>
            <th rowspan="2" data-options="field:'import_duty',width:80,align:'center'">Import<br>Duty</th> -->
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 230px; padding:10px;">
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" name="filter_plant" id="filter_plant" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier</span>
                    <input style="width:60%;" name="filter_suppliers" id="filter_suppliers" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Supplier Type</span>
                    <select style="width:60%;" id="filter_type" class="easyui-combobox" panelHeight="auto">
                        <option value="">ALL</option>
                        <option value="LOCAL">LOCAL</option>
                        <option value="IMPORT">IMPORT</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Maker</span>
                    <input style="width:60%;" name="filter_maker" id="filter_maker" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_category" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" name="filter_item_rm" id="filter_item_rm" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 30%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part Supplier</span>
                    <input style="width:60%;" name="filter_item_vendor" id="filter_item_vendor" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Share Order</span>
                    <input style="width:60%;" name="filter_share_order" id="filter_share_order" class="easyui-combobox">
                </div>
                <div class="fitem" style="margin-bottom: 5px;" hidden>
                    <span style="width:35%; display:inline-block;">Approval</span>
                    <select style="width:60%;" name="filter_status_price" id="filter_status_price" panelHeight="auto" class="easyui-combobox">
                        <option value="">ALL</option>
                        <option value="Approved">Approved</option>
                        <option value="Checking">Checking</option>
                    </select>
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Double Item</span>
                    <input name="filter_check" id="filter_check" class="easyui-checkbox" value="1">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" style="width: 100%;" hidden></iframe>
<script>

    //FILTER DATA
    function filter() {
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_suppliers = $("#filter_suppliers").combogrid('getValue');
        var filter_maker = $("#filter_maker").combogrid('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_item_vendor = $("#filter_item_vendor").combobox('getValue');
        var filter_share_order = $("#filter_share_order").combobox('getValue');
        var filter_status_price = $("#filter_status_price").combobox('getValue');
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_check = $("#filter_check").checkbox('options');

        var url = "?filter_suppliers=" + window.btoa(filter_suppliers) +
            "&filter_item_rm=" + window.btoa(filter_item_rm) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_maker=" + window.btoa(filter_maker) +
            "&filter_item_vendor=" + window.btoa(filter_item_vendor) +
            "&filter_share_order=" + window.btoa(filter_share_order) +
            "&filter_type=" + window.btoa(filter_type) +
            "&filter_status_price=" + window.btoa(filter_status_price) +
            "&filter_check=" + filter_check.checked;

        $('#dg').datagrid({
            url: '<?= base_url('planning/marketing_price/datatables') ?>' + url,
            pagination: true,
            clientPaging: false,
            remoteFilter: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
        });

        //$("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        //$("#printout").attr('src', '<?= base_url('planning/marketing_price/print') ?>' + url);
    }

    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_category = $("#filter_category").combobox('getValue');
        var filter_plant = $("#filter_plant").combobox('getValue');
        var filter_suppliers = $("#filter_suppliers").combogrid('getValue');
        var filter_maker = $("#filter_maker").combogrid('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_item_vendor = $("#filter_item_vendor").combobox('getValue');
        var filter_share_order = $("#filter_share_order").combobox('getValue');
        var filter_status_price = $("#filter_status_price").combobox('getValue');
        var filter_type = $("#filter_type").combobox('getValue');
        var filter_check = $("#filter_check").checkbox('options');

        var url = "?filter_suppliers=" + window.btoa(filter_suppliers) +
            "&filter_item_rm=" + window.btoa(filter_item_rm) +
            "&filter_plant=" + window.btoa(filter_plant) +
            "&filter_category=" + window.btoa(filter_category) +
            "&filter_maker=" + window.btoa(filter_maker) +
            "&filter_item_vendor=" + window.btoa(filter_item_vendor) +
            "&filter_share_order=" + window.btoa(filter_share_order) +
            "&filter_type=" + window.btoa(filter_type) +
            "&filter_status_price=" + window.btoa(filter_status_price) +
            "&filter_check=" + filter_check.checked;

        window.location.assign('<?= base_url('planning/marketing_price/print/excel') ?>' + url);
    }
    //RELOAD
    function reload() {
        window.location.reload();
    }

    function numberformat(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 4
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function numberformatComa(value, row) {
        if (value != null) {
            const formatter = new Intl.NumberFormat('ja-JP', {
                minimumFractionDigits: 0
            });
            return "<b>" + formatter.format(value) + "</b>";
        }
    }

    function cellStylerApproval(value, row, index) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'background: #53D636; color:white;';
        } else {
            return 'background: #FF5F5F; color:white;';
        }
    }

    function cellFormatterApproval(val, row) {
        if (row.approved_to == "" || row.approved_to == null) {
            return 'Approved';
        } else {
            return 'Checking';
        }
    };

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

    $(function() {
        //SETTING DATAGRID EASYUI
        filter();

        
        $('#maker_id').combogrid({
            url: '<?php echo base_url('master/makers/reads'); ?>',
            panelWidth: 450,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Maker",
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 200
                }, {
                    field: 'type',
                    title: 'Type',
                    width: 100
                }]
            ]
        });

        //Get Supplier
        $('#filter_suppliers').combogrid({
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
                    title: 'Supplier ID',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 250
                }, ]
            ]
        });

        $('#filter_maker').combogrid({
            url: '<?php echo base_url('master/makers/reads'); ?>',
            panelWidth: 450,
            idField: 'number',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Maker",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Code',
                    width: 100
                }, {
                    field: 'name',
                    title: 'Name',
                    width: 200
                }, {
                    field: 'type',
                    title: 'Type',
                    width: 100
                }]
            ]
        });

        $('#filter_item_vendor').combobox({
            url: '<?php echo base_url('planning/marketing_price/readVendor'); ?>',
            valueField: 'item_supplier',
            textField: 'item_supplier',
            prompt: 'Select Part Supplier',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_share_order').combobox({
            url: '<?php echo base_url('planning/marketing_price/readShareOrder'); ?>',
            valueField: 'share_order',
            textField: 'share_order',
            prompt: 'Select Share Order',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_category').combobox({
            url: '<?php echo base_url('master/item_familys/reads/C01'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Product Family',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row) {
                $('#filter_item_rm').combogrid({
                    url: '<?= base_url('master/item_rm/reads/') ?>' + row.id,
                    panelWidth: 420,
                    idField: 'id',
                    textField: 'number',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: "Select Part",
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
                        }, ]
                    ]
                });
            }
        });

        //Get Part
        $('#supplier_id').combogrid({
            url: '<?= base_url('master/suppliers/reads') ?>',
            panelWidth: 700,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Supplier",
            columns: [
                [{
                    field: 'id',
                    title: 'Supplier No',
                    width: 80
                }, {
                    field: 'name',
                    title: 'Supplier Name',
                    width: 250
                }, {
                    field: 'type',
                    title: 'Type',
                    width: 100
                }, {
                    field: 'origin',
                    title: 'Origin',
                    width: 150
                }, ]
            ],
            onSelect: function(val, row) {
                $("#supplier_po").textbox("setValue", row.supplier_po);
                $("#currency").textbox("setValue", row.currency);
                //if(row.commodity == "NO"){
                //$("#valid_date").datebox("disable");
                //}else{
                //$("#valid_date").datebox("enable");
                //}
            }
        });

        $('#item_rm_id').combogrid({
            url: '<?= base_url('master/item_rm/reads/') ?>',
            panelWidth: 550,
            idField: 'id',
            textField: 'number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Part No",
            columns: [
                [{
                    field: 'id',
                    title: 'Original Part No',
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
            onSelect: function(valMaker, rowMaker) {
                $("#maker_id").combogrid('setValue', rowMaker.maker_id);
            }
        });

        $('#account_number').combogrid({
            url: '<?= base_url('finance/account_coa/reads'); ?>',
            panelWidth: 300,
            idField: 'account_number',
            textField: 'account_number',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose COA Number",
            columns: [
                [{
                    field: 'account_number',
                    title: 'COA Number',
                    width: 100
                }, {
                    field: 'account_name',
                    title: 'COA Name',
                    width: 200
                }]
            ],
            onSelect: function(val, coa) {
                $("#account_name").textbox('setValue', coa.account_name);
            }
        });

        $('#currency').combobox({
            url: '<?php echo base_url('master/currencies/reads'); ?>',
            valueField: 'name',
            textField: 'name',
            prompt: 'Select Currency',
        });

        $('#plant').combobox({
            url: '<?php echo base_url('master/plants/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Plant',
        });

        $('#filter_plant').combobox({
            url: '<?php echo base_url('master/plants/reads'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Plant',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });

        $('#filter_item_rm').combogrid({
            url: '<?= base_url('master/item_rm/reads'); ?>',
            panelWidth: 350,
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
                    width: 100
                }, ]
            ]
        });
    });
</script>