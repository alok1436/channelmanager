<!DOCTYPE html>
<html>
    <head>
        <title></title>
    </head>
    <body>
        <form class="form-horizontal" id="" method="post" action="passwordreset">
            {{ csrf_field() }}
            <div class="form-group ">
                <div class="col-xs-12">
                    <h3>New Password</h3>
                </div>
            </div>
            <input type="hidden" class="form-control"  placeholder="userid" name="userid" id="userid" value="{{$userid}}" required>
            <div class="form-group ">
                <div class="col-xs-6">
                    <input type="password" class="form-control"  placeholder="Password" name="password" id="password" onchange="validatePassword()" required>
                </div>
            </div>     

            <div class="form-group ">
                <div class="col-xs-6">
                    <input type="password" class="form-control" placeholder="Confirm Password" onkeyup="validatePassword()" name="confirm_password" id="confirm_password" required>
                </div>
            </div>  

            <div class="form-group ">
                <div class="col-xs-6">
                    <button type="submit" name="sub" class="pure-button pure-button-primary">Confirm</button>
                </div>
            </div>
        </form>
        <script type="text/javascript">
            var password = document.getElementById("password")
                , confirm_password = document.getElementById("confirm_password");

            function validatePassword(){
                if(password.value != confirm_password.value) {
                    confirm_password.setCustomValidity("Passwords Don't Match");
                } else {
                    confirm_password.setCustomValidity('');
                }
            }
        </script>
        <style type="text/css">
            .form-control {
                color: #67757c;
                min-height: 38px;
                display: initial;
                width: 26%;
            }
        </style>
    </body>
</html>
	 