<!DOCTYPE html>
<html lang="en">

<head>
    <title>DIGIT | Versi 3.1</title>
    <meta charset="utf-8" />
    <meta name="description" content="Layanan Single Sign On Ditjen Perbendaharaan" />
    <meta name="keywords" content="digit, djpb, sso" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="sso aplication" />
    <meta property="og:title" content="DIGIT | Digital Treasury" />
    <meta property="og:url" content="https://digit.kemenkeu.go.id" />
    <meta property="og:site_name" content="DIGIT | Digital Treasury" />
    <link rel="icon" href="https://digitstorage.kemenkeu.go.id/public/template/default/assets/images/digit-icon.svg" type="image/x-icon" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="https://digitstorage.kemenkeu.go.id/public/template/default/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://digitstorage.kemenkeu.go.id/public/template/default/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="https://digitstorage.kemenkeu.go.id/public/template/default/assets/plugins/custom/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet" />
    <!--<link href="https://digitstorage.kemenkeu.go.id/public/template/default/assets/plugins/custom/chosen/chosen.min.css" rel="stylesheet">-->
    <!--end::Global Stylesheets Bundle-->
    <link href="https://digitstorage.kemenkeu.go.id/public/template/default/assets/css/custom.css" rel="stylesheet" type="text/css">
    <link href="plugins/jquery-kdrecorder/jquery-kdrecorder.css" rel="stylesheet" />

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
    .field-icon {
        position: absolute;
        top: 30%;
        right: 5%;
        cursor: pointer;
    }
    </style>

</head>
<!--end::Head-->
<!--begin::Body-->
<style>
.login-hidden {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    /* Atur lebar agar sesuai dengan kebutuhan */
    transition: opacity 0.5s ease;
}

.login-visible {
    visibility: visible;
    opacity: 1;
    position: relative;
    transition: opacity 0.5s ease;
}
</style>

