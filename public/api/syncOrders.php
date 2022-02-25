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
    
    function syncOrder(channelId){
        $.ajax({
              type: 'GET',
              url: "orders.php?channelId="+channelId,
              dataType: "json",
              success: function(res) {
                    console.log(res);
                    var html = '<div class="alert row response alert-success">'+res.message+'</div>';
                    $("#results").append(html);
                    if(res.idchannel !=''){
                      syncOrder(res.idchannel);
                    }
              }
        });
    }
    
    syncOrder(2);
    
</script>
</body>
</html>
