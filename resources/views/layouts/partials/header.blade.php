<div class="site-mobile-menu">
    <div class="site-mobile-menu-header">
        <div class="site-mobile-menu-close mt-3">
            <span class="icon-close2 js-menu-toggle"></span>
        </div>
    </div>
    <div class="site-mobile-menu-body"></div>
</div>

<header class="site-navbar py-3" role="banner">

    <div class="container">
        <div class="row align-items-center">

            <!-- Logo -->
            <div class="col-11 col-xl-2">
                <a href="{{ route('home') }}" class="d-inline-block">
                    <img src="{{ asset('asset/images/caaip-logo.png') }}" alt="CAAIP Logo"
                        style="max-height:80px; width:auto;">
                </a>
            </div>

            <div class="col-12 col-md-10 d-none d-xl-block">
                <nav class="site-navigation position-relative text-right" role="navigation">

                    <ul class="site-menu js-clone-nav mx-auto d-none d-lg-block">

                        <li class="{{ request()->routeIs('home') ? 'active' : '' }}">
                            <a href="{{ route('home') }}">Home</a>
                        </li>

                        <li>
                            <a href="#">About Us</a>
                        </li>

                        <li class="has-children">
                            <a href="#">Services</a>
                            <ul class="dropdown">
                                <li><a href="#">Air Freight</a></li>
                                <li><a href="#">Ocean Freight</a></li>
                                <li><a href="#">Ground Shipping</a></li>
                                <li><a href="#">Warehousing</a></li>
                                <li><a href="#">Storage</a></li>
                            </ul>
                        </li>

                        <li>
                            <a href="#">Industries</a>
                        </li>

                        <li>
                            <a href="#">Blog</a>
                        </li>

                        <li class="{{ request()->routeIs('contact') ? 'active' : '' }}">
                            <a href="{{ route('contact') }}">Contact</a>
                        </li>

                    </ul>

                </nav>
            </div>

            <div class="d-inline-block d-xl-none ml-md-0 mr-auto py-3" style="position: relative; top: 3px;">
                <a href="#" class="site-menu-toggle js-menu-toggle text-white">
                    <span class="icon-menu h3"></span>
                </a>
            </div>

        </div>
    </div>
</header>