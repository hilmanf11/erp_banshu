<body style="overflow: hidden;">
    <div class="greeting" style="height: 70px; background-position: center; background-repeat: no-repeat; background-size: cover; background-color: black;">
        <div style="float: left;">
            <p style="font-size: 25px !important; margin:0;">DASHBOARD MONITORING PURCHASE REQUEST</p>
        </div>
    </div>
    <table id="dg" class="easyui-datagrid" style="width:100%; height: 400px;" toolbar="#toolbar">
        <thead>
            <tr>
                <th rowspan="2" data-options="field:'number',width:150,align:'center'">Product Family</th>
                <th colspan="17" data-options="field:'name',width:150,halign:'center'">Qty Purchase Requests </th>
            </tr>
            <tr>
                <th data-options="field:'jan',width:80,halign:'center'">Jan</th>
                <th data-options="field:'feb',width:80,halign:'center'">Feb</th>
                <th data-options="field:'mar',width:80,halign:'center'">Mar</th>
                <th data-options="field:'xbarq1',width:80,halign:'center',hstyler:xbarStyle">Xbar Q1</th>
                <th data-options="field:'apr',width:80,halign:'center'">Apr</th>
                <th data-options="field:'may',width:80,halign:'center'">May</th>
                <th data-options="field:'jun',width:80,halign:'center'">Jun</th>
                <th data-options="field:'xbarq2',width:80,halign:'center',hstyler:xbarStyle">Xbar Q2</th>
                <th data-options="field:'jul',width:80,halign:'center'">Jul</th>
                <th data-options="field:'aug',width:80,halign:'center'">Aug</th>
                <th data-options="field:'sep',width:80,halign:'center'">Sep</th>
                <th data-options="field:'xbarq3',width:80,halign:'center',hstyler:xbarStyle">Xbar Q3</th>
                <th data-options="field:'oct',width:80,halign:'center'">Oct</th>
                <th data-options="field:'nov',width:80,halign:'center'">Nov</th>
                <th data-options="field:'dec',width:80,halign:'center'">Dec</th>
                <th data-options="field:'xbarq4',width:80,halign:'center',hstyler:xbarStyle">Xbar Q4</th>
                <th data-options="field:'lastmonth',width:80,halign:'center'">%</th>
            </tr>
        </thead>
    </table>
    <div class="row" style="margin: 0;">
        <div class="col-12">
            <div id="pr_month"></div>
        </div>
    </div>
    
    <div id="toolbar">
        <div class="fitem">
            <span style="width:100px; display:inline-block;"> &nbsp; Period</span>
            <input style="width:100px;" name="filter_year" id="filter_year" value="<?= date("Y") ?>" class="easyui-combobox">
        </div>
    </div>
</body>
<script>
    $(function(){
        $('#filter_year').combobox({
            url: '<?php echo base_url('planning/mst_data/readYears'); ?>',
            valueField: 'id',
            textField: 'name',
            prompt: 'Select Year',
        });

        Highcharts.chart('pr_month', {
            chart: {
                zoomType: 'xy'
            },
            title: {
                text: 'Control and Monitoring Purchase and Used Materials'
            },
            xAxis: [{
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                crosshair: true
            }],
            yAxis: [{ // Sumbu Y pertama untuk Bar Chart
                labels: {
                    format: '{value}',
                    style: {
                        color: Highcharts.getOptions().colors[1]
                    }
                },
                title: {
                    text: 'Total Qty',
                    style: {
                        color: Highcharts.getOptions().colors[1]
                    }
                }
            }, { // Sumbu Y kedua untuk Line Chart (opsional)
                labels: {
                    format: '{value}',
                    style: {
                        color: Highcharts.getOptions().colors[0]
                    }
                },
                title: {
                    text: 'Total Qty',
                    style: {
                        color: Highcharts.getOptions().colors[0]
                    }
                },
                opposite: true
            }],
            tooltip: {
                shared: true
            },
            legend: {
                layout: 'vertical',
                align: 'left',
                x: 80,
                verticalAlign: 'top',
                y: 55,
                floating: true,
                backgroundColor: (
                    Highcharts.theme && Highcharts.theme.legendBackgroundColor) ||
                    'rgba(255,255,255,0.25)'
            },
            series: [{
                name: 'Wire',
                type: 'column', // Tipe seri untuk bar chart
                yAxis: 1,
                data: [100, 150, 200, 180, 250, 300,100, 150, 200, 180, 250, 300],
            }, {
                name: 'Connector',
                type: 'column', // Tipe seri untuk bar chart
                yAxis: 1,
                data: [100, 150, 200, 180, 250, 300,100, 150, 200, 180, 250, 300],
            }, {
                name: 'Total PR',
                type: 'line', // Tipe seri untuk line chart
                data: [110, 120, 180, 220, 240, 280,100, 150, 200, 180, 250, 300],
            }, {
                name: 'Total Used',
                type: 'line', // Tipe seri untuk line chart
                data: [210, 320, 280, 290, 280, 380,100, 150, 200, 180, 250, 300],
            }]
        });
    });

    function xbarStyle(){
        return "background-color:#BABFFF;";
    }

    function jam() {
        var time = new Date(),
            hours = time.getHours(),
            minutes = time.getMinutes(),
            seconds = time.getSeconds();
        document.querySelectorAll('.jam')[0].innerHTML = harold(hours) + ":" + harold(minutes) + ":" + harold(seconds);

        function harold(standIn) {
            if (standIn < 10) {
                standIn = '0' + standIn
            }
            return standIn;
        }
    }
</script>