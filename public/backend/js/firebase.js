var config = {
            apiKey: "AIzaSyDrMsB-8KiuGpmHRrNf27ecXdLziV8HRwA",
            authDomain: "klothus-58b02.firebaseapp.com",
            databaseURL: "https://klothus-58b02.firebaseio.com",
            projectId: "klothus-58b02",
            storageBucket: "klothus-58b02.appspot.com",
            messagingSenderId: "941400791388",
            appId: "1:941400791388:web:2a241f1e2e328946f50999",
            measurementId: "G-M4M8T51HVE"
        };
        firebase.initializeApp(config);

        // Retrieve Firebase Messaging object.
        const messaging = firebase.messaging();
        messaging.requestPermission()
        .then(function() {
          console.log('Notification permission granted.');
          // TODO(developer): Retrieve an Instance ID token for use with FCM.
          if(isTokenSentToServer()) {
            console.log('Token already saved.');
          } else {
            getRegToken();
          }

        })
        .catch(function(err) {
          console.log('Unable to get permission to notify.', err);
        });

        function getRegToken(argument) {
            messaging.getToken()
              .then(function(currentToken) {
                if (currentToken) {
                  saveToken(currentToken);
                  console.log(currentToken);
                  setTokenSentToServer(true);
                } else {
                  console.log('No Instance ID token available. Request permission to generate one.');
                  setTokenSentToServer(false);
                }
              })
              .catch(function(err) {
                console.log('An error occurred while retrieving token. ', err);
                setTokenSentToServer(false);
              });
        }

        function setTokenSentToServer(sent) {
            window.localStorage.setItem('sentToServer', sent ? 1 : 0);
        }

        function isTokenSentToServer() { console.log(window.localStorage.getItem('sentToServer'));
            return window.localStorage.getItem('sentToServer') == 1;
        }

        function saveToken(currentToken) {
            $.ajax({
                url: ajaxurl+'/save-token',
                method: 'post',
                data: 'token=' + currentToken
            }).done(function(result){
                console.log(result);
            })
        }

        messaging.onMessage(function(payload) {
          console.log("Message received. ", payload);
          notificationTitle = payload.data.title;
          notificationOptions = {
            body: payload.data.body,
            icon: payload.data.icon,
            image:  payload.data.image
          };
          console.log(payload.data.badge);
          $(".number_alert").empty().html(payload.data.badge);
          $(".number_alert_message").empty().html('You have '+ payload.data.badge+ ' messages');
          var notification = new Notification(notificationTitle,notificationOptions);
        });

        function sendNotification(){
            $.ajax({
                url: ajaxurl+'/send-notification',
                method: 'get',
            }).done(function(result){
                console.log(result);
            })
        }