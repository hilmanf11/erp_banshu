<table id="dg" class="easyui-treegrid" style="width:99.5%;" toolbar="#toolbar">
    <thead>
        <tr>
            <th rowspan="2" field="ck" checkbox="true"></th>
            <th rowspan="2" data-options="field:'number',width:150,halign:'center'">Asset Code</th>
            <th rowspan="2" data-options="field:'name',width:200,halign:'center'">Asset Name</th>
            <th rowspan="2" data-options="field:'description',width:300,halign:'center'">Description</th>
            <th rowspan="2" data-options="field:'request_no',width:150,halign:'center'">Purchase Request</th>
            <th rowspan="2" data-options="field:'status',width:100, align:'center', styler:cellStyler, formatter:cellFormatter">Status</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Created</th>
            <th colspan="2" data-options="field:'',width:100,halign:'center'"> Updated</th>
        </tr>
        <tr>
            <th data-options="field:'created_by',width:100,align:'center'"> By</th>
            <th data-options="field:'created_date',width:150,align:'center'"> Date</th>
            <th data-options="field:'updated_by',width:100,align:'center'"> By</th>
            <th data-options="field:'updated_date',width:150,align:'center'"> Date</th>
        </tr>
    </thead>
</table>

<div id="toolbar" style="height: 200px; padding:10px;">
    <!-- <div style="width: 100%; display: grid; grid-template-columns: auto auto auto; grid-gap: 5px; display: flex;"> -->
    <div style="width: 100%;">
        <fieldset style="width: 70%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="float: left; width: 50%;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="filter_item_familys" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Part No</span>
                    <input style="width:60%;" id="filter_item_rm" class="easyui-combogrid">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
        </fieldset>
        <?= $button ?>
    </div>
</div>

<!-- DIALOG SAVE AND UPDATE -->
<div id="dlg_insert" class="easyui-dialog" title="Add New" data-options="closed: true,modal:true" style="width: 400px; padding:10px; top: 20px;">
    <form id="frm_insert" method="post" novalidate>
        <fieldset style="width:100%; border:1px solid #d0d0d0; margin-bottom: 10px; border-radius:4px; float: left;">
            <legend><b>Form Data</b></legend>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Product Family</span>
                <input style="width:60%;" name="item_family_id" id="item_family_id" class="easyui-combogrid" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Asset Code</span>
                <input style="width:60%;" name="number" id="number" readonly required="" class="easyui-textbox">
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Asset Name</span>
                <input style="width:60%;" name="item_rm_id" id="item_rm_id" class="easyui-combogrid" required>
            </div>
            <div class="fitem">
                <span style="width:35%; display:inline-block;">Description</span>
                <input style="width:60%; height: 80px;" multiline="true" name="description" class="easyui-textbox">
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
    <div id="p_upload" class="easyui-progressbar" style="width:100%; margin-top: 10px;"></div>
    <center><b id="p_start">0</b> Of <b id="p_finish">0</b></center>
    <div id="p_remarks" title="History Upload" class="easyui-panel" style="width:100%; height:200px; padding:10px; margin-top: 10px;">
        <ul id="remarks">
        </ul>
    </div>
</div>

