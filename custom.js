checkMessages();
setInterval(checkMessages, 3000);

function checkMessages()
{
    var checkNewMessages = new XMLHttpRequest();
    var url = "getmessages.php";
    checkNewMessages.open('GET', url, false);
    checkNewMessages.send();
    var newMessages = JSON.parse(checkNewMessages.response).newMessages;
    document.getElementById("messageBadge").innerHTML = newMessages;
    if (newMessages>0)
        $("#messageBadge").show();
    else
        $("#messageBadge").hide();
}