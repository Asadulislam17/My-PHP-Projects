<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'he', 'fa', 'ur']) ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://bunny.net">
        <link href="https://bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        
        <!-- 📑 টেস্ট করার লাল বক্স -->
        <div id="debug-box" style="background: red; color: white; padding: 15px; position: fixed; top: 10px; left: 10px; z-index: 99999; font-weight: bold; border-radius: 5px;">
            Current Locale: {{ app()->getLocale() }} <br>
            Generated Dir: {{ in_array(app()->getLocale(), ['ar', 'he', 'fa', 'ur']) ? 'rtl' : 'ltr' }}
        </div>

        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- 📑 Roksyn টেমপ্লেটের ক্যাশ লক ভাঙার জন্য জাভাস্ক্রিপ্ট (যুক্ত করা হয়েছে) -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                let currentLocale = "{{ app()->getLocale() }}";
                let rtlLanguages = ['ar', 'he', 'fa', 'ur'];

                // যদি ভাষা RTL না হয় (যেমন English বা Bangla)
                if (!rtlLanguages.includes(currentLocale)) {
                    // Roksyn টেমপ্লেট ব্রাউজারে যে যে নামে RTL মোড সেভ করে রাখে, সেগুলো মুছে দেওয়া হচ্ছে
                    localStorage.removeItem('theme-direction'); 
                    localStorage.removeItem('roksyn-direction'); 
                    localStorage.removeItem('direction'); 
                    sessionStorage.clear(); // সেশন স্টোরেজও পরিষ্কার করা হলো
                    
                    // জোরপূর্বক পেজের ডিরেকশন LTR এ ফিরিয়ে আনা
                    document.documentElement.setAttribute('dir', 'lrt');
                    document.documentElement.dir = "ltr";
                }
            });
        </script>
    </body>
</html>
