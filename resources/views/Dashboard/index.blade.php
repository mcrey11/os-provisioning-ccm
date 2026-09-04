@extends('Layout.default')

@section('content')
<div class="p-3 md:p-6">
    <h1 class="page-header dark:text-slate-100 text-xl font-semibold mb-6">{{ $view_header }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

        {{-- Modems --}}
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-signal',
            'title' => 'Total Modems',
            'value' => number_format($stats['modems']),
            'widget_bg_color' => 'bg-white',
            'link_target' => route('NetElement.index'),
        ])

        {{-- Contracts --}}
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-file-text-o',
            'title' => 'Total Contracts',
            'value' => number_format($stats['contracts']),
            'widget_bg_color' => 'bg-white',
            'link_target' => '#',
        ])

        {{-- Network Elements --}}
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-sitemap',
            'title' => 'Network Elements',
            'value' => number_format($stats['net_elements']),
            'widget_bg_color' => 'bg-white',
            'link_target' => route('NetElement.index'),
        ])

        {{-- Config Files --}}
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-cogs',
            'title' => 'Config Files',
            'value' => number_format($stats['configfiles']),
            'widget_bg_color' => 'bg-white',
            'link_target' => '#',
        ])

        {{-- Products --}}
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-cube',
            'title' => 'Products',
            'value' => number_format($stats['products']),
            'widget_bg_color' => 'bg-white',
            'link_target' => '#',
        ])

        {{-- Users --}}
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-users',
            'title' => 'Users',
            'value' => number_format($stats['users']),
            'widget_bg_color' => 'bg-white',
            'link_target' => route('User.index'),
        ])

        {{-- Tickets --}}
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-ticket',
            'title' => 'Tickets',
            'value' => number_format($stats['tickets']),
            'widget_bg_color' => 'bg-white',
            'link_target' => '#',
        ])

        {{-- Support Requests --}}
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-life-ring',
            'title' => 'Support Requests',
            'value' => number_format($stats['support_requests']),
            'widget_bg_color' => 'bg-white',
            'link_target' => route('SupportRequest.index'),
        ])

        {{-- System Config --}}
        @include('bootstrap.widget', [
            'widget_icon' => 'fa-sliders',
            'title' => 'System Config',
            'value' => number_format($stats['global_config']),
            'widget_bg_color' => 'bg-white',
            'link_target' => route('GlobalConfig.index'),
        ])

    </div>
</div>
@endsection
