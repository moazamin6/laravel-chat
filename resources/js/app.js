require('./bootstrap');

window.Vue = require('vue');


Vue.component('chat', require('./components/Chat.vue').default);


const app = new Vue({
   el: '#app',
   data: {},

   mounted() {
      // let msg = 'this is test message';
      // axios.post(this.base_url + "/message", {msg: msg})
      //     .then(function (response) {
      //        console.log(response);
      //     });
      //
      // window.Echo.private('chat')
      //     .listen('ChatEvent', (e) => {
      //        console.log(e);
      //     })
   }
});