<body data-kt-name="metronic" id="kt_body" class="app-blank app-blank digit font-dm">
    <!--begin::Theme mode setup on page load-->
    <script>
    if (document.documentElement) {
        const defaultThemeMode = "system";
        const name = document.body.getAttribute("data-kt-name");
        let themeMode = localStorage.getItem("kt_" + (name !== null ? name + "_" : "") + "theme_mode_value");
        if (themeMode === null) {
            if (defaultThemeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            } else {
                themeMode = defaultThemeMode;
            }
        }
        document.documentElement.setAttribute("data-theme", themeMode);
    }
    </script>
    <!--end::Theme mode setup on page load-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">
            <!--begin::Aside-->
            <!-- <div class="d-none d-xl-flex flex-column w-lg-600px bgi-size-cover bgi-position-center"
                style="background-image: url('assets/images/banner.svg');">
            </div> -->
            <div class="d-none d-xl-flex flex-column w-lg-600px bgi-size-cover bgi-position-center bg-primary">
                <div class="d-flex flex-column flex-center py-20 my-10 px-5 px-md-20 h-100">
                    <h1 class="text-white fs-5x fw-bolder mb-5 w-100" style="z-index: 2">Sinergi di Era Digitalisasi
                    </h1>
                    <div class="text-white fs-2x" style="z-index: 2">
                        Menghubungkan dan mempermudah akses antar layanan digital Ditjen Perbendaharaan
                    </div>
                    <div class="h-lg-50 mb-lg-10"></div>
                </div>
            </div>
            <div class="d-none d-lg-flex" style="position: absolute; bottom: 0;">
                <img src="assets/images/landing.svg" alt="Landing" class="w-xl-600px" style="width:50vw;">
            </div>
            <!--end::Aside-->
            <!--begin::Body-->
            <div class="d-flex flex-column flex-lg-row-fluid w-lg-auto p-10">
                <!--begin::Form-->
                <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                    <!--begin::Wrapper-->
                    <div class="w-lg-500px p-10">

                        <div class="text-center mb-11">
                            <img src="assets/images/logo_digit_horizontal.png" alt="Logo DJPB" width="35%" />
                        </div>

                        <form class="form w-100" id="form-step1" name="form-step1" role="form" onsubmit="return false"
                            action="#">

                            <input type="hidden" name="_token" value="Kc2soMcuSfILjLzXSZ6jrojD7g4lTV73zXYyjUvx">
                            <input type="hidden" name="auth_code_step1" id="auth_code_step1"
                                value="ewogICAgICAgICAgICAgICAgICAgICAgICAidHlwIjoiSldUIiwKICAgICAgICAgICAgICAgICAgICAgICAgImFsZyI6IkhTMjU2IgogICAgICAgICAgICAgICAgICAgIH0.ewogICAgICAgICAgICAgICAgICAgICAgICAiaXNzIjoiRGlnaXQtU3VwZXItQXBwcyIsCiAgICAgICAgICAgICAgICAgICAgICAgICJleHAiOjE3OTQ4MjIxNDUsCiAgICAgICAgICAgICAgICAgICAgICAgICJpc3N1ZWQiOjE3OTQ4MjIwODUsCiAgICAgICAgICAgICAgICAgICAgICAgICJjbGllbnRfaWQiOiI4ODk2ZmJhYi0xZWY3LTQyMGMtODYzMi1lYzAxOTQxMjVmOWUiLAogICAgICAgICAgICAgICAgICAgICAgICAicmVkaXJlY3RfdXJpIjoiaHR0cHM6Ly9kaWdpdC5rZW1lbmtldS5nby5pZCIsCiAgICAgICAgICAgICAgICAgICAgICAgICJzdGF0ZSI6IjIwMjYxMTE2MDk0MTI1IiwKICAgICAgICAgICAgICAgICAgICAgICAgImlkX3VzZXIiOiIwIiwKICAgICAgICAgICAgICAgICAgICAgICAgImxhc3RfbG9naW4iOiIiLAogICAgICAgICAgICAgICAgICAgICAgICAiY3VycmVudF9sb2dpbiI6IiIsCiAgICAgICAgICAgICAgICAgICAgICAgICJicm93c2VyIjoiIiwKICAgICAgICAgICAgICAgICAgICAgICAgInBsYXRmb3JtIjoiIiwKICAgICAgICAgICAgICAgICAgICAgICAgImRldmljZSI6IiIsCiAgICAgICAgICAgICAgICAgICAgICAgICJnb29nbGUyZmFfc3RhdHVzIjoiMCIsCiAgICAgICAgICAgICAgICAgICAgICAgICJ2YWxpZCI6IjAiCiAgICAgICAgICAgICAgICAgICAgfQ.9CkGpYVl0Oo3FFhPD-4tse3036YWFj-yaFtIHdhYGcc">

                            <div class="card p-10 bg-white">
                                <div class="text-gray-500 text-center fw-semibold fs-6">
                                    <p class="text-black mb-2">Permintaan Izin Akses <b>Digital Treasury</b></p>
                                </div>
                                <div class="text-gray-500 text-center fw-semibold fs-6">
                                    <p class="text-muted mb-5">Untuk melanjutkan, harap masukkan <b>Akun Pengguna
                                            DIGIT</b> Anda.</p>
                                </div>
                                <div class="fv-row mb-5 form-floating" id="div-user-1">
                                    <input type="text" class="form-control form-control-solid font-dm" id="username"
                                        name="username" tabindex="1" placeholder="NIK/NIP/Username" maxlength="18">
                                    <label for="username">NIK/NIP/Username</label>
                                </div>
                                <div class="fs-6 mb-5 text-center">
                                    Belum punya Akun Digit? <span class=""><a href="registrasi"
                                            class="link-primary fw-bolder">Daftar disini</a></span>
                                </div>
                                <div class="d-grid mb-5">
                                    <button type="submit" id="submit-step1" class="btn btn-primary fw-bolder"
                                        tabindex="2">
                                        <span class="indicator-label">Selanjutnya</span>
                                    </button>
                                </div>
                                <div class="text-gray-500 text-center fw-semibold fs-6">
                                    <p class="text-black mb-5">Atau Login menggunakan Akun Kemenkeu (Khusus Pegawai
                                        Kemenkeu)</p>
                                </div>
                                <div class="d-grid mb-5">
                                    <a href="https://sso.kemenkeu.go.id/connect/authorize?grant_type=authorization_code&amp;client_id=digit&amp;scope=openid profile profil.hris&amp;response_type=code&amp;redirect_uri=https://digit.kemenkeu.go.id/oauth2kemenkeuid&amp;state=20261116094125"
                                        class="btn btn-outline btn-outline-primary font-dm btn-active-light-primary fw-bolder">Login
                                        Kemenkeu ID</a>
                                </div>
                            </div>
                        </form>

                        <form class="form w-100" id="form-step2" name="form-step2" role="form" onsubmit="return false"
                            action="#">

                            <input type="hidden" name="_token" value="Kc2soMcuSfILjLzXSZ6jrojD7g4lTV73zXYyjUvx">
                            <input type="hidden" name="auth_code_step2" id="auth_code_step2">

                            <div class="card p-10 bg-white">
                                <div class="text-gray-500 text-center fw-semibold fs-6">
                                    <p class="text-black mb-2">Selamat datang, <b id="text-nama"></b></p>
                                </div>
                                <div class="text-gray-500 text-center fw-semibold fs-6">
                                    <p class="text-muted mb-5" id="text-email"></p>
                                </div>
                                <div class="text-gray-500 text-center fw-semibold fs-6">
                                    <p class="text-muted mb-5">Untuk melanjutkan, harap masukkan <b>Sandi DIGIT</b>
                                        Anda.</p>
                                </div>
                                <div class="fv-row mb-5 form-floating" id="div-user-1">
                                    <input type="password" class="form-control form-control-solid font-dm" id="password"
                                        name="password" tabindex="3">
                                    <label for="password">Password</label>
                                    <span toggle="#password" class="fa-regular fa-eye toggle-password field-icon"
                                        title="Show/Hide Password"></span>
                                </div>
                                                                <div class="row mb-5">
                                    <div class="col">
                                        <div class="overlay-layer card-rounded bg-dark bg-opacity-5" id="captcha_loader"
                                            style="display: none;">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Sedang proses...</span>
                                            </div>
                                        </div>
                                        <img src="https://digit.kemenkeu.go.id/captcha/default?tE4csqGT" alt="captcha" id="captcha_image"
                                            style="width: 100%;">
                                    </div>
                                    <div class="col form-floating">
                                        <input type="text" class="form-control form-control-solid font-dm" id="captcha"
                                            name="captcha" tabindex="4">
                                        <label for="captcha">Captcha</label>
                                    </div>
                                </div>
                                <div class="fs-6 mb-5 text-center">
                                    <span class=""><a id="reset-captcha" href="javascript:;"
                                            class="link-primary fw-bolder">Klik disini</a></span> untuk memuat ulang
                                    CAPTCHA
                                </div>
                                                                <div class="fs-6 mb-5 text-center">
                                    Lupa sandi/ password? <span class=""><a href="forgot-password"
                                            class="link-primary fw-bolder">Klik disini</a></span>
                                </div>
                                                                <div class="text-gray-500 text-center fw-semibold fs-8">
                                    <p class="text-black mb-5">Dengan mengklik "Masuk dan Izinkan Akses", Anda
                                        mengizinkan <b>Digital Treasury</b> untuk menggunakan informasi ini sesuai
                                        dengan <a href="terms-and-conditions?versi=2.0.0" class="link-primary fw-bolder"
                                            target="_blank">Kebijakan Privasi</a> kami.</p>
                                </div>
                                <div class="d-grid mb-5">
                                    <button type="submit" id="submit-step2" name="submit"
                                        class="btn btn-primary fw-bolder" tabindex="5">
                                        <span class="indicator-label">Masuk dan Izinkan Akses</span>
                                    </button>
                                    <button type="button" id="cancel-step2" name="submit" class="btn fw-bolder"
                                        tabindex="6">
                                        <span class="indicator-label">Kembali</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Form-->
                <!--begin::Footer-->
                <div class="d-flex flex-center flex-wrap px-5">
                    <!--begin::Links-->
                    <div class="d-flex flex-column fw-semibold text-primary fs-base">

                        <!--begin::Copyright-->
