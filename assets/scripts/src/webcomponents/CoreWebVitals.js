import { LitElement, css, html } from 'lit';
import { repeat } from 'lit/directives/repeat.js';
import './MetricGraph';

export class CoreWebVitals extends LitElement {
	static properties = {
		metrics: { type: Object, state: true },
		url: { type: String },
	}

	static rows = ['lcp', 'fid', 'cls', 'ttfb'];
	static graphs = ['lcp', 'ttfb', 'inp'];

	constructor() {
		super();

		this.url = '';

		this.metrics = {
			lcp: {
				id: "LCP",
				label: "Largest Contentful Paint",
				description: "The time taken for the largest image or text block to load, relative to when the page first started loading.",
				thresholds: [2500, 4000],
				average: null,
				count: null,
				plot: null,
				unit: "ms",
			},
			fid: {
				id: "FID",
				label: "First Input Delay",
				description: "The time from when a user begins interacting to when the browser is able to respond to the interaction.",
				thresholds: [100, 300],
				average: null,
				count: null,
				plot: null,
				unit: "ms",
			},
			cls: {
				id: "CLS",
				label: "Cumulative Layout Shift",
				description: "The amount elements on the page shift around as the page loads.",
				thresholds: [0.1, 0.25],
				average: null,
				count: null,
				plot: null,
				unit: "",
			},
			ttfb: {
				id: "TTFB",
				label: "Time to First Byte",
				description: "The measurement of time between when a request for a resource and when it first begins to arrive.",
				thresholds: [800, 1800],
				average: null,
				count: null,
				plot: null,
				unit: "ms",
			},
			inp: {
				id: "INP",
				label: "Interaction to Next Paint",
				description: "The time taken for an user interaction to take effect on the screen.",
				thresholds: [200, 500],
				average: null,
				count: null,
				plot: null,
				unit: "ms",
			},
			fcp: {
				id: "FCP",
				label: "First Contentful Paint",
				description: "Measures the time taken from when the page starts loading to when any part of the content appears on screen.",
				thresholds: [1800, 3000],
				average: null,
				count: null,
				plot: null,
				unit: "ms",
			},
		}
	}

	connectedCallback() {
		super.connectedCallback();
		this.getData();
	}

	async getAPI(route, opts = {}) {
		const url = new URL(CWVM.rest_url + route);

		for (const key in opts) {
			url.searchParams.append(key, opts[key]);
		}

		return await fetch(url.href, {
			method: 'GET',

			headers: {
				'X-WP-Nonce': CWVM.rest_nonce,
			},
		}).then(resp => resp.json());
	}

	async getData() {
		this.constructor.rows.forEach(metricID => {
			const metric = this.metrics[metricID];
			if (!metric) return;

			this.getAPI('corewebvitalsmonitor/v1/metric/average', {
				metric: metric.id,
				url: this.url
			}).then(value => {
				this.metrics[metricID].average = value;
				this.requestUpdate();
			});
		});


		this.constructor.graphs.forEach(metricID => {
			const metric = this.metrics[metricID];
			if (!metric) return;

			this.getAPI('corewebvitalsmonitor/v1/metric/plot', {
				metric: metric.id,
				url: this.url
			}).then(value => {
				this.metrics[metricID].plot = value;
				this.requestUpdate();
			});
		});


		this.getAPI('corewebvitalsmonitor/v1/metric/count', {
			metric: 'TTFB',
			url: this.url
		}).then(count => { this.metrics.ttfb.count = count; this.requestUpdate() });
	}

	getRating(metricID) {
		const metric = this.metrics[metricID];

		if (!metric) {
			return {
				label: 'Unknown',
				slug: 'unknown',
			};
		}

		const value = metric.average;
		const thresholds = metric.thresholds;

		if (typeof value !== 'number' || value === 0) {
			return {
				label: 'Unknown',
				slug: 'unknown',
			};
		}

		if (value > thresholds[1]) {
			return {
				label: 'Poor',
				slug: 'poor',
			};
		}

		if (value > thresholds[0]) {
			return {
				label: 'Needs Improvement',
				slug: 'needs-improvement',
			};
		}

		return {
			label: 'Good',
			slug: 'good',
		};
	}

	formatScore(value) {
		if (typeof value !== 'number' || value === 0) {
			return '-';
		}
		else if (value < 10) {
			return Math.round(value * 100) / 100;
		}
		else if (value < 100) {
			return Math.round(value * 10) / 10;
		}
		else {
			return Math.round(value);
		}
	}

	render() {
		return html`
            <table class="table">
                ${repeat(this.constructor.rows, metricID => this.row(metricID))}
            </table>

			${this.metrics.ttfb.count === 0 ?
				this.alert("No data has been collected for this URL yet.", true) :

				this.metrics.ttfb.count < 300 && this.metrics.ttfb.count !== null ?
					this.alert("This data is based on under 300 visits.") :
					null
			}

            ${repeat(this.constructor.graphs, metricID => this.graph(metricID))}

			${this.url ? html`
			<a href="${CWVM.admin_url}" target="_blank" class="btn btn__primary">
				See sitewide core web vitals
			</a>` : null}

			<a href="https://web.dev/fast/" target="_blank" class="btn btn__secondary">
				How can I improve my scores?
			</a>

            <p class="info">
                Data displayed is collected from approx. ${this.metrics.ttfb.count} page loads across the last 28 days to ${this.url ? html`<a href="${this.url}" target="_blank">${this.url}</a>` : "all pages"}.
            </p>
        `;
	}

	row(metricID) {
		const metric = this.metrics[metricID];
		if (!metric) return;

		return html`
            <tr>
                <th>${metric.label}</th>

                <td>${this.score(metricID)}</td>
            </tr>
        `;
	}

