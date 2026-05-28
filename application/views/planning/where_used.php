<div class="easyui-accordion" style="width:100%;">
    <div title="Hide Filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Product No</span>
                    <input style="width:60%;" id="filter_item_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_part_id" name="filter_part_id" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 50%; float:left;">
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Maker</span>
                    <input style="width:60%;" id="filter_maker" name="filter_maker" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:30%; display:inline-block;">Customer</span>
                    <input style="width:60%;" id="filter_customer" class="easyui-combobox">
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;" data-options="fit:true">
    <iframe id="printout" src="" style="width: 100%; height: 95%; border: 0;"></iframe>
</div>

<script>
    function reload() {
        window.location.reload();
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function filter() {
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_part_id = $("#filter_part_id").combogrid('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_maker = $("#filter_maker").combobox('getValue');

        var url = "?filter_item_id=" + btoa(filter_item_id) + "&filter_part_id=" + btoa(filter_part_id) + "&filter_maker=" + btoa(filter_maker) + "&filter_customer=" + btoa(filter_customer);

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('planning/where_used/print') ?>' + url);
    }

    function excel() {
        var filter_item_id = $("#filter_item_id").combogrid('getValue');
        var filter_part_id = $("#filter_part_id").combogrid('getValue');
        var filter_customer = $("#filter_customer").combobox('getValue');
        var filter_maker = $("#filter_maker").combobox('getValue');

        var url = "?filter_item_id=" + btoa(filter_item_id) + "&filter_part_id=" + btoa(filter_part_id) + "&filter_maker=" + btoa(filter_maker) + "&filter_customer=" + btoa(filter_customer);

        window.location.assign('<?= base_url('planning/where_used/print/excel') ?>' + url);
    }

    $(function() {
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

        //Filter Cutoff
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

        $('#filter_maker').combobox({
            url: '<?php echo base_url('master/bom/readMaker'); ?>',
            valueField: 'name',
            textField: 'name',
            prompt: 'Select Maker',
        });

        $('#filter_customer').combobox({
            url: '<?php echo base_url('master/bom/readCustomer'); ?>',
            valueField: 'cust_id',
            textField: 'cust_name',
            prompt: 'Select Customer',
        });
    });
</script>