<!DOCTYPE html>
<html lang="en">
<head>
  <title></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
  <h2>Please wait, orders are being sync...</h2>
  <div class="" id="results">
  </div>
</div>
<script>
    
    function getChannel(channelId){
        $.ajax({
              type: 'GET',
              async:true,
              url: "order.php?channelId="+channelId,
              dataType: "json",
              success: function(res) {
                    
                    var html = '<div class="item channel" style="color:green;padding:10px 0px">getting order for channel- '+res.channel.shortname+'</div>';
                    $("#results").append(html);
                  
                    if(res.orders){
                        // for (let x in res.orders) {
                        //      getUpdateOrderItem(res.orders[x], res.channel)
                        //     console.log(x);
                        // }   
                        
                        $(res.orders).each(function(i, elm) {
                            
                             getUpdateOrderItem(elm, res.channel);
                            
                            
                        }).promise().done( function(){ 
                            
                            getChannel(2);
                            console.log('hello');    
                        
                        } );
                      
                        // $.each(res.orders, function(k,v){
                        //     getUpdateOrderItem(v, res.channel);
                        //     //console.log(k,v);
                        // }).promise().done( function(){ alert("All was done"); } );
                        // for(var i=0; i<= res.orders.length; i++){
                        //     getUpdateOrderItem(res.orders[0], res.channel);
                        //     //break;
                        // }
                        
                         
                    }
              }
        });
    }
    
    function getUpdateOrderItem(order, channel){
        $.ajax({
              type: 'POST',
              async:false,
              url: "orderitems.php",
              data: {
                  order:order,
                  channel:channel
              },
              //dataType: "json",
              success: function(res) {
                var html = '<div class="item" style="color:green;padding:10px 0px">'+res+'</div>';
                $("#results").append(html);
              }
        });
    }
    
    getChannel(1);
</script>
</body>
</html>
