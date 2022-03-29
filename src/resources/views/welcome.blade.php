<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">


        <link rel="apple-touch-icon-precomposed" sizes="57x57" href="/icon/apple-touch-icon-57x57.png" />
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/icon/apple-touch-icon-114x114.png" />
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/icon/apple-touch-icon-72x72.png" />
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/icon/apple-touch-icon-144x144.png" />
        <link rel="apple-touch-icon-precomposed" sizes="60x60" href="/icon/apple-touch-icon-60x60.png" />
        <link rel="apple-touch-icon-precomposed" sizes="120x120" href="/icon/apple-touch-icon-120x120.png" />
        <link rel="apple-touch-icon-precomposed" sizes="76x76" href="/icon/apple-touch-icon-76x76.png" />
        <link rel="apple-touch-icon-precomposed" sizes="152x152" href="/icon/apple-touch-icon-152x152.png" />
        <link rel="icon" type="image/png" href="/icon/favicon-196x196.png" sizes="196x196" />
        <link rel="icon" type="image/png" href="/icon/favicon-96x96.png" sizes="96x96" />
        <link rel="icon" type="image/png" href="/icon/favicon-32x32.png" sizes="32x32" />
        <link rel="icon" type="image/png" href="/icon/favicon-16x16.png" sizes="16x16" />
        <link rel="icon" type="image/png" href="/icon/favicon-128.png" sizes="128x128" />
        <meta name="msapplication-TileColor" content="#FFFFFF" />
        <meta name="msapplication-TileImage" content="/icon/mstile-144x144.png" />
        <meta name="msapplication-square70x70logo" content="/icon/mstile-70x70.png" />
        <meta name="msapplication-square150x150logo" content="/icon/mstile-150x150.png" />
        <meta name="msapplication-wide310x150logo" content="/icon/mstile-310x150.png" />
        <meta name="msapplication-square310x310logo" content="/icon/mstile-310x310.png" />


        <title>{{$system_name}}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,600,700" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i" rel="stylesheet">
        <link href="css/app.css" rel="stylesheet">
    </head>

    <body data-spy="scroll" data-target=".fixed-top">
    
    <!-- Preloader -->
	<div class="spinner-wrapper">
        <div class="spinner">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
    </div>
    <!-- end of preloader -->
    

    <!-- Navbar -->
    <nav class="navbar navbar-expand-md navbar-dark navbar-custom fixed-top">
        <!-- Text Logo - Use this if you don't have a graphic logo -->
        <a class="navbar-brand logo-text page-scroll" href="#header">{{$system_name}}</a>

        <!-- Image Logo -->
        {{-- <a class="navbar-brand logo-image page-scroll" href="#header"><img src="images/logo.svg" alt="alternative"></a>  --}}
        
        <!-- Mobile Menu Toggle Button -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-awesome fas fa-bars"></span>
            <span class="navbar-toggler-awesome fas fa-times"></span>
        </button>
        <!-- end of mobile menu toggle button -->

        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link page-scroll" href="#features">FEATURES</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link page-scroll" href="#pricing">PRICING</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link page-scroll" href="#contact">CONTACT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link page-scroll" href="#techs">TECHNOLOGIES</a>
                </li>
            </ul>
            @if (Route::has('login'))
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item p-2">
                            <a class="btn-solid-sm page-scroll mx-2" href="{{ url('/home') }}">DASHBOARD</a>
                        </li>
                    @else
                        <li class="nav-item p-2">
                            <a class="btn-outline-sm page-scroll mx-2" href="{{ route('login') }}">LOGIN</a>
                        </li>
                        <li class="nav-item p-2">
                            <a class="btn-solid-sm page-scroll mx-2" href="{{ route('register') }}">REGISTER</a>
                        </li>
                    @endauth
                </ul>
            @endif
        </div>
    </nav> <!-- end of navbar -->
    <!-- end of navbar -->


    <!-- Header -->
    <header id="header" class="header">
        <div class="header-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-xl-5">
                        <div class="text-container">
                            <h3 class="light">SCHOOL BUS TRACKER </h3>
                            <h3 class="light">FOR <span id="js-rotating">PARENTS, SCHOOLS, BUS DRIVERS</span></h3>
                            <div class="p-large pt-4"><strong>{{$system_name}}</strong> is a comprehensive <strong>SaaS</strong> school bus tracker system for managing the daily school bus operation 
                    that links the school, the parents and the buses into one powerful system that contains
                            <h5 class="pt-4">ADMIN WEB PANEL</h5>
                            <h5>PARENT MOBILE APP</h5>
                            <h5>DRIVER MOBILE APP</h5>
                            </div>
                            @if (Route::has('login'))
                                @auth
                                    <a class="btn-solid-lg py-4" href="{{ url('/home') }}">VIEW DASHBOARD</a>
                                @else
                                    <a class="btn-solid-lg py-4" href="{{ route('register') }}">START FOR FREE</a>
                                @endauth
                            @endif
                            
                        </div>
                    </div> <!-- end of col -->
                    <div class="col-lg-6 col-xl-7">                    
                        <div class="image-container">
                            <img class="img-fluid" src="images/screens.png" alt="alternative">
                        </div> <!-- end of image-container -->
                    </div> <!-- end of col -->
                </div> <!-- end of row -->
            </div> <!-- end of container -->
        </div> <!-- end of header-content -->
    </header> <!-- end of header -->
    <!-- end of header -->

    <!-- Features -->
    <div id="features" class="tabs">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                <h2 class="section-heading text-uppercase">FEATURES</h2>
                <h4 class="section-subheading pb-2">Two mobile apps for parents and bus drivers with school and super admin web panels</h4>
                </div> <!-- end of col -->
            </div> <!-- end of row -->
            <div class="row">

                <!-- Tabs Links -->
                <ul class="nav nav-tabs" id="lenoTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="nav-tab-1" data-toggle="tab" href="#tab-1" role="tab" aria-controls="tab-1" aria-selected="true"><i class="fas fa-user-alt"></i>PARENT APP</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="nav-tab-2" data-toggle="tab" href="#tab-2" role="tab" aria-controls="tab-2" aria-selected="false"><i class="fas fa-bus"></i>DRIVER APP</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="nav-tab-3a" data-toggle="tab" href="#tab-3a" role="tab" aria-controls="tab-3a" aria-selected="false"><i class="fas fa-users-cog"></i>SCHOOL ADMIN</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="nav-tab-3b" data-toggle="tab" href="#tab-3b" role="tab" aria-controls="tab-3b" aria-selected="false"><i class="fab fa-superpowers"></i> SUPER ADMIN</a>
                    </li>
                </ul>
                <!-- end of tabs links -->


                <!-- Tabs Content-->
                <div class="tab-content" id="lenoTabsContent">
                    <!-- Tab -->
                    <div class="tab-pane fade show active" id="tab-1" role="tabpanel" aria-labelledby="tab-1">
                        <div class="container">
                            <div class="row">
                                
                                <!-- Icon Cards Pane -->
                                <div class="col-lg-4">
                                    <div class="card left-pane first">
                                        <div class="card-body d-flex">
                                            <div class="text-wrapper">
                                                <h4 class="card-title">MVVM Architecture</h4>
                                                <p>We employ the MVVM architecture to create a professional level application that has decoupled, testable, maintainable, and clean code.</p>
                                            </div>
                                            <div class="card-icon">
                                                <i class="fas fa-tools"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card left-pane">
                                        <div class="card-body d-flex">
                                            <div class="text-wrapper">
                                                <h4 class="card-title">Android Studio Project</h4>
                                                <p>Built with native Java in Android Studio for easy customization</p>
                                            </div>
                                            <div class="card-icon">
                                                <i class="fab fa-android"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card left-pane">
                                        <div class="card-body d-flex">
                                            <div class="text-wrapper">
                                                <h4 class="card-title">Call Driver</h4>
                                                <p>Parents can call the bus driver from the app.</p>
                                            </div>
                                            <div class="card-icon">
                                                <i class="fas fa-phone"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end of icon cards pane -->

                                <!-- Image Pane -->
                                <div class="col-lg-4">
                                    <img class="img-fluid" src="images/parent_app.png" alt="alternative">
                                </div>
                                <!-- end of image pane -->
                                
                                <!-- Icon Cards Pane -->
                                <div class="col-lg-4">
                                    <div class="card right-pane first">
                                        <div class="card-body d-flex">
                                            <div class="card-icon">
                                                <i class="fas fa-bus"></i>
                                            </div>
                                            <div class="text-wrapper">
                                                <h4 class="card-title">Real Time - No Pooling</h4>
                                                <p>App recieves the school bus location update in real time without pooling the server. Driver location tracked by parents who assigned to the driver.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card right-pane">
                                        <div class="card-body d-flex">
                                            <div class="card-icon">
                                                <i class="far fa-bell"></i>
                                            </div>
                                            <div class="text-wrapper">
                                                <h4 class="card-title">Notifications</h4>
                                                <p>Parents receive notifications according to their preferences.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card right-pane">
                                        <div class="card-body d-flex">
                                            <div class="card-icon">
                                                <i class="fas fa-thumbtack"></i>
                                            </div>
                                            <div class="text-wrapper">
                                                <h4 class="card-title">Set Location</h4>
                                                <p>Parents can set their preferred pick-up/drop-off location</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end of icon cards pane -->
                            </div> <!-- end of row -->
                        </div> <!-- end of container -->
                    </div> <!-- end of tab-pane -->
                    <!-- end of tab -->

                    <!-- Tab -->
                    <div class="tab-pane fade" id="tab-2" role="tabpanel" aria-labelledby="tab-2">
                        <div class="container">
                            <div class="row">
                                <!-- Text And Icon Cards Area -->
                                <div class="col-md-4">
                                    <div>
                                        <div class="card">
                                            <div class="card-icon">
                                                <i class="far fa-file-code"></i>
                                            </div>
                                            <div class="card-body">
                                                <h4 class="card-title">MVVM Architecture</h4>
                                                <p>We employ the MVVM architecture to create a professional level application that has decoupled, testable, maintainable, and clean code.</p>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-icon">
                                                <i class="fas fa-home"></i>
                                            </div>
                                            <div class="card-body">
                                                <h4 class="card-title">View Parents Locations</h4>
                                                <p>Driver can view the pick-up/drop-off location of all parents</p>
                                            </div>
                                        </div>
                                    </div> <!-- end of icon cards area -->
                                </div> <!-- end of col-md-4 -->
                                <!-- Text And Icon Cards Area -->
                                <div class="col-md-4">
                                    <div>
                                        <div class="card">
                                            <div class="card-icon">
                                                <i class="fab fa-android"></i>
                                            </div>
                                            <div class="card-body">
                                                <h4 class="card-title">Android Studio Project</h4>
                                                <p>Built with native Java in Android Studio for easy customization</p>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-icon">
                                                <i class="fas fa-phone"></i>
                                            </div>
                                            <div class="card-body">
                                                <h4 class="card-title">Call Parents</h4>
                                                <p>Driver can search parents by name or telephone number and can call a parent from the app</p>
                                            </div>
                                        </div>
                                    </div> <!-- end of icon cards area -->
                                </div> <!-- end of col-md-4 -->    
                                
                                <!-- Image Pane -->
                                <div class="col-md-4">
                                    <img class="img-fluid" src="images/driver_app.png" alt="alternative">
                                </div>
                                <!-- end of image pane -->
                                    
                            </div> <!-- end of row -->
                        </div> <!-- end of container -->
                    </div><!-- end of tab-pane -->
                    <!-- end of tab -->

                    <!-- Tab -->
                    <div class="tab-pane fade" id="tab-3a" role="tabpanel" aria-labelledby="tab-3a">
                        <div class="container">
                            <div class="row">
                                <!-- Image Pane -->
                                <div class="col-md-4">
                                    <img class="img-fluid" src="images/school-admin.png" alt="alternative">
                                </div>
                                <!-- end of image pane -->
                                
                                <!-- Text And Icon Cards Area -->
                                <div class="col-md-8 pt-4">
                                    <div class="text-area">
                                        <h3>School Admin Panel</h3>
                                        <p> Transport Managers or school administrators have various tasks including student safety, addressing parent concerns, allocation, and optimization.</p>
                                        <p>Our admin panel is designed to manage these tasks without any hassle.</p>
                                    </div> <!-- end of text-area -->
                                </div> <!-- end of text and icon cards area -->
                            </div> <!-- end of row -->
                            <div class="row pt-4">   
                                <!-- Text And Icon Cards Area -->
                                <div class="col-sm-12 col-md-4">
                                    <div class="card">
                                        <div class="card-icon">
                                            <i class="fas fa-tachometer-alt"></i>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="card-title">Dashboard</h4>
                                            <p>View your registered parents, drivers, and get statistics about mobile apps usage at a glance</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <div class="card">
                                        <div class="card-icon">
                                            <i class="fas fa-landmark"></i>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="card-title">School</h4>
                                            <p>Update school address by writing its address. Do not worry! Google maps places API will show up a list of addresses that match your entered address</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <div class="card">
                                        <div class="card-icon">
                                            <i class="fas fa-bus-alt"></i>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="card-title">Drivers</h4>
                                            <p>Manage drivers with add, read, update, and delete capabilities. Also, view the real-time location of a driver on the map</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <div class="card">
                                        <div class="card-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="card-title">Parents</h4>
                                            <p>Manage parents with add, read, update, and delete capabilities. Also, view the pick-up/drop-off location of a parent on the map</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <div class="card">
                                        <div class="card-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="card-title">Profile</h4>
                                            <p>Manage the school profile</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <div class="card">
                                        <div class="card-icon">
                                            <i class="fas fa-trophy"></i>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="card-title">Choose Plan</h4>
                                            <p>Choose a suitable plan for the school account</p>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- end of row -->
                        </div> <!-- end of container -->
                    </div> <!-- end of tab-pane -->
                    <!-- end of tab -->

                    <!-- Tab -->
                    <div class="tab-pane fade" id="tab-3b" role="tabpanel" aria-labelledby="tab-3b">
                        <div class="container">
                            <div class="row">
                                <!-- Image Pane -->
                                <div class="col-md-4">
                                    <img class="img-fluid" src="images/super-admin.png" alt="alternative">
                                </div>
                                <!-- end of image pane -->
                                
                                <!-- Text And Icon Cards Area -->
                                <div class="col-md-8 pt-4">
                                    <div class="text-area">
                                        <h3>Super Admin Panel</h3>
                                        <p> Super admin of {{$system_name}} has many tasks including managing different school accounts, configuring payment plans, and monitoring his Stripe balance.</p>
                                        <p>Our admin panel is designed to manage these tasks without any hassle.</p>
                                    </div> <!-- end of text-area -->
                                </div> <!-- end of text and icon cards area -->
                            </div> <!-- end of row -->
                            <div class="row pt-4">
                                <!-- Text And Icon Cards Area -->
                                <div class="col-sm-12 col-md-4">
                                    <div class="card">
                                        <div class="card-icon">
                                            <i class="fas fa-tachometer-alt"></i>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="card-title">Dashboard</h4>
                                            <p>View your registered schools and your Stripe balance at a glance</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <div class="card">
                                        <div class="card-icon">
                                            <i class="fas fa-landmark"></i>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="card-title">Manage Schools</h4>
                                            <p>Manage your registered schools and view their current plans</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <div class="card">
                                        <div class="card-icon">
                                            <i class="far fa-handshake"></i>
                                        </div>
                                        <div class="card-body">
                                            <h4 class="card-title">Payment & Settings</h4>
                                            <p>Manage the payment plans and configure every plan detail. Also, configure the system by providing necessary keys for various features including Google Maps, Stripe, ...</p>
                                        </div>
                                    </div>
                                </div>
                                <!-- end of text and icon cards area -->
                            </div> <!-- end of row -->
                        </div> <!-- end of container -->
                    </div> <!-- end of tab-pane -->
                    <!-- end of tab -->
                </div> <!-- end of tab-content -->
                <!-- end of tabs content -->

            </div> <!-- end of row --> 
        </div> <!-- end of container --> 
    </div> <!-- end of tabs -->
    <!-- end of features -->


    <!-- Pricing -->
    <div class="pricing basic-1" id="pricing">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
            <h2 class="section-heading text-uppercase light">PRICES</h2>
            <h4 class="section-subheading pb-2 light">Flexible pricing plans managed by the super admin</h4>
            </div>
        </div>
        <div class="row">
            @foreach ($plans as $plan)
            <?php 
            $plan_driver_display_count = "";
            if($plan->allowed_drivers==-1)
                $plan_driver_display_count = "Unlimited Tracked Buses";
            else if($plan->allowed_drivers==1)
                $plan_driver_display_count = "Single Tracked Bus";
            else
                $plan_driver_display_count = $plan->allowed_drivers." Tracked Buses";
            ?>
                <div class="col-lg-4 mb-2">
                    <div class="card mb-5 mb-lg-0">
                    <div class="card-body">
                        <h5 class="card-title text-uppercase text-center light">{{$plan->name}}</h5>
                        <h6 class="card-price text-center yellow">
                        <?php if($currency == "USD")  
                                echo '$'. $plan->price; 
                              else
                                echo $plan->price. ' '. $currency; 
                        ?>
                        <span class="period">/{{$billing_cycle}}</span></h6>
                        <hr>
                        <ul class="fa-ul light">
                        <li><span class="fa-li"><i class="fas fa-check"></i></span><strong>{{$plan_driver_display_count}}</strong></li>
                        </ul>
                    </div>
                    </div>
                </div>
            @endforeach            
        </div>
    </div>
    </div>


    <!-- Details 1 -->
    <div id="techs" class="basic-2">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                <h2 class="section-heading text-uppercase">Technologies</h2>
                <h4 class="section-subheading py-2">Employing latest technologies, providing clean and well commented code are our goals</h4>
                </div>
            </div>
            <div class="row pt-4 d-flex justify-content-center">
                <div class="px-4 text-area-right p-large-dark">
                    <ul>
                        <li>Android</li>
                        <li>Laravel</li>
                        <li>VueJS</li>
                        <li>Firebase Cloud Messaging</li>
                        <li>Socket.io</li>
                        <li>Redis</li>
                        <li>Bootstrap</li>
                        <li>Chart.js</li>
                        <li>Google Maps SDK</li>
                        <li>Stripe SDK</li>
                        <li>Twilio SDK</li>
                    </ul>
                </div> <!-- end of col -->
                <div>
                    <img class="img-fluid" src="images/brands.png" alt="alternative">
                </div> <!-- end of col -->

            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </div> <!-- end of basic-2 -->
    <!-- end of details 1 -->


    
    <!-- Footer -->
    <div id="contact" class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="footer-col">
                        <h4>Contact us</h4>
                        <ul class="list-unstyled li-space-lg">
                            <li class="media">
                                <i class="fas fa-square"></i>
                                <div class="media-body">Website: <a href="{{$company_website}}">{{$company_website}}</a></div>
                            </li>
                            <li class="media">
                                <i class="fas fa-square"></i>
                                <div class="media-body">Email: <a href="mailto: {{$company_email}}">{{$company_email}}</a></div>
                            </li>
                            <li class="media">
                                <i class="fas fa-square"></i>
                                <div class="media-body">Tel : {{$company_telephone}}</div>
                            </li>
                        </ul>
                    </div>
                </div> <!-- end of col -->
                <div class="col-md-6">
                    <div class="footer-col last">
                        <h4>Social Media</h4>
                        <span class="fa-stack">
                            <a href="{{$facebook_link}}">
                                <i class="fas fa-circle fa-stack-2x"></i>
                                <i class="fab fa-facebook-f fa-stack-1x"></i>
                            </a>
                        </span>
                        <span class="fa-stack">
                            <a href="{{$twitter_link}}">
                                <i class="fas fa-circle fa-stack-2x"></i>
                                <i class="fab fa-twitter fa-stack-1x"></i>
                            </a>
                        </span>
                        <span class="fa-stack">
                            <a href="{{$instagram_link}}">
                                <i class="fas fa-circle fa-stack-2x"></i>
                                <i class="fab fa-instagram fa-stack-1x"></i>
                            </a>
                        </span>
                        <span class="fa-stack">
                            <a href="{{$linkedin_link}}">
                                <i class="fas fa-circle fa-stack-2x"></i>
                                <i class="fab fa-linkedin-in fa-stack-1x"></i>
                            </a>
                        </span>
                    </div> 
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </div> <!-- end of footer -->  
    <!-- end of footer -->


    <!-- Copyright -->
    <div class="copyright">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <p class="p-small">Copyright © <a href="{{$company_website}}" style="color: #f6c611;">{{$company_title}}</a></p>
                </div> <!-- end of col -->
            </div> <!-- enf of row -->
        </div> <!-- end of container -->
    </div> <!-- end of copyright --> 
    <!-- end of copyright -->
    
    	
    <!-- Scripts -->
    <script src="js/landing/jquery.min.js"></script> <!-- jQuery for Bootstrap's JavaScript plugins -->
    {{-- <script src="js/landing/popper.min.js"></script> <!-- Popper tooltip library for Bootstrap --> --}}
    <script src="js/landing/bootstrap.min.js"></script> <!-- Bootstrap framework -->
    <script src="js/landing/jquery.easing.min.js"></script> <!-- jQuery Easing for smooth scrolling between anchors -->
    {{-- <script src="js/landing/swiper.min.js"></script> <!-- Swiper for image and text sliders --> --}}
    {{-- <script src="js/landing/jquery.magnific-popup.js"></script> <!-- Magnific Popup for lightboxes --> --}}
    <script src="js/landing/morphext.min.js"></script> <!-- Morphtext rotating text in the header -->
    {{-- <script src="js/landing/validator.min.js"></script> <!-- Validator.js - Bootstrap plugin that validates forms --> --}}
    <script src="js/landing/scripts.js"></script> <!-- Custom scripts -->
</body>
</html>