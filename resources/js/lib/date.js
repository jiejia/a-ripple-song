import { DateTime } from 'luxon';

/**
 * Format a Luxon date using locale-aware absolute date patterns.
 *
 * @param {import('luxon').DateTime} date Luxon date instance.
 * @param {string} locale BCP-47 locale string.
 * @param {'short'|'long'} style Output style.
 * @return {string}
 */
function formatAbsoluteDate(date, locale, style = 'short') {
  const baseLocale = locale.split('-')[0];

  if (['zh', 'ja'].includes(baseLocale)) {
    return date.toFormat('yyyy年M月d日');
  }

  if (baseLocale === 'ko') {
    return date.toFormat('yyyy년 M월 d일');
  }

  return date.toLocaleString(style === 'long' ? DateTime.DATE_FULL : DateTime.DATE_MED);
}

/**
 * Format a Unix timestamp using WordPress locale settings.
 *
 * @param {number} timestamp Unix timestamp in seconds.
 * @param {'relative'|'short'|'long'} format Output format.
 * @return {string}
 */
export function formatLocalizedDate(timestamp, format = 'relative') {
  if (!timestamp || Number.isNaN(timestamp)) {
    return '-';
  }

  const wpLocale = document.documentElement.lang || 'en-US';
  const luxonLocale = wpLocale.replace('_', '-');
  const date = DateTime.fromSeconds(Number.parseInt(timestamp, 10)).setLocale(luxonLocale);

  if (format === 'relative') {
    const diffInDays = DateTime.now().diff(date, 'days').days;
    if (diffInDays < 7) {
      return date.toRelative() || formatAbsoluteDate(date, luxonLocale);
    }

    return formatAbsoluteDate(date, luxonLocale);
  }

  if (format === 'short' || format === 'long') {
    return formatAbsoluteDate(date, luxonLocale, format);
  }

  return formatAbsoluteDate(date, luxonLocale);
}

/**
 * Expose the date formatter on window for Blade/Alpine usage.
 *
 * @return {void}
 */
export function registerDateFormatter() {
  window.formatLocalizedDate = formatLocalizedDate;
}
