function setScrollBehavior(anchor, type) {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        window.location.hash = this.getAttribute('href');
        DoScrollIntoView(document.getElementById(this.getAttribute('href').replace("#", "")), false, type);
    });
}

var onceIsDone = false;
function autoScroll(once, type) {
    var hash = decodeURIComponent(window.location.hash);

    if(hash === ""){ return; }

    var element = document.getElementById(hash.replace("#", ""));
    openParentSummaryIfFound(element, once);
    DoScrollIntoView(element, once, type);
}

function DoScrollIntoView(element, once, type) {
    if (element !== null) {
        if (once === true && onceIsDone === true) {
            return;
        }
        else {
            element.scrollIntoView({ behavior: "smooth", block: type });
            element.focus(); // no browser good support yet : // element.focus({ focusVisible: true });
            onceIsDone = true;
        }
        console.log("Scrolling to : " + element.id);
    }
}

function openParentSummaryIfFound(element, once) {
    if (element !== null) {
        if (once === true && onceIsDone === true) {
            return;
        }
        let parentElements = [];
        let currentElement = element;

        while (currentElement !== document) {
            parentElements.push(currentElement.parentNode);
            currentElement = currentElement.parentNode;
        }

        for (parent in parentElements) {
            if (parentElements[parent].nodeName === 'DETAILS') {
                parentElements[parent].open = true;
            }
        }
    }
}