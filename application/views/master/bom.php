<!-- TABLE DATAGRID -->
<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'bom_par_item',width:250,halign:'center'">Assy No</th>
            <th data-options="field:'item_name',width:300,halign:'center'">Assy Name</th>
            <th data-options="field:'customer_name',width:350,halign:'center'">Customer Name</th>
            <th data-options="field:'um_name',width:80,halign:'center'">Uom</th>
            <th data-options="field:'total_part',width:100,halign:'center'">Total Part</th>
            <!-- <th data-options="field:'bom_user',width:120,halign:'center'">Created By</th> -->
            <!-- <th data-options="field:'bom_date',width:150,halign:'center'">Created Date</th> -->
        </tr>
    </thead>
</table>

<!-- TOOLBAR DATAGRID -->
<div id="toolbar" style="height: 160px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="filter_customer" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_id" class="easyui-combogrid">
                </div>
            </div>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_part_id" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
        <!-- <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="openDlgCompare()"><i class="fa fa-retweet"></i> Compare Bom</a>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="openDlgCopy()"><i class="fa fa-copy"></i> Copy Bom</a>
        <a href="javascript:;" class="easyui-linkbutton" data-options="plain:true" onclick="syncPullMrp()"><i class="fa fa-retweet"></i> Synchronize</a> -->
    </div>
</div>

<!-- PDF -->
<iframe id="printout" style="width: 100%;" hidden></iframe>

<script>
    //FILTER DATA
    function filter() {
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_part_id = $("#filter_part_id").combogrid('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');

        var url = "?filter_item_id=" + window.btoa(filter_item_id) +
            "&filter_part_id=" + window.btoa(filter_part_id) +
            "&filter_customer=" + window.btoa(filter_customer);

        $('#dg').datagrid({
            url: '<?= base_url('master/bom/datatables') ?>' + url
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('master/bom/print') ?>' + url);
    }
    
    //PRINT PDF
    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    //PRINT EXCEL
    function excel() {
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_part_id = $("#filter_part_id").combogrid('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');

        var url = "?filter_item_id=" + window.btoa(filter_item_id) +
            "&filter_part_id=" + window.btoa(filter_part_id) +
            "&filter_customer=" + window.btoa(filter_customer);

        window.location.assign('<?= base_url('master/bom/print/excel') ?>' + url);
    }

    //RELOAD
    function reload() {
        window.location.reload();
    }

    $(function() {
        //SETTING DATAGRID EASYUI
        $('#dg').datagrid({
            url: '<?= base_url('master/bom/datatables') ?>',
            pagination: true,
            rownumbers: true,
            fit: true,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            view: detailview,
            detailFormatter: function(index, row) {
                return '<div style="padding:2px;position:relative;"><table class="ddv" title="Detail Of ' + row.item_id + '"></table></div>';
            },
            onExpandRow: function(index, row) {
                var ddv = $(this).datagrid('getRowDetail', index).find('table.ddv');
                var filter_part_id = $("#filter_part_id").combogrid('getValue');

                ddv.datagrid({
                    url: '<?= base_url('master/bom/datatableDetails?item_id=') ?>' + window.btoa(row.bom_par_item) + "&filter_part_id=" + window.btoa(filter_part_id),
                    singleSelect: true,
                    rownumbers: true,
                    columns: [
                        [{
                            field: 'bom_com_item',
                            title: 'Part No',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'item_name',
                            title: 'Part Name',
                            halign: 'center',
                            width: 200
                        }, {
                            field: 'bom_qty_perassy',
                            title: 'Qty',
                            halign: 'center',
                            width: 100
                        }, {
                            field: 'bom_effdate',
                            title: 'Eff Date',
                            halign: 'center',
                            width: 120
                        }, {
                            field: 'bom_maker',
                            title: 'Maker',
                            halign: 'center',
                            width: 150
                        }, {
                            field: 'um_name',
                            title: 'UoM',
                            align: 'center',
                            width: 80
                        }, {
                            field: 'bom_routid',
                            title: 'Rout ID',
                            width: 80,
                            halign: 'center',
                            align: 'right',
                        }, {
                            field: 'bom_user',
                            title: 'Create By',
                            width: 120,
                            halign: 'center',
                        }, {
                            field: 'bom_date',
                            title: 'Create Date',
                            width: 150,
                            halign: 'center',
                        }]
                    ],
                    onResize: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    },
                    onLoadSuccess: function() {
                        setTimeout(function() {
                            $('#dg').datagrid('fixDetailRowHeight', index);
                        }, 0);
                    },
                    rowStyler: function(index, row) {
                        if (row.remove == 1) {
                            return 'background-color:#e5b5b5;font-weight:bold;';
                        }
                    }
                });
                $('#dg').datagrid('fixDetailRowHeight', index);
            }
        });

        $('#filter_item_id').combogrid({
            url: '<?= base_url('master/bom/readProduct'); ?>',
            panelWidth: 300,
            idField: 'item_id',
            textField: 'item_id',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Product No",
            columns: [
                [{
                    field: 'item_id',
                    title: 'Part No',
                    width: 150
                }, {
                    field: 'item_name',
                    title: 'Part Name',
                    width: 150
                }]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });

        $('#filter_part_id').combogrid({
            url: '<?= base_url('master/bom/readPart'); ?>',
            panelWidth: 300,
            idField: 'item_id',
            textField: 'item_id',
            mode: 'remote',
            fitColumns: true,
            prompt: "Choose Part No",
            columns: [
                [{
                    field: 'item_id',
                    title: 'Part No',
                    width: 150
                }, {
                    field: 'item_name',
                    title: 'Part Name',
                    width: 150
                }]
            ],
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
        });
        
        $('#filter_customer').combobox({
            url: '<?php echo base_url('master/bom/readCustomer'); ?>',
            valueField: 'cust_id',
            textField: 'cust_name',
            prompt: 'Select Customer',
        });
    });
</script>