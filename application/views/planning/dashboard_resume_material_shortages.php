<style>
    @media print { .no-print { display: none !important; } }

    body {
        padding: 24px;
        font-family: "Poppins";
        background-color: #F2F4F8;
    }

    .btn {
        pointer-events: auto;
        opacity: 1;
    }

    .badge-soft-warning {
        background-color: #fff3cd;
        color: #856404;
        font-weight: 500;
    }

    .badge-soft-danger {
        background-color: #f8d7da;
        color: #842029;
        font-weight: 500;
    }

    .badge-soft-success {
        background-color: #d2f4ea;
        color: #198754;
        font-weight: 500;
    }

    .badge-outline-danger {
        color: #dc3545;
        border: 1px solid #dc3545;
        background: transparent;
        font-weight: 500;
    }

    .badge-outline-warning {
        color: #d39e00;
        border: 1px solid #ffc107;
        background: transparent;
        font-weight: 500;
    }

    .badge-outline-success {
        color: #198754;
        border: 1px solid #198754;
        background: transparent;
        font-weight: 500;
    }

    .section-divider {
        border-top: 1px dashed #ddd;
        margin: 20px 0;
    }
</style>
<div class="position-fixed top-0 end-0 m-3 d-flex gap-2 no-print" style="z-index: 9999;">
    <button class="btn btn-outline-secondary" onclick="window.print()"><i class="fa fa-print"></i></button>
    <button class="btn btn-outline-secondary" onclick="location.reload()"><i class="fa fa-rotate-right"></i></button>
    <button class="btn btn-outline-secondary" id="btnFullscreen"><i id="iconFullscreen" class="fa fa-expand"></i></button>
</div>
<div class="row mb-4"><div class="col">
    <div class="container-fluid p-3 bg-white" style="border-radius: 20px;">
        <div class="row">
            <div class="col-auto pr-0"><img src="<?= base_url("assets/image/config/logo/1741863196.png") ?>" style="height: 60px;"></div>
            <div class="col-auto">
                <span style="font-size: 23px !important; font-weight: 700;">Material Availability Report</span><br>
                <span style="font-size: 17px !important;" class="text-secondary">BANSHU ELECTRIC INDONESIA</span>
            </div>
        </div>
    </div>
</div></div>
<div class="row mb-2"><div class="col">
    <div class="container-fluid p-4 bg-white" style="border-radius: 20px;">
        <div class="row">
            <div class="col-auto"><div class="form-group">
                <label for="periode">Periode</label>
                <select id="periode" class="form-control"><?= implode('', $months) ?></select>
            </div></div>
            <div class="col-auto"><div class="form-group">
                <label for="revision">Revision</label>
                <select id="revision" class="form-control"><?= implode('', $revisions) ?></select>
            </div></div>
            <div class="col-auto"><div class="form-group">
                <label for="week">Week</label>
                <select id="week" class="form-control">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
            </div></div>
            <div class="col"></div>
            <div class="col-auto align-self-center"><button class="btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfo" aria-expanded="false" aria-controls="collapseInfo"><i class="fa fa-angle-down" id="iconBtnCollapse"></i></button></div>
        </div>
    </div>
</div></div>
<div class="collapse pt-0 mb-2" id="collapseInfo"><div class="card card-body border-0" style="border-radius: 20px;"><div class="row"><div class="col">
    <div class="container-fluid bg-white">
        <h5 class="fw-bold mb-2">🤖 Supply Risk Intelligence</h5>
        <span class="badge rounded-pill badge-soft-success fw-bold mb-3" id="risk_index"></span>

        <p>
            Analisis availability material pada <b><span id="info_periode"></span> – Week <span id="info_week"></span></b> dibandingkan dengan 
            <b>minggu sebelumnya</b> menunjukkan kondisi <b>perlu perhatian</b>. 
            <span id="info_detail">Teridentifikasi pergeseran availability pada beberapa material utama 
            yang perlu dimonitor ketat sebelum berkembang menjadi risiko produksi.</span>
        </p>

        <div class="section-divider"></div>

        <!-- Highest Risk -->
        <h6 class="fw-bold">📉 Highest Risk Exposure</h6>
        <ol class="ps-3">
            <li>
                <b id="risk_1"></b> — <span id="persent_risk_1"></span>% 
                <span id="detail_risk_1"></span>
                <span class="badge rounded-pill badge-outline-danger fw-bold ms-2" id="type_risk_1"></span>
            </li>
            <li>
                <b id="risk_2"></b> — <span id="persent_risk_2"></span>% 
                <span id="detail_risk_2"></span>
                <span class="badge rounded-pill badge-outline-danger fw-bold ms-2" id="type_risk_2"></span>
            </li>
            <li>
                <b id="risk_3"></b> — <span id="persent_risk_3"></span>% 
                <span id="detail_risk_3"></span>
                <span class="badge rounded-pill badge-outline-warning fw-bold ms-2" id="type_risk_3"></span>
            </li>
        </ol>

        <!-- Early Warning -->
        <div class="section-divider indication_warning"></div>

        <h6 class="fw-bold indication_warning">🚨 Early Warning</h6>
        <ul class="indication_warning" id="indication_warning_index"></ul>

        <!-- Positive Signals -->
        <div class="section-divider indication_positive"></div>

        <h6 class="fw-bold indication_positive">📈 Positive Signals</h6>
        <ul class="indication_positive" id="indication_positive_index"></ul>

        <div class="section-divider"></div>

        <!-- Business Impact -->
        <h6 class="fw-bold">⚠️ Business Impact</h6>
        <ul id="impact_index"></ul>

        <div class="section-divider"></div>

        <!-- Recommended -->
        <h6 class="fw-bold">🎯 Recommended Actions</h6>
        <ul id="recomend_index"></ul>
    </div></div>
