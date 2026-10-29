/**
 * Dashboard charts: draws every [data-ars-chart] element as an SVG.
 * data-ars-points holds [{label, title, value}]; label goes on the axis, title in the tooltip.
 * Charts redraw when their box resizes; arrow keys move the tooltip when a chart has focus.
 * data-ars-label is the chart's accessible name; the table view below each chart carries the values.
 */
(function () {
	'use strict';

	var SVG_NS = 'http://www.w3.org/2000/svg';
	var MARGIN = { top: 12, right: 12, bottom: 28 };
	var BAR_MAX = 24;
	var BAR_RADIUS = 4;

	var locale = document.documentElement.lang || undefined;
	var numberFormat = new Intl.NumberFormat(locale);
	var tickFormat = new Intl.NumberFormat(locale, { notation: 'compact', maximumFractionDigits: 1 });

	function svgElement(name, attributes, parent) {
		var element = document.createElementNS(SVG_NS, name);

		Object.keys(attributes || {}).forEach(function (key) {
			element.setAttribute(key, attributes[key]);
		});

		if (parent) {
			parent.appendChild(element);
		}

		return element;
	}

	// Round steps (1, 2, 5 × 10^n), integers only since every series counts things.
	function niceTicks(max, count) {
		if (max <= 0) {
			return [0, 1];
		}

		var raw = max / count;
		var power = Math.pow(10, Math.floor(Math.log10(raw)));
		var step = [1, 2, 5, 10].map(function (factor) {
			return factor * power;
		}).find(function (candidate) {
			return candidate >= raw;
		});

		step = Math.max(1, step);

		var ticks = [];

		for (var value = 0; value < max + step; value += step) {
			ticks.push(value);

			if (value >= max) {
				break;
			}
		}

		return ticks;
	}

	// Indexes of at most `count` evenly spaced axis labels, always including the last one.
	function labelIndexes(length, count) {
		if (length <= count) {
			return Array.from({ length: length }, function (_, index) {
				return index;
			});
		}

		var step = Math.ceil((length - 1) / (count - 1));
		var indexes = [];

		for (var index = length - 1; index >= 0; index -= step) {
			indexes.unshift(index);
		}

		return indexes;
	}

	// A column with a rounded top and a square base.
	function columnPath(x, y, width, height) {
		var radius = Math.min(BAR_RADIUS, width / 2, height);

		return [
			'M', x, y + height,
			'V', y + radius,
			'Q', x, y, x + radius, y,
			'H', x + width - radius,
			'Q', x + width, y, x + width, y + radius,
			'V', y + height,
			'Z'
		].join(' ');
	}

	function createTooltip(container, series) {
		var tooltip = document.createElement('div');
		var title = document.createElement('div');
		var row = document.createElement('div');
		var key = document.createElement('span');
		var value = document.createElement('span');
		var name = document.createElement('span');

		tooltip.className = 'ars-chart__tooltip';
		tooltip.hidden = true;
		title.className = 'ars-chart__tooltip-title';
		row.className = 'ars-chart__tooltip-row';
		key.className = 'ars-chart__tooltip-key';
		value.className = 'ars-chart__tooltip-value';
		name.className = 'ars-chart__tooltip-series';
		name.textContent = series;

		row.append(key, value, name);
		tooltip.append(title, row);
		container.appendChild(tooltip);

		return {
			show: function (point, x, y) {
				title.textContent = point.title;
				value.textContent = numberFormat.format(point.value);
				tooltip.hidden = false;

				// Keep the box inside the chart: flip to the left of the pointer near the right edge.
				var width = tooltip.offsetWidth;
				var left = x + 12 + width > container.clientWidth ? x - 12 - width : x + 12;

				tooltip.style.transform = 'translate(' + Math.max(0, left) + 'px, ' + Math.max(0, y - tooltip.offsetHeight - 8) + 'px)';
			},
			hide: function () {
				tooltip.hidden = true;
			}
		};
	}

	// Shared frame: gridlines, y ticks, x labels. Returns the plot geometry.
	function drawFrame(svg, points, width, height, bandPadding) {
		var max = Math.max.apply(null, points.map(function (point) {
			return point.value;
		}));
		var ticks = niceTicks(max, 4);
		var top = ticks[ticks.length - 1];
		var labels = ticks.map(function (tick) {
			return tickFormat.format(tick);
		});
		var longest = Math.max.apply(null, labels.map(function (label) {
			return label.length;
		}));
		var left = 8 + longest * 7;
		var plotWidth = Math.max(10, width - left - MARGIN.right);
		var plotHeight = height - MARGIN.top - MARGIN.bottom;
		var y = function (value) {
			return MARGIN.top + plotHeight - (value / top) * plotHeight;
		};
		var x;
		var band = 0;

		if (bandPadding) {
			band = plotWidth / points.length;
			x = function (index) {
				return left + band * index + band / 2;
			};
		} else {
			x = function (index) {
				return points.length === 1 ? left + plotWidth / 2 : left + (plotWidth / (points.length - 1)) * index;
			};
		}

		ticks.forEach(function (tick, index) {
			var tickY = Math.round(y(tick)) + 0.5;

			svgElement('line', {
				class: tick === 0 ? 'ars-chart__baseline' : 'ars-chart__grid',
				x1: left,
				x2: left + plotWidth,
				y1: tickY,
				y2: tickY
			}, svg);

			svgElement('text', {
				class: 'ars-chart__tick',
				x: left - 8,
				y: tickY,
				'text-anchor': 'end',
				'dominant-baseline': 'middle'
			}, svg).textContent = labels[index];
		});

		var maxLabels = Math.max(2, Math.floor(plotWidth / 64));

		labelIndexes(points.length, maxLabels).forEach(function (index, position, all) {
			var anchor = 'middle';

			// Line charts run edge to edge, so the outer labels hang inwards.
			if (!bandPadding && all.length > 1) {
				anchor = index === 0 ? 'start' : (index === points.length - 1 ? 'end' : 'middle');
			}

			svgElement('text', {
				class: 'ars-chart__tick',
				x: x(index),
				y: height - 8,
				'text-anchor': anchor
			}, svg).textContent = points[index].label;
		});

		return { left: left, plotWidth: plotWidth, plotHeight: plotHeight, x: x, y: y, band: band };
	}

	function drawArea(svg, points, width, height, tooltip, chart) {
		var frame = drawFrame(svg, points, width, height, false);
		var baseY = frame.y(0);
		var line = points.map(function (point, index) {
			return (index === 0 ? 'M' : 'L') + frame.x(index) + ' ' + frame.y(point.value);
		}).join(' ');

		svgElement('path', {
			class: 'ars-chart__area',
			d: line + ' L' + frame.x(points.length - 1) + ' ' + baseY + ' L' + frame.x(0) + ' ' + baseY + ' Z'
		}, svg);
		svgElement('path', { class: 'ars-chart__line', d: line }, svg);

		var crosshair = svgElement('line', {
			class: 'ars-chart__crosshair',
			y1: MARGIN.top,
			y2: baseY,
			visibility: 'hidden'
		}, svg);
		var dot = svgElement('circle', { class: 'ars-chart__dot', r: 4, visibility: 'hidden' }, svg);
		var overlay = svgElement('rect', {
			class: 'ars-chart__hit',
			x: frame.left,
			y: MARGIN.top,
			width: frame.plotWidth,
			height: frame.plotHeight
		}, svg);

		function select(index) {
			var x = frame.x(index);
			var y = frame.y(points[index].value);

			crosshair.setAttribute('x1', x);
			crosshair.setAttribute('x2', x);
			crosshair.setAttribute('visibility', 'visible');
			dot.setAttribute('cx', x);
			dot.setAttribute('cy', y);
			dot.setAttribute('visibility', 'visible');
			tooltip.show(points[index], x, y);
			chart.active = index;
		}

		function clear() {
			crosshair.setAttribute('visibility', 'hidden');
			dot.setAttribute('visibility', 'hidden');
			tooltip.hide();
		}

		overlay.addEventListener('pointermove', function (event) {
			var box = svg.getBoundingClientRect();
			var step = points.length > 1 ? frame.plotWidth / (points.length - 1) : 1;
			var index = Math.round((event.clientX - box.left - frame.left) / step);

			select(Math.min(points.length - 1, Math.max(0, index)));
		});
		overlay.addEventListener('pointerleave', clear);

		return { select: select, clear: clear };
	}

	function drawColumns(svg, points, width, height, tooltip, chart) {
		var frame = drawFrame(svg, points, width, height, true);
		var barWidth = Math.min(BAR_MAX, Math.max(4, frame.band * 0.6));
		var baseY = frame.y(0);
		var bars = [];

		points.forEach(function (point, index) {
			var barHeight = baseY - frame.y(point.value);
			var bar = svgElement('path', {
				class: 'ars-chart__bar',
				d: barHeight > 0 ? columnPath(frame.x(index) - barWidth / 2, frame.y(point.value), barWidth, barHeight) : ''
			}, svg);

			bars.push(bar);
		});

		// Hit areas span the whole band so short and empty columns are easy to point at.
		points.forEach(function (point, index) {
			var hit = svgElement('rect', {
				class: 'ars-chart__hit',
				x: frame.x(index) - frame.band / 2,
				y: MARGIN.top,
				width: frame.band,
				height: frame.plotHeight
			}, svg);

			hit.addEventListener('pointerenter', function () {
				select(index);
			});
			hit.addEventListener('pointerleave', clear);
		});

		function select(index) {
			bars.forEach(function (bar, barIndex) {
				bar.classList.toggle('is-active', barIndex === index);
			});
			chart.element.classList.add('ars-chart--hovering');
			tooltip.show(points[index], frame.x(index), frame.y(points[index].value));
			chart.active = index;
		}

		function clear() {
			chart.element.classList.remove('ars-chart--hovering');
			tooltip.hide();
		}

		return { select: select, clear: clear };
	}

	function render(chart) {
		var element = chart.element;
		var width = element.clientWidth;
		var height = element.clientHeight;

		if (width === chart.width) {
			return;
		}

		chart.width = width;
		element.textContent = '';

		var svg = svgElement('svg', {
			width: width,
			height: height,
			viewBox: '0 0 ' + width + ' ' + height,
			tabindex: 0,
			role: 'img',
			'aria-label': element.dataset.arsLabel || ''
		}, element);
		var tooltip = createTooltip(element, element.dataset.arsSeries || '');
		var draw = element.dataset.arsChart === 'column' ? drawColumns : drawArea;

		chart.active = null;
		chart.controls = draw(svg, chart.points, width, height, tooltip, chart);

		svg.addEventListener('keydown', function (event) {
			var last = chart.points.length - 1;
			var index = chart.active === null ? last : chart.active;

			if (event.key === 'ArrowLeft') {
				index = Math.max(0, index - 1);
			} else if (event.key === 'ArrowRight') {
				index = Math.min(last, index + 1);
			} else if (event.key === 'Home') {
				index = 0;
			} else if (event.key === 'End') {
				index = last;
			} else if (event.key === 'Escape') {
				chart.controls.clear();
				return;
			} else {
				return;
			}

			event.preventDefault();
			chart.controls.select(index);
		});
		svg.addEventListener('focus', function () {
			chart.controls.select(chart.active === null ? chart.points.length - 1 : chart.active);
		});
		svg.addEventListener('blur', function () {
			chart.controls.clear();
		});
	}

	function init() {
		var charts = [];

		document.querySelectorAll('[data-ars-chart]').forEach(function (element) {
			var points;

			try {
				points = JSON.parse(element.dataset.arsPoints || '[]');
			} catch (error) {
				points = [];
			}

			if (points.length) {
				charts.push({ element: element, points: points, width: 0, active: null, controls: null });
			}
		});

		charts.forEach(render);

		if ('ResizeObserver' in window) {
			var observer = new ResizeObserver(function () {
				charts.forEach(render);
			});

			charts.forEach(function (chart) {
				observer.observe(chart.element);
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
