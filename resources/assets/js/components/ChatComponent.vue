<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Chat Component</div>

                    <div class="panel-body">
                        <div class="col-md-3">
                            <ul class="nav flex-column">
                                <li class="nav-item" v-for="user in users" :key="user.index">
                                    <a class="nav-link" @click="loadMessages(user)"> {{user.name}} </a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-9" v-if="!started">
                            <div class="panel panel-warning" v-if="!loading">
                                <div class="panel-heading">
                                    Falando com <strong>{{ currentChat.name }}</strong>
                                </div>
                            </div>
                            <div class="panel panel-info" v-if="messages.length === 0">
                                <div class="panel-heading">
                                    Nenhuma Mensagem para ouvir
                                </div>
                            </div>
                            <div class="panel" :class="{'panel-warning' : currentChat.id == message.sender }"
                            v-for="message in messages" :key="message.index">
                                <div class="panel-body">
                                    <audio controls>
                                        <source :src="'/audios/' + message.audio">
                                    </audio>
                                </div>
                            </div>
                            <a :class="{'recording':recording}" id="rec"
                            @click="rec()"></a>
                        </div>
                        <div class="col-md-9" v-if="started">
                            <div class="panel panel-success" v-if="!loading">
                                <div class="panel-heading">
                                    Escolha alguém pra conversar ....
                                </div>
                            </div>
                            <div class="panel panel-info" v-if="loading">
                                <div class="panel-heading">
                                    Obtendo conversas ....
                                </div>
                            </div>
                            <div class="panel panel-danger" v-if="error">
                                <div class="panel-heading">
                                    Erro ao carregar conversas ....
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                started     :   true,
                loading     :   false,
                error       :   false,
                currentChat :   {},
                users       :   [],
                messages    :   [],
                recording   :   false,
                recorder    :   null,
                recordedData:   [],
                received    :   []
            }
        },
        methods : {
            loadMessages(user){
              this.loading = true;  
              this.started = true;

              window.axios.get('/messages/' + user.id)
              .then((response) => {
                  this.loading = false;
                  this.started = false;
                  this.currentChat = user;
                  this.messages = response.data
              })
              .catch(()=>{
                  this.loading = false;
                  this.error = false;
              })
            },
            rec(){
                this.recording = !this.recording;
                if(this.recording)
                {
                    this.startRec();
                }else{
                    this.stopRec();
                }
            },
            startRec() {
                const config = {
                    audio   :   true,
                    video   :   false
                }
                const successCallback = (stream) => {
                    this.recorder = new MediaRecorder(stream);

                    this.recorder.ondataavailable = (e) => {
                        this.recordedData.push(e.data);
                        console.log(this.recordedData);
                    }

                    this.recorder.onstop = () => {
                        let blob = new Blob(this.recordedData,{type:'video/webm'});
                        
                        this.recorder = null;
                        this.recordedData = [];

                        let formData = new FormData();
                        formData.append('audio',blob);
                        formData.append('receiver',this.currentChat.id);

                        window.axios.post('/messages',formData)
                            .then((response) =>{
                                console.log('done');
                                this.messages.splice(0,0, response.data)
                            })
                    }

                    this.recorder.start();
                }

                const errorCallback = (err) => {
                    console.log(err);
                }

                navigator.getUserMedia(config,successCallback,errorCallback);
            },
            stopRec() {
                this.recorder.stop();
            }
        },
        mounted() {
             
             window.axios.get('/users').then((response) => {
                 this.users = response.data;
             });

             Echo.channel('channel.messages.' + me)
                 .listen('AudioSended',(e) => {
                     if(e.message.sender === this.currentChat.id)
                     {
                         this.messages.splice(0,0,e.message);
                     }
                 });
        }
    }
</script>


<style>
@keyframes pulse {
    50% {
        background: transparent;
    }
}

a {
    cursor : pointer;
}

#rec{
    position :fixed;
    right: 10px;
    bottom: 10px;
}

#rec:after{
    display: block;
    content:'';
    width: 30px;
    height: 30px;
    background: red;
    border-radius: 30px;
}
#rec.recording:after{
 animation : pulse 1s infinite;
}
</style>

