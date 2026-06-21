export const EPISODE_REST_TYPE = 'aripplesong_episode';

export const PLAYBACK_RATES = [0.5, 0.75, 1, 1.25, 1.5, 2];

export const AUTOPLAY_COUNTDOWN_SECONDS = 10;

export const PLAYER_STORAGE_KEYS = {
  playlist: 'aripplesong-playlist',
  currentIndex: 'aripplesong-current-index',
  volume: 'aripplesong-volume',
  currentTime: 'aripplesong-current-time',
  isPlaying: 'aripplesong-is-playing',
  playbackRate: 'aripplesong-playback-rate',
};

export const ANALYZER_OPTIONS = {
  connectSpeakers: false,
  mode: 4,
  alphaBars: false,
  ansiBands: false,
  barSpace: 0.25,
  channelLayout: 'single',
  colorMode: 'bar-level',
  frequencyScale: 'log',
  gradient: 'prism',
  linearAmplitude: true,
  linearBoost: 1.6,
  maxFreq: 16000,
  minFreq: 30,
  reflexRatio: 0.5,
  reflexAlpha: 1,
  roundBars: true,
  showPeaks: false,
  showScaleX: false,
  smoothing: 0.7,
  weightingFilter: 'D',
  overlay: true,
  showBgColor: false,
  maxDecibels: -30,
};
