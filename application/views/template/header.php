<?php
date_default_timezone_set("Asia/Bangkok");

//Config
$username = $this->session->username;
$this->db->select('*');
$this->db->from('users');
$this->db->where('username', $username);
$profile = $this->db->get()->row();

$this->db->select('*');
$this->db->from('config');
$config = $this->db->get()->row();

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv='cache-control' content='no-cache'>
    <meta http-equiv='expires' content='0'>
    <meta http-equiv='pragma' content='no-cache'>
    <title><?= $config->name ?> | <?= $config->description ?></title>
    <link rel="icon" href="<?= $config->favicon ?>" type="image/png" sizes="16x16">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <!-- Easyui -->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/themes/' . $profile->theme . '/easyui.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/themes/icon.css?' . time()) ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/themes/color.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/themes/style.css?' . time()) ?>">
    <script type="text/javascript" src="<?= base_url('assets/jquery.min.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/jquery.easyui.min.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/datagrid-cellediting.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/datagrid-scrollview.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/datagrid-detailview.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/datagrid-filter.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/datagrid-export.js?') . time() ?>"></script>
    <!-- Fontawesome -->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/icons/fontawesome/css/font-awesome.min.css') ?>">

    <!-- Vendors -->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/vendors/toastr.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/vendors/sweetalert2.min.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/bootstrap/css/bootstrap.min.css?4') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/vendors/summernote/summernote-lite.css') ?>" />
    <script type="text/javascript" src="<?= base_url('assets/vendors/toastr.min.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/vendors/sweetalert2.all.min.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/vendors/summernote/summernote-lite.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/vendors/charts/code/highcharts.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/vendors/charts/code/modules/exporting.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/vendors/charts/code/modules/export-data.js') ?>"></script>
    <script type="text/javascript" src="<?= base_url('assets/vendors/charts/code/modules/accessibility.js') ?>"></script>

    <!-- <script type="text/javascript" src="<?= base_url('assets/bootstrap/js/bootstrap.min.js?3') ?>"></script> -->
    <!-- <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script> -->

    <!-- <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
        }
    </script> -->

    <style type="text/css">
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background-color: #fff;
        }

        .preloader .loading {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            font: 14px arial;
        }

        .swal2-container {
            z-index: 9100 !important;
        }
    </style>
    <script>
        $(document).ready(function() {
            $(".preloader").fadeOut();
        })
    </script>
</head>

<body style="margin: 0;">
    <div class="preloader">
        <div class="loading">
            <img src="<?= $config->logo ?>" width="100">
        </div>
    </div>
</body>