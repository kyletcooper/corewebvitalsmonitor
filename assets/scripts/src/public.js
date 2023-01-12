import { onCLS, onFID, onLCP, onFCP, onINP, onTTFB } from 'web-vitals';

(() => {
	const report = async metric => {
		if (metric.delta !== metric.value) {
			return; // Only use the first value.
		}

		fetch(`${CWVM.rest_url}corewebvitalsmonitor/v1/metric`, {
			method: 'POST',

			headers: new Headers({
				'X-WP-Nonce': CWVM.rest_nonce,
				'Content-Type': 'application/json',
			}),

			body: JSON.stringify({
				metric: metric.name,
				value: metric.value,
				url: window.location.href,
				connection_speed: navigator?.connection?.downlink || -1,
			})
		}).then(resp => resp.json())
	}

	onCLS(report);
	onFID(report);
	onLCP(report);
	onFCP(report);
	onINP(report);
	onTTFB(report);
})();