<table id="dg" class="easyui-datagrid" style="width:100%;" toolbar="#toolbar"></table>
<div id="toolbar" style="height: 200px; padding: 10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <fieldset style="width: 100%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
        <legend><b>Form Filter Data</b></legend>
        <div style="width: 50%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Receipt Date</span>
                <input style="width:28%;" id="filter_from" class="easyui-datebox" value="<?= date("Y-m-01") ?>" data-options="formatter:myformatter,parser:myparser, editable:false"> To
                <input style="width:28%;" id="filter_to" class="easyui-datebox" value="<?= date("Y-m-t") ?>" data-options="formatter:myformatter,parser:myparser, editable:false">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" id="filter_item_family" class="easyui-combobox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;"></span>
                <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
            </div>
        </div>
        <div style="width: 49%; float:left;">
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Part No</span>
                <input style="width:60%;" id="filter_item_rm" class="easyui-combogrid">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Report Display</span>
                <select style="width:60%;" id="filter_display" class="easyui-combobox" panelHeight="auto">
                    <option value="RECAP">RECAP</option>
                    <option value="DETAIL">DETAIL</option>
                </select>
            </div>
            <div class="fitem" hidden>
                <span style="width:35%; display:inline-block;">Trans Type</span>
                <input style="width:60%;" id="filter_transtype" class="easyui-combobox">
            </div>
        </div>
    </fieldset>
    <?= $button ?>
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

<div id="dlg_generate" class="easyui-dialog" title="Save Data" data-options="closed: true,modal:true,closable: false" style="width: 500px; padding:10px; top: 20px;">
    <div class="alert alert-warning" role="alert">
        Please wait until the save process is complete
    </div>
    <div id="p_upload" class="easyui-progressbar" style="width:460px; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" class="easyui-panel" style="width:460px; height:200px; padding:10px; margin-top: 10px;">
        <p>History Save Data</p>
        <ul id="remarks">

        </ul>
    </div>
</div>

<div class="easyui-panel" title="Print Preview" style="width:100%;padding:10px;">
    <iframe id="printout" src="" style="width: 100%; height:520px; border: 0;"></iframe>
</div>