</div></div></div>
<div class="row mt-4">
    <div class="col-6"><div class="container-fluid p-4 mb-2 bg-white" style="border-radius: 20px;">
        <div class="row"><div class="col fw-bold">ITEM AVAILABILITY</div></div>
        <div class="row pt-5"><div class="col"><div id="chart_item"></div></div></div>
        <div class="row pb-4 justify-content-center"><div class="col-auto rounded-3 p-3 text-light bg-gradient bg-light" id="bg_status_item"><span id="status_item"></span></div></div>
        <div class="row justify-content-evenly">
            <div class="col-3 rounded-3 bg-light py-2 px-4">
                <span>Total</span><br>
                <span id="total_item" class="fw-bold">0</span>
            </div>
            <div class="col-3 rounded-3 bg-light py-2 px-4">
                <span>Available</span><br>
                <span id="available_item" class="fw-bold">0</span>
            </div>
            <div class="col-3 rounded-3 bg-light py-2 px-4">
                <span>Shortage</span><br>
                <span id="shortage_item" class="fw-bold">0</span>
            </div>
        </div>
    </div></div>
    <div class="col-6"><div class="container-fluid p-4 mb-2 bg-white" style="border-radius: 20px;">
        <div class="row"><div class="col fw-bold">QTY AVAILABILITY</div></div>
        <div class="row pt-5"><div class="col"><div id="chart_qty"></div></div></div>
        <div class="row pb-4 justify-content-center"><div class="col-auto rounded-3 p-3 text-light bg-gradient bg-light" id="bg_status_qty"><span id="status_qty"></span></div></div>
        <div class="row justify-content-evenly">
            <div class="col-3 rounded-3 bg-light py-2 px-4">
                <span>Total</span><br>
                <span id="total_qty" class="fw-bold">0</span>
            </div>
            <div class="col-3 rounded-3 bg-light py-2 px-4">
                <span>Available</span><br>
                <span id="available_qty" class="fw-bold">0</span>
            </div>
            <div class="col-3 rounded-3 bg-light py-2 px-4">
                <span>Shortage</span><br>
                <span id="shortage_qty" class="fw-bold">0</span>
            </div>
        </div>
    </div></div>
</div>
<div class="row mt-4">
    <div class="col-6"><div class="container-fluid p-4 mb-2 bg-white" style="border-radius: 20px;">
        <div class="row"><div class="col fw-bold">WEEKLY ITEM AVAILABILITY</div></div>
        <div class="row pt-5"><div class="col"><div id="bar_item"></div></div></div>
    </div></div>
    <div class="col-6"><div class="container-fluid p-4 mb-2 bg-white" style="border-radius: 20px;">
        <div class="row"><div class="col fw-bold">WEEKLY QTY AVAILABILITY</div></div>
        <div class="row pt-5"><div class="col"><div id="bar_qty"></div></div></div>
    </div></div>
