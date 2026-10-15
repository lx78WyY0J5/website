
// https://discord.com/developers/docs/reference
var Token = "AAAAAAAAAAAAAAAAAAAA.AAAAAA-AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";

// sendToAPI(9, "TOKEN", "Bot", "POST", "", "");
function sendToAPI(Version, Token, AccountType, Methode, URL, Message, referrer){

    fetch(`https://discord.com/api/v` + Version + `/` + URL, {
        body: JSON.stringify({
        content: Message,
    }),
    headers: {
        "accept": "*/*",
        "Content-Type": "application/json",
        'Authorization': AccountType + Token,
    },
    "referrer": "https://discord.com/channels/" + referrer,
    "referrerPolicy": "strict-origin-when-cross-origin",
    "mode": "cors",
    "credentials": "include",
    "method": Methode,
    })
    .then(function (res) {
        console.log(res);
    })
    .catch(function (res) {
        console.log(res);
    });
}

// sendMessage(9, t, "", "POST", "channels/1111111111111111111/messages", "Test", "1111111111111111111"); //send message to a serverChannel
// sendMessage(9, t, "", "POST", "channels/1111111111111111111/messages", "Test", ""); //send message to a UserChannel
function sendMessage(Version, Token, AccountType, Methode, ChannelID, Message, ServerID){
    var referrer = "channels/" + ServerID + "/" + ChannelID;
    sendToAPI(Version, Token, AccountType, Methode, ChannelID, Message, referrer);
}

// sendToWebHook("000000000000000", "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA", "test", "POST");
function sendToWebHook(ServerID, Token, Message, Methode){
    fetch("https://discord.com/api/webhooks/" + ServerID + "/" + Token, {
        body: JSON.stringify({
        content: Message,
    }),
    headers: {
        "Content-Type": "application/json",
    },
        method: Methode,
    })
    .then(function (res) {
        console.dir(res);
    })
    .catch(function (res) {
        console.error(res);
    });
}