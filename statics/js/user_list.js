$(function () {

	$('#order_add').click(function () {

		$.ajax({
			method: 'POST',
			url: '/user/create_order_list',
			data: {user_flag: 15313177905, price: 100},
			dataType:'json',
			success: function (data) {
				if(data.code == 1){
					alert(data.msg);
				}else{
					alert(data.msg);
				}
			}
		});
	});


});