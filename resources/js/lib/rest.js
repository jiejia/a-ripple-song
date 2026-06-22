const METRIC_ROUTES = {
  view: 'aripplesong/v1/metrics/views',
  play: 'aripplesong/v1/metrics/plays',
};

export const METRIC_ACTIONS = {
  view: 'view',
  play: 'play',
};

let lastViewMetricKey = null;

/**
 * Build a WordPress REST API URL for both pretty and plain permalink structures.
 *
 * @param {string} route REST route without a leading slash.
 * @param {URLSearchParams|Record<string, string>|null} queryParams Optional query params.
 * @return {string}
 */
export function buildRestUrl(route, queryParams = null) {
  const restUrl = window.aripplesongData?.restUrl || '/wp-json/';
  const normalizedRoute = String(route).replace(/^\//, '');
  let url = `${restUrl}${normalizedRoute}`;

  if (!queryParams) {
    return url;
  }

  const params = queryParams instanceof URLSearchParams
    ? queryParams
    : new URLSearchParams(queryParams);
  const queryString = params.toString();

  if (!queryString) {
    return url;
  }

  return `${url}${url.includes('?') ? '&' : '?'}${queryString}`;
}

/**
 * Increment play count elements rendered in the current DOM.
 *
 * @param {number} postId Episode post ID.
 * @return {void}
 */
export function bumpPlayCountDom(postId) {
  if (!postId) {
    return;
  }

  document.querySelectorAll(`.js-play-count[data-post-id="${postId}"]`).forEach((element) => {
    const current = Number.parseInt(element.textContent, 10);
    const safe = Number.isNaN(current) ? 0 : current;
    element.textContent = String(safe + 1);
  });
}

/**
 * Resolve the primary post ID for view metrics on the current page.
 *
 * @return {number}
 */
export function resolvePrimaryPostId() {
  const ajaxPostId = window.aripplesongData?.ajax?.postId;
  if (ajaxPostId) {
    return ajaxPostId;
  }

  const ids = [...new Set(
    Array.from(document.querySelectorAll('.js-views-count[data-post-id]'))
      .map((element) => Number(element.dataset.postId))
      .filter(Boolean),
  )];

  return ids.length === 1 ? ids[0] : 0;
}

/**
 * Send a metric increment request to the theme REST API.
 *
 * @param {string} action Metric action key.
 * @param {number} postId Target post ID.
 * @return {Promise<object|null>}
 */
export async function sendMetric(action, postId) {
  if (!window.aripplesongData?.restUrl || !postId) {
    return null;
  }

  const route = METRIC_ROUTES[action];
  if (!route) {
    return null;
  }

  try {
    const response = await fetch(buildRestUrl(route), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: postId }),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    console.error(`[aripplesong] Failed to send metric "${action}"`, error);
    return null;
  }
}

/**
 * Send a page view metric once per post and URL combination.
 *
 * @return {Promise<object|null>}
 */
export function maybeSendViewMetric() {
  const postId = resolvePrimaryPostId();
  if (!postId) {
    return Promise.resolve(null);
  }

  const key = `${postId}:${window.location.href}`;
  if (lastViewMetricKey === key) {
    return Promise.resolve(null);
  }

  lastViewMetricKey = key;
  return sendMetric(METRIC_ACTIONS.view, postId);
}

/**
 * Fetch current metrics for a list of post IDs.
 *
 * @param {number[]} postIds Post IDs to query.
 * @return {Promise<Record<string, {views:number,plays:number|null}>|null>}
 */
export async function fetchMetrics(postIds = []) {
  if (!window.aripplesongData?.restUrl || !Array.isArray(postIds) || postIds.length === 0) {
    return null;
  }

  try {
    const params = new URLSearchParams();
    postIds.forEach((id) => params.append('post_ids[]', id));

    const response = await fetch(buildRestUrl('aripplesong/v1/metrics', params));
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const json = await response.json();
    return json?.counts || null;
  } catch (error) {
    console.error('[aripplesong] Failed to fetch metrics', error);
    return null;
  }
}

/**
 * Mark metrics hydration as complete for Alpine consumers.
 *
 * @return {void}
 */
export function markMetricsReady() {
  window.aripplesongMetricsReady = true;
  window.dispatchEvent(new CustomEvent('aripplesong:metrics:ready'));
}

/**
 * Hydrate view and play counters from the metrics REST API.
 *
 * @return {void}
 */
export function hydrateMetricsFromDom() {
  window.aripplesongMetricsReady = false;

  const viewElements = Array.from(document.querySelectorAll('.js-views-count'));
  if (!viewElements.length) {
    markMetricsReady();
    return;
  }

  const ids = [...new Set(
    viewElements.map((element) => Number(element.dataset.postId)).filter(Boolean),
  )];

  void fetchMetrics(ids)
    .then((counts) => {
      if (!counts) {
        return;
      }

      viewElements.forEach((element) => {
        const entry = counts[Number(element.dataset.postId)];
        if (entry && typeof entry.views === 'number') {
          element.textContent = String(entry.views);
        }
      });

      document.querySelectorAll('.js-play-count').forEach((element) => {
        const entry = counts[Number(element.dataset.postId)];
        if (entry && typeof entry.plays === 'number') {
          element.textContent = String(entry.plays);
        }
      });
    })
    .catch(() => null)
    .finally(markMetricsReady);
}
