/* -- DO NOT REMOVE --
 * jQuery DCalendar 1.1 and DCalendar Picker 1.3 plugin
 *
 * Author: Dionlee Uy
 * Email: dionleeuy@gmail.com
 *
 * Date: Sat Mar 2 2013
 *
 * @requires jQuery
 * -- DO NOT REMOVE --
 */
if (typeof jQuery === 'undefined') {
	throw new Error('DCalendar.Picker: This plugin requires jQuery');
}

+function ($) {

	Date.prototype.getDays = function () {
		return new Date(this.getFullYear(), this.getMonth() + 1, 0).getDate();
	};
	var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
		short_months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
		daysofweek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],

		DCalendar = function (elem, options) {
			this.calendar = $(elem);
			this.today = new Date();	//system date
			this.options = options;

			//current selected date, default is today if no value given
			if (this.calendar.prev().val() === '') {
				this.date = new Date();
			} else {
				var dateObj = this.reformatDate(this.calendar.prev().val());
				this.date = isNaN(parseInt(dateObj.m)) ? new Date(dateObj.m + " " + dateObj.d + ", " + dateObj.y) : new Date(dateObj.y, dateObj.m - 1, dateObj.d);
			}

			this.viewMode = 'days';
			this.selected = (this.date.getMonth() + 1) + "/" + this.date.getDate() + "/" + this.date.getFullYear();
			this.minDate = this.calendar.prev().data('mindate');
			this.maxDate = this.calendar.prev().data('maxdate');

			if (options.mode === 'calendar')
				this.tHead = $('<thead><tr><th id="prev">&lsaquo;</th><th colspan="5" id="currM"></th><th id="next">&rsaquo;</th></tr><tr><th>Su</th><th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th></tr></thead>');
			else if (options.mode === 'datepicker')
				this.tHead = $('<thead><tr><th id="prev">&lsaquo;</th><th colspan="5" id="currM"></th><th id="next">&rsaquo;</th></tr><tr><th>S</th><th>M</th><th>T</th><th>W</th><th>T</th><th>F</th><th>S</th></tr></thead>');
			this.tHead.find('#currM').text(months[this.today.getMonth()] + " " + this.today.getFullYear());
			this.calendar.prepend(this.tHead);
			var that = this;

			this.calendar.on('click', '#next', function () {
				initCreate('next');
			})
				.on('click', '#prev', function () {
					initCreate('prev');
				})
				.on('click', '#today', function () {
					that.viewMode = 'days';
					var curr = new Date(that.date),
						sys = new Date(that.today);
					if (curr.toString() != sys.toString()) {
						that.date = sys;
					}
					that.create('days');
				}).on('click', '.date, .pMDate, .nMDate', function () {
				var isPrev = $(this).hasClass('pMDate'),
					isNext = $(this).hasClass('nMDate'),
					sdate = $(this).text(),
					cmonth = that.date.getMonth(),
					cyear = that.date.getFullYear(),
					min = that.minDate === "today" ? new Date(that.today.getFullYear(), that.today.getMonth(), that.today.getDate()) : new Date(that.minDate),
					max = that.maxDate === "today" ? new Date(that.today.getFullYear(), that.today.getMonth(), that.today.getDate()) : new Date(that.maxDate);

				/* Calculate year */
				if (isPrev) {
					cyear = (cmonth === 0 ? cyear - 1 : cyear);
				}
				else if (isNext) {
					cyear = (cmonth + 2 === 13 ? cyear + 1 : cyear);
				}
				/* Calculate month */
				if (isPrev) {
					cmonth = (cmonth === 0 ? '12' : cmonth);
				}
				else if (isNext) {
					cmonth = (cmonth + 2 === 13 ? '1' : cmonth + 2);
				}
				else {
					cmonth = cmonth + 1;
				}

				// Selected date
				var selected = new Date(cyear, cmonth - 1, sdate);
				if ((that.minDate && selected < min) || (that.maxDate && selected > max)) return;

				that.selected = cmonth + '/' + sdate + '/' + cyear;

				if (that.options.mode === 'datepicker') {
					that.calendar.find('td').removeClass('selected');
					$(this).addClass('selected');
				}

				that.selectDate();
				return true;
			}).on('click', '#currM', function () {
				that.viewMode = 'months';
				that.create(that.viewMode);
			}).on('click', '.month', function (e) {
				that.viewMode = 'days';
				var curr = new Date(that.date), y = that.calendar.find('#currM').text();
				curr.setMonth($(e.currentTarget).attr('data-num'));
				that.date = curr;
				that.create(that.viewMode);
			});

			function initCreate(o) {
				var curr = new Date(that.date),
					currMonth = curr.getMonth(),
					currYear = curr.getFullYear();
				curr.setDate(1);
				if (that.viewMode === 'days') {
					curr.setMonth(currMonth + (o === 'next' ? 1 : -1));
				} else {
					curr.setFullYear(currYear + (o === 'next' ? 1 : -1));
				}
				that.date = curr;
				that.create(that.viewMode);
			}

			this.create(this.viewMode);
		};

	DCalendar.prototype = {

		constructor: DCalendar,

		setDate: function () {
			var that = this,
				dateObj = that.reformatDate(that.calendar.prev().val()),
				value = isNaN(parseInt(dateObj.m)) ? new Date(dateObj.m + " " + dateObj.d + ", " + dateObj.y) : new Date(dateObj.y, dateObj.m - 1, dateObj.d);

			that.selected = (value.getMonth() + 1) + "/" + value.getDate() + "/" + value.getFullYear();
			that.selectDate();
			that.date = value;
			that.create(that.viewMode);
		},

		selectDate: function () {
			var that = this,
				newDate = that.formatDate(that.options.format),
				e = $.Event('selectdate', {date: newDate});

			that.calendar.trigger(e);
		},

		reformatDate: function (date) {
			var that = this,
				format = that.options.format,
				dayLength = (format.match(/d/g) || []).length,
				monthLength = (format.match(/m/g) || []).length,
				yearLength = (format.match(/y/g) || []).length,
				isFullMonth = monthLength == 4,
				isMonthNoPadding = monthLength == 1,
				isDayNoPadding = dayLength == 1,
				lastIndex = date.length,
				firstM = format.indexOf('m'), firstD = format.indexOf('d'), firstY = format.indexOf('y'),
				month = '', day = '', year = '';

			// Get month on given date string using the format (default or specified)
			if (isFullMonth) {
				var monthIndex = -1;
				$.each(months, function (i, month) {
					if (date.indexOf(month) >= 0) monthIndex = i;
				});
				month = months[monthIndex];
				format = format.replace('mmmm', month);
				firstD = format.indexOf('d');
				firstY = format.indexOf('y');
			} else if (!isDayNoPadding && !isMonthNoPadding || (isDayNoPadding && !isMonthNoPadding && firstM < firstD)) {
				month = date.substr(firstM, monthLength);
			} else {
				var lastIndexM = format.lastIndexOf('m'),
					before = format.substring(firstM - 1, firstM),
					after = format.substring(lastIndexM + 1, lastIndexM + 2);

				if (lastIndexM == format.length - 1) {
					month = date.substring(date.indexOf(before, firstM - 1) + 1, lastIndex);
				} else if (firstM == 0) {
					month = date.substring(0, date.indexOf(after, firstM));
				} else {
					month = date.substring(date.indexOf(before, firstM - 1) + 1, date.indexOf(after, firstM + 1));
				}
			}

			// Get date on given date string using the format (default or specified)
			if (!isDayNoPadding && !isMonthNoPadding || (!isDayNoPadding && isMonthNoPadding && firstD < firstM)) {
				day = date.substr(firstD, dayLength);
			} else {
				var lastIndexD = format.lastIndexOf('d');
				before = format.substring(firstD - 1, firstD),
					after = format.substring(lastIndexD + 1, lastIndexD + 2);

				if (lastIndexD == format.length - 1) {
					day = date.substring(date.indexOf(before, firstD - 1) + 1, lastIndex);
				} else if (firstD == 0) {
					day = date.substring(0, date.indexOf(after, firstD));
				} else {
					day = date.substring(date.indexOf(before, firstD - 1) + 1, date.indexOf(after, firstD + 1));
				}
			}

			// Get year on given date string using the format (default or specified)
			if (!isMonthNoPadding && !isDayNoPadding || (isMonthNoPadding && isDayNoPadding && firstY < firstM && firstY < firstD)
				|| (!isMonthNoPadding && isDayNoPadding && firstY < firstD) || (isMonthNoPadding && !isDayNoPadding && firstY < firstM)) {
				year = date.substr(firstY, yearLength);
			} else {
				var before = format.substring(firstY - 1, firstY);
				year = date.substr(date.indexOf(before, firstY - 1) + 1, yearLength);
			}

			var today = new Date();

			return /\d{8}/.test(month + day + year) ? {m: month, d: day, y: year} : {m: today.getMonth() + 1, d: today.getDate(), y: today.getFullYear()};
		},

		formatDate: function (format) {
			var that = this;
			var d = new Date(that.selected), day = d.getDate(), m = d.getMonth(), y = d.getFullYear();

			return format.replace(/(yyyy|yy|mmmm|mmm|mm|m|dd|d)/gi, function (e) {
				switch (e.toLowerCase()) {
					case 'd':
						return day;
					case 'dd':
						return (day < 10 ? "0" + day : day);
					case 'm':
						return m + 1;
					case 'mm':
						return (m + 1 < 10 ? "0" + (m + 1) : (m + 1));
					case 'mmm':
						return short_months[m];
					case 'mmmm':
						return months[m];
					case 'yy':
						return y.toString().substr(2, 2);
					case 'yyyy':
						return y;
				}
			});
		},

		create: function (mode) {
			var sysDate;
			var that = this, cal = [],
				tBody = $('<tbody></tbody>'),
				d = new Date(that.date.getFullYear(), that.date.getMonth(), that.date.getDate()),
				days = that.date.getDays(),
				day = 1, nStartDate = 1,
				selDate = that.selected.split('/'),
				selected = new Date(selDate[2], selDate[0] - 1, selDate[1]),
				today = new Date(that.today.getFullYear(), that.today.getMonth(), that.today.getDate()),
				min = that.minDate === "today" ? today : new Date(that.minDate),
				max = that.maxDate === "today" ? today : new Date(that.maxDate);

			that.calendar.empty();
			if (mode === "days") {
				if (that.options.mode === 'calendar') {
					that.tHead = $('<thead><tr><th id="prev">&lsaquo;</th><th colspan="5" id="currM"></th><th id="next">&rsaquo;</th></tr><tr><th>Su</th><th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th></tr></thead>');
				} else if (that.options.mode === 'datepicker') {
					that.tHead = $('<thead><tr><th id="prev">&lsaquo;</th><th colspan="5" id="currM"></th><th id="next">&rsaquo;</th></tr><tr><th>S</th><th>M</th><th>T</th><th>W</th><th>T</th><th>F</th><th>S</th></tr></thead>');
				}
				that.tHead.find('#currM').text(months[that.date.getMonth()] + " " + that.date.getFullYear());
				that.calendar.append(that.tHead);

				for (var i = 1; i <= 6; i++) {
					var temp = [$('<td></td>'), $('<td></td>'), $('<td></td>'), $('<td></td>'), $('<td></td>'), $('<td></td>'), $('<td></td>')];

					while (day <= days) {
						d.setDate(day);
						var dayOfWeek = d.getDay();

						if (d.getTime() == today.getTime()) temp[dayOfWeek].attr('id', 'currDay');

						if ((that.minDate && d < min) || (that.maxDate && d > max)) temp[dayOfWeek].addClass('disabled');

						if (that.options.mode === 'datepicker' && d.getTime() == selected.getTime()) temp[dayOfWeek].addClass('selected');

						if (i === 1 && dayOfWeek === 0) break;
						else if (dayOfWeek < 6) temp[dayOfWeek].html('<span>' + (day++) + '</span>').addClass('date');
						else {
							temp[dayOfWeek].html('<span>' + (day++) + '</span>').addClass('date');
							break;
						}
					}
					/* For days of previous and next month */
					if (i === 1 || i > 4) {
						// First week
						if (i === 1) {
							var p = new Date(that.date.getFullYear(), that.date.getMonth() - 1, 1),
								pMonth = p.getMonth(), pDays = p.getDays();

							for (var a = 6; a >= 0; a--) {
								if (temp[a].text() === '') {

									p.setDate(pDays);

									temp[a].html('<span>' + (pDays--) + '</span>').addClass('pMDate');

									if ((that.minDate && p < min) || (that.maxDate && p > max)) temp[a].addClass('disabled');
									if (that.options.mode === 'datepicker' && p.getTime() == selected.getTime()) temp[a].addClass('selected');
								}
							}
						}
						// Last week
						else if (i > 4) {
							var nextMonth = new Date(that.date.getFullYear(), that.date.getMonth() + 1, 1);
							for (var a = 0; a <= 6; a++) {
								if (temp[a].text() === '') {

									nextMonth.setDate(nStartDate);

									temp[a].html('<span>' + (nStartDate++) + '</span>').addClass('nMDate');

									if ((that.minDate && nextMonth < min) || (that.maxDate && nextMonth > max)) temp[a].addClass('disabled');
									if (that.options.mode === 'datepicker' && nextMonth.getTime() == selected.getTime()) temp[a].addClass('selected');
								}
							}
						}
					}
					cal.push(temp);
				}

				$.each(cal, function (i, v) {
					var row = $('<tr></tr>'), l = v.length;
					for (var i = 0; i < l; i++) {
						row.append(v[i]);
					}
					tBody.append(row);
				});

				sysDate = "Today: " + daysofweek[that.today.getDay()] + ", " + months[that.today.getMonth()] + " " + that.today.getDate() + ", " + that.today.getFullYear();
				tBody.append('<tr><td colspan="7" id="today">' + sysDate + '</td></tr>').appendTo(that.calendar);
			} else {
				this.tHead = $('<thead><tr><th id="prev">&lsaquo;</th><th colspan="2" id="currM"></th><th id="next">&rsaquo;</th></tr>');
				that.tHead.find('#currM').text(that.date.getFullYear());
				that.tHead.appendTo(that.calendar);
				var currI = 0;
				for (var i = 0; i < 3; i++) {
					var row = $('<tr></tr>');
					for (var x = 0; x < 4; x++) {
						var col = $('<td align="center"></td>');
						var m = $('<span class="month" data-num="' + currI + '">' + short_months[currI] + '</span>');
						col.append(m).appendTo(row);
						currI++;
					}
					tBody.append(row);
				}
				sysDate = "Today: " + daysofweek[that.today.getDay()] + ", " + months[that.today.getMonth()] + " " + that.today.getDate() + ", " + that.today.getFullYear();
				tBody.append('<tr><td colspan="4" id="today">' + sysDate + '</td></tr>').appendTo(that.calendar);
			}
		}
	};

	/* DEFINITION FOR DCALENDAR */
	$.fn.dcalendar = function (opts) {
		return $(this).each(function (index, elem) {
			var that = this;
			var $this = $(that),
				data = $(that).data('dcalendar'),
				options = $.extend({}, $.fn.dcalendar.defaults, $this.data(), typeof opts === 'object' && opts);
			if (!data) {
				$this.data('dcalendar', (data = new DCalendar(this, options))).addClass('calendar');
			}
			if (typeof opts === 'string') data[opts]();
		});
	};

	$.fn.dcalendar.defaults = {
		mode: 'calendar',
		format: 'yyyy-mm-dd'
	};

	$.fn.dcalendar.Constructor = DCalendar;

	/* DEFINITION FOR DCALENDAR PICKER */
	$.fn.dcalendarpicker = function (opts) {
		return $(this).each(function () {
			var that = $(this),
				hovered = false, selectedDate = false,
				cal = null;

			if (typeof opts === 'string') {
				var data = that.next('.calendar').data('dcalendar');
				data[opts]();
			} else {
				cal = $('<table class="calendar"></table>');
				that.next('.btn').click(function () {
					console.log('btn clicked');
					cal.trigger('click');
				});
				that.wrap($('<div class="datepicker" style="display:inline-block;position:relative;"></div>'));
				cal.css({
					position: 'absolute',
					left: 0, display: 'none',
					'box-shadow': '0 4px 6px 1px rgba(0, 0, 0, 0.14)',
					width: '230px'
				}).appendTo(that.parent());
				if (opts) {
					opts.mode = 'datepicker';
					cal.dcalendar(opts);
				} else {
					cal.dcalendar({mode: 'datepicker'});
				}
				cal.hover(function () {
					hovered = true;
				}, function () {
					hovered = false;
				}).on('click', function () {
					if (!selectedDate)
						that.focus();
					else {
						selectedDate = false;
						$(this).hide();
					}
				}).on('selectdate', function (e) {
					that.val(e.date).trigger('onchange');
					that.trigger($.Event('dateselected', {date: e.date, elem: that}));
					selectedDate = true;
				});
				that.on('keydown', function (e) {
					if (e.which) return false;
				})
					.on('focus', function () {
						$('.datepicker').find('.calendar').not(cal).hide();
						cal.show();
					})
					.on('blur', function () {
						if (!hovered) cal.hide();
					});
			}
		});
	}

}(jQuery);