</div>
<div class="row mt-4"><div class="col"><div class="container-fluid p-4 mb-2 bg-white" style="border-radius: 20px;">
    <div class="row"><div class="col fw-bold">AVAILABILITY PER PRODUCT FAMILY</div></div>
    <div class="row pt-5">
        <div class="col-4"><div id="border_1" class="container-fluid p-4 mb-2 shadow bg-white" style="border-radius: 20px;">
            <div class="row"><div class="col fw-bold" id="name_1"></div></div>
            <div class="row"><div class="col">
                <table style="width: 100%;">
                    <tr>
                        <td rowspan="2" style="width: 300px;"><div id="chart_1"></div></td>
                        <td valign="bottom" style="height: 70px;"><span id="percent_1" class="fw-bold fs-1"></span></td>
                    </tr>
                    <tr><td valign="top" style="height: 70px;"><span id="type_1" class="fw-bold"></span></td></tr>
                </table>
            </div></div>
            <div class="row justify-content-evenly">
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Total</span><br>
                    <span id="total_1" class="fw-bold">0</span>
                </div>
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Available</span><br>
                    <span id="available_1" class="fw-bold">0</span>
                </div>
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Shortage</span><br>
                    <span id="shortage_1" class="fw-bold">0</span>
                </div>
            </div>
        </div></div>
        <div class="col-4"><div id="border_2" class="container-fluid p-4 mb-2 shadow bg-white" style="border-radius: 20px;">
            <div class="row"><div class="col fw-bold" id="name_2"></div></div>
            <div class="row"><div class="col">
                <table style="width: 100%;">
                    <tr>
                        <td rowspan="2" style="width: 300px;"><div id="chart_2"></div></td>
                        <td valign="bottom" style="height: 70px;"><span id="percent_2" class="fw-bold fs-1"></span></td>
                    </tr>
                    <tr><td valign="top" style="height: 70px;"><span id="type_2" class="fw-bold"></span></td></tr>
                </table>
            </div></div>
            <div class="row justify-content-evenly">
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Total</span><br>
                    <span id="total_2" class="fw-bold">0</span>
                </div>
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Available</span><br>
                    <span id="available_2" class="fw-bold">0</span>
                </div>
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Shortage</span><br>
                    <span id="shortage_2" class="fw-bold">0</span>
                </div>
            </div>
        </div></div>
        <div class="col-4"><div id="border_3" class="container-fluid p-4 mb-2 shadow bg-white" style="border-radius: 20px;">
            <div class="row"><div class="col fw-bold" id="name_3"></div></div>
            <div class="row"><div class="col">
                <table style="width: 100%;">
                    <tr>
                        <td rowspan="2" style="width: 300px;"><div id="chart_3"></div></td>
                        <td valign="bottom" style="height: 70px;"><span id="percent_3" class="fw-bold fs-1"></span></td>
                    </tr>
                    <tr><td valign="top" style="height: 70px;"><span id="type_3" class="fw-bold"></span></td></tr>
                </table>
            </div></div>
            <div class="row justify-content-evenly">
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Total</span><br>
                    <span id="total_3" class="fw-bold">0</span>
                </div>
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Available</span><br>
                    <span id="available_3" class="fw-bold">0</span>
                </div>
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Shortage</span><br>
                    <span id="shortage_3" class="fw-bold">0</span>
                </div>
            </div>
        </div></div>
    </div>
    <div class="row pt-5">
        <div class="col-4"><div id="border_4" class="container-fluid p-4 mb-2 shadow bg-white" style="border-radius: 20px;">
            <div class="row"><div class="col fw-bold" id="name_4"></div></div>
            <div class="row"><div class="col">
                <table style="width: 100%;">
                    <tr>
                        <td rowspan="2" style="width: 300px;"><div id="chart_4"></div></td>
                        <td valign="bottom" style="height: 70px;"><span id="percent_4" class="fw-bold fs-1"></span></td>
                    </tr>
                    <tr><td valign="top" style="height: 70px;"><span id="type_4" class="fw-bold"></span></td></tr>
                </table>
            </div></div>
            <div class="row justify-content-evenly">
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Total</span><br>
                    <span id="total_4" class="fw-bold">0</span>
                </div>
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Available</span><br>
                    <span id="available_4" class="fw-bold">0</span>
                </div>
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Shortage</span><br>
                    <span id="shortage_4" class="fw-bold">0</span>
                </div>
            </div>
        </div></div>
        <div class="col-4"><div id="border_5" class="container-fluid p-4 mb-2 shadow bg-white" style="border-radius: 20px;">
            <div class="row"><div class="col fw-bold" id="name_5"></div></div>
            <div class="row"><div class="col">
                <table style="width: 100%;">
                    <tr>
                        <td rowspan="2" style="width: 300px;"><div id="chart_5"></div></td>
                        <td valign="bottom" style="height: 70px;"><span id="percent_5" class="fw-bold fs-1"></span></td>
                    </tr>
                    <tr><td valign="top" style="height: 70px;"><span id="type_5" class="fw-bold"></span></td></tr>
                </table>
            </div></div>
            <div class="row justify-content-evenly">
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Total</span><br>
                    <span id="total_5" class="fw-bold">0</span>
                </div>
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Available</span><br>
                    <span id="available_5" class="fw-bold">0</span>
                </div>
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Shortage</span><br>
                    <span id="shortage_5" class="fw-bold">0</span>
                </div>
            </div>
        </div></div>
        <div class="col-4"><div id="border_6" class="container-fluid p-4 mb-2 shadow bg-white" style="border-radius: 20px;">
            <div class="row"><div class="col fw-bold" id="name_6"></div></div>
            <div class="row"><div class="col">
                <table style="width: 100%;">
                    <tr>
                        <td rowspan="2" style="width: 300px;"><div id="chart_6"></div></td>
                        <td valign="bottom" style="height: 70px;"><span id="percent_6" class="fw-bold fs-1"></span></td>
                    </tr>
                    <tr><td valign="top" style="height: 70px;"><span id="type_6" class="fw-bold"></span></td></tr>
                </table>
            </div></div>
            <div class="row justify-content-evenly">
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Total</span><br>
                    <span id="total_6" class="fw-bold">0</span>
                </div>
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Available</span><br>
                    <span id="available_6" class="fw-bold">0</span>
                </div>
                <div class="col-3 rounded-3 bg-light py-2 px-4">
                    <span>Shortage</span><br>
                    <span id="shortage_6" class="fw-bold">0</span>
                </div>
            </div>
        </div></div>
    </div>
