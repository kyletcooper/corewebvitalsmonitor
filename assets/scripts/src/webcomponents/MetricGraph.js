import { LitElement, css, html } from 'lit';
import Chart from 'chart.js/auto';

export class MetricGraph extends LitElement {
	static properties = {
		metric: { type: Object },
		data: { type: Array },
	}

	updated() {
		if (!this.data) return;

		const computedStyle = getComputedStyle(this);

		const opts = {
			type: 'line',
			options: {
				animation: false,

				plugins: {
					legend: {
						display: false
					},
				},

				elements: {
					point: {
						radius: 0
					},

					line: {
						tension: 0.25,
						borderColor: computedStyle.getPropertyValue('--black-500'),
						borderJoinStyle: 'round',
						borderWidth: 2,
						fill: {
							target: 'origin',
							above: computedStyle.getPropertyValue('--black-100'),
						},
					}
				},

				layout: {
					padding: 20
				},

				maintainAspectRatio: false,

				scales: {
					y: {
						min: 0,
						display: false,
					},
					x: {
						min: 0,
						max: this.metric.thresholds[1],
						border: {
							display: false
						},
						ticks: {
							autoSkip: true,
							maxTicksLimit: 4,
							maxRotation: 0,
							minRotation: 0
						},
					},
				},
			},
			data: {
				labels: this.data.map(point => point.value + "ms"),

				datasets: [{
					label: this.metric.label,
					data: this.data.map(point => point.count),
					segment: {
						borderColor: (ctx) => {
							const thresholds = this.metric.thresholds;
							const value1 = parseInt(ctx.chart.data.labels[ctx.p0DataIndex]);

							if (value1 > thresholds[1]) { // Poor
								return computedStyle.getPropertyValue('--danger-500');
							}
							else if (value1 > thresholds[0]) { // Needs Improvement
								return computedStyle.getPropertyValue('--warning-500');
							}
							else { // Good
								return computedStyle.getPropertyValue('--success-500');
							}
						},

						backgroundColor: (ctx) => {
							const thresholds = this.metric.thresholds;
							const value1 = parseInt(ctx.chart.data.labels[ctx.p0DataIndex]);

							if (value1 > thresholds[1]) { // Poor
								return computedStyle.getPropertyValue('--danger-100');
							}
							else if (value1 > thresholds[0]) { // Needs Improvement
								return computedStyle.getPropertyValue('--warning-100');
							}
							else { // Good
								return computedStyle.getPropertyValue('--success-100');
							}
						},
					},
				}]
			},
		};

		new Chart(this.renderRoot.getElementById('canvas'), opts);
	}

	render() {
		return html`
            <canvas id="canvas"></canvas>
        `;
	}

	static styles = css`
        :host,
        #canvas{
            box-sizing: border-box;
            display: block;
            height: inherit;
			max-width: 100%;
        }
    `;
}

customElements.define('metric-graph', MetricGraph);