	graph(metricID) {
		const metric = this.metrics[metricID];

		if (!metric) {
			return null;
		}

		return html`
            <figure class="graph-wrapper">
                <metric-graph class="graph" .metric="${metric}" .data="${metric.plot}"></metric-graph>

                <figcaption class="graph-caption">
                    <h2>${metric.label}</h2>

                    <p>${metric.description}</p>
                </figcaption>
            </figure>
        `;
	}

	score(metricID) {
		const metric = this.metrics[metricID];

		if (!metric) {
			return html`<span>-</span>`;
		}

		const rating = this.getRating(metricID);
		const score = this.formatScore(metric.average);

		return html`
            <span class="score ${rating.slug}" title="${rating.label}">
                ${score}
                <span class="unit">${metric.unit}</span>
            </span>
        `;
	}

	alert(text, danger) {
		return html`<div class="alert ${danger ? "danger" : "warning"}">${text}</div>`;
	}

	static styles = css`
        :host{
            --black-900: #191E23;
            --black-800: #23282D;
            --black-700: #32373C;
            --black-600: #82878C;
            --black-500: #A0A5AA;
            --black-400: #B4B9BE;
            --black-300: #cfd3d7;
            --black-200: #dce1e6;
            --black-100: #e7ebee;
            --black-50: #FFFFFF;

            --active-400: #00A0D2;
            --active-500: #007cba;
			--active-600: #006ba1;

            --warning-100: #FBC5A9;
            --warning-500: #F56E28;
            --warning-800: #BF461D;

            --success-100: #C7E8CA;
            --success-500: #46B450;
            --success-800: #31843F;

            --danger-100: #F1ADAD;
            --danger-500: #DC3232;
            --danger-800: #9A2323;

            display: block;

            font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
            font-size: 13px;
            line-height: 1.4em;
            font-weight: 400;
            color: var(--black-800);
        }

        *{
            box-sizing: border-box;
        }

        h2{
            font-size: 14px;
            font-weight: 500;
            margin: 0;
            margin-bottom: 0.5rem;
        }
        p{
            margin: 0;
        }
        a{
            color: var(--active-500);
			text-decoration: none;
        }
		a:hover,
		a:focus{
            color: var(--active-600);
        }

        table{
            border-collapse: collapse;
            width: 100%;

			margin-bottom: 2rem;
        }
        tr{
            padding: 0.5rem 0;
        }
        th{
            padding: 0.5rem 0;

            font-weight: 500;
            text-align: left;
            color: var(--black-900);
        }

        .score{
            color: var(--black-800);
            font-weight: 500;
			text-align: center;
			padding: 0.2rem 0.5rem;
			border-radius: 0.1rem;
			display: block;
			width: 100%;
			max-width: 10ch;
			margin-left: auto;
        }
		.score.unknown{
            color: var(--black-800);
			background: var(--black-300);
        }
        .score.good{
            color: var(--success-800);
			background: var(--success-100);
        }
        .score.needs-improvement{
            color: var(--warning-800);
			background: var(--warning-100);
        }
        .score.poor{
            color: var(--error-800);
			background: var(--error-100);
        }
        .unit{
            color: var(--black-900);
			opacity: 0.5;
            font-size: 0.8rem;
            font-weight: 400;
        }

        .graph-wrapper{
            display: flex;
            flex-direction: column-reverse;
            gap: 1rem;

            margin: 0;
            padding: 0;
        }
        .graph{
            width: 100%;
            height: 13rem;

            border: 1px solid var(--black-300);

			margin-bottom: 2rem;
        }

        .alert{
			height: 42px;
			padding: 0 0.75rem;
    		line-height: 42px;
			margin-bottom: 2rem;
			margin-top: -1rem;
			overflow: hidden;
            
            background: var(--warning-100);
            border-left: 3px solid var(--warning-500);
            color: var(--black-900);

			animation: alert-enter 600ms ease;
        }
        .alert.danger{
            background: var(--danger-100);
            border-left-color: var(--danger-500);
        }
		@keyframes alert-enter{
			from{
				height: 0px;
				margin-bottom: 1rem;
			}
		}

		.btn{
			display: flex;
			align-items: center;
			justify-content: center;
			text-decoration: none;
			font-family: inherit;
			font-weight: 400;
			font-size: 13px;
			margin: 0;
			border: 0;
			cursor: pointer;
			-webkit-appearance: none;
			background: none;
			transition: box-shadow .1s linear;
			height: 42px;
			align-items: center;
			box-sizing: border-box;
			padding: 10px 15px;
			border-radius: 2px;
			color: #1e1e1e;
			margin-bottom: 1rem;
		}
		.btn:focus{
			box-shadow: 0 0 0 2px var(--active-500);
			outline: 3px solid transparent;
		}
		.btn__primary{
			white-space: nowrap;
			background: var(--active-500);
			color: #fff;
			text-decoration: none;
			text-shadow: none;
			outline: 1px solid transparent;
		}
		.btn__primary:hover{
			background: var(--active-600);
			color: #fff;
		}
		.btn__secondary{
			white-space: nowrap;
			color: var(--active-500);
			background: transparent;
			box-shadow: inset 0 0 0 1px var(--active-500);
			outline: 1px solid transparent;
		}
		.btn__secondary:hover{
			color: var(--active-400);
			box-shadow: inset 0 0 0 1px var(--active-600);
		}

		.info{
			max-width: 100%;
			overflow-wrap: break-word;
  			word-wrap: break-word;
			word-break: break-all;
		}
    `;
}

customElements.define('core-web-vitals', CoreWebVitals);
