<!DOCTYPE html>
<html lang="en">

    <head>
        <title>Student Exam</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--===============================================================================================-->
        <link rel="icon" type="image/png" href="images/icons/favicon.ico">
        <!--===============================================================================================-->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

        <!-- Animate CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

        <!-- Hamburgers CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/hamburgers/1.2.1/hamburgers.min.css">

        <!-- Select2 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
        <!--===============================================================================================-->
        <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/util.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('frontend/css/main.css') }}">
        <!--===============================================================================================-->
        <meta name="robots" content="noindex, follow">
    </head>

    <body>

        <div class="bg-contact100" style="background-image: url('{{ asset('frontend/images/bg-01.jpg') }}');">
            <div class="container-contact100">
                <div class="wrap-contact100">
                    <div class="contact100-pic js-tilt" data-tilt="" style="will-change: transform; transform: perspective(300px) rotateX(0deg) rotateY(0deg);">
                        <img src="{{ asset('frontend/images/img-01.png') }}" alt="IMG">
                    </div>

                    <form class="contact100-form validate-form" action="{{ route('frontend.contact.store') }}" method="POST">
                        @csrf
                        @method('POST')
                        <span class="contact100-form-title">
                            Get in touch
                        </span>

                        <div class="wrap-input100 validate-input" data-validate="Name is required">
                            <input class="input100" type="text" name="name" placeholder="Name">
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-user" aria-hidden="true"></i>
                            </span>
                        </div>

                        <div class="wrap-input100 validate-input" data-validate="Valid email is required: ex@abc.xyz">
                            <input class="input100" type="text" name="email" placeholder="Email">
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                            </span>
                        </div>

                        <div class="wrap-input100 validate-input" data-validate="Subject is required">
                            <input class="input100" type="text" name="subject" placeholder="subject">
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-pencil" aria-hidden="true"></i>
                            </span>
                        </div>

                        <div class="wrap-input100 validate-input" data-validate="Message is required">
                            <textarea class="input100" name="message" placeholder="Message"></textarea>
                            <span class="focus-input100"></span>
                        </div>

                        <div class="container-contact100-form-btn">
                            <button class="contact100-form-btn">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--===============================================================================================-->
        <script type="text/javascript" async="" src="https://www.google-analytics.com/analytics.js"></script>
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <!-- Popper.js (needed for Bootstrap 4) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
        <!-- Select2 -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <!-- Tilt.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tilt.js/1.2.1/tilt.jquery.min.js"></script>
        <script>
            $('.js-tilt').tilt({
                scale: 1.1
            })
        </script>
        <!--===============================================================================================-->
        <script src="{{ asset('frontend/js/main.js') }}"></script>

        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async="" src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());

            gtag('config', 'UA-23581568-13');
        </script>
    </body>

</html>