</div></div></div>
<div class="row mt-4"><div class="col"><div class="container-fluid p-4 mb-2 bg-white" style="border-radius: 20px;">
    <div class="row">
        <div class="col-1 align-self-center"><span class="fs-1 fst-italic align-self-center" style="writing-mode: sideways-lr; margin-left: 4vh;">Item Shortage</span></div>
        <div class="col-10">
            <div class="row"><div class="col fw-bold">PARETO ITEM SHORTAGE BY PRODUCT FAMILY (TOTAL:<span id="pareto_total"></span>)</div></div>
            <div class="row"><div class="col"><div id="bar_pareto"></div></div></div>
        </div>
        <div class="col-1 align-self-center"><span class="fs-1 fst-italic align-self-center" style="writing-mode: sideways-lr; margin-left: 4vh;">Cumulative %</span></div>
    </div>
</div></div></div>
<div class="row mt-4"><div class="col"><div class="container-fluid p-4 mb-2 bg-white" style="border-radius: 20px;">
    <div class="row">
        <div class="col-auto"><div class="form-group">
            <label for="podfam">Product Family</label>
            <select id="podfam" class="form-control">
                <option value="">ALL</option>
                <?php foreach ($prodfams as $key => $value) { ?>
                    <option value="<?= $value ?>"><?= $value ?></option>
                <?php } ?>
            </select>
        </div></div>
    </div>
    <div class="row mt-2">
        <div class="col"><div style="height: 300px; overflow: auto;">
            <table class="table table-bordered table-striped">
                <thead><tr>
                    <th style="text-align: center;">NO</th>
                    <th style="text-align: center;">PART NO ORIGINAL</th>
                    <th style="text-align: center;">PART NO</th>
                    <th style="text-align: center;">DESCRIPTION</th>
                    <th style="text-align: center;">PRODUCT FAMILY</th>
                    <th style="text-align: center;">BALANCE WIP</th>
                    <th style="text-align: center;">STOCK WHS</th>
                    <th style="text-align: center;">TOTAL WHS</th>
                    <th style="text-align: center;">OS SUPPPLY</th>
                    <th style="text-align: center;">ON HAND STOCK</th>
                    <th style="text-align: center;">TOTAL INCOMING</th>
                    <th style="text-align: center;">TOTAL SUPPLY</th>
                    <th style="text-align: center;">TOTAL NEED (MPP)</th>
                    <th style="text-align: center;">TOTAL NEED (LTPP)</th>
                    <th style="text-align: center; background-color: red; color: white;">TOTAL NEED (LTPP VS MPP)</th>
                    <th style="text-align: center;">WEEK NEED</th>
                    <th style="text-align: center; background-color: red; color: white;">WEEK BALANCE</th>
                </tr></thead>
                <tbody id="tbl_detail"></tbody>
            </table>
        </div></div>
    </div>