<!-- PDF -->
<iframe id="printout" src="<?= base_url('assets/assets/print') ?>" style="width: 100%;" hidden></iframe>
<script>
    //Add Data
    function add() {
        $('#dlg_insert').dialog('open');
        url_save = '<?= base_url('assets/assets/create') ?>';
        $('#frm_insert').form('clear');
        $("#item_family_id").combogrid('enable');
        $("#number").textbox('enable');
        $("#item_rm_id").combogrid('enable');
    }

    //EDIT DATA
    function update() {
        var row = $('#dg').treegrid('getSelected');
        if (row) {
            if(row.datatable == 1){
                toastr.error("Please Select Detail in the table first!");
            }else{
                $('#dlg_insert').dialog('open');
                $('#frm_insert').form('load', row);
                $("#item_family_id").combogrid('disable');
                $("#number").textbox('disable');
                $("#item_rm_id").combogrid('disable');

                url_save = '<?= base_url('assets/assets/update') ?>?id=' + btoa(row.id);
            }
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    function number(item_family_number){
        $.ajax({
            type: "post",
            url: "<?= base_url("assets/assets/autoid") ?>",
            data: {item_family_number: item_family_number},
            dataType: "json",
            success: function (response) {
                $("#number").textbox('setValue', response.code);
            }
        });
    }

    //Delete Data
    function deleted() {
        var rows = $('#dg').treegrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('Warning', 'Are you sure you want to delete this data?', function(r) {
                if (r) {
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        if (row.state == "closed") {
                            toastr.error("Please Select Detail of Asset Code <br>" + row.id);
                        } else if (row.status == "1") {
                            toastr.error("Status False Cannot Delete this data");
                        } else {
                            $.ajax({
                                method: 'post',
                                url: '<?= base_url('assets/assets/delete') ?>',
                                data: {
                                    id: row.id
                                },
                                success: function(result) {
                                    var result = eval('(' + result + ')');
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    toastr.error(jqXHR.statusText);
                                    $.messager.alert("Error", jqXHR.statusText, 'error');
                                },
                                complete: function(data) {
                                    $('#dg').treegrid('reload');
                                }
                            });
                        }
                    }
                }
            });
        } else {
            toastr.warning("Please select one of the data in the table first!", "Information");
        }
    }

    //Upload Data
    function upload() {
        $('#dlg_upload').dialog('open');
    }

    function download_excel() {
        window.location.assign('<?= base_url('template/tmp_assets.xls') ?>');
    }

    function filter() {
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_item_familys = $("#filter_item_familys").combogrid('getValue');

        url = "?filter_item_familys=" + filter_item_familys + "&filter_item_rm=" + filter_item_rm;
        
        $('#dg').treegrid({
            url: '<?= base_url('assets/assets/datatables') ?>' + url,
            pagination: true,
            rownumbers: true,
            idField: 'id',
            treeField: 'number',
            fit: true,
            singleSelect: false,
            pageList: [20, 50, 100, 500, 1000],
            pageSize: 20,
            onBeforeLoad: function(row, param) {
                if (!row) {
                    param.id = 0;
                }
            },
        });

        $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
        $("#printout").attr('src', '<?= base_url('assets/assets/print') ?>' + url);
    }

    function pdf() {
        $("#printout").get(0).contentWindow.print();
    }

    function excel() {
        var filter_item_rm = $("#filter_item_rm").combogrid('getValue');
        var filter_item_familys = $("#filter_item_familys").combogrid('getValue');

        url = "?filter_item_familys=" + filter_item_familys + "&filter_item_rm=" + filter_item_rm;
        window.location.assign('<?= base_url('assets/assets/print/excel') ?>' + url);
    }

    function reload() {
        window.location.reload();
    }

    //CELLSTYLE STATUS
    function cellStyler(value, row, index) {
        if(row.datatable != 1){
            if (value == 0) {
                return 'background: #53D636; color:white;';
            } else {
                return 'background: #FF5F5F; color:white;';
            }
        }
    }
    //FORMATTER STATUS
    function cellFormatter(value, row) {
        if(row.datatable != 1){
            if (value == 0) {
                return 'TRUE';
            } else {
                return 'FALSE';
            }
        }
    };

    $(function() {
        filter();

        //Save Data
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

                            $('#dg').treegrid('reload');
                            Swal.close();
                        }
                    });
                }
            }]
        });

        //Upload Data
        $('#dlg_upload').dialog({
            buttons: [{
                text: 'List Failed',
                handler: function() {
                    window.open('<?= base_url('assets/assets/uploadDownloadFailed') ?>', '_blank');
                }
            }, {
                text: 'Upload',
                iconCls: 'icon-ok',
                handler: function() {
                    $('#frm_upload').form('submit', {
                        url: '<?= base_url('assets/assets/upload') ?>',
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
                                url: "<?= base_url('assets/assets/uploadclearFailed') ?>"
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
                                        url: "<?= base_url('assets/assets/uploadCreate') ?>",
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
                                                    url: "<?= base_url('assets/assets/uploadcreateFailed') ?>",
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

        $('#item_family_id').combogrid({
            url: '<?= base_url('master/item_familys/reads/C06') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product Family",
            columns: [
                [{
                    field: 'number',
                    title: 'Product Family ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Product Family Name',
                    width: 250
                }, ]
            ],
            onSelect: function(val, row){
                number(row.number);
                $('#item_rm_id').combogrid({
                    url: '<?= base_url('master/item_rm/readNotRm/') ?>' + row.id,
                    panelWidth: 600,
                    idField: 'id',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: 'Choose Part No',
                    // multiple: true,
                    columns: [
                        [{
                            field: 'id',
                            title: 'Part No',
                            width: 100
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
                });
            }
        });

        //Get Customer
        $('#filter_item_familys').combogrid({
            url: '<?= base_url('master/item_familys/reads/C06') ?>',
            panelWidth: 420,
            idField: 'id',
            textField: 'name',
            mode: 'remote',
            fitColumns: true,
            prompt: "Select Product Family",
            icons: [{
                iconCls: 'icon-clear',
                handler: function(e) {
                    $(e.data.target).combogrid('clear').combogrid('textbox').focus();
                }
            }],
            columns: [
                [{
                    field: 'number',
                    title: 'Product Family ID',
                    width: 120
                }, {
                    field: 'name',
                    title: 'Product Family Name',
                    width: 250
                }, ]
            ],
            onSelect: function(val, row){
                $('#filter_item_rm').combogrid({
                    url: '<?= base_url('master/item_rm/readNotRm/') ?>' + row.id,
                    panelWidth: 600,
                    idField: 'id',
                    mode: 'remote',
                    fitColumns: true,
                    prompt: 'Choose Part No',
                    columns: [
                        [{
                            field: 'id',
                            title: 'Part No',
                            width: 100
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
                });
            }
        });
    });
</script>