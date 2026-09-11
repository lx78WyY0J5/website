main();

async function main() {
    var deviseType = "eur";

    await getPriceBinance("btc", deviseType);
    await getPriceBinance("eth", deviseType);
    //await getPriceBinance("xmr", deviseType);
    await getPriceBinance("xrp", deviseType);
    await getPriceBinance("ada", deviseType);
    //await getPriceBinance("usdt", deviseType);
}

function setData(id, data, deviseType) {
    var priceFormat = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: deviseType });
    let price = priceFormat.format(data);
    id = id.toUpperCase();
    var elem = document.getElementById("prix-" + id);
    elem.textContent = price;
}

//https://developers.binance.com/docs/derivatives/option/websocket-market-streams/24-hour-TICKER-by-underlying-asset-and-expiration-data
async function getPriceBinance(token, deviseType) {
    let URL = "wss://stream.binance.com:9443/ws/" + token + deviseType + "@ticker"
    const ws = new WebSocket(URL);

    ws.onmessage = (event) => {
        const data = JSON.parse(event.data);
        let price = getValue(data, "c");
        if(price !== undefined){
            setData(token, price, "eur");
        }else {
            setData(token, 0, "eur");
        }
    };
}