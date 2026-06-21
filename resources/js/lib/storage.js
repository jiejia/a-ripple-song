/**
 * Create an in-memory Storage-like fallback.
 *
 * @return {Storage}
 */
function createMemoryStorage() {
  const store = {};

  return {
    getItem(key) {
      return Object.prototype.hasOwnProperty.call(store, key) ? store[key] : null;
    },
    setItem(key, value) {
      store[key] = String(value);
    },
    removeItem(key) {
      delete store[key];
    },
    clear() {
      Object.keys(store).forEach((key) => delete store[key]);
    },
    key(index) {
      return Object.keys(store)[index] || null;
    },
    get length() {
      return Object.keys(store).length;
    },
  };
}

/**
 * Return a Storage implementation that falls back to memory when blocked.
 *
 * @param {'localStorage'|'sessionStorage'} type Storage type.
 * @return {Storage}
 */
export function createSafeStorage(type = 'localStorage') {
  if (typeof window === 'undefined') {
    return createMemoryStorage();
  }

  try {
    const storage = window[type];
    const testKey = '__aripplesong_storage_test__';
    storage.setItem(testKey, '1');
    storage.removeItem(testKey);
    return storage;
  } catch (error) {
    console.warn(`[aripplesong] ${type} is not accessible; falling back to memory storage.`, error);
    return createMemoryStorage();
  }
}

export const safeLocalStorage = createSafeStorage('localStorage');
