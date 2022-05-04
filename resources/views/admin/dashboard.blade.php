@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Home</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <div class="card card-widget">
                    <div class="card-body gradient-3">
                        <div class="media">
                            <span class="card-widget__icon"><i class="icon-people"></i></span>
                            <div class="media-body">
                                <h2 class="card-widget__title">{{ $customers }}</h2>
                                <h5 class="card-widget__subtitle">Total Customers</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <div class="card card-widget">
                    <div class="card-body gradient-4">
                        <div class="media">
                            <span class="card-widget__icon"><i class="icon-tag"></i></span>
                            <div class="media-body">
                                <h2 class="card-widget__title">{{ $products }}</h2>
                                <h5 class="card-widget__subtitle">Total Products</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <div class="card card-widget">
                    <div class="card-body gradient-4">
                        <div class="media">
                            <span class="card-widget__icon"><i class="icon-info"></i></span>
                            <div class="media-body">
                                <h2 class="card-widget__title">{{ $invoice_today }}</h2>
                                <h5 class="card-widget__subtitle">Today Invoice</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6 col-md-6 col-sm-12 col-xl-3">
                <div class="card card-widget">
                    <div class="card-body gradient-9">
                        <div class="media">
                            <span class="card-widget__icon"><i class="icon-ghost"></i></span>
                            <div class="media-body">
                                <h2 class="card-widget__title">{{ $amount_invoice_today }}</h2>
                                <h5 class="card-widget__subtitle">Total Amount of Invoice</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <canvas id="invoice_line_chart" height="100px"></canvas>
    </div>
@endsection

@section('js')
<script type="text/javascript">
    var cData = JSON.parse('<?php echo $final_chart_data; ?>');
    // console.log("cData:",cData);

    const data = {
        labels: cData.label,
        datasets: [{
            label: 'Invoice',
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: cData.data,
        }]
    };

    const config = {
        type: 'line',
        data: data,
        options: {}
    };

    const myChart = new Chart(
        document.getElementById('invoice_line_chart'),
        config
    );
</script>
@endsection
