<!DOCTYPE html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
data-assets-path="{{ config('app.assets_path') }}" data-theme="theme-default"
    data-template="vertical-menu-template">

<head>
    @include('layout.head')
     <!-- Page -->
     <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-misc.css') }}" />
</head>


<body>
    <!-- Layout wrapper -->
    <div class="container-xxl container-p-y">
        <div class="misc-wrapper">
          <h1 class="mb-2 mx-2">@yield('code')</h1>
          <p class="mb-4 mx-2">@yield('message')</p>
          @yield('btn')
          <div class="mt-4">
            <img
              src="{{ asset('assets/img/illustrations/boy-working-light.png') }}"
              alt="girl-doing-yoga-light"
              width="500"
              class="img-fluid"
              data-app-light-img="illustrations/boy-working-light.png"
              data-app-dark-img="illustrations/boy-working-dark.png" />
          </div>
        </div>
      </div>
    <!-- / Layout wrapper -->


    <!-- Javascripts -->
    @include('layout.script')

</body>

</html>
