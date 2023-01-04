import {
    createElement,
    getElementById
} from "./helpers.js";

export function buildWidget(currencies) {
    const $wrapper = createElement('div');
    $wrapper.id = 'currencies-widget';

    currencies.forEach((currency) => {
        const div = buildRow(currency);
        $wrapper.append(div);
    })

    return $wrapper;
}

function buildRow(currency) {
    let div = createElement('div');
    div.id = currency['char_code'];

    div = updateDiv(div, currency);
    return div;
}

function updateRates(currencies) {
    currencies.forEach((currency) => {
        const div = getElementById(currency['char_code']);
        updateDiv(div, currency);
    })
}

function updateDiv(div, currency) {
    div.innerText = makeInnerText(currency);

    const classesToRemove = formClassesToRemove(div);
    if (classesToRemove.length) {
        div.classList.remove(classesToRemove);
    }

    const classesToAdd = formClassesToAdd(currency);
    if (classesToAdd.length) {
        div.classList.add(classesToAdd);
    }

    return div;
}

function makeInnerText(currency) {
    const charCode = currency['char_code'];
    const value = currency['value'];
    const nominal = currency['nominal'];
    const date = currency['date'];

    return `${charCode}: ${value * nominal} - updated ${date}`;
}

function formClassesToAdd(currency) {
    let classes = [];

    if (currency['status'] === 'rateUp') {
        classes.push('rateUp');
    } else if (currency['status'] === 'rateDown') {
        classes.push('rateDown');
    }

    return classes;
}

function formClassesToRemove(div) {
    let classes = [];

    if (div.classList.contains('rateUp')) {
        classes.push('rateUp')
    } else if (div.classList.contains('rateDown')) {
        classes.push('rateDown')
    }

    return classes;
}

export function updateWidgetByTimer(request, timer) {
    const timerMilliseconds = timer * 1000;

    return setInterval(async () => {
        const {data: {currencies}} = await request();
        updateRates(currencies);
    }, timerMilliseconds);
}
