
function getPlayerGoldFromLocalStorage(){
    let storage = localStorage.getItem("playerGold");
    if(storage === null){
        return 0;
    }
    return parseInt(storage);
}

function setPlayerGoldToLocalStorage(gold){
    if(gold > 0) {
        let golds = getPlayerGoldFromLocalStorage();
        let newgold = golds + gold;
        localStorage.setItem("playerGold", newgold);

        updateGoldUI(newgold);

        // ui message
    }
}

function updateGoldUI(newgold){
    if(newgold === undefined){ newgold = getPlayerGoldFromLocalStorage(); }
    document.getElementById("idlegold").innerText = formatGoldText_k_m_b_t(newgold, false);
    document.getElementById("idlegoldformat").innerText = formatGoldText_k_m_b_t(newgold, true);
}

function GoldLootPerAction(action, actionlvl, playerLVLWithAction){
    let goldLooted;

    switch(action){
       case "gather":
            duration = 0;
            break;
        case "fight":
            duration = 1;
            break;
        case "production":
            duration = 0;
            break;
        case "moving":
            duration = 1;
            break;
        default:
            duration = 0;
    }

    goldLooted = goldLooted * (actionlvl + 1);
    goldLooted = goldLooted * (1 + playerLVLWithAction);

    return goldLooted;
}

function formatGoldText_k_m_b_t(goldValue, format){
    if(goldValue <= 999){
        if(!format){
            return goldValue;
        }
        else{
            return "ù"
        }
    }
    if(goldValue >= 1000 && goldValue <= 999_999){
        if(!format){
            return (goldValue / 1000).toFixed(2);
        }
        else{
            return "k";
        }
    }
    else if(goldValue >= 1_000_000 && goldValue <= 999_999_999){
        if(!format){
            return (goldValue / 1_000_000).toFixed(2);
        }
        else{
            return "m";
        }
    }
    else if(goldValue >= 1_000_000_000 && goldValue <= 999_999_999_999){
        if(!format){
            return (goldValue / 1_000_000_000).toFixed(2);
        }
        else{
            return "b";
        }
    }
    else if(goldValue >= 1_000_000_000_000 && goldValue <= 999_999_999_999_999){
        if(!format){
            return (goldValue / 1_000_000_000_000).toFixed(2);
        }
        else{
            return "t";
        }
    }
    else{
        if(!format){
            return goldValue.toExponential();
        }
        else{
            return "e";
        }
    }
}