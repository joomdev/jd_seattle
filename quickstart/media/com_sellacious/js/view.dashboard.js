/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
(function ($) {
	$(document).ready(function () {
		if ($.fn.sparkline) {
			$('.sparkline').each(function () {
				var $this = $(this);
				var sparklineType = $this.data('sparkline-type') || 'bar';

				// BAR CHART
				if (sparklineType == 'bar') {
					var barColor = $this.data('sparkline-bar-color') || $this.css('color') || '#0000f0',
						sparklineHeight = $this.data('sparkline-height') || '26px',
						sparklineBarWidth = $this.data('sparkline-barwidth') || 5,
						sparklineBarSpacing = $this.data('sparkline-barspacing') || 2,
						sparklineNegBarColor = $this.data('sparkline-negbar-color') || '#A90329',
						sparklineStackedColor = $this.data('sparkline-barstacked-color') ||
							["#A90329", "#0099c6", "#98AA56", "#da532c", "#4490B1", "#6E9461", "#990099", "#B4CAD3"];

					$this.sparkline('html', {
						barColor: barColor,
						type: sparklineType,
						height: sparklineHeight,
						barWidth: sparklineBarWidth,
						barSpacing: sparklineBarSpacing,
						stackedBarColor: sparklineStackedColor,
						negBarColor: sparklineNegBarColor,
						zeroAxis: 'false'
					});
				}

				//LINE CHART
				else if (sparklineType == 'line') {
					var sparklineHeight = $this.data('sparkline-height') || '20px',
						sparklineWidth = $this.data('sparkline-width') || '90px',
						thisLineColor = $this.data('sparkline-line-color') || $this.css('color') || '#0000f0',
						thisLineWidth = $this.data('sparkline-line-width') || 1, thisFill = $this.data('fill-color') || '#c0d0f0',
						thisSpotColor = $this.data('sparkline-spot-color') || '#f08000',
						thisMinSpotColor = $this.data('sparkline-minspot-color') || '#ed1c24',
						thisMaxSpotColor = $this.data('sparkline-maxspot-color') || '#f08000',
						thisHighlightSpotColor = $this.data('sparkline-highlightspot-color') || '#50f050',
						thisHighlightLineColor = $this.data('sparkline-highlightline-color') || 'f02020',
						thisSpotRadius = $this.data('sparkline-spotradius') || 1.5,
						thisChartMinYRange = $this.data('sparkline-min-y') || 'undefined',
						thisChartMaxYRange = $this.data('sparkline-max-y') || 'undefined',
						thisChartMinXRange = $this.data('sparkline-min-x') || 'undefined',
						thisChartMaxXRange = $this.data('sparkline-max-x') || 'undefined',
						thisMinNormValue = $this.data('min-val') || 'undefined',
						thisMaxNormValue = $this.data('max-val') || 'undefined',
						thisNormColor = $this.data('norm-color') || '#c0c0c0',
						thisDrawNormalOnTop = $this.data('draw-normal') || false;

					$this.sparkline('html', {
						type: 'line',
						width: sparklineWidth,
						height: sparklineHeight,
						lineWidth: thisLineWidth,
						lineColor: thisLineColor,
						fillColor: thisFill,
						spotColor: thisSpotColor,
						minSpotColor: thisMinSpotColor,
						maxSpotColor: thisMaxSpotColor,
						highlightSpotColor: thisHighlightSpotColor,
						highlightLineColor: thisHighlightLineColor,
						spotRadius: thisSpotRadius,
						chartRangeMin: thisChartMinYRange,
						chartRangeMax: thisChartMaxYRange,
						chartRangeMinX: thisChartMinXRange,
						chartRangeMaxX: thisChartMaxXRange,
						normalRangeMin: thisMinNormValue,
						normalRangeMax: thisMaxNormValue,
						normalRangeColor: thisNormColor,
						drawNormalOnTop: thisDrawNormalOnTop
					});
				}

				//PIE CHART
				else if (sparklineType == 'pie') {

					var pieColors = $this.data('sparkline-piecolor') ||
							["#B4CAD3", "#4490B1", "#98AA56", "#da532c", "#6E9461", "#0099c6", "#990099", "#717D8A"],
						pieWidthHeight = $this.data('sparkline-piesize') || 90,
						pieBorderColor = $this.data('border-color') || '#45494C',
						pieOffset = $this.data('sparkline-offset') || 0;

					$this.sparkline('html', {
						type: 'pie',
						width: pieWidthHeight,
						height: pieWidthHeight,
						tooltipFormat: '<span style="color: {{color}}">&#9679;</span> ({{percent.1}}%)',
						sliceColors: pieColors,
						borderWidth: 1,
						offset: pieOffset || 0,
						borderColor: pieBorderColor
					});

				}

				//BOX PLOT
				else if (sparklineType == 'box') {

					var thisBoxWidth = $this.data('sparkline-width') || 'auto', thisBoxHeight = $this.data('sparkline-height') || 'auto', thisBoxRaw = $this.data('sparkline-boxraw') || false, thisBoxTarget = $this.data('sparkline-targetval') || 'undefined', thisBoxMin = $this.data('sparkline-min') || 'undefined', thisBoxMax = $this.data('sparkline-max') || 'undefined', thisShowOutlier = $this.data('sparkline-showoutlier') || true, thisIQR = $this.data('sparkline-outlier-iqr') || 1.5, thisBoxSpotRadius = $this.data('sparkline-spotradius') || 1.5, thisBoxLineColor = $this.css('color') || '#000000', thisBoxFillColor = $this.data('fill-color') || '#c0d0f0', thisBoxWhisColor = $this.data('sparkline-whis-color') || '#000000', thisBoxOutlineColor = $this.data('sparkline-outline-color') || '#303030', thisBoxOutlineFill = $this.data('sparkline-outlinefill-color') || '#f0f0f0', thisBoxMedianColor = $this.data('sparkline-outlinemedian-color') || '#f00000', thisBoxTargetColor = $this.data('sparkline-outlinetarget-color') || '#40a020';

					$this.sparkline('html', {
						type: 'box',
						width: thisBoxWidth,
						height: thisBoxHeight,
						raw: thisBoxRaw,
						target: thisBoxTarget,
						minValue: thisBoxMin,
						maxValue: thisBoxMax,
						showOutliers: thisShowOutlier,
						outlierIQR: thisIQR,
						spotRadius: thisBoxSpotRadius,
						boxLineColor: thisBoxLineColor,
						boxFillColor: thisBoxFillColor,
						whiskerColor: thisBoxWhisColor,
						outlierLineColor: thisBoxOutlineColor,
						outlierFillColor: thisBoxOutlineFill,
						medianColor: thisBoxMedianColor,
						targetColor: thisBoxTargetColor

					})

				}

				//BULLET
				else if (sparklineType == 'bullet') {

					var thisBulletHeight = $this.data('sparkline-height') || 'auto',
						thisBulletWidth = $this.data('sparkline-width') || 2,
						thisBulletColor = $this.data('sparkline-bullet-color') || '#ed1c24',
						thisBulletPerformanceColor = $this.data('sparkline-performance-color') || '#3030f0',
						thisBulletRangeColors = $this.data('sparkline-bulletrange-color') || ["#d3dafe", "#a8b6ff", "#7f94ff"];

					$this.sparkline('html', {
						type: 'bullet',
						height: thisBulletHeight,
						targetWidth: thisBulletWidth,
						targetColor: thisBulletColor,
						performanceColor: thisBulletPerformanceColor,
						rangeColors: thisBulletRangeColors

					})

				}

				//DISCRETE
				else if (sparklineType == 'discrete') {

					var thisDiscreteHeight = $this.data('sparkline-height') || 26, thisDiscreteWidth = $this.data('sparkline-width') || 50, thisDiscreteLineColor = $this.css('color'), thisDiscreteLineHeight = $this.data('sparkline-line-height') || 5, thisDiscreteThrushold = $this.data('sparkline-threshold') || 'undefined', thisDiscreteThrusholdColor = $this.data('sparkline-threshold-color') || '#ed1c24';

					$this.sparkline('html', {

						type: 'discrete',
						width: thisDiscreteWidth,
						height: thisDiscreteHeight,
						lineColor: thisDiscreteLineColor,
						lineHeight: thisDiscreteLineHeight,
						thresholdValue: thisDiscreteThrushold,
						thresholdColor: thisDiscreteThrusholdColor

					})

				}

				//TRISTATE
				else if (sparklineType == 'tristate') {

					var thisTristateHeight = $this.data('sparkline-height') || 26, thisTristatePosBarColor = $this.data('sparkline-posbar-color') || '#60f060', thisTristateNegBarColor = $this.data('sparkline-negbar-color') || '#f04040', thisTristateZeroBarColor = $this.data('sparkline-zerobar-color') || '#909090', thisTristateBarWidth = $this.data('sparkline-barwidth') || 5, thisTristateBarSpacing = $this.data('sparkline-barspacing') || 2, thisZeroAxis = $this.data('sparkline-zeroaxis') || false;

					$this.sparkline('html', {

						type: 'tristate',
						height: thisTristateHeight,
						posBarColor: thisBarColor,
						negBarColor: thisTristateNegBarColor,
						zeroBarColor: thisTristateZeroBarColor,
						barWidth: thisTristateBarWidth,
						barSpacing: thisTristateBarSpacing,
						zeroAxis: thisZeroAxis

					})

				}

				//COMPOSITE: BAR
				else if (sparklineType == 'compositebar') {

					var sparklineHeight = $this.data('sparkline-height') || '20px',
						sparklineWidth = $this.data('sparkline-width') || '100%',
						sparklineBarWidth = $this.data('sparkline-barwidth') || 3,
						thisLineWidth = $this.data('sparkline-line-width') || 1,
						thisLineColor = $this.data('sparkline-color-top') || '#ed1c24',
						thisBarColor = $this.data('sparkline-color-bottom') || '#333333';

					$this.sparkline($this.data('sparkline-bar-val'), {

						type: 'bar',
						width: sparklineWidth,
						height: sparklineHeight,
						barColor: thisBarColor,
						barWidth: sparklineBarWidth
						//barSpacing: 5
					});

					$this.sparkline($this.data('sparkline-line-val'), {
						width: sparklineWidth,
						height: sparklineHeight,
						lineColor: thisLineColor,
						lineWidth: thisLineWidth,
						composite: true,
						fillColor: false

					})
				}

				//COMPOSITE: LINE
				else if (sparklineType == 'compositeline') {
					var sparklineHeight = $this.data('sparkline-height') || '20px',
						sparklineWidth = $this.data('sparkline-width') || '90px',
						sparklineValue = $this.data('sparkline-bar-val'),
						sparklineValueSpots1 = $this.data('sparkline-bar-val-spots-top') || null,
						sparklineValueSpots2 = $this.data('sparkline-bar-val-spots-bottom') || null,
						thisLineWidth1 = $this.data('sparkline-line-width-top') || 1,
						thisLineWidth2 = $this.data('sparkline-line-width-bottom') || 1,
						thisLineColor1 = $this.data('sparkline-color-top') || '#333333',
						thisLineColor2 = $this.data('sparkline-color-bottom') || '#ed1c24',
						thisSpotRadius1 = $this.data('sparkline-spotradius-top') || 1.5,
						thisSpotRadius2 = $this.data('sparkline-spotradius-bottom') || thisSpotRadius1,
						thisSpotColor = $this.data('sparkline-spot-color') || '#f08000',
						thisMinSpotColor1 = $this.data('sparkline-minspot-color-top') || '#ed1c24',
						thisMaxSpotColor1 = $this.data('sparkline-maxspot-color-top') || '#f08000',
						thisMinSpotColor2 = $this.data('sparkline-minspot-color-bottom') || thisMinSpotColor1,
						thisMaxSpotColor2 = $this.data('sparkline-maxspot-color-bottom') || thisMaxSpotColor1,
						thisHighlightSpotColor1 = $this.data('sparkline-highlightspot-color-top') || '#50f050',
						thisHighlightLineColor1 = $this.data('sparkline-highlightline-color-top') || '#f02020',
						thisHighlightSpotColor2 = $this.data('sparkline-highlightspot-color-bottom') || thisHighlightSpotColor1,
						thisHighlightLineColor2 = $this.data('sparkline-highlightline-color-bottom') || thisHighlightLineColor1,
						thisFillColor1 = $this.data('sparkline-fillcolor-top') || 'transparent',
						thisFillColor2 = $this.data('sparkline-fillcolor-bottom') || 'transparent';

					$this.sparkline(sparklineValue, {
						type: 'line',
						spotRadius: thisSpotRadius1,
						spotColor: thisSpotColor,
						minSpotColor: thisMinSpotColor1,
						maxSpotColor: thisMaxSpotColor1,
						highlightSpotColor: thisHighlightSpotColor1,
						highlightLineColor: thisHighlightLineColor1,
						valueSpots: sparklineValueSpots1,
						lineWidth: thisLineWidth1,
						width: sparklineWidth,
						height: sparklineHeight,
						lineColor: thisLineColor1,
						fillColor: thisFillColor1
					});

					$this.sparkline($this.data('sparkline-line-val'), {
						type: 'line',
						spotRadius: thisSpotRadius2,
						spotColor: thisSpotColor,
						minSpotColor: thisMinSpotColor2,
						maxSpotColor: thisMaxSpotColor2,
						highlightSpotColor: thisHighlightSpotColor2,
						highlightLineColor: thisHighlightLineColor2,
						valueSpots: sparklineValueSpots2,
						lineWidth: thisLineWidth2,
						width: sparklineWidth,
						height: sparklineHeight,
						lineColor: thisLineColor2,
						composite: true,
						fillColor: thisFillColor2
					})
				}
			});
		} else {
			$('.sparkline').hide();
			console.log('Missing Sparkline library!');
		}

		// Page scripts
		var tState = 0;
		var $sparks = $('.transaction-summary');
		$sparks.click(function () {
			$sparks.find('.sparks-info').addClass('hidden').filter('.span-' + tState + ',.visible').removeClass('hidden');
			tState = ++tState % 3;
		}).trigger('click');
	});
})(jQuery);

