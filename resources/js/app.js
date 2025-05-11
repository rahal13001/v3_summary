import './bootstrap';

// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
import { getMessaging, getToken, onMessage } from "firebase/messaging";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyCycfq9CMdzLXq1vYmLJMWkc54hLEpyDLA",
  authDomain: "summary-209f2.firebaseapp.com",
  projectId: "summary-209f2",
  storageBucket: "summary-209f2.firebasestorage.app",
  messagingSenderId: "347726553568",
  appId: "1:347726553568:web:3b6fe288f71e18666a0b85",
  measurementId: "G-T4JSQXMWL2"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);
const messaging = getMessaging(app);

onMessage(messaging, (payload) => {
    // console.log('Message received. ', payload);
    alert(
      "Informasi Disposisi :"+"\n\n"+payload.notification.title+"\n\n"+payload.notification.body
    );
  });

getToken(messaging, { vapidKey: 'BJJZKhEaHj6Bw2ehmnrC2GKzrStyReRd5AeAwF05uUTVbtJinZRd4c1KmPN8MYQwGPj14466QPVxWTAdpUHGyjY' }).then((currentToken) => {
    if (currentToken) {
      // Send the token to your server and update the UI if necessary
      sendTokenToServer(currentToken);
      // ...
      // console.log(currentToken);
    } else {
      // Show permission request UI
      requestPermission();
      // console.log('No registration token available. Request permission to generate one.');
      // ...
    }
  }).catch((err) => {
    console.log('An error occurred while retrieving token. ', err);
    // ...
  });

function requestPermission() {
    // [START messaging_request_permission_modular]
    Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
        // console.log('Notification permission granted.');
        alert('Terimakasih Telah Mengizinkan Notifikasi');
        // TODO(developer): Retrieve a registration token for use with FCM.
        // ...
        } else {
        alert('Kasih Aktif Izin Notifikasi Dolo KK');
        // console.log('Unable to get permission to notify.');
        }
    });
    // [END messaging_request_permission_modular]
}

function sendTokenToServer(token) {
  var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  let formData = new FormData();
  formData.append('fcm_token', token);

  fetch('/webtoken', {
    headers: {
      'X-CSRF-TOKEN': csrf,
      _method: '_POST',
    },
    method: 'POST',
    credentials: 'same-origin',
    body: formData
  })
  // .then((response) => {
  //   alert(response.status);
  // })
  ;


}
