<template>
   <div class="messaging">
      <div class="inbox_msg">
         <div class="inbox_people">

            <div class="inbox_chat scroll">
               <div :class="{ active_chat : active_chat_flag === index }" class="chat_list"
                    @click="selectUserToSendMessage(value,index)"
                    v-for="(value,index) in all_users"
                    :key="value.id">
                  <div class="chat_people">
                     <div class="chat_ib">
                        <h4>{{value.name}}</h4>
                        <p v-if="value.status==='Online'" class="user_online">{{value.status}}</p>
                        <p v-else>{{value.status}}</p>
                        <p>{{value.typing}}</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div v-if="active_chat_flag > -1" class="mesgs">

            <div class="msg_history">
               <ul class="list-group msg-list">
                  <li class="list-group-item" :class="msgs.msg_class" v-for="(msgs,index) in user_messages"
                      :key="index">
                     {{msgs.message}}
                  </li>
               </ul>


            </div>
            <div class="type_msg">
               <div class="input_msg_write">
                  <input @keyup.enter="sendMessage" v-model="message" type="text" class="write_msg"
                         placeholder="Type a message"/>
                  <button class="msg_send_btn" type="button" @click="sendMessage">
                     <i class="fa fa-paper-plane" aria-hidden="true"></i>
                  </button>
               </div>
            </div>
         </div>
      </div>
   </div>
</template>

<script>
   export default {
      name: "chat",
      props: ['auth_user'],

      data() {
         return {
            message: '',
            user: {},
            all_messages: [],
            user_messages: [],
            online_users: [],
            all_users: [],
            active_chat_flag: '-1',
            receiver_user: {},
            messaging: '',
            whisper_id: ''
         };
      },
      computed: {
         // msg_class(){
         //
         // }
      },
      created() {
         this.getAllUsers();
         // this.getAllMessages();
      },
      watch: {
         message() {
            window.Echo.private('laravel-chat-' + this.receiver_user.id)
                .whisper('messaging', {
                   msg: this.message,
                   whisper_id: this.auth_user.id
                });
         }
      },

      methods: {
         sendMessage() {
            if (this.message.length !== 0) {

               let receiver_id = this.receiver_user.id;
               let message_data = {
                  message: this.message,
                  receiver_user: this.receiver_user,
                  sender_user: this.auth_user
               };
               axios.post(window.base_url + "/message", message_data)
                   .then(function (response) {
                      // console.log(response.data);
                   });
               message_data = null;
               message_data = {
                  sender_id: this.auth_user.id,
                  receiver_id: this.receiver_user.id,
                  message: this.message,
                  msg_class: 'sent_msg'
               };
               this.all_messages.push(message_data);
               this.user_messages.push(message_data);
               // console.log(this.user_messages);
               this.message = '';
            }
         },

         selectUserToSendMessage(user, index) {

            let self = this;

            self.receiver_user = user;
            self.active_chat_flag = index;
            if (self.all_messages.length !== 0) {
               self.user_messages = [];
               self.all_messages.forEach(function (value, i) {

                  if (value.receiver_id === self.receiver_user.id || value.sender_id === self.all_users.id) {
                     self.user_messages.push(value);
                  }
               });
            }
            // console.log(self.user_messages)
         },
         getAllUsers() {

            let self = this;
            axios.get(window.base_url + "/users")
                .then(function (response) {
                   self.all_users = response.data;
                   self.all_users.forEach(function (user, index) {
                      self.$set(user, 'status', 'Offline');
                      self.$set(user, 'typing', '');
                   });
                });
         },
         getAllMessages() {

            let self = this;
            axios.get(window.base_url + "/get-messages")
                .then(function (response) {

                   console.log(response.data);
                });
         }
      },
      mounted() {
         window.Echo.private('laravel-chat-' + this.auth_user.id)
             .listen('ChatEvent', (e) => {
                let message_data = {
                   sender_id: this.receiver_user.id,
                   receiver_id: this.auth_user.id,
                   message: e.message,
                   msg_class: 'received_msg'
                };
                this.all_messages.push(message_data);
                this.user_messages.push(message_data);
             })
             .listenForWhisper('messaging', (e) => {

                this.all_users.forEach(function (user, index) {

                   if (e.msg !== '') {
                      if (e.whisper_id === user.id) {
                         Vue.set(user, 'typing', 'Typing...');
                      }
                   } else {
                      Vue.set(user, 'typing', '');
                   }

                });
             });

         window.Echo.join(`user-join`)
             .here((users) => {

                let self = this;
                self.all_users.forEach(function (user, index) {

                   users.forEach(function (on_user, index) {

                      if (user.id === on_user.id) {
                         Vue.set(user, 'status', 'Online');
                         // user.status = 'Online';
                      }
                   });
                });
             })
             .joining((on_user) => {

                this.all_users.forEach(function (user, index) {

                   if (user.id === on_user.id) {
                      Vue.set(user, 'status', 'Online');
                      // user.status = 'Online';
                   }
                });
             })
             .leaving((off_user) => {

                this.all_users.forEach(function (user, index) {

                   if (user.id === off_user.id) {
                      Vue.set(user, 'status', 'Offline');
                   }
                });
             })
      }
   }
