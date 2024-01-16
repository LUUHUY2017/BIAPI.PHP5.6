<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Customer</title>
    {{-- <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script> --}}
</head>

<body>
    <div class="container-fuild">
        <div style="margin:30px;" class="row">
            <div class="table-responsive ">
                <table id="customer-daily" class="table  table-bordered table-striped table-visit ">
                    <thead>
                        <tr role="row">
                            <th rowspan="3" style="text-align: center;vertical-align: inherit;">Time</th>
                            <th rowspan="2" style="text-align: center;vertical-align: inherit;">FLOOR</th>
                            @foreach($parent_tree as $key => $item)
                            <th colspan="{{$item->number}}" style="text-align: center;">{{$item->site_name}} </th>
                            @endforeach
                            <th rowspan="3" style="text-align: center;vertical-align: inherit;width:20px ">Subtotal
                            </th>
                        </tr>
                        <tr role="row">
                            <th> </th>
                            <th> </th>
                            @foreach($child_tree as $key => $item)
                            <th> {{$item->site_name}}</th>
                            @endforeach
                        </tr>
                        <tr role="row">
                            <th> </th>
                            <th>Number of electric counter</th>

                            @foreach($child_tree as $key => $item)
                            <th> {{$item->site_code}}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Total -->
                        <tr role="row">
                            <td rowspan="2" style="text-align: center;vertical-align: inherit;font-weight: bold">ToTal
                            </td>
                            <td style="text-align: center;vertical-align: inherit;font-weight: bold">In</td>
                            @foreach($duLieuTongVaoCacCua as $key => $item)
                            <td style="text-align: center;vertical-align: inherit">
                                {{$item }} </td>
                            @endforeach
                        </tr>
                        <tr role="row">
                            <td style="text-align: center;vertical-align: inherit;font-weight: bold"> </td>
                            <td style="text-align: center;vertical-align: inherit;font-weight: bold">Out</td>
                            @foreach($duLieuTongRaCacCua as $key => $item)
                            <td style="text-align: center;vertical-align: inherit">
                                {{$item }} </td>
                            @endforeach
                        </tr>

                        <!-- Average of In and Out -->
                        <tr style="font-weight: bold" role="row">
                            <td colspan="2" style="text-align: left;vertical-align: inherit; ">Average of In and Out
                            </td>
                            @foreach($duLieuTrungBinhCacCua as $key => $item)
                            <td style="text-align: center;vertical-align: inherit">
                                {{$item }} </td>
                            @endforeach
                        </tr>

                        <!-- Rank of In and Out -->
                        <tr role="row">
                            <td colspan="2" style="text-align: left;vertical-align: inherit;font-weight: bold">Rank of
                                In and Out</td>
                            @foreach($duLieuThuTuCacCua as $key => $item)
                            <td style="text-align: center;vertical-align: inherit">
                                {{$item}} </td>
                            @endforeach
                            <td style="text-align: center;vertical-align: inherit">
                            </td>
                        </tr>

                        <!-- Body -->
                        @foreach($duLieuChiTiet as $key => $item)
                        <tr role="row">
                            <td rowspan="2" style="text-align: center;vertical-align: inherit;">
                                {{$item->time_period}}
                            </td>
                            <td style="text-align: center;vertical-align: inherit">In</td>
                            @foreach($item->ins as $key => $value)
                            <td style="text-align: center;vertical-align: inherit">{{$value }}</td>
                            @endforeach
                        </tr>
                        <tr role="row">
                            <td style="text-align: center;vertical-align: inherit"></td>
                            <td style="text-align: center;vertical-align: inherit">Out</td>
                            @foreach($item->outs as $key => $value)
                            <td style="text-align: center;vertical-align: inherit">{{$value }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>