</div></div></div>
<script>
    var datas = [], data_details = [];
    $(function() {
        $("#btnFullscreen").click(function () {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                $("#iconFullscreen").removeClass("fa-expand").addClass("fa-compress");
            } else {
                document.exitFullscreen();
                $("#iconFullscreen").removeClass("fa-compress").addClass("fa-expand");
            }
        });

        $("#collapseInfo").on('hide.bs.collapse', function () {
            $("#iconBtnCollapse").addClass("fa-angle-down");
            $("#iconBtnCollapse").removeClass("fa-angle-up");
        });
        $("#collapseInfo").on('show.bs.collapse', function () {
            $("#iconBtnCollapse").addClass("fa-angle-up");
            $("#iconBtnCollapse").removeClass("fa-angle-down");
        });

        $("#periode").on('change', function () {
            $.ajax({
                url: "<?= $base_url ?>revision",
                data: {periode: btoa($(this).val())},
                method: 'POST',
                dataType: 'json',
                success: function (result) {
                    $("#revision").html("");
                    result.forEach(function (value, key) {
                        var option = $("<option>"), rev = value.revision;

                        option.val(rev);
                        option.html(rev);
                        if (key == 0) { option.prop("selected", true); }

                        $("#revision").append(option);
                    });
                    selectData();
                }
            });
        });
        $("#revision").on('change', function () { selectData(); });
        $("#week").on('change', function () { getData($(this).val()); });
        $("#podfam").on('change', function () { filterDetail($(this).val()); });

        setTimeout(function () {
            var periode = $("#periode option:selected");
            selectData(periode.text(), periode.val());
        },0);
    });

    function selectData() {
        var periode = $("#periode option:selected"), text = periode.text(), value = periode.val();

        $("#info_periode").html(text);
        $.ajax({
            url: "<?= $base_url ?>datatables",
            data: {periode: btoa(value), revision: btoa($("#revision option:selected").val())},
            async: false,
            method: 'POST',
            dataType: 'json',
            success: function (result) {
                var prodfams = ["tube", "accessories", "vta", "connector", "terminal", "wire"];
                datas = [[],[],[],[],[]];
                data_details = [];

                for (var i = 0; i <= 4; i++) {
                    datas[i].item = {total: 0, available: 0, shortage: 0, percent: 0};
                    datas[i].qty = {total: 0, available: 0, shortage: 0, percent: 0};

                    prodfams.forEach(function (value, key) {
                        datas[i][value] = {total: 0, available: 0, shortage: 0, percent: 0};
                    });
                }

                result.last.forEach(function (value, key) {
                    var id_name = value.prodfam.toLowerCase();
                    var need = value.need;
                    datas[0][id_name] = {
                        total: need.count_item,
                        available: need.count_item - need.count_bal[3],
                        shortage: need.count_bal[3],
                        percent: need.percent_count[3]
                    };

                    datas[0].item.total += datas[0][id_name].total;
                    datas[0].item.available += datas[0][id_name].available;
                    datas[0].item.shortage += datas[0][id_name].shortage;
                    datas[0].item.percent = datas[0][id_name].percent;
                    datas[0].qty.total += need.total_item;
                    datas[0].qty.available += need.total_item - need.total_bal[3];
                    datas[0].qty.shortage += need.total_bal[3];
                    datas[0].qty.percent = need.percent_total[3];
                });

                result.now.forEach(function (value, key) {
                    var name = value.prodfam;
                    var id_name = name.toLowerCase();
                    var need = value.need;
                    data_details[name] = [];

                    for (var i = 1; i <= 4; i++) {
                        var idx = i - 1;
                        datas[i][id_name] = {
                            total: need.count_item,
                            available: need.count_item - need.count_bal[idx],
                            shortage: need.count_bal[idx],
                            percent: need.percent_count[idx]
                        };

                        datas[i].item.total += datas[i][id_name].total;
                        datas[i].item.available += datas[i][id_name].available;
                        datas[i].item.shortage += datas[i][id_name].shortage;
                        datas[i].item.percent = datas[i][id_name].percent;
                        datas[i].qty.total += need.total_item;
                        datas[i].qty.available += need.total_item - need.total_bal[idx];
                        datas[i].qty.shortage += need.total_bal[idx];
                        datas[i].qty.percent = need.percent_total[idx];

                        data_details[name][idx] = need.items[idx];
                    }
                });

                var data_item = {available: [], total: []};
                var data_qty = {available: [], total: []};
                for (var i = 1; i <= 4; i++) {
                    data_item.available.push(datas[i]["item"].available);
                    data_item.total.push(datas[i]["item"].total);
                    data_qty.available.push(datas[i]["qty"].available);
                    data_qty.total.push(datas[i]["qty"].total);
                }

                barChart('bar_item', [
                    {name: 'Available Item', data: data_item.available},
                    {name: 'Total Item', data: data_item.total}
                ]);
                barChart('bar_qty', [
                    {name: 'Available Qty', data: data_qty.available},
                    {name: 'Total Qty', data: data_qty.total}
                ]);

                getData(1);
                filterDetail('');
            }
        });
    }

    function getData(week) {
        var data = datas[week], last_data = datas[week - 1];
        $("#info_week").html(week);
        if ($("#week").val() != week) { $("#week").val(week); }
        $(".indication_positive").hide();
        $(".indication_warning").hide();
        $("#indication_warning_index").html("");
        $("#indication_positive_index").html("");

        speedometerChart('chart_item', data['item']);
        speedometerChart('chart_qty', data['qty']);

        var arr = [data['tube'], data['accessories'], data['vta'], data['connector'], data['terminal'], data['wire']],
            names = <?= json_encode($prodfams) ?>;
        names.forEach(function (value, key) { arr[key].name = value; });
        arr.sort((a, b) => a.percent - b.percent);
        arr.forEach(function (obj, key) {
            var no = key + 1;
            var name = obj.name;
            var percent = obj.percent;
            var last_percent = last_data[obj.name.toLowerCase()].percent;

            if (no <= 3) {
                var detail = "&#9210; Stabil";
                var style = "danger";
                var type = "HIGH";

                if (percent >= 95) { type = "LOW"; style = "success"; }
                else if (percent >= 90) { type = "MEDIUM"; style = "warning"; }

                if (last_percent != percent) {
                    var total_percent = percent - last_percent;
                    if (total_percent > 0) { detail = "🔺"; }
                    else if (total_percent < 0) { detail = "🔻"; }
                    detail += Math.abs(total_percent)+"% vs last week";
                }

                $("#risk_"+no).html(name);
                $("#persent_risk_"+no).html(percent);
                $("#detail_risk_"+no).html(detail);
                $("#type_risk_"+no).html(type);
                $("#type_risk_"+no).removeClass("badge-outline-danger");
                $("#type_risk_"+no).removeClass("badge-outline-warning");
                $("#type_risk_"+no).removeClass("badge-outline-success");
                $("#type_risk_"+no).addClass("badge-outline-"+style);
            }

            if (last_percent != percent) {
                var total_percent = percent - last_percent;

                if (total_percent >= 2) {
                    $(".indication_positive").show();
                    $("#indication_positive_index").append(
                        $("<li>").html("<b>"+name+"</b> membaik <b>+"+total_percent+"%</b> dibanding minggu lalu")
                    );
                }
                if (total_percent <= -2) {
                    $(".indication_warning").show();
                    $("#indication_warning_index").append(
                        $("<li>").html("Availability <b>"+name+"</b> turun <b>"+Math.abs(total_percent)+"%</b> → indikasi potensi shortage lanjutan")
                    );
                }
            }

            avaibilityForm(no, obj);
        });

        var obj = {
            TUBE: data['tube'].shortage,
            ACCESSORIES: data['accessories'].shortage,
            VTA: data['vta'].shortage,
            CONNECTOR: data['connector'].shortage,
            TERMINAL: data['terminal'].shortage,
            WIRE: data['wire'].shortage
        }, sortedMap = new Map( Object.entries(obj).sort((a, b) => b[1] - a[1]) ),
            name_shortage = [],
            data_shortage = [];
        sortedMap.entries().forEach(function (value, key) {
            name_shortage.push(value[0]);
            data_shortage.push(value[1]);
        });
        $("#pareto_total").html(data_shortage.reduce((accumulator, currentValue) => accumulator + currentValue, 0));
        barChart('bar_pareto', [
            {name: 'Shortage', data: data_shortage},
            {name: 'Cum %', data: [45, 65, 83, 91, 95, 100]}
        ], name_shortage);

        var risk_index = "KRITIKAL",
            bg_color = "badge-soft-danger",
            percent = data['item'].percent,
            impact = $("#impact_index"),
            recomend = $("#recomend_index"),
            info = "Terdapat penurunan availability signifikan lintas product family yang berpotensi langsung mempengaruhi kontinuitas produksi.";
        impact.html("");
        recomend.html("");
        if (percent >= 95) {
            risk_index = "TERKENDALI";
            bg_color = "badge-soft-success";
            info = "Secara umum supply material masih dalam batas aman tanpa indikasi shortage kritikal.";

            impact.append($("<li>").html("Potensi keterbatasan material pada family tertentu"));
            impact.append($("<li>").html("Fleksibilitas produksi dapat menurun jika tren berlanjut"));
            impact.append($("<li>").html("Perlu antisipasi buffer & inbound"));
            recomend.append($("<li>").html("Review inbound plan untuk Top Risk Family"));
            recomend.append($("<li>").html("Koordinasi awal dengan supplier terkait potensi delay"));
            recomend.append($("<li>").html("Siapkan safety buffer terbatas"));
            recomend.append($("<li>").html("Monitor harian untuk material kritikal"));
        } else if (percent >= 90) {
            risk_index = "PERLU PERHATIAN";
            bg_color = "badge-soft-warning";
            info = "Teridentifikasi pergeseran availability pada beberapa material utama yang perlu dimonitor ketat sebelum berkembang menjadi risiko produksi.";

            impact.append($("<li>").html("Risiko gangguan produksi tinggi"));
            impact.append($("<li>").html("Ancaman keterlambatan delivery ke customer"));
            impact.append($("<li>").html("Potensi eskalasi biaya (expedite, overtime, alternate sourcing)"));
            recomend.append($("<li>").html("Percepat inbound material kritikal"));
            recomend.append($("<li>").html("Aktifkan alternatif supplier"));
            recomend.append($("<li>").html("Reprioritasi produksi berbasis risiko material"));
            recomend.append($("<li>").html("Eskalasi ke Procurement & Production Planning Lead"));
            recomend.append($("<li>").html("Siapkan skenario recovery plan"));
        } else {
            impact.append($("<li>").html("Produksi berjalan sesuai rencana"));
            impact.append($("<li>").html("Tidak ada indikasi shortage jangka pendek"));
            impact.append($("<li>").html("Service level ke customer relatif aman"));
            recomend.append($("<li>").html("Lanjutkan monitoring mingguan"));
            recomend.append($("<li>").html("Pastikan inbound material sesuai plan"));
            recomend.append($("<li>").html("Validasi forecast vs aktual"));
        }

        $("#risk_index").removeClass("badge-soft-success");
        $("#risk_index").removeClass("badge-soft-warning");
        $("#risk_index").removeClass("badge-soft-danger");
        $("#risk_index").addClass(bg_color);
        $("#risk_index").html("Risk Index: "+risk_index);
        $("#info_detail").html(info);
    }

    function speedometerChart(id, data) {
        var name = id.replace('chart_','');
        var percent = data.percent;
        var options = {
            exporting: {enabled: true},
            credits: {enabled: false},
            chart: {
                type: 'gauge',
                plotBackgroundColor: null,
                plotBackgroundImage: null,
                plotBorderWidth: 0,
                plotShadow: false,
                height: '40%'
            },
            title: {text: ''},
            pane: {
                startAngle: -150,
                endAngle: 150,
                background: null,
                center: ['50%', '50%'],
                size: '90%'
            },
            yAxis: {
                min: 0,
                max: 100,
                tickPixelInterval: 100,
                tickPosition: 'inside',
                tickColor: 'var(--highcharts-background-color, #FFFFFF)',
                tickLength: 20,
                tickWidth: 2,
                minorTickInterval: null,
                labels: {
                    distance: 20,
                    style: {fontSize: '14px'}
                },
                lineWidth: 0,
                plotBands: [{
                    from: 0,
                    to: 90,
                    color: '#DF5353', // red
                    thickness: 16,
                    borderRadius: '50%'
                }, {
                    from: 90,
                    to: 95,
                    color: '#DDDF0D', // yellow
                    thickness: 16,
                    borderRadius: '50%'
                }, {
                    from: 95,
                    to: 100,
                    color: '#55BF3B', // green
                    thickness: 16,
                    borderRadius: '50%'
                }]
            },
            series: [{
                name: name.toUpperCase(),
                data: [percent],
                tooltip: null,
                dataLabels: {
                    y: 97,
                    format: '{y}',
                    borderWidth: 0,
                    style: {fontSize: '6px'}
                },
                dial: {
                    radius: '80%',
                    backgroundColor: 'gray',
                    baseWidth: 6,
                    baseLength: '0%',
                    rearLength: '20%'
                },
                pivot: {backgroundColor: 'blue', radius: 9}
            }]
        };

        $('#bg_status_'+name).removeClass('bg-light');
        $('#bg_status_'+name).removeClass('bg-danger');
        $('#bg_status_'+name).removeClass('bg-warning');
        $('#bg_status_'+name).removeClass('bg-success');

        var status = "Critical", bg_color = "bg-danger";
        if (percent >= 95) { status = "Safe"; bg_color = "bg-success"; }
        else if (percent >= 90) { status = "Warning"; bg_color = "bg-warning"; }
        $('#bg_status_'+name).addClass(bg_color);
        $('#status_'+name).html(status+" &#9679; "+percent);

        if (!['chart_item', 'chart_qty'].includes(id)) {
            options.pane.center = ['50%', '50%'];
            options.chart.height = '250';
        }
        Highcharts.chart(id, options);

        $("#total_"+name).html(data.total.toLocaleString('de-DE'));
        $("#available_"+name).html(data.available.toLocaleString('de-DE'));
        $("#shortage_"+name).html(data.shortage.toLocaleString('de-DE'));
    }

    function barChart(id, data, categories = ['1', '2', '3', '4']) {
        var new_data = [
            {name: data[0].name, data: data[0].data, type: 'column', color: '#3366cc', yAxis: 0, dataLabels: { style: { color: '#3366cc' } }},
            {name: data[1].name, data: data[1].data, type: 'line', color: '#15ff00', yAxis: 1, dataLabels: { style: { color: '#15ff00' } }}
        ];
        var min = 0, max = 0;

        data[0].data.forEach(function (value, key) {
            min = (min > value) ? value : min;
            max = (max < value) ? value : max;
        });

        data[1].data.forEach(function (value, key) {
            min = (min > value) ? value : min;
            max = (max < value) ? value : max;
        });

        var min2 = min, max2 = max;
        if (id == 'bar_pareto') { min2 = 0; max2 = 100; }

        Highcharts.chart(id, {
            exporting: {enabled: true},
            credits: {enabled: false},
            title: {text: ''},
            chart: {type: 'column'},
            xAxis: {categories: categories},
            yAxis: [
                {min: min, max: max, title: {text: null}},
                {min: min2, max: max2, title: {text: null}, opposite: true}
            ],
            legend: {enabled: true},
            plotOptions: { series: { dataLabels: {enabled: true} } },
            series: new_data
        });
    }

    function avaibilityForm(id, data) {
        var type = 'High';
        var color = '#DF5353';
        var boder_color = '#DF5353';
        var percent = data.percent;
        var boder = (percent < 95) ? 1 : 0;

        if (percent >= 95) {
            type = 'Low';
            color = '#55BF3B';
            boder_color = '#FFFFFF';
        } else if (percent >= 90) {
            type = 'Medium';
            color = '#DDDF0D';
            boder_color = '#DDDF0D';
        }

        $("#name_"+id).html(data.name);
        $("#type_"+id).html(type);
        $("#percent_"+id).html(percent+'%');
        $("#percent_"+id).css('color', color);
        $("#border_"+id).css("border", boder+"px solid "+boder_color);
        speedometerChart('chart_'+id, data);
    }

    function filterDetail(prodfam='') {
        var week = parseInt($("#week").val()) - 1, tbl = $("#tbl_detail"), no = 0,
            filter = (prodfam == '') ? <?= json_encode($prodfams) ?> : [prodfam];
        tbl.html("");
        filter.forEach(function (value, key) {
            console.log(data_details[value][week]);
            data_details[value][week].forEach(function (row, index) {
                var tr = $("<tr>"),
                balance_wip = $(`<td align="right">`).html(row.balance_wip),
                stock_whs = $(`<td align="right">`).html(row.stock_whs),
                total_whs = $(`<td align="right">`).html(row.total_whs),
                os_supply = $(`<td align="right">`).html(row.os_supply),
                stock_hand = $(`<td align="right">`).html(row.stock_hand),
                total_incoming = $(`<td align="right">`).html(row.total_incoming),
                total_supply = $(`<td align="right">`).html(row.total_supply),
                total = $(`<td align="right">`).html(row.total),
                lttp = $(`<td align="right">`).html(row.lttp),
                lttp_bal = $(`<td align="right">`).html(row.lttp_bal),
                wp_need = $(`<td align="right">`).html(row.wp_need),
                wp_bal = $(`<td align="right">`).html(row.wp_bal);

                if (row.balance_wip < 0) { balance_wip.css("color", "red"); }
                if (row.stock_whs < 0) { stock_whs.css("color", "red"); }
                if (row.total_whs < 0) { total_whs.css("color", "red"); }
                if (row.os_supply < 0) { os_supply.css("color", "red"); }
                if (row.stock_hand < 0) { stock_hand.css("color", "red"); }
                if (row.total_incoming < 0) { total_incoming.css("color", "red"); }
                if (row.total_supply < 0) { total_supply.css("color", "red"); }
                if (row.total < 0) { total.css("color", "red"); }
                if (row.lttp < 0) { lttp.css("color", "red"); }
                if (row.lttp_bal < 0) { lttp_bal.css("color", "red"); }
                if (row.wp_need < 0) { wp_need.css("color", "red"); }
                if (row.wp_bal < 0) { wp_bal.css("color", "red"); }

                no++;
                tr.append($(`<td align="center">`).html(no));
                tr.append($(`<td>`).html(row.item_rm_id));
                tr.append($(`<td>`).html(row.item_rm_number));
                tr.append($(`<td>`).html(row.item_rm_name));
                tr.append($(`<td>`).html(row.prodfam));
                tr.append(balance_wip);
                tr.append(stock_whs);
                tr.append(total_whs);
                tr.append(os_supply);
                tr.append(stock_hand);
                tr.append(total_incoming);
                tr.append(total_supply);
                tr.append(total);
                tr.append(lttp);
                tr.append(lttp_bal);
                tr.append(wp_need);
                tr.append(wp_bal);

                tbl.append(tr);
            });
        });
    }
</script>