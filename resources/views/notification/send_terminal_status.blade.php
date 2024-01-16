<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<style>
		table { 
			width: 750px; 
			border-collapse: collapse; 
			margin:50px auto;
		}

		tr:nth-of-type(odd) { 
			background: #eee; 
		}

		th { 
			background: #3498db; 
			color: white; 
			font-weight: bold; 
		}

		td, th { 
			padding: 10px; 
			border: 1px solid #ccc; 
			text-align: left; 
			font-size: 18px;
		}

/* 
Max width before this PARTICULAR table gets nasty
This query will take effect for any screen smaller than 760px
and also iPads specifically.

@media only screen and (max-width: 760px),
(min-device-width: 768px) and (max-device-width: 1024px)  {

	table { 
	  	width: 100%; 
	}

	table, thead, tbody, th, td, tr { 
		display: block; 
	}
	
	thead tr { 
		position: absolute;
		top: -9999px;
		left: -9999px;
	}
	
	tr { border: 1px solid #ccc; }
	
	td { 
		border: none;
		border-bottom: 1px solid #eee; 
		position: relative;
		padding-left: 50%; 
	}

	td:before { 
		position: absolute;
		top: 6px;
		left: 6px;
		width: 45%; 
		padding-right: 10px; 
		white-space: nowrap;
		content: attr(data-column);
		color: #000;
		font-weight: bold;
	}
}
*/
	</style>
</head>
<body>
	<h1 style="text-align: center;font-family: Arial, Helvetica, sans-serif;">{{isset($title) ? $title : 'Thống kê thiết bị 3D BrickStream'}}</h1>
	<table>
	  <thead>
	    <tr>
	      <th>Tên thiết bị</th>
	      <th>Tổ chức</th>
	      <th>Serial Number</th>
	      <th>Last time update data</th>
	      <th>Last time update socket</th>
	      <th>Trạng thái</th>
	    </tr>
	  </thead>
	  <tbody>
	  	@if (isset($body))
	  		@foreach ($body as $value)
			    <tr>
			      <td>{{$value->device_name}}</td>
			      <td>{{$value->organization_name}}</td>
			      <td>{{$value->serial_number}}</td>
			      <td>{{$value->last_time_update_data ? $value->last_time_update_data : 'Chưa xác định'}}</td>
			      <td>{{$value->last_time_update_socket ? $value->last_time_update_socket : 'Chưa xác định'}}</td>
			      <td>Ngắt kết nối</td>
			    </tr>
		    @endforeach
		@else
			<tr>
		      <td>Andor</td>
		      <td>Nagy</td>
		      <td>Nagy</td>
		      <td>Designer</td>
		      <td>@andornagy</td>
		      <td>Offline</td>
		    </tr>
		    <tr>
		      <td>Tamas</td>
		      <td>Biro</td>
		      <td>Biro</td>
		      <td>Game Tester</td>
		      <td>@tamas</td>
		      <td>Offline</td>
		    </tr>
		    <tr>
		      <td>Zoli</td>
		      <td>Mastah</td>
		      <td>Mastah</td>
		      <td>Developer</td>
		      <td>@zoli</td>
		      <td>Offline</td>
		    </tr>
		    <tr>
		      <td>Szabi</td>
		      <td>Nagy</td>
		      <td>Nagy</td>
		      <td>Chief Sandwich Eater</td>
		      <td>@szabi</td>
		      <td>Offline</td>
		    </tr>
	  	@endif
	  </tbody>
	</table>
	{{-- @foreach ($body as $value)
		<p>Thiết bị <b style="color: crimson;">{{$value->serial_number}}</b> tại tổ chức {{$value->organization_name}} đang ngắt kết nối.<p>
		<p><b>Last Time Update Socket:</b>{{$value->last_time_update_socket ?$value->last_time_update_socket : 'Chưa xác định'}} <b>Last Time Update Data: </b>{{$value->last_time_update_data ? $value->last_time_update_data : 'Chưa xác định'}}</p>
	@endforeach --}}
</body>
</html>