var SellaciousDashboard = function () {};

(function ($) {
	SellaciousDashboard.prototype = {
		init: function (params, chart, slider, data) {

			var $this = this;
			$this.options = {
				min: params.min,
				max: params.max,
				range: params.range,
				chart: chart,
				slider: slider,
				data: data
			};

			$this.min = $this.options.min * 1000;
			$this.max = $this.options.max * 1000;

			$this.range = {from: $this.min, to: $this.max};
			// $this.data = [[$this.min, 0], [$this.max, 0]];
			$this.data = $this.options.data;

			$this.plot = $.plot($($this.options.chart), [$this.data], $this.getChartOptions());

			var chartPlotter = $this.drawPartialChart.bind($this);

			$this.ionSlider = $($this.options.slider).ionRangeSlider({
				type: 'double',
				min: $this.min,
				max: $this.max + 1,
				from: $this.min,
				to: $this.max + 1,
				step: 1800,
				min_interval: 86400,
				force_edges: true,
				prettify_enabled: true,
				prettify: function (num) {
					return moment.utc(num).format($this.options.range == 'm' ? 'MMM Do, YYYY hh:mm A' : 'MMM Do, YYYY hh:mm A');
				},
				// onFinish: chartPlotter,
				onUpdate: chartPlotter,
				onChange: chartPlotter
			});

			$this.handleChartData($this.options.data);
		},

		handleChartData: function (data) {
			var $this = this;
			$this.data = data;

			$this.drawPartialChart($this.range);
		},

		getTimeStep: function (min, max) {
			var span = (max - min) / 1000;
			var step = [];

			if (span > 2 * 365 * 86400) {
				step = [4, 'month'];
			} else if (365 * 86400 < span && span <= 2 * 365 * 86400) {
				step = [1, 'month'];
			} else if (181 * 86400 < span && span <= 365 * 86400) {
				step = [14, 'day'];
			} else if (30 * 86400 < span && span <= 181 * 86400) {
				step = [7, 'day'];
			} else if (14 * 86400 < span && span <= 30 * 86400) {
				step = [2, 'day'];
			} else if (5 * 86400 < span && span <= 14 * 86400) {
				step = [1, 'day'];
			} else if (3 * 86400 < span && span <= 5 * 86400) {
				step = [12, 'hour'];
			} else if (86400 < span && span <= 3 * 86400) {
				step = [6, 'hour'];
			} else if (12 * 3600 < span && span <= 86400) {
				step = [2, 'hour'];
			} else if (6 * 3600 < span && span <= 86400 / 2) {
				step = [1, 'hour'];
			} else {
				step = [30, 'minute'];
			}

			return step;
		},

		getTicksYAxis: function (axis) {
			var $this = this;
			var t_res, t_lbl, m;

			m = 10;
			t_res = [[0, 0], [2 * m, 2], [4 * m, 4], [6 * m, 6], [8 * m, 8], [10 * m, 10]];
			t_lbl = 'Y x ' + (Number(m.toFixed(2)).toLocaleString());

			$($this.options.chart).closest('.chart-area').find('.axis-label-y').text(t_lbl);

			return t_res;
		},

		getTicksXAxis: function (axis) {
			var res = [];
			var $start = moment.utc(axis.min);
			var offset = +moment.utc().utcOffset() * 60000;

			var step = this.getTimeStep(axis.min, axis.max);
			var v_min = $start.subtract(step[0], step[1]).startOf(step[1]).valueOf();
			res.push([v_min]);

			do {
				$start.add(step[0], step[1]);
				var v = $start.valueOf() + offset;
				var l = $start.format(step[1] == 'hour' ? 'MMM DD[<br/>]hh:mm A' : 'MMM DD');
				res.push([v, l]);
			} while (v <= axis.max);

			return res;
		},

		getMarkingsXAxis: function (axes) {
			var $this = this;
			var markings = [];
			var step = $this.getTimeStep(axes.xaxis.min, axes.xaxis.max);

			var c_v = moment.utc(axes.xaxis.min);
			var b_v, e_v = c_v.subtract(step[0], step[1]).valueOf();

			for (; e_v <= axes.xaxis.max;) {
				b_v = c_v.add(step[0], step[1]).valueOf();
				e_v = c_v.add(step[0], step[1]).valueOf();
				markings.push({xaxis: {from: b_v, to: e_v}, colors: ["#ccffcc"]});
			}

			return markings;
		},

		getChartOptions: function () {
			var $this = this;
			return {
				xaxis: {
					mode: 'time',
					tickLength: 5,
					// timeformat: "%d<br/>%b",
					// tickSize: [1, 'day']
					ticks: $this.getTicksXAxis.bind($this)
				},
				yaxis: {
					mode: null,
					min: 0,
					tickDecimals: 0
					// ticks: $this.getTicksYAxis.bind($this)
				},
				series: {
					lines: {
						show: true,
						lineWidth: 1,
						fill: true,
						fillColor: {
							colors: [{
								opacity: 0.1
							}, {
								opacity: 0.55
							}]
						}
					},
					points: {show: true},
					bars: {show: false},
					shadowSize: 0
				},
				selection: {
					mode: 'x'
				},
				grid: {
					hoverable: true,
					clickable: true,
					tickColor: "#ddd",
					borderWidth: 1,
					borderColor: "#ddd",
					markings: $this.getMarkingsXAxis.bind($this)
				},
				tooltip: true,
				tooltipOpts: {
					content: "<span>%y</span> on <span>%x</span>",
					dateFormat: "%b %0d, %y %0h:%0M %P",
					defaultTheme: true
				},
				colors: ['#6595b4']
			}
		},

		drawPartialChart: function (obj) {
			var $this = this;

			// Update from and to
			$this.range = obj;
			var d = $.grep($this.data, function (v) {
				return ($this.range.from <= v[0] && v[0] <= $this.range.to);
			});

			var options = $this.plot.getOptions();
			options.tooltipOpts.content = "<span>%y</span> on <span>%x</span>";
			options.tooltipOpts.dateFormat = "%b %0d, %y";

			$this.plot.setData([d]);
			$this.plot.setupGrid();
			$this.plot.draw();
		}
	}
})(jQuery);
