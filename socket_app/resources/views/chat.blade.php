@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div>
                <div class="chat">
                  <div class="chat-title">
                    <h1>CHAT</h1>
                  </div>
                  <div class="messages">
                    <div class="messages-content">
                    </div>
                  </div>
                  <div class="message-box">
                    <input
                      type="text"
                      class="message-input"
                      placeholder="Type message..."
                      name="message"
                      id="msgContent"
                    />
                    <button type="button" class="message-submit" id="btnSend">
                      Enviar
                    </button>
                  </div>
                </div>
              </div>
        </div>
        <div class="col-md-2">
            <div class="users-online">
                <button type="button" class="btn btn-primary">
                    Usuários online: <span class="badge badge-light" id="userOnline"></span>
                </button>
            </div>
            <div class="online-users">
                <div class="d-flex flex-column mb-3 available-users">
                </div>
            </div>
            <div class="users-online">
                <button type="button" class="btn btn-primary">
                    Contatos
                </button>
            </div>
            <div class="user-rooms">
                <div class="d-flex flex-column mb-3 available-rooms">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra_script')
<script src="//{{ Request::getHost() }}:6001/socket.io/socket.io.js"></script>

<script>
    var currentUserId = <?php echo Auth::user()->id;?>;

    var userNotifications = <?php echo json_encode($userNotifications); ?>;

    let channel = 'messages';

    //Chat Broadcast
    window.Echo.join(channel)
        .here((users) => {
            console.log('===HERE===');
            usersOnline = users;
            getTotalUsersOnline();
            getRooms();
        })
        .joining((user) => {
            console.log('===JOINING===');
            usersOnline.push(user);
            getTotalUsersOnline();
            getRooms();
        })
        .leaving((user) => {
            const index = usersOnline.findIndex(item => item.id === user.id)
            if (index > -1) {
                usersOnline.splice(index, 1)
            }
            getTotalUsersOnline();
            getRooms();
        })
        .listen('PublicMessage', function (data) {
            console.log('===LISTEN===');
            let chat = data.message;
            chat.sender = data.user;
            appendMessage(chat);
            $('.messages').animate({
                scrollTop: $('.messages').get(0).scrollHeight
            }, 1000);
        });

    //Notification Broadcast
    window.Echo.private('notify_users.' + currentUserId)
        .notification((notification) => {
            console.log("===Notify===");
            userNotifications.push(notification);
            displayNotify();
        });

    $(document).ready(function() {
        getUserLogin();
        var listMessages = <?php echo $messages->toJson();?>;
        loadMessages(listMessages);
        scrollToButtom('.messages');
        displayNotify();
    });

    $('#btnSend').click(function(){
        $.ajaxSetup(ajaxSetupHeader);
        var messageContent = $("#msgContent").val();
        $.ajax({
            url: "/messages/public",
            method: "POST",
            data: { message : messageContent }
        }).done(function( msg ) {
            console.log('DONE');
            $("#msgContent").val("");
        }).fail(function( jqXHR, textStatus ) {
            console.log( "Request failed: " + textStatus );
        });
    });

</script>
@endsection
