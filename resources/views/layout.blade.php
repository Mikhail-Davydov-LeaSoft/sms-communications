<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <!-- Meta Information -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fmtod{{ config('app.name') ? ' - ' . config('app.name') : '' }}</title>

    <!-- Style sheets-->
    <link href="https://fonts.bunny.net/css?family=Nunito&display=swap" rel="stylesheet">
</head>
<body class="overflow-hidden h-100" style="height: 100%" >
@inertia
<script src="{{asset(mix('app.js', 'vendor/sms-communications'))}}"></script>
</body>
</html>
