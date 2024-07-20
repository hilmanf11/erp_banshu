<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar">
    <thead>
    <tr>
            <th field="ck" checkbox="true"></th>
            <th data-options="field:'customer',width:250,halign:'center'">Customer</th>
            <th data-options="field:'product_no',width:150,halign:'center'">Product No</th>
            <th data-options="field:'product_name',width:200,halign:'center'">Product Name</th>
            <th data-options="field:'revision_0',width:120,halign:'center',align:'right', formatter: numberformat">Rev 0 Prodplan</th>
            <th data-options="field:'revision_1',width:120,halign:'center',align:'right', formatter: numberformat">Rev 1 Prodplan</th>
            <th data-options="field:'revision_2',width:120,halign:'center',align:'right', formatter: numberformat">Rev 2 Prodplan</th>
            <th data-options="field:'total_mps',width:80,halign:'center',align:'right', formatter: numberformat">Total MPS</th>
            <th data-options="field:'total_mpp',width:80,halign:'center',align:'right', formatter: numberformat">Total MPP</th>
            <th data-options="field:'balance',width:80,halign:'center',align:'right', formatter: numberformat">Balance</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding:10px;">
    <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;">
        <fieldset style="width: 60%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Filter Data</b></legend>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period Cutoff</span>
                    <input style="width:30%;" name="filter_month" value="<?= date("m") ?>" id="filter_month" class="easyui-combobox" data-options="prompt:'Month'">
                    <input style="width:30%;" name="filter_year" value="<?= date("Y") ?>" id="filter_year" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Customer</span>
                    <input style="width:60%;" name="filter_customer_id" id="filter_customer_id" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float: left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product No</span>
                    <input style="width:60%;" name="filter_product_no" id="filter_product_no" class="easyui-combogrid">
                </div>
            </div>
        </fieldset>
    </div>
    <?= $button ?>
</div>

<!-- PDF -->
<iframe id="printout" src="" style="width: 100%;" hidden></iframe>

<script>
    function filter() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_product_no=" + window.btoa(filter_product_no);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            $('#dg').datagrid({
                url: '<?= base_url('planning/mps_vs_mpp/datatables') ?>' + url,
            });

            var month = numericToMonthName(filter_month);
            var header = $('#dg').datagrid('getPanel').find('.datagrid-view2 .datagrid-header');

            header.find('td[field="revision_0"] .datagrid-cell').html(month+'<br>Rev 0 Prodplan');
            header.find('td[field="revision_1"] .datagrid-cell').html(month+'<br>Rev 1 Prodplan');
            header.find('td[field="revision_2"] .datagrid-cell').html(month+'<br>Rev 2 Prodplan');

            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('planning/mps_vs_mpp/print') ?>' + url);
        }
    }
    
    function numericToMonthName(monthNumber) {
        var months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        var monthIndex = parseInt(monthNumber, 10) - 1;
        if (monthIndex < 0 || monthIndex > 11) {
            return "Invalid Month";
        }
        return months[monthIndex];
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_month = $("#filter_month").combobox('getValue');
        var filter_year = $("#filter_year").combobox('getValue');
        var filter_customer_id = $("#filter_customer_id").combobox('getValue');
        var filter_product_no = $("#filter_product_no").combogrid('getValue');

        var url = "?filter_month=" + window.btoa(filter_month) +
            "&filter_year=" + window.btoa(filter_year) +
            "&filter_customer_id=" + window.btoa(filter_customer_id) +
            "&filter_product_no=" + window.btoa(filter_product_no);

        if (filter_month == "" || filter_year == "") {
            toastr.warning("Please Choose Filter Date!", "Information");
        } else {
            window.location.assign('<?= base_url('planning/mps_vs_mpp/print/excel') ?>' + url);
        }
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_mps_vs_mpp.xls') ?>');
    }

    function reload() {
        window.location.reload();
    }

    function numberformat(value, row) {
        const formatter = new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0
        });

        return "<b>" + formatter.format(value) + "</b>";
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

        $('#filter_product_no').combogrid({
            url: '<?= base_url('planning/mst_data/readProducts') ?>',
            panelWidth: 400,
            idField: 'item_id',
            textField: 'item_id',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product No",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'item_id',
                    title: 'Item ID',
                    width: 200
                }, {
                    field: 'item_name',
                    title: 'Item Name',
                    width: 200
                }]
            ]
        });

        $('#filter_customer_id').combobox({
            url: '<?php echo base_url('planning/mst_data/readCustomers'); ?>',
            valueField: 'number',
            textField: 'name',
            prompt: 'Select Customer',
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
        });
    });
</script>