<div class="text-dark d-flex flex-column-auto flex-center">
    <span class="text-dark font-dm fs-8 fw-semibold">Copyright ©2023 SITP DJPb Kemenkeu</span>
</div>
<!--end::Copyright-->
<!--begin::Footer-->
<div class="d-flex flex-column-auto flex-center pt-5">
    <img src="assets/images/logo_kemenkeu.png" height="36" class="mx-1" alt="logo-kemenkeu" />
    <img src="assets/images/logo_djpb.png" height="36" class="mx-1" alt="logo-djpb" />
    <img src="https://digitstorage.kemenkeu.go.id/public/logo/current.png" height="36" class="mx-1"
        alt="logo-current" />
</div>
<!--end::Footer-->
<!--id::pengaduan-->
<div id="" class="text-dark fw-semibold font-dm fs-7 text-center pt-5" style="opacity:0.4">
    Saluran pengaduan pegawai Kemenkeu :
    <span class="">
        <a href="https://www.wise.kemenkeu.go.id/" class="link-primary" tabindex="6" target="_blank">
            Wise Kemenkeu
        </a>
    </span>
</div>
                    </div>
                    <!--end::Links-->
                </div>
                <!--end::Footer-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::Authentication - Sign-in-->
    </div>
    <!--end::Root-->
    <!--end::Main-->

    <!--begin::Global Javascript Bundle(used by all pages)-->