</script>

<style scoped>
   .container {
      max-width: 900px;
   }

   .msg-list {
      position: absolute;
      bottom: 0;
   }

   .msg-list li {
      margin-bottom: 5px;
      width: 700px;
   }

   .user_online {
      color: #148002 !important;
      font-weight: bold;
   }

   .inbox_people {
      background: #fff;
      float: left;
      overflow: hidden;
      width: 30%;
      border-right: 1px solid #ddd;
   }

   .inbox_msg {
      border: 1px solid #ddd;
      clear: both;
      overflow: hidden;
   }

   .top_spac {
      margin: 20px 0 0;
   }

   .recent_heading {
      float: left;
      width: 40%;
   }

   .srch_bar {
      display: inline-block;
      text-align: right;
      width: 60%;
   }

   .headind_srch {
      padding: 10px 29px 10px 20px;
      overflow: hidden;
      border-bottom: 1px solid #c4c4c4;
   }

   .recent_heading h4 {
      color: #0465ac;
      font-size: 16px;
      margin: auto;
      line-height: 29px;
   }

   .srch_bar input {
      outline: none;
      border: 1px solid #cdcdcd;
      border-width: 0 0 1px 0;
      width: 80%;
      padding: 2px 0 4px 6px;
      background: none;
   }

   .srch_bar .input-group-addon button {
      background: rgba(0, 0, 0, 0) none repeat scroll 0 0;
      border: medium none;
      padding: 0;
      color: #707070;
      font-size: 18px;
   }

   .srch_bar .input-group-addon {
      margin: 0 0 0 -27px;
   }

   .chat_ib h5 {
      font-size: 15px;
      color: #464646;
      margin: 0 0 8px 0;
   }

   .chat_ib h5 span {
      font-size: 13px;
      float: right;
   }

   .chat_ib p {
      font-size: 12px;
      color: #989898;
      margin: auto;
      display: inline-block;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
   }

   .chat_img {
      float: left;
      width: 11%;
   }

   .chat_img img {
      width: 100%
   }

   .chat_ib {
      float: left;
      padding: 0 0 0 15px;
      width: 88%;
   }

   .chat_people {
      overflow: hidden;
      clear: both;
   }

   .chat_list {
      border-bottom: 1px solid #ddd;
      margin: 0;
      padding: 18px 16px 10px;
      height: 70px;
   }

   .chat_list:hover {
      cursor: pointer;
   }

   .inbox_chat {
      height: 550px;
      overflow-y: scroll;
   }

   .active_chat {
      background: #e8f6ff;
   }

   .incoming_msg_img {
      display: inline-block;
      width: 6%;
   }

   .received_msg {
      background: #b6a999;
   }

   .received_msg p {
      background: #ebebeb none repeat scroll 0 0;
      border-radius: 0 15px 15px 15px;
      color: #646464;
      font-size: 14px;
      margin: 0;
      padding: 5px 10px 5px 12px;
      width: 100%;
   }

   .received_withd_msg p {
      background: #ebebeb none repeat scroll 0 0;
      border-radius: 0 15px 15px 15px;
      color: #646464;
      font-size: 14px;
      margin: 0;
      padding: 5px 10px 5px 12px;
      width: 100%;
   }

   .time_date {
      color: #747474;
      display: block;
      font-size: 12px;
      margin: 8px 0 0;
   }

   .received_withd_msg {
      width: 57%;
   }

   .mesgs {
      float: left;
      padding: 30px 15px 0 25px;
      width: 70%;
      background: #e7e3de;
   }

   .outgoing_msg {
      overflow: hidden;
      margin: 26px 0 26px;
   }

   .sent_msg {
      margin-left: 300px;
      width: 46%;
      background: #15ff89;
   }

   .input_msg_write input {
      background: rgba(0, 0, 0, 0) none repeat scroll 0 0;
      border: medium none;
      color: #4c4c4c;
      font-size: 15px;
      min-height: 48px;
      width: 100%;
      outline: none;
   }

   .type_msg {
      border-top: 1px solid #c4c4c4;
      position: relative;
   }

   .msg_send_btn {
      background: #05728f none repeat scroll 0 0;
      border: none;
      border-radius: 50%;
      color: #fff;
      cursor: pointer;
      font-size: 15px;
      height: 33px;
      position: absolute;
      right: 0;
      top: 11px;
      width: 33px;
   }

   .messaging {
      padding: 0 0 50px 0;
   }

   .msg_history {
      position: relative;
      height: 516px;
      overflow-y: auto;
   }
</style>