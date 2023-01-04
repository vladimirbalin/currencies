import config from "./config.js";
import {
    buildWidget, updateWidgetByTimer
} from "./includes/mainFunctions.js";


document.addEventListener('DOMContentLoaded', async function () {
    const $app = document.getElementById('app');
    const params = {currencies: config.currencies};

    const request = function () {
        return axios.get(config.backendEndpoint, {params})
    }

    try {
        const {data: {currencies}} = await request();
        const widget = buildWidget(currencies);
        $app.append(widget);

        const intervalID = updateWidgetByTimer(request, config.refreshTimer);
    } catch (e) {
        console.log(e)
    }

})