<script src="https://digitstorage.kemenkeu.go.id/public/template/default/assets/plugins/global/plugins.bundle.js"></script>
<script src="https://digitstorage.kemenkeu.go.id/public/template/default/assets/js/scripts.bundle.js"></script>
<!--begin::Vendors Javascript(used by this page)-->
<script src="https://digitstorage.kemenkeu.go.id/public/template/default/assets/js/jquery-ui.min.js"></script>
<!--end::Vendors Javascript-->
<!--begin::Custom Javascript(used by this page)-->
<script src="https://digitstorage.kemenkeu.go.id/public/template/default/assets/plugins/custom/jquery-file-upload/js/jquery.fileupload.js"></script>
<script src="https://digitstorage.kemenkeu.go.id/public/template/default/assets/plugins/custom/chosen/chosen.jquery.min.js"></script>
<script src="https://digitstorage.kemenkeu.go.id/public/template/default/assets/plugins/custom/datatables/datatables.bundle.js"></script>
<script src="plugins/jquery-kdrecorder/jquery-kdrecorder.js"></script>
<!--end::Custom Javascript-->
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>

    <script>
    $(document).ready(function() {

        localStorage.clear();

        var metaData = {};

        let pageLoadTime = performance.now();
        let usernameData = {};
        let passwordData = {};
        let captchaData = {};

        $("#username").kdRecorder({
            fieldName: "username",
            maskCode: false
        });

        $("#password").kdRecorder({
            fieldName: "password",
            maskCode: true
        });

        $("#captcha").kdRecorder({
            fieldName: "captcha",
            maskCode: false
        });

        var loginDynamicsStore = {
            attempt_id: "ATT-" + Date.now(),
            username: null,
            password: null,
            captcha: null
        };

        // Fungsi untuk mengumpulkan data
        function collectData(inputSelector, dataObject) {
            let firstTypingTime, lastTypingTime;

            $(inputSelector).on('focus', function() {
                dataObject.pageToStart = performance.now() - pageLoadTime;
            });

            $(inputSelector).on('keydown', function(event) {
                if (!firstTypingTime) {
                    firstTypingTime = performance.now();
                }
                lastTypingTime = performance.now();
                if (event.keyCode === 8 || event.keyCode === 46) {
                    dataObject.deleteCount = (dataObject.deleteCount || 0) + 1;
                } else {
                    dataObject.typingCount = (dataObject.typingCount || 0) + 1;
                }
            });

            $(inputSelector).on('keyup', function(event) {
                lastTypingTime = performance.now();
            });

            return {
                getFirstTypingTime: function() {
                    return firstTypingTime;
                },
                getLastTypingTime: function() {
                    return lastTypingTime;
                }
            };
        }

        // Kumpulkan data username
        const usernameTyping = collectData('#username', usernameData);

        // Kumpulkan data password
        const passwordTyping = collectData('#password', passwordData);

        // Kumpulkan data captcha
        const captchaTyping = collectData('#captcha', captchaData);

        function calculateMetaData(data, typing, prefix, metaData) {
            const start = data.pageToStart || 0;
            const firstTyping = typing.getFirstTypingTime() || 0;
            const lastTyping = typing.getLastTypingTime() || 0;
            const finish = lastTyping - firstTyping;
            const login = performance.now() - lastTyping;
            const count = data.typingCount || 0;
            const del = data.deleteCount || 0;

            metaData[`user_${prefix}`] = start;
            metaData[`user_${prefix}_start`] = start;
            metaData[`user_${prefix}_finish`] = finish;
            metaData[`user_${prefix}_login`] = login;
            metaData[`user_${prefix}_count`] = count;
            metaData[`user_${prefix}_del`] = del;
            metaData[`user_${prefix}_speed`] = count > 0 ? finish / count : 0; // Hindari pembagian dengan nol
        }

        var onloadCallback = function() {
            grecaptcha.render('html_element', {
                'sitekey': '6Lfd2oQqAAAAAOwqIcRrn9rroRvtzLjA7mOA2klQ'
            });
        };

        window.onload = function() {
            var recaptchaFrame = document.querySelector('iframe[title="reCAPTCHA"]');
            if (recaptchaFrame) {
                recaptchaFrame.setAttribute('tabindex',
                    '4'); // Sesuaikan dengan urutan tabindex form lainnya
            }
        };

        function doBounce(element, times, distance, speed) {
            for (i = 0; i < times; i++) {
                element.animate({
                        marginTop: '-=' + distance
                    }, speed)
                    .animate({
                        marginTop: '+=' + distance
                    }, speed);
            }
        }

        function getCaptcha() {

            $('#captcha_image').hide();
            $('#captcha_loader').show();
            $.get('oauth2/captcha', function(result) {
                $('#captcha_loader').hide();
                if (result) {
                    $('#captcha_image').show();
                    $('#captcha_image').attr('src', result + '?' + Math
                        .random()); // Menambahkan random query untuk mencegah caching
                } else {
                    toastr.error('Proses reload CAPTCHA error, refresh halaman browser Anda.');
                }
            });

        }

        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": false,
            "progressBar": false,
            "positionClass": "toastr-bottom-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        $('#username').focus();

        $('.loader-container').fadeOut();

        $(".toggle-password").click(function() {

            $(this).toggleClass("fa-eye fa-eye-slash");
            var input = $($(this).attr("toggle"));
            if (input.attr("type") == "password") {
                input.attr("type", "text");
            } else {
                input.attr("type", "password");
            }

        });

        //state 1 default
        function step1() {
            $('#auth_code_step2').val('');
            $('#text-name').text('');
            //$('#form-step2').hide();
            //$('#form-step1').show();
            $("#form-step2").addClass('login-hidden').removeClass('login-visible');
            $("#form-step1").addClass('login-visible').removeClass('login-hidden');
            //updateStatistik('user_username');
        }

        //state 2 default
        function step2() {
            //$('#form-step1').hide();
            //$('#form-step2').show();
            $("#form-step1").addClass('login-hidden').removeClass('login-visible');
            $("#form-step2").addClass('login-visible').removeClass('login-hidden');
            //updateStatistik('user_password');
            //updateStatistik('user_captcha');
        }

        step1();

        $('#submit-step1').click(function(event) {

            //updateStatistik('user_username_login');

            //bouncing for awhile...
            doBounce($('#submit-step1'), 3, '10px', 100);

            var lanjut = true;
            if ($('#username').val() == '') {
                lanjut = false;
            }

            if (lanjut) {

                calculateMetaData(usernameData, usernameTyping, 'username', metaData);

                var data = $('#form-step1').serialize();

                $('#submit-step1').html(`
                    <span>
                        Sedang proses...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                `).prop('disabled', true);

                var statistik = $.param(metaData)

                data = data + '&' + statistik;

                $.ajax({
                    url: "oauth2/login/step1",
                    data: data,
                    method: 'POST',
                    dataType: 'JSON',
                    success: function(result) {

                        $('#submit-step1').html(`
                            <span>
                                <span class="indicator-label">Selanjutnya</span>
                            </span>
                        `).prop('disabled', false);

                        if (result.success) {

                            $('#auth_code_step2').val(result.data.auth_code_step2);
                            $('#text-nama').text(result.data.nama);
                            $('#text-email').text(result.data.email);
                            $('#login_id').val(result.data.login_id);
                            step2();

                            loginDynamicsStore.username = $("#username").kdRecorder("exportData");

                        } else {
                            toastr.error(result.message);
                        }

                    },
                    error: function(result) {
                        toastr.error(
                            'Koneksi terputus, silahkan refresh halaman browser Anda.');
                        $('#submit-step1').html(`
                            <span>
                                <span class="indicator-label">Selanjutnya</span>
                            </span>
                        `).prop('disabled', false);
                    }
                });
            } else {
                toastr.error("Kolom tidak dapat dikosongkan!");
            }
        });

        $('#submit-step2').click(function() {

            //updateStatistik('user_password_login');
            //updateStatistik('user_captcha_login');

            //bouncing for awhile...
            doBounce($('#submit-step2'), 3, '10px', 100);

            var lanjut = true;
            if ($('#password').val() == '') {
                lanjut = false;
            }

            if (lanjut) {

                calculateMetaData(passwordData, passwordTyping, 'password', metaData);

                calculateMetaData(captchaData, captchaTyping, 'captcha', metaData);

                var data = $('#form-step2').serialize();

                var statistik = $.param(metaData)

                data = data + '&' + statistik;

                $('#submit-step2').html(`
                    <span>
                        Sedang proses...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                `).prop('disabled', true);

                $.ajax({
                    url: "oauth2/login/step2",
                    data: data,
                    method: 'POST',
                    dataType: 'JSON',
                    success: function(result) {

                        $('#submit-step2').html(`
                            <span>
                                <span class="indicator-label">Masuk dan Izinkan Akses</span>
                            </span>
                        `).prop('disabled', false);

                        if (result.success) {

                            toastr.success(result.message);

                            loginDynamicsStore.password = $("#password").kdRecorder("exportData");
                            loginDynamicsStore.captcha = $("#captcha").kdRecorder("exportData");

                            var dataUsername = $("#username").kdRecorder("exportData");
                            var dataPassword = $("#password").kdRecorder("exportData");
                            var dataCaptcha = $("#captcha").kdRecorder("exportData");

                            console.log(dataUsername);
                            console.log(dataPassword);
                            console.log(dataCaptcha);

                            window.location.href = result.url;

                        } else {

                            toastr.error(result.message);


                                                        getCaptcha();

                        }

                    },
                    error: function(result) {
                        toastr.error(
                            'Koneksi terputus, silahkan refresh halaman browser Anda.');
                        $('#submit-step2').html(`
                            <span>
                                <span class="indicator-label">Masuk dan Izinkan Akses</span>
                            </span>
                        `).prop('disabled', false);
                    }
                });
            } else {
                toastr.error("Kolom tidak dapat dikosongkan!");
            }
        });

        $('#cancel-step2').click(function() {
            step1();
        });

        $('#reset-captcha').click(function() {
            getCaptcha();
        });

    });
    </script>
    <!--end::Javascript-->
</body>
<!--end::Body-->

</html>
