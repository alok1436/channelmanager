jQuery( function( $ ){	

	jQuery.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
		}
	});

	
 	/*Action : ajax
 	* used to: submit forms
 	* Instance of: Jquery vailidate libaray
	* @JSON 
 	**/
	$("#form").validate({
		errorPlacement: function (error, element) {
			 return;
		},
		highlight: function(element) {
        	$(element).addClass('is-invalid');
        	$(element).parent().addClass("error");
	    },
	    unhighlight: function(element) {
	    	$(element).parent().removeClass("error");
	        $(element).removeClass('is-invalid').addClass('is-valid');
	    },
		submitHandler: function(form){
			
			var formData = new FormData($("#form")[0]);
			$.ajax({
			  	beforeSend:function(){
			  		$("#form").find('button').attr('disabled',true);
					$("#form").find('button>i').show(); 
			  	},
			  	url: $("#form").attr('action'),
			  	data: formData,
			  	type: 'POST',
			  	processData: false,
    			contentType: false,
			  	success:function(response){
				  	if(response.success){
				        toastr.success(response.message,'Success');
				        console.log(response.reload);
				        if (response.reload != undefined) {
				        	location.reload();
				        }else if (response.redirect_url != undefined) {
							setTimeout(function(){
								 location.href = response.redirect_url;
							},1000);
						}
				  	}else{
					  
				  	}
				  	$(".modal").modal('hide');
			  	},
			  	complete:function(){
			  		$("#form").find('button').attr('disabled',false);
					$("#form").find('button>i').hide(); 
			  	},
              	error:function(status,error){
					var errors = JSON.parse(status.responseText);
					var msg_error = '';
					if(status.status == 401){
	                    $("#form").find('button').attr('disabled',false);
	                    $("#form").find('button>i').hide();  
						$.each(errors.error, function(i,v){	
							msg_error += v[0]+'!</br>';
						});
						toastr.error( msg_error,'Opps!'); 
					}else{
						toastr.error(errors.message,'Opps!');
					} 				
              	}		  
			});	
			return false;
		}
	});

	//send mail to customer
	$("body").on("click",".sendMailToCustomer",function(){
		var id = $(this).data('id');
		$.ajax({
		    url: ajaxurl+'/admin/sendMailToCustomer',
		    type:'POST',
		    data:{ id:id},
		    beforeSend:function(){
		  		$(".sendMailToCustomer").find('i#spin_loader1').show(); 
		  	},
		    success:function(response){
		      	if (response.success) {
			      	if(response.success){
				        toastr.success(response.message,'Success');
				         
				  	}
		      	}
		  	},   
		  	complete:function(){
		  		$(".sendMailToCustomer").find('i#spin_loader1').hide(); 
		  	},
          	error:function(status,error){
				var errors = JSON.parse(status.responseText);
				var msg_error = '';
				if(status.status == 401){
                    $(".sendMailToCustomer").find('i#spin_loader1').hide(); 
					$.each(errors.error, function(i,v){	
						msg_error += v[0]+'!</br>';
					});
					toastr.error( msg_error,'Opps!'); 
				}else{
					toastr.error(errors.message,'Opps!');
				} 				
          	}   
		}); 
		return false;
	});

	$("body").on("click",".update_user",function(){
		$("#form")[0].reset();
		var r = $(this).data('data');
		$("#hid_id").val(r.id);
		$("#crm_url").val(r.meta_data.crm_url);
		$("#crm_status").val(r.status);
		$("#crm_username").val(r.meta_data.crm_username);
		$("#crm_password").val(r.meta_data.crm_password);
	});
	//send mail to admin
	$("body").on("click",".sendMailToAdmin",function(){
		var id = $(this).data('id');
		$.ajax({
		    url: ajaxurl+'/admin/sendMailToAdmin',
		    type:'POST',
		    data:{ id:id},
		    beforeSend:function(){
		  		$(".sendMailToAdmin").find('i#spin_loader2').show(); 
		  	},
		    success:function(response){
		      	if (response.success) {
			      	if(response.success){
				        toastr.success(response.message,'Success');
				         
				  	}
		      	}
		  	},
		  	complete:function(){
		  		$(".sendMailToAdmin").find('i#spin_loader2').hide(); 
		  	},
          	error:function(status,error){
				var errors = JSON.parse(status.responseText);
				var msg_error = '';
				if(status.status == 401){
                    $(".sendMailToAdmin").find('i#spin_loader2').hide(); 
					$.each(errors.error, function(i,v){	
						msg_error += v[0]+'!</br>';
					});
					toastr.error( msg_error,'Opps!'); 
				}else{
					toastr.error(errors.message,'Opps!');
				} 				
          	}        
		}); 
		return false;
	});
});
