<div class="easyui-accordion" style="width:100%;">
    <div title="Hide Filter" data-options="selected:true" style="padding:10px; background:#F4F4F4;">
        <fieldset style="width: 99%; border:2px solid #d0d0d0; margin-bottom: 5px; margin-top: 5px; border-radius:4px;">
            <legend><b>Form Filter Data</b></legend>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Period</span>
                    <input style="width:30%;" id="period_month" class="easyui-combobox">
                    <input style="width:30%;" id="period_year" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Revision</span>
                    <input style="width:60%;" id="revision" class="easyui-combobox">
                </div>
                <div>
                    <span style="width:35%; display:inline-block;"></span>
                    <a href="javascript:;" class="easyui-linkbutton" onclick="filter()"><i class="fa fa-search"></i> Filter Data</a>
                </div>
            </div>
            <div style="width: 30%; float:left;">
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Plant</span>
                    <input style="width:60%;" id="plant" class="easyui-combobox">
                </div>
                <div class="fitem">
                    <span style="width:35%; display:inline-block;">Product Family</span>
                    <input style="width:60%;" id="prodfam" class="easyui-combobox">
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
    $(function() {
        $('#period_month').combobox({
            prompt:'Month',
            valueField: 'id',
            textField: 'name',
            data: <?= json_encode($month) ?>
        });

        $('#period_year').combobox({
            prompt:'Year',
            valueField: 'id',
            textField: 'name',
            data: <?= json_encode($year) ?>
        });

        $('#revision').combobox({
            prompt:'Revision',
            valueField: 'id',
            textField: 'name',
            data: <?= json_encode($revision) ?>
        });

        $('#plant').combobox({
            prompt:'Plant',
            valueField: 'number',
            textField: 'name',
            data: <?= json_encode($plant) ?>
        });

        $('#prodfam').combobox({
            prompt:'Product Family',
            valueField: 'number',
            textField: 'name',
            multiple: true,
            data: <?= json_encode($prodfam) ?>
        });
    });

    function reload() { window.location.reload(); }

    function pdf() { $("#printout").get(0).contentWindow.print(); }

    function filter() { display(); }

    function excel() { display('excel'); }

    function display(act = 'filter') {
        var period_month = $('#period_month').combobox('getValue');
        var period_year = $('#period_year').combobox('getValue');
        var revision = $('#revision').combobox('getValue');
        var plant = $('#plant').combobox('getValue');
        var prodfam = $('#prodfam').combobox('getValues');

        if ([period_month, period_year, revision, plant].includes(''))
            { toastr.warning("Please select filter month, year, revision and plant", "Information"); }
        else {
            var url = [
                'month='+btoa(period_month),
                'year='+btoa(period_year),
                'revision='+btoa(revision),
                'plant='+btoa(plant),
                'prodfam='+btoa(JSON.stringify(prodfam))
            ].join('&');

            if (act == 'excel') {
                window.location.assign('<?= base_url('planning/resume_material_shortages/excel?') ?>' + url);
            } else {
                $("#printout").contents().find('html').html("<center><br><br><br><b style='font-size:20px;'>Please Wait...</b></center>");
                $("#printout").attr('src', '<?= base_url('planning/resume_material_shortages/print?') ?>' + url);
            }
        }
    }
</script>