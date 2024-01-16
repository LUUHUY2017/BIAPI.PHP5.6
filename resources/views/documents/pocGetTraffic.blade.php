<style>
	* {
		margin: 0;
		padding: 0;
	}
	h1 {
		padding: 25px;
		background: #efefef;
		text-align: center;
	}
	ul {
		padding: 20px;
	}
	.trElement * {
		padding: 10px;
	}
</style>

<h1><img style="position: absolute;top: 0px;width: 50px;left: 0px;" src="{{$acsLogo}}" title="logo của ACS Solution">Tài liệu hướng dẫn sử dụng API lấy dữ liệu đếm người</h1>

<ul>
	<li>
		<b>API link: </b><a href="document/pocGetTraffic">{{asset('api/apiGetSourceDataInOut')}}</a>
	</li>
	<li>
		<b>Method: </b><span>POST</span>
	</li>
	<li>
		<b>Content-Type: </b><span>application/x-www-form-urlencoded</span>
	</li>
	<li>
		<b>Tham số truyền vào </b><small>( Request header )</small> :
		<div>
			<table border="1">
				<tbody>
					<tr class="trElement">
						<td>Content-Type</td>
						<td>application/x-www-form-urlencoded</td>
					</tr>
					<tr class="trElement">
						<td>Authorization</td>
						<td>Bearer *chuỗi access token*</td>
					</tr>
				</tbody>
			</table>
		</div>
		<b>Tham số truyền vào </b><small>( Request body )</small> :
		<div>
			<table border="1">
				<tbody>
					<tr class="trElement">
						<td>siteCode</td>
						<td><b>Kiểu dữ liệu:</b>string</td>
						<td><input type="text" placeholder="Nhập mã code của miền hoặc cửa hàng cụ thể"></td>
						<td style="color: crimson;"><small>* dữ liệu chỉ bao gồm chữ cái từ a đến Z, 0 đến 9, dấu "_" và dấu "-"</small></td>
					</tr>
					<tr class="trElement">
						<td>startDate</td>
						<td><b>Kiểu dữ liệu:</b>date</td>
						<td><input type="date" placeholder="Nhập ngày"></td>
						<td style="color: crimson;"><small>* dữ liệu chỉ bao gồm 2 kiểu là YYYY-MM-DD hoặc YYYY/MM/DD </small></td>
					</tr>
					<tr class="trElement">
						<td>endDate</td>
						<td><b>Kiểu dữ liệu:</b>date</td>
						<td><input type="date" placeholder="Nhập ngày"></td>
						<td style="color: crimson;"><small>* dữ liệu chỉ bao gồm 2 kiểu là YYYY-MM-DD hoặc YYYY/MM/DD </small></td>
					</tr>
					<tr class="trElement">
						<td>startHour</td>
						<td><b>Kiểu dữ liệu:</b>time</td>
						<td><input type="time" placeholder="Nhập giờ"></td>
						<td style="color: crimson;"><small>* giờ bắt đầu lấy dữ liệu </small></td>
					</tr>
					<tr class="trElement">
						<td>endHour</td>
						<td><b>Kiểu dữ liệu:</b>time</td>
						<td><input type="time" placeholder="Nhập giờ"></td>
						<td style="color: crimson;"><small>* giờ kết thúc lấy dữ liệu </small></td>
					</tr>
				</tbody>
			</table>
		</div>
	</li>
	<li>
		<b>Dữ liệu trả về:</b> <span>String JSON</span>
	</li>
	<li>
		<b>Ví dụ dữ liệu gửi về thành công:</b> <span>{
    "dataResponse": {
        "num_to_enter": "149",
        "num_to_exit": "139",
        "traffic": "288",
        "organization_id": "Tên tổ chức"
    },
    "status": "Success",
    "code": 1
}</span>
	</li>
	<li>
		<b>Ví dụ dữ liệu gửi về thất bại:</b> <span>{"status":"Fail","code":0,"dataResponse":null}</span>
	</li>
	<p style="color: crimson; margin-top: 10px;">* hiện tại API này chỉ trả về kết quả dữ liệu Traffic. Chúng tôi đang trong giai đoạn phát triển tính năng này. Rất mong khách hàng thông cảm. </p>
	<p><a href="{{asset('document')}}">Bấm vào đây để quay lại</a></p>
</ul>