<script>
    function add(){
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_transtype = $("#filter_transtype").combobox('getValue');

        $.ajax({
            type: "post",
            url: "<?= base_url('closing/locks/checkLock') ?>",
            data: "period=" + filter_from + "&menus_id=<?= $menus_id ?>",
            dataType: "json",
            success: function (lock) {
                if(lock.total > 0){
                    toastr.error("This period is not active by Accounting");
                    return false;
                }

                // if(filter_item_family != ""){
                    $.messager.confirm('Warning', 'Are you sure you want to save this data?', function(r) {
                        if (r) {
                            Swal.fire({
                                title: 'Please Wait for Save Inventory Data',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                },
                            });

                            $("#p_remarks").html("");

                            $.ajax({
                                type: "post",
                                url: '<?= base_url('finance/inventory_rm/getData') ?>',
                                data: {
                                    filter_from: filter_from,
                                    filter_to: filter_to,
                                    filter_item_family: filter_item_family,
                                    filter_item_rm: filter_item_rm,
                                },
                                dataType: "json",
                                success: function(data) {
                                    Swal.close();

                                    if(data.total > 0){
                                        requestData(data.total, data.rows);
                                        $('#dlg_generate').dialog('open');

                                        function requestData(total, json, jml = 1, value = 0) {
                                            if (value < 100) {
                                                value = Math.floor((jml / total) * 100);
                                                var i = (jml - 1);

                                                $('#p_upload').progressbar('setValue', value);
                                                $('#p_start').html(jml);
                                                $('#p_finish').html(data.total);

                                                $.ajax({
                                                    type: "post",
                                                    url: '<?= base_url('finance/inventory_rm/create') ?>',
                                                    data: {
                                                        data: json[i]
                                                    },
                                                    dataType: "json",
                                                    success: function(result) {
                                                        requestData(total, json, jml + 1, value);

                                                        if (result.theme == "success") {
                                                            var title = "<b style='color: green;'>" + result.title + "</b> | " + result.message;
                                                        } else {
                                                            var title = "<b style='color: red;'>" + result.title + "</b> | " + result.message;
                                                        }

                                                        $("#p_remarks").append(title + "<br>");

                                                        if (i == (data.total - 1)) {
                                                            $('#dlg_generate').dialog('close');

                                                            Swal.fire({
                                                                title: result.message,
                                                                icon: result.theme,
                                                                confirmButtonText: 'Ok',
                                                                allowOutsideClick: false,
                                                            }).then((result) => {
                                                                // if (result.isConfirmed) {
                                                                //     window.location.reload();
                                                                // }
                                                            });
                                                        }
                                                    }
                                                });
                                            }
                                        }
                                    }else{
                                        toastr.warning("Data not Found!");
                                    }
                                }
                            });
                        }
                    });
                // }else{
                //     toastr.warning("Please select Product Family!");
                // }
            }
        });
    }

    // UPLOAD DATA
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    // DOWNLOAD
    function download_excel() {
        window.location.assign('<?= base_url('finance/inventory_rm/downloadTemplate') ?>');
    }

    function reload() {
        window.location.reload();
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function filter() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_transtype = $("#filter_transtype").combobox('getValue');

        // if(filter_item_family != ""){
            url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_family=" + filter_item_family + "&filter_item_rm=" + filter_item_rm + "&filter_display=" + filter_display + "&filter_transtype=" + filter_transtype;
            $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
            $("#printout").attr('src', '<?= base_url('finance/inventory_rm/print') ?>' + url);
        // }else{
        //     toastr.warning("Please select Product Family!");
        // }
    }

    function excel() {
        var filter_from = $("#filter_from").datebox('getValue');
        var filter_to = $("#filter_to").datebox('getValue');
        var filter_item_family = $("#filter_item_family").combobox('getValue');
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_display = $("#filter_display").combobox('getValue');
        var filter_transtype = $("#filter_transtype").combobox('getValue');

        // if(filter_item_family != ""){
            url = "?filter_from=" + filter_from + "&filter_to=" + filter_to + "&filter_item_family=" + filter_item_family + "&filter_item_rm=" + filter_item_rm + "&filter_display=" + filter_display + "&filter_transtype=" + filter_transtype;
            window.location.assign('<?= base_url('finance/inventory_rm/print/excel') ?>' + url);
        // }else{
        //     toastr.warning("Please select Product Family!");
        // }
    }

    $(function() {
        $("#add").html("Save Inventory RM");
        
        // $("#filter_transtype").combobox({
        //     url: '<?= base_url('master/transaction_types/reads') ?>',
        //     valueField: 'number',
        //     textField: 'name',
        //     prompt: "Select Transaction Type",
        //     icons: [{
        //         iconCls: 'icon-clear',
        //         handler: function(e) {
        //             $(e.data.target).combobox('clear').combobox('textbox').focus();
        //         }
        //     }],
        // });

        $("#filter_item_family").combobox({
            url: '<?= base_url('master/item_familys/reads/C01') ?>',
            valueField: 'number',
            textField: 'name',
            prompt: "Select Product Family",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combobox('clear').combobox('textbox').focus();
                }
            }],
            onSelect: function(row) {
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
                            title: 'Part No EBWS',
                            width: 120
                        },{
                            field: 'number',
                            title: 'Part No Supplier',
                            width: 200
                        }, {
                            field: 'name',
                            title: 'Description',
                            width: 200
                        }, ]
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
                    title: 'Part No EBWS',
                    width: 120
                },{
                    field: 'number',
                    title: 'Part No Supplier',
                    width: 200
                }, {
                    field: 'name',
                    title: 'Description',
                    width: 200
                }, ]
            ]
        });

        // UPLOAD DATA
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('finance/inventory_rm/upload') ?>',
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
                                        url: "<?= base_url('finance/inventory_rm/uploadCreate') ?>",
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
</script>